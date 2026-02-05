<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Dossier;
use App\Models\Vehicule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class GestionnaireController extends Controller
{
    //


    //

    public function showGestionnaireDashboard()
    {
        return inertia('Gestionnaire/Dashbord');
    }


    //
    public function showGestionnaireList()
    {
        return inertia('Gestionnaire/index');
    }

    public function getGestionnaireData(Request $request)
    {
        $query = Dossier::with([
            'r_dossier_vehicule.r_vehicule_entreprise',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions',
        ])->where('statut_paiement', 2)->where('statut_numerisation', 2);

        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $filtre_type = $request->input('filtre_type');
        $search_data = $request->input('search_data');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        // Filtre par VIN (via search_data) et num_chrono
        if ($search_data) {
            // Récupérer les IDs des véhicules qui correspondent au VIN
            $vehicules = Vehicule::where('vin', 'like', "%{$search_data}%")->pluck('id');

            // Appliquer le filtre sur les dossiers
            $query->where(function ($q) use ($search_data, $vehicules) {
                $q->whereIn('id_vehicule', $vehicules)
                    ->orWhere('num_chrono', 'like', "%{$search_data}%");
            });
        }

        // Filtre par statut
        if ($statut) {
            $query->where("statut", $statut);
        }

        // Filtre par date de création (created_at)
        if ($date_start && $date_end) {
            // On s'assure que ce sont des dates valides
            try {
                // $start = Carbon::parse($date_start)->startOfDay();
                //  $end = Carbon::parse($date_end)->endOfDay();
                $start = $date_start;
                $end = $date_end;

                $query->whereBetween('date_creation', [$start, $end]);
            } catch (\Exception $e) {
                // Optionnel : log ou ignorer si erreur de date
            }
        }

        $dossiers = $query->latest()->paginate($filtre_per_page);

        return response()->json([
            'dossiers' => $dossiers,
            'filtres' => $request->only(
                "filtre_per_page",
                "statut",
                "search_data",
                "filtre_type",
                "date_start",
                "date_end"
            ),
        ]);
    }

    public function showGestionnaireGetData($vin)
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
        return inertia('Gestionnaire/components/createForm', [
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

        return inertia('Gestionnaire/components/Modification', [
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
    //valider dossier
    public function validerDossier(Request $request)
    {
        // Logique pour valider le dossier
        $dossierId = $request->input('dossier_id');
        $dossier = Dossier::find($dossierId);

        if (!$dossier) {
            return response()->json(['message' => 'Dossier non trouvé'], 404);
        }

        // Vérifier si l'utilisateur a le droit de valider le dossier
        if (getConnectedUserRole() !== 'MT1') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        // Si le dossier est déjà validé
        if ($dossier->statut == '2') {
            return response()->json(['message' => 'Dossier déjà validé'], 400);
        }
        // Si le dossier est rejeté
        if ($dossier->statut == '3') {
            return response()->json(['message' => 'Dossier rejeté, ne peut pas être validé'], 400);
        }
        // Vous pouvez implémenter la logique de validation ici
        $dossier->statut = '2'; // Mettre à jour le statut à "validé"
        $dossier->date_validation = now();
        $dossier->id_user = getConnectedUserId(); // Mettre à jour l'ID de l'utilisateur qui valide

        // Enregistrer le dossier
        $dossier->save();
        // Par exemple, changer le statut du dossier, etc.
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
        return inertia('Gestionnaire/selectDossier', [
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
