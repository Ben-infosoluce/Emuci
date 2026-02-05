<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caisse;
use App\Models\CaisseOuverture;
use App\Models\ControlleurCaisse;
use App\Models\Dossier;
use App\Models\Service;
use App\Models\Vehicule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ControleCaisseController extends Controller
{
    //
    public function showCaisseControllerDashboard()
    {
        return inertia('ControlleurCaisse/Dashbord');
    }



    public function getStatsCaisseController()
    {
        // Forcer la langue française pour les noms de mois
        DB::statement("SET lc_time_names = 'fr_FR'");

        $results = DB::table('dossiers')
            ->selectRaw('
            DATE_FORMAT(STR_TO_DATE(date_creation, "%Y-%m-%d"), "%M") AS name,
            COUNT(*) as Total,
            COUNT(CASE WHEN statut = 2 THEN 1 END) AS `Terminer`,
            COUNT(CASE WHEN statut = 1 THEN 1 END) AS `En attente`,
            COUNT(CASE WHEN statut = 3 THEN 1 END) AS `Rejeter`,
            COUNT(CASE WHEN statut = 4 THEN 1 END) AS `En cours de traitement`,
            COUNT(CASE WHEN id_service = 1 THEN 1 END) AS `Immatriculation-Special`,
            COUNT(CASE WHEN id_service = 2 THEN 1 END) AS `Re-immatriculation`,
            COUNT(CASE WHEN id_service = 3 THEN 1 END) AS `Post-immatriculation`,
            COUNT(CASE WHEN id_service = 4 THEN 1 END) AS `Duplicata`
        ')
            ->whereNotNull('date_creation')
            ->groupBy('name')
            ->orderByRaw("STR_TO_DATE(CONCAT('2025-', name, '-01'), '%Y-%M-%d')")
            ->get();

        return response()->json($results);
    }


    public function getStatsByDate(Request $request)
    {

        $start = $request->query('start_date');
        $end = $request->query('end_date');

        if (!$start || !$end) {
            return response()->json(['error' => 'start_date et end_date sont requis'], 422);
        }

        // Forcer la langue française pour les noms de mois
        DB::statement("SET lc_time_names = 'fr_FR'");

        $results = DB::table('dossiers')
            ->selectRaw('
            DATE_FORMAT(STR_TO_DATE(date_creation, "%Y-%m-%d"), "%M") AS name,
            COUNT(*) as Total,
            COUNT(CASE WHEN statut = 2 THEN 1 END) AS `Terminer`,
            COUNT(CASE WHEN statut = 1 THEN 1 END) AS `En attente`,
            COUNT(CASE WHEN id_service = 1 THEN 1 END) AS `Immatriculation`,
            COUNT(CASE WHEN id_service = 2 THEN 1 END) AS `Re-immatriculation`,
            COUNT(CASE WHEN id_service = 3 THEN 1 END) AS `Post-immatriculation`,
            COUNT(CASE WHEN id_service = 4 THEN 1 END) AS `Duplicata`
        ')
            ->whereBetween(DB::raw('STR_TO_DATE(date_creation, "%Y-%m-%d")'), [$start, $end])
            ->groupBy('name')
            ->orderByRaw("STR_TO_DATE(CONCAT('2025-', name, '-01'), '%Y-%M-%d')")
            ->get();

        return response()->json($results);
    }


    public function getGlobalStats()
    {
        // Forcer la langue française pour les noms de mois
        DB::statement("SET lc_time_names = 'fr_FR'");

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        // 📌 Stats par mois
        $monthlyStats = DB::table('dossiers')
            ->selectRaw('
            DATE_FORMAT(STR_TO_DATE(date_creation, "%Y-%m-%d"), "%M") AS month,
            COUNT(*) as Total,
            COUNT(CASE WHEN statut = 2 THEN 1 END) AS `Terminer`,
            COUNT(CASE WHEN statut = 1 THEN 1 END) AS `En attente`,
            COUNT(CASE WHEN statut = 3 THEN 1 END) AS `Rejeter`,
            COUNT(CASE WHEN statut = 4 THEN 1 END) AS `En cours de traitement`,
            COUNT(CASE WHEN id_service = 1 THEN 1 END) AS `Immatriculation-Special`,
            COUNT(CASE WHEN id_service = 2 THEN 1 END) AS `Re-immatriculation`,
            COUNT(CASE WHEN id_service = 3 THEN 1 END) AS `Post-immatriculation`,
            COUNT(CASE WHEN id_service = 4 THEN 1 END) AS `Duplicata`
        ')
            ->whereNotNull('date_creation')
            ->whereBetween('date_creation', [$startOfMonth, $endOfMonth])
            ->groupBy('month')
            // ->orderByRaw("STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', name, '-01'), '%Y-%M-%d')")
            ->get();

        $startOfWeek = Carbon::now()->startOfWeek(); // lundi par défaut
        $endOfWeek = Carbon::now()->endOfWeek();

        // 📌 Stats par semaine en cours
        $weeklyStats = DB::table('dossiers')
            ->selectRaw('
            WEEK(date_creation, 1) AS week_number,
            COUNT(*) as Total,
            COUNT(CASE WHEN statut = 2 THEN 1 END) AS `Terminer`,
            COUNT(CASE WHEN statut = 1 THEN 1 END) AS `En attente`,
            COUNT(CASE WHEN statut = 3 THEN 1 END) AS `Rejeter`,
            COUNT(CASE WHEN statut = 4 THEN 1 END) AS `En cours de traitement`,
            COUNT(CASE WHEN id_service = 1 THEN 1 END) AS `Immatriculation-Special`,
            COUNT(CASE WHEN id_service = 2 THEN 1 END) AS `Re-immatriculation`,
            COUNT(CASE WHEN id_service = 3 THEN 1 END) AS `Post-immatriculation`,
            COUNT(CASE WHEN id_service = 4 THEN 1 END) AS `Duplicata`
        ')
            ->whereBetween('date_creation', [$startOfWeek, $endOfWeek])
            // ->groupBy('id_service')
            // ->get();
            ->groupBy('week_number')
            ->get();

        // 📌 Stats du jour
        $dailyStats = DB::table('dossiers')
            ->selectRaw('
            DATE(date_creation) AS day,
            COUNT(*) as Total,
            COUNT(CASE WHEN statut = 2 THEN 1 END) AS `Terminer`,
            COUNT(CASE WHEN statut = 1 THEN 1 END) AS `En attente`,
            COUNT(CASE WHEN statut = 3 THEN 1 END) AS `Rejeter`,
            COUNT(CASE WHEN statut = 4 THEN 1 END) AS `En cours de traitement`,
            COUNT(CASE WHEN id_service = 1 THEN 1 END) AS `Immatriculation-Special`,
            COUNT(CASE WHEN id_service = 2 THEN 1 END) AS `Re-immatriculation`,
            COUNT(CASE WHEN id_service = 3 THEN 1 END) AS `Post-immatriculation`,
            COUNT(CASE WHEN id_service = 4 THEN 1 END) AS `Duplicata`
        ')
            ->whereDate('date_creation', now()->toDateString())
            ->groupBy('day')
            ->get();

        return response()->json([
            'par_mois' => $monthlyStats,
            'par_semaine' => $weeklyStats,
            'par_jour' => $dailyStats,
        ]);
    }


    public function showCaisseDossiersStatistics()
    {
        return inertia('ControlleurCaisse/StatsDossiers',);
    }

    public function showCaisseCaissesStatistics()
    {
        return inertia('ControlleurCaisse/StatsCaisses',);
    }

    //
    public function getMontantTotal(Request $request)
    {
        // dd(Carbon::today());
        $range = $request->input('range', 'day'); // day, week, month, custom
        $from = $request->input('from');
        $to = $request->input('to');

        $query = DB::table('paiements');

        // Filtrage par période
        if ($range === 'day') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($range === 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($range === 'month') {
            $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($range === 'custom' && $from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        // Montant total global
        $total = $query->sum('montant');

        // Répartition par site
        $parSite = (clone $query)
            ->select('id_site', DB::raw('SUM(montant) as total_montant'), DB::raw('COUNT(*) as total_dossiers'))
            ->groupBy('id_site')
            ->get();

        // Répartition par service
        $parService = (clone $query)
            ->select('id_service', DB::raw('SUM(montant) as total_montant'), DB::raw('COUNT(*) as total_dossiers'))
            ->groupBy('id_service')
            ->get();

        // Répartition par type de véhicule
        $parTypeVehicule = (clone $query)
            ->select('id_vehicule', DB::raw('SUM(montant) as total_montant'), DB::raw('COUNT(*) as total_dossiers'))
            ->groupBy('id_vehicule')
            ->get();

        // Récupérer tous les sites, services et types de véhicules pour afficher même ceux sans paiements
        $allSites = DB::table('sites')->select('id', 'nom_site')->get();
        $allServices = DB::table('services')->select('id', 'nom_service')->get();
        $allTypesVehicule = DB::table('genre')->select('id', 'nom')->get();
        // dd($total);
        return response()->json([
            'filters' => compact('range', 'from', 'to'),
            'total_montant' => number_format($total, 0, ',', ' '),
            'par_site' => $parSite,
            'par_service' => $parService,
            'par_type_vehicule' => $parTypeVehicule,
            'allSites' => $allSites,
            'allServices' => $allServices,
            'allTypesVehicule' => $allTypesVehicule
        ]);
    }

    public function validateMontant(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'id_caisse' => 'required|integer|exists:caisse_ouvertures,id',
            'montant_controlleur' => 'required|numeric|min:0',
        ], [
            'id_caisse.required' => 'L\'identifiant de la caisse est requis.',
            'id_caisse.integer' => 'L\'identifiant de la caisse doit être un entier.',
            'id_caisse.exists' => 'La caisse spécifiée n\'existe pas.',
            'montant_controlleur.required' => 'Le montant du contrôleur est requis.',
            'montant_controlleur.numeric' => 'Le montant du contrôleur doit être un nombre.',
            'montant_controlleur.min' => 'Le montant du contrôleur doit être au moins de 0.',
        ]);

        $ouverture = CaisseOuverture::find($request->id_caisse);

        // dd($ouverture);

        if (!$ouverture) {
            return response()->json(['message' => 'Caisse non trouvée.'], 404);
        }

        // 🔹 Créer un enregistrement dans la table controlleur_caisse
        $controlleurCaisse = ControlleurCaisse::create([
            'montant_caisse' => $ouverture->montant_caisse,
            'montant_controlleur' => $request->montant_controlleur,
            'caisse_id' => $ouverture->id,
            'date' => now()->toDateString(),
            'code_caisse' => $ouverture->code,
            'id_site' => $ouverture->site_id,
        ]);

        return response()->json([
            'message' => 'Montant du contrôleur enregistré avec succès.',
            'controlleur_caisse' => $controlleurCaisse,
        ]);
    }


    // 🔹 Récupérer toutes les caisses actives
    public function getCaisses(Request $request)
    {
        $query = Caisse::where('is_active', 1);

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $subQuery = ControlleurCaisse::selectRaw("
            CASE WHEN date_fermeture_controlleur IS NULL THEN 1 ELSE 0 END
        ")
            ->whereColumn('caisse_id', 'caisses.id')
            ->orderByDesc('date_ouverture_controlleur')
            ->limit(1);

        $caisses = $query->select('id', 'code', 'libelle', 'site_id')
            ->selectSub($subQuery, 'is_open')
            ->orderBy('libelle')
            ->get();

        return response()->json($caisses);
    }

    public function getCaisseOfAuthenticatedUser(Request $request)
    {
        // $user = Auth::user();

        $date = $request->query('date', now()->toDateString());

        // ✅ Source unique et fiable
        $idSite = getIdSite();
        $idCaisse = getIdCaisse();

        if (!$idSite || !$idCaisse) {
            return response()->json([
                'message' => 'Utilisateur non correctement rattaché à un site ou une caisse.'
            ], 403);
        }

        // ✅ Caisse strictement liée au site
        $caisse = Caisse::where('id', $idCaisse)
            ->where('site_id', $idSite)
            ->first();

        if (!$caisse) {
            return response()->json([
                'message' => 'Caisse introuvable ou non autorisée.'
            ], 404);
        }

        // ✅ Dernière ouverture non fermée (du bon site)
        $ouvertureNonFermee = DB::table('controlleur_caisses')
            ->where('caisse_id', $caisse->id)
            ->where('id_site', $idSite)
            ->where('status', 1)
            ->orderByDesc('date_ouverture_controlleur')
            ->first();

        // ✅ Ouverture du jour (du bon site)
        $ouvertureCible = DB::table('controlleur_caisses')
            ->where('caisse_id', $caisse->id)
            ->where('id_site', $idSite)
            ->whereDate('date_ouverture_controlleur', $date)
            ->first();

        return response()->json([
            'caisse' => $caisse,
            'site_id' => $idSite,
            'date_cible' => $date,
            'ouverture_du_jour' => $ouvertureCible,
            'ouverture_non_fermee' => $ouvertureNonFermee,
        ]);
    }



    public function validateCotrolleurMontant(Request $request)
    {
        if ($request->is_fermeture == 1) {
            return $this->closeControlleurCaisse($request);
        }
        return $this->openControlleurCaisse($request);
    }


    public function openControlleurCaisse(Request $request)
    {
        $request->validate([
            'id_caisse' => 'required|integer|exists:caisses,id',
            'code_caisse' => 'required|string',
            'id_site' => 'required|integer|exists:sites,id',
        ]);

        // dd($request->all());
        $today = now()->toDateString();

        $record = ControlleurCaisse::firstOrCreate(
            [
                'caisse_id' => $request->id_caisse,
                'date' => $today,
            ],
            [
                'montant_caisse' => 0,
                'montant_controlleur' => 0,
                'status' => 0,
                'status_raf' => 0,
                'code_caisse' => $request->code_caisse,
                'id_site' => $request->id_site,
            ]
        );
        // dd($record);

        if ($record->date_ouverture_controlleur) {
            return response()->json([
                'message' => "La caisse contrôleur a déjà été ouverte aujourd'hui.",
            ], 409);
        }

        $record->update([
            'date_ouverture_controlleur' => now(),
            'status' => 1,
        ]);

        return response()->json([
            'message' => 'Ouverture du contrôleur enregistrée.',
            'type' => 'ouverture',
            'controlleur_caisse' => $record->fresh(),
        ]);
    }


    public function closeControlleurCaisse(Request $request)
    {
        $request->validate([
            'id_caisse' => 'required|integer|exists:caisse_ouvertures,id',
            'montant_controlleur' => 'required|numeric|min:0',
            'date_operation' => 'required|date', // ← ici
        ]);

        $date = $request->date_operation;   // ← c'est cette date qui compte
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Vérifier que la date est autorisée
        if (!in_array($date, [$today, $yesterday])) {
            return response()->json([
                'message' => "Vous ne pouvez fermer la caisse que pour aujourd'hui ou hier.",
            ], 403);
        }

        // Chercher la session du contrôleur correspondant à la date
        $record = ControlleurCaisse::where('caisse_id', $request->id_caisse)
            ->where('date', $date) // ← on compare à la colonne `date`
            ->first();

        if (!$record || !$record->date_ouverture_controlleur) {
            return response()->json([
                'message' => "Aucune session contrôleur ouverte à fermer pour cette date.",
            ], 409);
        }

        if ($record->date_fermeture_controlleur) {
            return response()->json([
                'message' => "La session contrôleur est déjà fermée pour cette date.",
            ], 409);
        }

        // Fermer la session
        $record->update([
            'date_fermeture_controlleur' => now(),
            'montant_controlleur' => $request->montant_controlleur,
            'status' => 0,
        ]);

        return response()->json([
            'message' => 'Fermeture contrôleur enregistrée.',
            'type' => 'fermeture',
            'controlleur_caisse' => $record->fresh(),
        ]);
    }
}
