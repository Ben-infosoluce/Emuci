<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Dossier;
use App\Models\ModificationLog;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class Mt1Controller extends Controller
{
    //

    public function showMt1Dashboard()
    {
        return inertia('Minister/Mt1/Dashbord');
    }


    //
    public function showMt1List()
    {
        return inertia('Minister/Mt1/index');
    }

    public function getMt1Data(Request $request)
    {
        $permissions = getUserPermissions(); // ex: [1,2,4]

        $query = Dossier::with([
            'r_dossier_vehicule.r_vehicule_entreprise',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions',
        ])
            ->where('statut_paiement', 2)
            ->where('statut_numerisation', 2);

        /** 🔐 Filtre par permissions */
        if (!empty($permissions)) {
            $query->whereIn('id_service', $permissions);
        }

        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $search_data = $request->input('search_data');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        // 🔍 Recherche VIN / num_chrono
        if ($search_data) {
            $vehicules = Vehicule::where('vin', 'like', "%{$search_data}%")->pluck('id');

            $query->where(function ($q) use ($search_data, $vehicules) {
                $q->whereIn('id_vehicule', $vehicules)
                    ->orWhere('num_chrono', 'like', "%{$search_data}%");
            });
        }

        // 📌 Filtre statut
        if ($statut) {
            $query->where('statut', $statut);
        }

        // 📅 Filtre date
        if ($date_start && $date_end) {
            $query->whereBetween('date_creation', [$date_start, $date_end]);
        }

        $dossiers = $query->latest()->paginate($filtre_per_page);

        return response()->json([
            'dossiers' => $dossiers,
            'filtres' => $request->only(
                "filtre_per_page",
                "statut",
                "search_data",
                "date_start",
                "date_end"
            ),
        ]);
    }


    public function showMt1GetData($vin)
    {
        // 1️⃣ Récupération du dossier principal
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions',
        ])->where('num_chrono', $vin)->first();

        if (!$dossier) {
            abort(404, 'Dossier non trouvé');
        }

        // 2️⃣ Récupération du dossier lié si id_dossier_lier existe
        // $dossier_lier = null;
        // if (!empty($dossier->id_dossier_lier)) {
        //     $dossier_lier = Dossier::with([
        //         'r_dossier_vehicule',
        //         'r_dossier_user',
        //         'r_dossier_client',
        //         'r_dossier_documents',
        //         'r_dossier_services',
        //         'r_dossier_services.r_service_types',
        //         'r_dossier_transactions',
        //     ])->where('id', $dossier->id_dossier_lier)->first();
        // }

        // 3️⃣ Gestion du log de modification
        $log_id = $dossier->modification_log_id;
        $log = DB::table('modification_logs')->where('id', $log_id)->first();

        if ($log) {
            $old = json_decode($log->old_values, true);
            $new = json_decode($log->new_values, true);
        } else {
            $old = $new = [];
        }

        // 4️⃣ Retour Inertia
        return inertia('Minister/Mt1/components/createForm', [
            'chrono'        => $vin,
            'dossier'       => $dossier,
            // 'dossier_lier'  => $dossier_lier,
            'log'           => $log,
            'old'           => $old,
            'new'           => $new
        ]);
    }

    public function showModificationGetData($vin)
    {
        $dossiers = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->where('num_chrono', $vin)->first();

        if (!$dossiers) {
            abort(404, 'Dossier non trouvé');
        }

        return inertia('Minister/Mt1/components/Modification', [
            'chrono' => $vin,
            'dossier' => $dossiers,
        ]);
    }
    public function rejeterDossier(Request $request)
    {

        // dd($request->all());
        $dossierId = $request->input('dossier_id');
        // Vérifier si le dossier existe
        $dossier = Dossier::find($dossierId);
        if (!$dossier) {
            return response()->json(['message' => 'Dossier non trouvé'], 404);
        }
        // dd(getConnectedUserRole());

        // Vérifier si l'utilisateur a le droit de rejeter le dossier
        if (getConnectedUserRole() !== 'MT1') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }
        //si le dossier est déjà rejeté
        if ($dossier->statut == '3') {
            return response()->json(['message' => 'Dossier déjà rejeté'], 400);
        }
        // Mettre à jour le statut du dossier
        $dossier->statut = '3';
        $dossier->motif_rejet = $request->input('motif', 'Aucun motif fourni');
        $dossier->date_rejet = now();
        $dossier->id_user = getConnectedUserId();
        // Enregistrer le dossier
        $dossier->save();
        // Retourner une réponse JSON
        return response()->json(['message' => 'Dossier rejeté avec succès']);
    }



    //Pour valider post immatriculation(metre les valeurs dans les bonnes tables)
    private function validatePostImmatriculation($modificationLog)
    {
        $new = json_decode($modificationLog->new_values, true);

        // VEHICULE
        if (!empty($new['vehicule'])) {
            Vehicule::where('id', $modificationLog->model_id)
                ->update($new['vehicule']);
        }

        // CLIENT
        if (!empty($new['client'])) {
            $vehicule = Vehicule::find($modificationLog->model_id);
            if ($vehicule && $vehicule->r_vehicule_client) {
                $vehicule->r_vehicule_client->update($new['client']);
            }
        }

        // ENTREPRISE
        if (!empty($new['entreprise'])) {
            $vehicule = Vehicule::find($modificationLog->model_id);
            if ($vehicule && $vehicule->r_vehicule_entreprise) {
                $vehicule->r_vehicule_entreprise->update($new['entreprise']);
            }
        }

        return true;
    }


    //valider dossier
    public function validerDossier(Request $request)
    {
        $dossierId = $request->input('dossier_id');
        $dossier = Dossier::find($dossierId);

        if (!$dossier) {
            return response()->json(['message' => 'Dossier non trouvé'], 404);
        }

        // Vérification du rôle
        if (!in_array(getConnectedUserRole(), ['MT1', 'Gestionnaire'])) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        // Déjà validé
        if ($dossier->statut == '2') {
            return response()->json(['message' => 'Dossier déjà validé'], 400);
        }

        // Déjà rejeté
        if ($dossier->statut == '3') {
            return response()->json(['message' => 'Dossier rejeté, ne peut pas être validé'], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATION POST-IMMATRICULATION
        |--------------------------------------------------------------------------
        */
        if ($dossier->id_service == 3) {
            $log = ModificationLog::find($dossier->modification_log_id);

            if ($log) {
                $this->validatePostImmatriculation($log);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATION DU DOSSIER
        |--------------------------------------------------------------------------
        */
        $dossier->statut = '2'; // validé

        if ($dossier->id_service == 3 && $request->input('type_service') == true) {
            $dossier->status_pose_plaque = '2';
        } else {
            $dossier->status_pose_plaque = '1';
        }

        $dossier->date_validation = now();
        $dossier->id_user = getConnectedUserId();
        $dossier->save();

        return response()->json(['message' => 'Dossier validé avec succès']);
    }



    public function listDossiers($id_dossier)
    {
        // Charger tous les documents avec leurs dossiers associés
        // Récupère le dossier + ses documents
        $dossierPrincipal = Dossier::with(
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions',
        )->findOrFail($id_dossier);

        if (!$dossierPrincipal) {
            abort(404, 'Dossier non trouvé');
        }

        $dossierLier = null;
        // 🔵 2. Vérifier l'existence d'un dossier lié
        if (!empty($dossierPrincipal->id_dossier_lier)) {

            // Charger le dossier lié
            $dossierLier = Dossier::with([
                'r_dossier_vehicule',
                'r_dossier_user',
                'r_dossier_client',
                'r_dossier_documents',
                'r_dossier_services',
                'r_dossier_services.r_service_types',
                'r_dossier_transactions',
            ])
                ->where('id', $dossierPrincipal->id_dossier_lier)
                ->first();

            // 🔴 3. Si dossier lié → redirection vers selectDossier

        }
        return inertia('Minister/Mt1/selectDossier', [
            'dossier_lier' => $dossierLier,
            'dossier' => $dossierPrincipal,
            'documents' => $dossierPrincipal->r_dossier_documents
        ]);
        // return inertia('Numerisation/indexdocuments', [
        //     'dossier' => $dossierPrincipal,
        //     'documents' => $dossierPrincipal->r_dossier_documents
        // ]);
    }
}
