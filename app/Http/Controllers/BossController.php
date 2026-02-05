<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BossController extends Controller
{
    //
    public function showBossDashboard()
    {
        return inertia('Boss/Dashbord',);
    }

    public function showBossNewDashboard()
    {
        return inertia('Boss/NewDashbord',);
    }


    public function showBossFinancesStatistics()
    {
        return inertia('Boss/StatsFinances',);
    }
    public function showBossComparativeStatistics()
    {
        return inertia('Boss/StatsComparative',);
    }


    public function getStats()
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
        // Stats par mois
        $monthlyStats = DB::table('dossiers')
            ->selectRaw('
            CONCAT(YEAR(CURDATE()), "-", MONTH(CURDATE())) AS month,
            SUM(
                CASE
                    WHEN statut = 1 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1
                    WHEN statut = 2 AND MONTH(date_validation) = MONTH(CURDATE()) AND YEAR(date_validation) = YEAR(CURDATE()) THEN 1
                    WHEN statut = 3 AND MONTH(date_rejet) = MONTH(CURDATE()) AND YEAR(date_rejet) = YEAR(CURDATE()) THEN 1
                    WHEN statut = 4 AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE()) THEN 1
                    ELSE 0
                END
            ) AS Total,
            SUM(CASE WHEN statut = 1 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS `En attente`,
            SUM(CASE WHEN statut = 2 AND MONTH(date_validation) = MONTH(CURDATE()) AND YEAR(date_validation) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS `Valider`,
            SUM(CASE WHEN statut = 3 AND MONTH(date_rejet) = MONTH(CURDATE()) AND YEAR(date_rejet) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS `Refuser`,
            SUM(CASE WHEN statut = 4 AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS `En cours`,
            SUM(CASE WHEN (statut = 1 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) OR
                          statut = 2 AND MONTH(date_validation) = MONTH(CURDATE()) AND YEAR(date_validation) = YEAR(CURDATE()) OR
                          statut = 3 AND MONTH(date_rejet) = MONTH(CURDATE()) AND YEAR(date_rejet) = YEAR(CURDATE()) OR
                          statut = 4 AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE()))
                     AND id_service = 1 THEN 1 ELSE 0 END) AS `Immatriculation-Special`,
            SUM(CASE WHEN (statut = 1 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) OR
                          statut = 2 AND MONTH(date_validation) = MONTH(CURDATE()) AND YEAR(date_validation) = YEAR(CURDATE()) OR
                          statut = 3 AND MONTH(date_rejet) = MONTH(CURDATE()) AND YEAR(date_rejet) = YEAR(CURDATE()) OR
                          statut = 4 AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE()))
                     AND id_service = 2 THEN 1 ELSE 0 END) AS `Re-immatriculation`,
            SUM(CASE WHEN (statut = 1 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) OR
                          statut = 2 AND MONTH(date_validation) = MONTH(CURDATE()) AND YEAR(date_validation) = YEAR(CURDATE()) OR
                          statut = 3 AND MONTH(date_rejet) = MONTH(CURDATE()) AND YEAR(date_rejet) = YEAR(CURDATE()) OR
                          statut = 4 AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE()))
                     AND id_service = 3 THEN 1 ELSE 0 END) AS `Post-immatriculation`,
            SUM(CASE WHEN (statut = 1 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) OR
                          statut = 2 AND MONTH(date_validation) = MONTH(CURDATE()) AND YEAR(date_validation) = YEAR(CURDATE()) OR
                          statut = 3 AND MONTH(date_rejet) = MONTH(CURDATE()) AND YEAR(date_rejet) = YEAR(CURDATE()) OR
                          statut = 4 AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE()))
                     AND id_service = 4 THEN 1 ELSE 0 END) AS `Duplicata`
        ')
            ->get();

        // Stats par semaine
        $weeklyStats = DB::table('dossiers')
            ->selectRaw(
                '
            YEARWEEK(CURDATE(), 1) AS week,
            SUM(
                CASE
                    WHEN statut = 1 AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1
                    WHEN statut = 2 AND YEARWEEK(date_validation, 1) = YEARWEEK(CURDATE(), 1) THEN 1
                    WHEN statut = 3 AND YEARWEEK(date_rejet, 1) = YEARWEEK(CURDATE(), 1) THEN 1
                    WHEN statut = 4 AND YEARWEEK(date_paiement, 1) = YEARWEEK(CURDATE(), 1) THEN 1
                    ELSE 0
                END
            ) AS Total,
            SUM(CASE WHEN statut = 1 AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS `En attente`,
            SUM(CASE WHEN statut = 2 AND YEARWEEK(date_validation, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS `Valider`,
            SUM(CASE WHEN statut = 3 AND YEARWEEK(date_rejet, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS `Refuser`,
            SUM(CASE WHEN statut = 4 AND YEARWEEK(date_paiement, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS `En cours`,
            SUM(CASE WHEN (statut = 1 AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 2 AND YEARWEEK(date_validation, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 3 AND YEARWEEK(date_rejet, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 4 AND YEARWEEK(date_paiement, 1) = YEARWEEK(CURDATE(), 1))
                        AND id_service = 1 THEN 1 ELSE 0 END) AS `Immatriculation-Special`,
            SUM(CASE WHEN (statut = 1 AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 2 AND YEARWEEK(date_validation, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 3 AND YEARWEEK(date_rejet, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 4 AND YEARWEEK(date_paiement, 1) = YEARWEEK(CURDATE(), 1))
                        AND id_service = 2 THEN 1 ELSE 0 END) AS `Re-immatriculation`,
            SUM(CASE WHEN (statut = 1 AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 2 AND YEARWEEK(date_validation, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 3 AND YEARWEEK(date_rejet, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 4 AND YEARWEEK(date_paiement, 1) = YEARWEEK(CURDATE(), 1))
                        AND id_service = 3 THEN 1 ELSE 0 END) AS `Post-immatriculation`,
            SUM(CASE WHEN (statut = 1 AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 2 AND YEARWEEK(date_validation, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 3 AND YEARWEEK(date_rejet, 1) = YEARWEEK(CURDATE(), 1) OR
                            statut = 4 AND YEARWEEK(date_paiement, 1) = YEARWEEK(CURDATE(), 1))
                        AND id_service = 4 THEN 1 ELSE 0 END) AS `Duplicata`'
            )->get();

        // Stats par jour
        $dailyStats = DB::table('dossiers')
            ->selectRaw('
            CURDATE() AS day,
            SUM(
                CASE
                    WHEN statut = 1 AND DATE(created_at) = CURDATE() THEN 1
                    WHEN statut = 2 AND DATE(date_validation) = CURDATE() THEN 1
                    WHEN statut = 3 AND DATE(date_rejet) = CURDATE() THEN 1
                    WHEN statut = 4 AND DATE(date_paiement) = CURDATE() THEN 1
                    ELSE 0
                END
            ) AS Total,
            SUM(CASE WHEN statut = 1 AND DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS `En attente`,
            SUM(CASE WHEN statut = 2 AND DATE(date_validation) = CURDATE() THEN 1 ELSE 0 END) AS `Valider`,
            SUM(CASE WHEN statut = 3 AND DATE(date_rejet) = CURDATE() THEN 1 ELSE 0 END) AS `Refuser`,
            SUM(CASE WHEN statut = 4 AND DATE(date_paiement) = CURDATE() THEN 1 ELSE 0 END) AS `En cours`,
            SUM(CASE WHEN (statut = 1 AND DATE(created_at) = CURDATE() OR
                          statut = 2 AND DATE(date_validation) = CURDATE() OR
                          statut = 3 AND DATE(date_rejet) = CURDATE() OR
                          statut = 4 AND DATE(date_paiement) = CURDATE())
                     AND id_service = 1 THEN 1 ELSE 0 END) AS `Immatriculation-Special`,
            SUM(CASE WHEN (statut = 1 AND DATE(created_at) = CURDATE() OR
                          statut = 2 AND DATE(date_validation) = CURDATE() OR
                          statut = 3 AND DATE(date_rejet) = CURDATE() OR
                          statut = 4 AND DATE(date_paiement) = CURDATE())
                     AND id_service = 2 THEN 1 ELSE 0 END) AS `Re-immatriculation`,
            SUM(CASE WHEN (statut = 1 AND DATE(created_at) = CURDATE() OR
                          statut = 2 AND DATE(date_validation) = CURDATE() OR
                          statut = 3 AND DATE(date_rejet) = CURDATE() OR
                          statut = 4 AND DATE(date_paiement) = CURDATE())
                     AND id_service = 3 THEN 1 ELSE 0 END) AS `Post-immatriculation`,
            SUM(CASE WHEN (statut = 1 AND DATE(created_at) = CURDATE() OR
                          statut = 2 AND DATE(date_validation) = CURDATE() OR
                          statut = 3 AND DATE(date_rejet) = CURDATE() OR
                          statut = 4 AND DATE(date_paiement) = CURDATE())
                     AND id_service = 4 THEN 1 ELSE 0 END) AS `Duplicata`
        ')
            ->get();

        // Stats par année
        $yearlyStats = DB::table('dossiers')
            ->selectRaw('
            YEAR(CURDATE()) AS year,
            SUM(
                CASE
                    WHEN statut = 1 AND YEAR(created_at) = YEAR(CURDATE()) THEN 1
                    WHEN statut = 2 AND YEAR(date_validation) = YEAR(CURDATE()) THEN 1
                    WHEN statut = 3 AND YEAR(date_rejet) = YEAR(CURDATE()) THEN 1
                    WHEN statut = 4 AND YEAR(date_paiement) = YEAR(CURDATE()) THEN 1
                    ELSE 0
                END
            ) AS Total,
            SUM(CASE WHEN statut = 1 AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS `En attente`,
            SUM(CASE WHEN statut = 2 AND YEAR(date_validation) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS `Valider`,
            SUM(CASE WHEN statut = 3 AND YEAR(date_rejet) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS `Refuser`,
            SUM(CASE WHEN statut = 4 AND YEAR(date_paiement) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS `En cours`,
            SUM(CASE WHEN (statut = 1 AND YEAR(created_at) = YEAR(CURDATE()) OR
                          statut = 2 AND YEAR(date_validation) = YEAR(CURDATE()) OR
                          statut = 3 AND YEAR(date_rejet) = YEAR(CURDATE()) OR
                          statut = 4 AND YEAR(date_paiement) = YEAR(CURDATE()))
                     AND id_service = 1 THEN 1 ELSE 0 END) AS `Immatriculation-Special`,
            SUM(CASE WHEN (statut = 1 AND YEAR(created_at) = YEAR(CURDATE()) OR
                          statut = 2 AND YEAR(date_validation) = YEAR(CURDATE()) OR
                          statut = 3 AND YEAR(date_rejet) = YEAR(CURDATE()) OR
                          statut = 4 AND YEAR(date_paiement) = YEAR(CURDATE()))
                     AND id_service = 2 THEN 1 ELSE 0 END) AS `Re-immatriculation`,
            SUM(CASE WHEN (statut = 1 AND YEAR(created_at) = YEAR(CURDATE()) OR
                          statut = 2 AND YEAR(date_validation) = YEAR(CURDATE()) OR
                          statut = 3 AND YEAR(date_rejet) = YEAR(CURDATE()) OR
                          statut = 4 AND YEAR(date_paiement) = YEAR(CURDATE()))
                     AND id_service = 3 THEN 1 ELSE 0 END) AS `Post-immatriculation`,
            SUM(CASE WHEN (statut = 1 AND YEAR(created_at) = YEAR(CURDATE()) OR
                          statut = 2 AND YEAR(date_validation) = YEAR(CURDATE()) OR
                          statut = 3 AND YEAR(date_rejet) = YEAR(CURDATE()) OR
                          statut = 4 AND YEAR(date_paiement) = YEAR(CURDATE()))
                     AND id_service = 4 THEN 1 ELSE 0 END) AS `Duplicata`
        ')
            ->get();

        return response()->json([
            'par_jour'   => $dailyStats,
            'par_semaine' => $weeklyStats,
            'par_mois'   => $monthlyStats,
            'par_annee'  => $yearlyStats
        ]);
    }

    public function getGlobalPaiementStats(Request $request)
    {
        // Langue française pour les mois
        DB::statement("SET lc_time_names = 'fr_FR'");

        // Période
        $periode = $request->input('periode', 'today');

        switch ($periode) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default:
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
        }

        // BASE QUERY (sans join)
        $baseQuery = DB::table('paiements')
            ->whereBetween('paiements.created_at', [$startDate, $endDate]);

        // Montant par site
        $montantParSite = (clone $baseQuery)
            ->join('sites', 'paiements.id_site', '=', 'sites.id')
            ->select('sites.nom_site', DB::raw('SUM(paiements.montant) as montant'))
            ->groupBy('sites.nom_site')
            ->get()
            ->toArray();

        // Montant par service
        $montantParService = (clone $baseQuery)
            ->join('services', 'paiements.id_service', '=', 'services.id')
            ->select('services.nom_service', DB::raw('SUM(paiements.montant) as montant'))
            ->groupBy('services.nom_service')
            ->get()
            ->toArray();

        // Montant par genre de véhicule
        $montantParGenreVehicule = (clone $baseQuery)
            ->join('vehicules', 'paiements.id_vehicule', '=', 'vehicules.id')
            ->select('vehicules.genre_vehicule', DB::raw('SUM(paiements.montant) as montant'))
            ->groupBy('vehicules.genre_vehicule')
            ->get()
            ->toArray();

        // Résultat final
        return response()->json([
            'periode' => $periode,
            'sites' => $montantParSite,
            'services' => $montantParService,
            'vehicules' => $montantParGenreVehicule,
        ]);
    }


    /**
     * Récupère les statistiques comparatives (jour, semaine, mois, année).
     */
    public function getComparativeStats(Request $request)
    {
        // Récupérer la date actuelle
        $now = now();

        // Données pour le comparatif journalier (Aujourd'hui vs Hier)
        $todayStats = DB::table('paiements')
            ->whereDate('created_at', $now->toDateString())
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('SUM(montant) as montant'))
            ->groupBy('hour')
            ->pluck('montant', 'hour')
            ->toArray();

        $yesterdayStats = DB::table('paiements')
            ->whereDate('created_at', $now->subDay()->toDateString())
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('SUM(montant) as montant'))
            ->groupBy('hour')
            ->pluck('montant', 'hour')
            ->toArray();

        // Préparer les données pour le graphique journalier (8h-18h)
        $hours = range(8, 22); // De 8h à 22h
        $todayValues = array_map(function ($hour) use ($todayStats) {
            return $todayStats[$hour] ?? 0;
        }, $hours);

        $yesterdayValues = array_map(function ($hour) use ($yesterdayStats) {
            return $yesterdayStats[$hour] ?? 0;
        }, $hours);

        // Données pour le comparatif hebdomadaire (Cette semaine vs Semaine dernière)
        $weekLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        $currentWeekStats = DB::table('paiements')
            ->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()])
            ->select(DB::raw('DAYOFWEEK(created_at) as day'), DB::raw('SUM(montant) as montant'))
            ->groupBy('day')
            ->pluck('montant', 'day')
            ->toArray();

        $previousWeekStats = DB::table('paiements')
            ->whereBetween('created_at', [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()])
            ->select(DB::raw('DAYOFWEEK(created_at) as day'), DB::raw('SUM(montant) as montant'))
            ->groupBy('day')
            ->pluck('montant', 'day')
            ->toArray();

        $weekCurrent = array_map(function ($day) use ($currentWeekStats) {
            return $currentWeekStats[$day] ?? 0;
        }, range(2, 8)); // 2=Lundi, 8=Dimanche

        $weekPrevious = array_map(function ($day) use ($previousWeekStats) {
            return $previousWeekStats[$day] ?? 0;
        }, range(2, 8));

        // Données pour le comparatif mensuel (Ce mois vs Mois dernier)
        $monthLabels = range(1, $now->daysInMonth);
        $currentMonthStats = DB::table('paiements')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->select(DB::raw('DAY(created_at) as day'), DB::raw('SUM(montant) as montant'))
            ->groupBy('day')
            ->pluck('montant', 'day')
            ->toArray();

        $previousMonthStats = DB::table('paiements')
            ->whereYear('created_at', $now->subMonth()->year)
            ->whereMonth('created_at', $now->subMonth()->month)
            ->select(DB::raw('DAY(created_at) as day'), DB::raw('SUM(montant) as montant'))
            ->groupBy('day')
            ->pluck('montant', 'day')
            ->toArray();

        $monthCurrent = array_map(function ($day) use ($currentMonthStats) {
            return $currentMonthStats[$day] ?? 0;
        }, $monthLabels);

        $monthPrevious = array_map(function ($day) use ($previousMonthStats) {
            return $previousMonthStats[$day] ?? 0;
        }, $monthLabels);

        // Données pour le comparatif annuel (Cette année vs Année dernière)
        $yearLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        $currentYearStats = DB::table('paiements')
            ->whereYear('created_at', $now->year)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(montant) as montant'))
            ->groupBy('month')
            ->pluck('montant', 'month')
            ->toArray();

        $previousYearStats = DB::table('paiements')
            ->whereYear('created_at', $now->subYear()->year)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(montant) as montant'))
            ->groupBy('month')
            ->pluck('montant', 'month')
            ->toArray();

        $yearCurrent = array_map(function ($month) use ($currentYearStats) {
            return $currentYearStats[$month] ?? 0;
        }, range(1, 12));

        $yearPrevious = array_map(function ($month) use ($previousYearStats) {
            return $previousYearStats[$month] ?? 0;
        }, range(1, 12));

        // Retourner les données
        return response()->json([
            'day' => [
                'labels' => $hours,
                'current' => array_values($todayValues),
                'previous' => array_values($yesterdayValues),
            ],
            'week' => [
                'labels' => $weekLabels,
                'current' => array_values($weekCurrent),
                'previous' => array_values($weekPrevious),
            ],
            'month' => [
                'labels' => $monthLabels,
                'current' => array_values($monthCurrent),
                'previous' => array_values($monthPrevious),
            ],
            'year' => [
                'labels' => $yearLabels,
                'current' => array_values($yearCurrent),
                'previous' => array_values($yearPrevious),
            ],
        ]);
    }









    public function getControllerGlobalPaiementStats(Request $request)
    {
        DB::statement("SET lc_time_names = 'fr_FR'");

        $periode = $request->input('periode', 'today');

        switch ($periode) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default:
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
        }

        $idSite = getIdSite();
        $idCaisse = getIdCaisse();

        $baseQuery = DB::table('paiements')
            ->whereBetween('paiements.created_at', [$startDate, $endDate])
            ->where('paiements.id_site', $idSite)
            ->when($idCaisse, fn($q) => $q->where('paiements.caisse_id', $idCaisse));

        $stats = [
            'periode' => $periode,
            'sites' => (clone $baseQuery)
                ->join('sites', 'paiements.id_site', '=', 'sites.id')
                ->select('sites.nom_site', DB::raw('SUM(paiements.montant) as montant'))
                ->groupBy('sites.nom_site')
                ->get(),

            'services' => (clone $baseQuery)
                ->join('services', 'paiements.id_service', '=', 'services.id')
                ->select('services.nom_service', DB::raw('SUM(paiements.montant) as montant'))
                ->groupBy('services.nom_service')
                ->get(),

            'vehicules' => (clone $baseQuery)
                ->join('vehicules', 'paiements.id_vehicule', '=', 'vehicules.id')
                ->select('vehicules.genre_vehicule', DB::raw('SUM(paiements.montant) as montant'))
                ->groupBy('vehicules.genre_vehicule')
                ->get(),
        ];

        return response()->json($stats);
    }









    public function showBossDossiersStatistics()
    {
        return inertia('Boss/StatsDossiers',);
    }

    public function showBossCaissesStatistics()
    {
        return inertia('Boss/StatsCaisses',);
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
            $query->whereDate('date_paiement', Carbon::today());
        } elseif ($range === 'week') {
            $query->whereBetween('date_paiement', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($range === 'month') {
            $query->whereBetween('date_paiement', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($range === 'custom' && $from && $to) {
            $query->whereBetween('date_paiement', [$from, $to]);
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
}
