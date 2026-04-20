<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\CaisseOuverture;
use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Service;
use App\Models\Vehicule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\ControlleurCaisse;

class CaisseController extends Controller
{
    //
    public function showCaisseDashboard()
    {
        $currentDateTime = now();
        return inertia('Caisse/Dashbord', ['currentDateTime' => $currentDateTime]);
    }

    public function showCaisseData()
    {
        return inertia('Caisse/index');
    }

    public function showNewCaisse()
    {
        return inertia('Caisse/new');
    }

    public function showCaisseGetData($vin)
    {
        // Charger le dossier principal
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->where('num_chrono', $vin)->first();

        if (!$dossier) {
            abort(404, 'Dossier non trouvé');
        }

        // Charger le dossier lié s'il existe
        $dossier_lier = null;
        if (!empty($dossier->id_dossier_lier)) {
            $dossier_lier = Dossier::with([
                'r_dossier_vehicule',
                'r_dossier_user',
                'r_dossier_client',
                'r_dossier_services',
                'r_dossier_services.r_service_types'
            ])->find($dossier->id_dossier_lier);
        }
        // dd($dossier_lier);

        /*
         |--------------------------------------------------------------------------
         | DetailTypeServices du dossier principal
         |--------------------------------------------------------------------------
         */
        $details = json_decode($dossier->detail); // tableau de noms de services
        // dd($details);
        // $ids = DB::table('type_services')
        //     ->whereIn('id', $details)
        //     ->pluck('id');
        // dd($dossier);
        // if ($dossier->id_dossier_lier == null && $dossier->id_service == 4) {
        // dd('laba');
        if ($dossier->type == 'FDS') {
            $detailTypeServices = DB::table('detail_type_services')
                ->whereIn('id', $details)
                // ->where('id_site', getIdSite())
                ->get();
        }
        else {
            $detailTypeServices = DB::table('detail_type_services')
                ->whereIn('id', $details)
                ->where('id_site', getIdSite())
                ->get();
        }
        // dd($detailTypeServices);

        // dd($detailTypeServices);
        // } else {
        //     // dd('ici');
        //     $detailTypeServices = DB::table('detail_type_services')
        //         ->whereIn('id_type_services', $ids)
        //         ->where('id_site', getIdSite())
        //         ->get();
        // }
        /*
         |--------------------------------------------------------------------------
         | DetailTypeServices du dossier lié (si existe)
         |--------------------------------------------------------------------------
         */
        $detailTypeServices_lier = [];

        if ($dossier_lier) {
            $details_lier = json_decode($dossier_lier->detail);
            // dd($details_lier);

            $ids_lier = DB::table('type_services')
                ->whereIn('id', $details_lier)
                ->pluck('id');

            $detailTypeServices_lier = DB::table('detail_type_services')
                ->whereIn('id', $details_lier)
                ->where('id_site', getIdSite())
                ->get();
        }

        // récupération du genre et du nombre de plaques (pour choisir la facturation correcte)
        $nb_plaque = $dossier->r_dossier_vehicule->nb_plaque;
        $genre = $dossier->r_dossier_vehicule->genre_vehicule;

        // dd($genre, $nb_plaque);
        $autre_facturation = null;
        // dd($dossier);
        if ($dossier->id_service != '4') {
            if (stripos($genre, "REMORQUE") !== false) {
                $autre_facturation = DB::table('autre_facturation')
                    ->where('id', 3) // ID pour "REMORQUE"
                    ->where('status', 1)
                    ->first();
            }
            else if ($nb_plaque == 1) {
                $autre_facturation = DB::table('autre_facturation')
                    ->where('id', 2) // ID pour "1 plaque et non REMORQUE"
                    ->where('status', 1)
                    ->first();
            }
            else {
                $autre_facturation = DB::table('autre_facturation')
                    ->where('id', 1) // ID par défaut
                    ->where('status', 1)
                    ->first();
            }
        }



        return inertia('Caisse/components/createForm', [
            'chrono' => $vin,
            'dossier' => $dossier,
            'dossier_lier' => $dossier_lier,
            'detailTypeServices' => $detailTypeServices,
            'detailTypeServices_lier' => $detailTypeServices_lier,
            'autre_facturation' => $autre_facturation,
            // nb_plaque n'est plus nécessaire côté front-end
        ]);
    }


    public function showCaisseModificationGetData($vin)
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

        return inertia('Caisse/components/Modification', [
            'chrono' => $vin,
            'dossier' => $dossiers,
        ]);
    }

    public function getCaisseData(Request $request)
    {
        // Récupère les IDs des services accessibles pour l'utilisateur connecté
        $serviceIds = servicesAccessibles()->toArray(); // ou ->all() si c'est une collection
        $permissions = getUserPermissions(); // ex: [1,2,4]
        // dd(in_array(18, $permissions));
        $userSiteId = getIdSite();
        if (in_array(18, $permissions)) {
            $query = Dossier::with([
                'r_dossier_vehicule',
                'r_dossier_user',
                'r_dossier_client',
                'r_dossier_documents',
                'r_dossier_services',
                'r_dossier_services.r_service_types',
                'r_dossier_transactions',
            ])->where('type', 'FDS')
                // Ce bloc gère l'exclusion : soit MON site, soit le site 0
                ->where(function ($query) use ($userSiteId) {
                $query->where('id_site', $userSiteId)
                    ->orWhere('id_site', 0);
            });
        }
        else {
            $query = Dossier::with([
                'r_dossier_vehicule',
                'r_dossier_user',
                'r_dossier_client',
                'r_dossier_documents',
                'r_dossier_services',
                'r_dossier_services.r_service_types',
                'r_dossier_transactions'
            ])->where('type', null);
        }


        // Filtre par les services accessibles
        if (!empty($serviceIds)) {
            $query->whereHas('r_dossier_services', function ($q) use ($serviceIds) {
                $q->whereIn('id_service', $serviceIds);
            });
        }

        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $filtre_type = $request->input('filtre_type');
        $search_data = $request->input('search_data');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        // Filtre par VIN (via search_data) et num_chrono
        if ($search_data) {
            $vehicules = Vehicule::where('vin', 'like', "%{$search_data}%")->pluck('id');
            $query->where(function ($q) use ($search_data, $vehicules) {
                $q->whereIn('id_vehicule', $vehicules)
                    ->orWhere('num_chrono', 'like', "%{$search_data}%");
            });
        }

        // Filtre par statut
        if ($statut) {
            $query->where("statut", $statut);
        }

        // Filtre par date de création (date_creation)
        if ($date_start && $date_end) {
            try {
                $start = Carbon::parse($date_start)->startOfDay();
                $end = Carbon::parse($date_end)->endOfDay();
                $query->whereBetween('date_paiement', [$start, $end]);
            }
            catch (\Exception $e) {
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
        )
        ]);
    }



    // *************************Gestion ouverture & Fermeture de la caisse *************************//

    public function open(Request $request)
    {
        $request->validate([
            'fond_de_caisse' => 'required|numeric|min:0',
        ], [
            'fond_de_caisse.required' => 'Le montant d’ouverture est requis.',
            'fond_de_caisse.numeric' => 'Le montant d’ouverture doit être un nombre.',
            'fond_de_caisse.min' => 'Le montant d’ouverture ne peut pas être négatif.',
        ]);

        $user = Auth::user();
        $today = Carbon::now()->toDateString();
        // 🔹 Récupère la caisse liée au site de l'utilisateur
        $caisse = Caisse::where('site_id', $user->id_site)->first();
        // $controleurAlreadyOpen = ControlleurCaisse::where('caisse_id', $caisse->id)

        if (!$caisse) {
            return response()->json([
                'message' => 'Aucune caisse trouvée pour ce site.',
            ], 404);
        }

        // Cherche une ouverture non fermée aujourd'hui
        $controleurAlreadyOpen = ControlleurCaisse::where('caisse_id', $caisse->id)
            ->whereDate('date_ouverture_controlleur', $today)
            ->whereNull('date_fermeture_controlleur')
            ->first();

        // Si aucun enregistrement ou date_ouverture_controlleur est null → le contrôleur n'a pas encore ouvert
        if (!$controleurAlreadyOpen || is_null($controleurAlreadyOpen->date_ouverture_controlleur)) {
            return response()->json([
                'message' => "Le contrôleur n'a pas encore ouvert la caisse.",
            ], 403);
        }

        // 🔹 Vérifie si une caisse est déjà ouverte
        $alreadyOpen = CaisseOuverture::where('caisse_id', $caisse->id)
            ->where('statut', 'ouvert')
            ->exists();

        if ($alreadyOpen) {
            return response()->json([
                'message' => 'Cette caisse est déjà ouverte.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $ouverture = CaisseOuverture::create([
                'user_id' => $user->id,
                'caisse_id' => $caisse->id,
                'date_ouverture' => now(),
                'montant_ouverture' => $request->fond_de_caisse,
                'statut' => 'ouvert',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Caisse ouverte avec succès.',
                'data' => $ouverture,
            ], 201);
        }
        catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erreur lors de l’ouverture de la caisse.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function close(Request $request, $id)
    {
        // dd($id);

        $validated = $request->validate([
            'montant_fermeture' => 'required|numeric|min:0',
            // 'observations' => 'nullable|string',
            'montant_saisie_caisse' => 'required|numeric|min:0',
            'billetterie' => 'nullable|array',
        ]);

        $user = Auth::user();


        $ouverture = CaisseOuverture::where('caisse_id', $id)
            ->where('statut', 'ouvert')
            ->where('user_id', $user->id)
            ->firstOrFail();

        // dd($ouverture);
        $ouverture->update([
            'date_fermeture' => now(),
            'montant_fermeture' => $request->montant_fermeture,
            'montant_saisie_caisse' => $request->montant_saisie_caisse,
            'billetterie' => json_encode($request->billetterie),
            'statut' => 'fermé',
            // 'observations' => $request->observations,
        ]);

        return response()->json(['message' => 'Caisse fermée.']);
    }

    public function statut()
    {
        $userSiteId = getIdSite();
        $caisse = Caisse::where('site_id', $userSiteId)->first();

        if (!$caisse) {
            return response()->json([
                'statut' => 'aucune caisse définie pour ce site',
            ]);
        }

        $ouverture = $caisse->ouvertures()
            ->where('statut', 'ouvert')
            ->latest()
            ->with('user')
            ->first();

        if ($ouverture) {
            return response()->json([
                'statut' => 'ouverte',
                'ouvert_par' => $ouverture->user->nom,
                'date_ouverture' => $ouverture->date_ouverture,
                'caisse_id' => $ouverture->caisse_id,
            ]);
        }
        else {
            return response()->json([
                'statut' => 'fermée',
            ]);
        }
    }

    public function showBossValidationsStatistics()
    {
        $sites = DB::table('sites')->select('id', 'nom_site')->get();
        return inertia('Boss/HistoriqueValidations', [
            'sites' => $sites
        ]);
    }

    public function getValidationsHistory(Request $request)
    {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $status = $request->input('status'); // 'tout', 'perte', 'surplus', 'equilibre'
        $siteId = $request->input('site_id');

        $query = DB::table('caisse_ouvertures')
            ->join('caisses', 'caisse_ouvertures.caisse_id', '=', 'caisses.id')
            // ->join('users', 'caisse_ouvertures.user_id', '=', 'users.id')
            //ajouter les dossier qui on caisse id = caisse_ouvertures.caisse_id et date_paiement = date_ouverture
            ->select(
            'caisse_ouvertures.*',
            'caisses.libelle as nom_caisse',
            // 'users.nom as nom_caissier',
            DB::raw('(SELECT COUNT(DISTINCT dossiers.id) FROM paiements 
                          JOIN dossiers ON paiements.id_dossier = dossiers.id
                          WHERE paiements.caisse_id = caisse_ouvertures.caisse_id 
                          AND DATE(dossiers.date_paiement) = DATE(caisse_ouvertures.date_ouverture)) as nb_dossiers'),
            DB::raw('(SELECT SUM(paiements.montant) FROM paiements 
                          JOIN dossiers ON paiements.id_dossier = dossiers.id
                          WHERE paiements.caisse_id = caisse_ouvertures.caisse_id 
                          AND DATE(dossiers.date_paiement) = DATE(paiements.created_at)) as total_ventes')
        );
        // dd($query->get());

        // Filtre par site
        if ($siteId && $siteId !== 'tout') {
            $query->where('caisses.site_id', $siteId);
        }

        // Filtre par date
        if ($dateStart && $dateEnd) {
            $query->whereBetween('caisse_ouvertures.date_fermeture', [
                Carbon::parse($dateStart)->startOfDay(),
                Carbon::parse($dateEnd)->endOfDay()
            ]);
        }

        // Filtre par statut
        if ($status === 'perte') {
            $query->where('caisse_ouvertures.perte', '>', 0);
        }
        elseif ($status === 'surplus') {
            $query->where('caisse_ouvertures.surplus', '>', 0);
        }
        elseif ($status === 'equilibre') {
            $query->where(function ($q) {
                $q->where('caisse_ouvertures.perte', 0)->orWhereNull('caisse_ouvertures.perte');
            })->where(function ($q) {
                $q->where('caisse_ouvertures.surplus', 0)->orWhereNull('caisse_ouvertures.surplus');
            });
        }

        $history = $query->orderBy('caisse_ouvertures.date_fermeture', 'desc')
            ->paginate(15);

        return response()->json($history);
    }

    public function showControllerValidationsStatistics()
    {
        return inertia('ControlleurCaisse/HistoriqueValidations');
    }

    public function getControllerValidationsHistory(Request $request)
    {
        $user = Auth::user();
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $status = $request->input('status');

        $query = DB::table('caisse_ouvertures')
            ->join('caisses', 'caisse_ouvertures.caisse_id', '=', 'caisses.id')
            ->join('users', 'caisse_ouvertures.user_id', '=', 'users.id')
            ->select(
            'caisse_ouvertures.*',
            'caisses.libelle as nom_caisse',
            'users.nom as nom_caissier',
            DB::raw('(SELECT COUNT(DISTINCT dossiers.id) FROM paiements 
                          JOIN dossiers ON paiements.id_dossier = dossiers.id
                          WHERE paiements.caisse_id = caisse_ouvertures.caisse_id 
                          AND DATE(dossiers.date_paiement) = DATE(caisse_ouvertures.date_ouverture)) as nb_dossiers'),
            DB::raw('(SELECT SUM(paiements.montant) FROM paiements 
                          JOIN dossiers ON paiements.id_dossier = dossiers.id
                          WHERE paiements.caisse_id = caisse_ouvertures.caisse_id 
                          AND DATE(dossiers.date_paiement) = DATE(caisse_ouvertures.date_ouverture)) as total_ventes')
        )
            ->where('caisses.site_id', $user->id_site); // Filtre par site du contrôleur

        // Filtre par date
        if ($dateStart && $dateEnd) {
            $query->whereBetween('caisse_ouvertures.date_fermeture', [
                Carbon::parse($dateStart)->startOfDay(),
                Carbon::parse($dateEnd)->endOfDay()
            ]);
        }

        // Filtre par statut (perte, surplus, equilibre)
        if ($status === 'perte') {
            $query->where('caisse_ouvertures.perte', '>', 0);
        }
        elseif ($status === 'surplus') {
            $query->where('caisse_ouvertures.surplus', '>', 0);
        }
        elseif ($status === 'equilibre') {
            $query->where(function ($q) {
                $q->where('caisse_ouvertures.perte', 0)->orWhereNull('caisse_ouvertures.perte');
            })->where(function ($q) {
                $q->where('caisse_ouvertures.surplus', 0)->orWhereNull('caisse_ouvertures.surplus');
            });
        }

        $history = $query->orderBy('caisse_ouvertures.date_fermeture', 'desc')
            ->paginate(15);

        return response()->json($history);
    }

    public function showRafValidationsStatistics()
    {
        $sites = DB::table('sites')->select('id', 'nom_site')->get();
        return inertia('Raf/HistoriqueValidations', [
            'sites' => $sites
        ]);
    }

    public function getRafValidationsHistory(Request $request)
    {
        // Le RAF voit tout comme le Boss
        return $this->getValidationsHistory($request);
    }
}