<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Caisse;
use App\Models\CaisseOuverture;
use App\Models\ControlleurCaisse;
use App\Models\Dossier;
use App\Models\Service;
use App\Models\Site;
use App\Models\Vehicule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendPaiementsToX3Job;
use App\Models\Paiement;



class RafCaisseController extends Controller
{
    //

    public function showRafDashboard()
    {
        return inertia('RafCaisse/Dashbord',);
    }
    public function showRafCaisseCaissesStatistics()
    {
        return inertia('RafCaisse/RafStatsCaisses',);
    }


    public function getRafCaisseListe(Request $request)
    {

        $query = Caisse::query()->where('is_active', 1);

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        // Dernier état de chaque caisse
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


    public function getRafCaisseInfo(Request $request)
    {
        $caisseId = $request->query('caisse_id');
        $siteId = $request->query('site_id');
        $date = $request->query('date', now()->toDateString());

        // Caisses concernées : une caisse précise, celles d'un site, ou toutes si aucun filtre.
        if (!empty($caisseId)) {
            $caisseIds = collect([(int) $caisseId]);
        } else {
            $caisseQuery = Caisse::query();

            if (!empty($siteId)) {
                $caisseQuery->where('site_id', $siteId);
            }

            $caisseIds = $caisseQuery->pluck('id');
        }

        // 1) Session RAF NON FERMÉE (status_raf = 1) pour la ou les caisses concernées
        $ouvertureRafNonFermee = ControlleurCaisse::query()
            ->whereIn('caisse_id', $caisseIds)
            ->whereNotNull('date_ouverture_raf')
            ->where('status_raf', 1)
            ->orderByDesc('date_ouverture_raf')
            ->first();

        // 2) Ouverture RAF pour la date cible
        $ouvertureRafDuJour = ControlleurCaisse::query()
            ->whereIn('caisse_id', $caisseIds)
            ->whereDate('date', $date)
            ->whereNotNull('date_ouverture_raf')
            ->orderBy('caisse_id')
            ->first();

        // 3) Infos de clôture caissière (billetterie, perte, surplus, commentaire)
        $caisseOuvertures = DB::table('caisse_ouvertures')
            ->whereIn('caisse_id', $caisseIds)
            ->whereDate('date_ouverture', $date)
            ->get();

        if (!empty($caisseId)) {
            $caisseOuverture = $caisseOuvertures->first();
        } else {
            // Vue "toutes les caisses" : on agrège les montants et les commentaires.
            $commentaires = $caisseOuvertures
                ->pluck('commentaire')
                ->filter()
                ->unique()
                ->values();

            $caisseOuverture = [
                'montant_ouverture' => (float) $caisseOuvertures->sum('montant_ouverture'),
                'perte' => (float) $caisseOuvertures->sum('perte'),
                'surplus' => (float) $caisseOuvertures->sum('surplus'),
                'commentaire' => $commentaires->implode(' | '),
                'is_aggregate' => true,
            ];
        }

        // 4) Lire la config RAF (contrainte J-5)
        $rafConfig = (int) DB::table('settings')->value('raf_config');

        return response()->json([
            'caisse_id' => $caisseId,
            'site_id' => $siteId,
            'date_cible' => $date,
            'ouverture_raf_du_jour' => $ouvertureRafDuJour,
            'ouverture_raf_non_fermee' => $ouvertureRafNonFermee,
            'caisse_ouverture' => $caisseOuverture,
            'status_raf' => $ouvertureRafNonFermee ? 'ouverte' : 'fermée',
            'raf_config' => $rafConfig,
            'is_aggregate' => empty($caisseId),
        ]);
    }


    /**
     * Liste des sites avec le statut RAF agrégé de leurs caisses pour une date.
     * Statuts : ouverte (toutes les caisses ouvertes), fermee (toutes clôturées),
     * partielle (une partie seulement), non_ouverte, sans_caisse.
     */
    public function getRafSiteListe(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $sites = Site::query()->orderBy('nom_site')->get(['id', 'nom_site', 'code_site']);

        $caisses = Caisse::query()->where('is_active', 1)->get(['id', 'site_id']);

        $rafRows = ControlleurCaisse::query()
            ->whereIn('caisse_id', $caisses->pluck('id'))
            ->whereDate('date', $date)
            ->whereNotNull('date_ouverture_raf')
            ->get(['caisse_id', 'date_fermeture_raf']);

        $ouvertes = $rafRows->whereNull('date_fermeture_raf')->pluck('caisse_id')->unique();
        $fermees = $rafRows->whereNotNull('date_fermeture_raf')->pluck('caisse_id')->unique();

        $caissesParSite = $caisses->groupBy('site_id');

        $result = $sites->map(function ($site) use ($caissesParSite, $ouvertes, $fermees) {
            $ids = collect($caissesParSite->get($site->id, collect()))->pluck('id');
            $total = $ids->count();
            $nbOuvertes = $ids->intersect($ouvertes)->count();
            $nbFermees = $ids->intersect($fermees)->count();

            if ($total === 0) {
                $status = 'sans_caisse';
            } elseif ($nbOuvertes === $total) {
                $status = 'ouverte';
            } elseif ($nbFermees === $total) {
                $status = 'fermee';
            } elseif ($nbOuvertes > 0 || $nbFermees > 0) {
                $status = 'partielle';
            } else {
                $status = 'non_ouverte';
            }

            return [
                'id' => $site->id,
                'nom_site' => $site->nom_site,
                'code_site' => $site->code_site,
                'caisses_total' => $total,
                'caisses_ouvertes' => $nbOuvertes,
                'caisses_fermees' => $nbFermees,
                'status' => $status,
            ];
        })->values();

        return response()->json([
            'sites' => $result,
            'raf_config' => (int) DB::table('settings')->value('raf_config'),
        ]);
    }


    /**
     * Détail RAF d'un site : caisses actives, statut RAF de chacune,
     * total des paiements du jour par caisse et éventuelle session non fermée.
     */
    public function getRafSiteInfo(Request $request)
    {
        $request->validate([
            'site_id' => 'required|integer|exists:sites,id',
        ]);

        $date = $request->query('date', now()->toDateString());
        $siteId = $request->query('site_id');

        $site = Site::find($siteId);

        $caisses = Caisse::query()
            ->where('site_id', $siteId)
            ->where('is_active', 1)
            ->orderBy('libelle')
            ->get(['id', 'code', 'libelle', 'site_id']);

        $caisseIds = $caisses->pluck('id');

        $rafRows = ControlleurCaisse::query()
            ->whereIn('caisse_id', $caisseIds)
            ->whereDate('date', $date)
            ->whereNotNull('date_ouverture_raf')
            ->get(['caisse_id', 'date_ouverture_raf', 'date_fermeture_raf']);

        $ouvertes = $rafRows->whereNull('date_fermeture_raf')->pluck('caisse_id')->unique();
        $touchees = $rafRows->pluck('caisse_id')->unique();

        $totauxPaiements = Paiement::query()
            ->whereDate('created_at', $date)
            ->whereIn('caisse_id', $caisseIds)
            ->selectRaw('caisse_id, SUM(montant) as total, COUNT(*) as nb')
            ->groupBy('caisse_id')
            ->get()
            ->keyBy('caisse_id');

        $sessionNonFermee = ControlleurCaisse::query()
            ->whereIn('caisse_id', $caisseIds)
            ->whereNotNull('date_ouverture_raf')
            ->where('status_raf', 1)
            ->orderByDesc('date_ouverture_raf')
            ->first();

        $total = $caisseIds->count();
        $nbOuvertes = $caisseIds->intersect($ouvertes)->count();
        $nbFermees = $caisseIds->intersect($touchees->diff($ouvertes))->count();

        if ($total === 0) {
            $status = 'sans_caisse';
        } elseif ($nbOuvertes === $total) {
            $status = 'ouverte';
        } elseif ($nbFermees === $total) {
            $status = 'fermee';
        } elseif ($nbOuvertes > 0 || $nbFermees > 0) {
            $status = 'partielle';
        } else {
            $status = 'non_ouverte';
        }

        return response()->json([
            'site' => [
                'id' => $site->id,
                'nom_site' => $site->nom_site,
                'code_site' => $site->code_site,
            ],
            'date_cible' => $date,
            'status' => $status,
            'caisses' => $caisses->map(function ($caisse) use ($ouvertes, $touchees, $totauxPaiements) {
                $rafStatus = $ouvertes->contains($caisse->id)
                    ? 'ouverte'
                    : ($touchees->contains($caisse->id) ? 'cloturee' : 'non_ouverte');

                $row = $totauxPaiements->get($caisse->id);

                return [
                    'id' => $caisse->id,
                    'code' => $caisse->code,
                    'libelle' => $caisse->libelle,
                    'site_id' => $caisse->site_id,
                    'raf_status' => $rafStatus,
                    'total_paiements' => $row ? (float) $row->total : 0,
                    'nb_paiements' => $row ? (int) $row->nb : 0,
                ];
            })->values(),
            'session_non_fermee' => $sessionNonFermee,
            'raf_config' => (int) DB::table('settings')->value('raf_config'),
        ]);
    }


    /**
     * Ouverture / fermeture RAF de TOUTES les caisses actives d'un site.
     * À la fermeture, seules les caisses dont le total de paiements du jour
     * est supérieur à 0 sont envoyées vers Sage X3.
     */
    public function validateRafSite(Request $request)
    {
        // Le Trésorier consulte l'interface RAF mais ne peut ni ouvrir, ni fermer (ni déverser dans X3).
        if (Auth::user()?->hasRole('Trésorier')) {
            return response()->json([
                'message' => "Le rôle Trésorier ne peut pas ouvrir ou fermer une session RAF.",
            ], 403);
        }

        $request->validate([
            'site_id' => 'required|integer|exists:sites,id',
            'is_fermeture' => 'required|boolean',
            'date_operation' => 'required|date',
        ]);

        $site = Site::find($request->site_id);

        $dateOperation = Carbon::parse($request->date_operation)->toDateString();

        // ✅ Vérification dynamique via settings.raf_config (1 = contrainte active, 0 = libre)
        if ((int) DB::table('settings')->value('raf_config') === 1) {
            $today = Carbon::now()->toDateString();
            $fiveDaysAgo = Carbon::now()->subDays(5)->toDateString();

            if ($dateOperation < $fiveDaysAgo || $dateOperation > $today) {
                return response()->json([
                    'message' => "La date de l'opération doit être comprise dans les 5 derniers jours."
                ], 400);
            }
        }

        $caisses = Caisse::query()
            ->where('site_id', $site->id)
            ->where('is_active', 1)
            ->get();

        if ($caisses->isEmpty()) {
            return response()->json([
                'message' => 'Aucune caisse active pour ce site.'
            ], 404);
        }

        $isFermeture = (bool) $request->is_fermeture;

        /*
    |--------------------------------------------------------------------------
    | 🟢 OUVERTURE RAF DE TOUTES LES CAISSES DU SITE
    |--------------------------------------------------------------------------
    */
        if (!$isFermeture) {
            $ouvertes = 0;
            $ignorees = 0;

            DB::beginTransaction();

            try {
                foreach ($caisses as $caisse) {
                    $record = ControlleurCaisse::firstOrCreate(
                        [
                            'caisse_id' => $caisse->id,
                            'date' => $dateOperation,
                        ],
                        [
                            'montant_caisse' => 0,
                            'montant_controlleur' => 0,
                            'status' => 0,
                            'status_raf' => 0,
                            'code_caisse' => $caisse->code,
                            'id_site' => $caisse->site_id,
                        ]
                    );

                    if ($record->date_ouverture_raf !== null) {
                        $ignorees++;
                        continue;
                    }

                    $record->update([
                        'date_ouverture_raf' => now(),
                        'status_raf' => 1,
                    ]);

                    $ouvertes++;
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();

                return response()->json([
                    'message' => "Erreur lors de l'ouverture RAF du site.",
                    'error' => $e->getMessage(),
                ], 500);
            }

            if ($ouvertes === 0) {
                return response()->json([
                    'message' => "Toutes les caisses de ce site ont déjà une session RAF ouverte pour cette date."
                ], 409);
            }

            return response()->json([
                'message' => $ignorees > 0
                    ? "Ouverture RAF enregistrée pour {$ouvertes} caisse(s). {$ignorees} caisse(s) déjà ouverte(s) ont été ignorées."
                    : "Ouverture RAF enregistrée pour {$ouvertes} caisse(s) du site.",
                'type' => 'ouverture_raf_site',
                'caisses_ouvertes' => $ouvertes,
                'caisses_ignorees' => $ignorees,
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | 🔴 FERMETURE RAF DE TOUTES LES CAISSES DU SITE + ENVOI SAGE X3
    |--------------------------------------------------------------------------
    */
        $records = ControlleurCaisse::query()
            ->whereIn('caisse_id', $caisses->pluck('id'))
            ->whereDate('date', $dateOperation)
            ->whereNotNull('date_ouverture_raf')
            ->whereNull('date_fermeture_raf')
            ->get();

        if ($records->isEmpty()) {
            return response()->json([
                'message' => 'Aucune session RAF ouverte à fermer pour ce site à cette date.'
            ], 409);
        }

        DB::beginTransaction();

        try {
            $envoisX3 = 0;

            foreach ($records as $record) {
                // Total des paiements de la caisse pour la date : rien à envoyer à X3 si 0
                $totalPaiements = (float) Paiement::query()
                    ->whereDate('created_at', $dateOperation)
                    ->where('caisse_id', $record->caisse_id)
                    ->sum('montant');

                if ($totalPaiements > 0) {
                    // 🚀 Dispatch JOB X3 (asynchrone, retry, logs)
                    SendPaiementsToX3Job::dispatch($dateOperation, $record->caisse_id);
                    $envoisX3++;
                } else {
                    // Aucun paiement (ou montant total à 0) : on ferme sans déverser 0 dans X3
                    Log::info('Fermeture RAF site : aucun paiement, envoi Sage X3 ignoré.', [
                        'site_id' => $site->id,
                        'caisse_id' => $record->caisse_id,
                        'date_operation' => $dateOperation,
                    ]);
                }

                // Montant validé par le contrôleur pour cette caisse (0 si aucun)
                $montant = DB::table('caisse_ouvertures')
                    ->where('caisse_id', $record->caisse_id)
                    ->whereDate('date_ouverture', $dateOperation)
                    ->value('montant_controlleur');

                $record->update([
                    'montant_controlleur' => $montant ?? $record->montant_controlleur ?? 0,
                    'date_fermeture_raf' => now(),
                    'status_raf' => 0,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => "Erreur lors de la fermeture RAF du site.",
                'error' => $e->getMessage(),
            ], 500);
        }

        $nbFermees = $records->pluck('caisse_id')->unique()->count();

        return response()->json([
            'message' => $envoisX3 > 0
                ? "Fermeture RAF enregistrée pour {$nbFermees} caisse(s). Envoi vers Sage X3 lancé pour {$envoisX3} caisse(s)."
                : "Fermeture RAF enregistrée pour {$nbFermees} caisse(s). Aucun montant à envoyer vers Sage X3.",
            'type' => 'fermeture_raf_site',
            'caisses_fermees' => $nbFermees,
            'envois_x3' => $envoisX3,
        ]);
    }






    public function validateRafControlleurMontant(Request $request)
    {
        // Le Trésorier consulte l'interface RAF mais ne peut ni ouvrir, ni fermer (ni déverser dans X3).
        if (Auth::user()?->hasRole('Trésorier')) {
            return response()->json([
                'message' => "Le rôle Trésorier ne peut pas ouvrir ou fermer une session RAF.",
            ], 403);
        }

        // dd($request->all());
        // ✅ Validation
        $request->validate([
            'id_caisse' => 'required|integer',
            'montant_controlleur' => 'nullable|numeric|min:0',
            'is_fermeture' => 'required|boolean',
            'code_caisse' => 'nullable|string',
            'id_site' => 'nullable|integer',
            'date_operation' => 'required|date',
        ]);

        // ✅ Vérifier la caisse
        // $caisseOuverture = DB::table('caisse_ouvertures')
        //     ->where('caisse_id', $request->id_caisse)
        //     ->first();
        $caisse = Caisse::find($request->id_caisse);

        if (!$caisse) {
            return response()->json(['message' => 'Caisse introuvable.'], 404);
        }

        // ✅ Parse de la date d'opération
        $dateOperation = Carbon::parse($request->date_operation)->toDateString();

        // ✅ Vérification dynamique via settings.raf_config (1 = contrainte active, 0 = libre)
        $rafConfig = DB::table('settings')->value('raf_config');

        if ((int) $rafConfig === 1) {
            $today = Carbon::now()->toDateString();
            $fiveDaysAgo = Carbon::now()->subDays(5)->toDateString();

            if ($dateOperation < $fiveDaysAgo || $dateOperation > $today) {
                return response()->json([
                    'message' => "La date de l'opération doit être comprise dans les 5 derniers jours."
                ], 400);
            }
        }


        // ✅ Déterminer action
        $isFermeture = (bool) $request->is_fermeture;

        // ✅ Récupérer ou créer l’enregistrement RAF
        $record = ControlleurCaisse::firstOrCreate(
            [
                'caisse_id' => $request->id_caisse,
                'date' => $dateOperation,
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

        /*
    |--------------------------------------------------------------------------
    | 🟢 OUVERTURE RAF
    |--------------------------------------------------------------------------
    */
        if (!$isFermeture) {

            if ($record->date_ouverture_raf !== null) {
                return response()->json([
                    'message' => "La caisse RAF a déjà été ouverte pour cette date."
                ], 409);
            }

            $record->update([
                'date_ouverture_raf' => now(),
                'status_raf' => 1,
            ]);

            return response()->json([
                'message' => 'Ouverture RAF enregistrée.',
                'type' => 'ouverture_raf',
                'controlleur_caisse' => $record->fresh(),
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | 🔴 FERMETURE RAF + ENVOI SAGE X3
    |--------------------------------------------------------------------------
    | Cette section gère la clôture de la session RAF pour une caisse donnée.
    | Une fois la clôture validée, un job asynchrone est dispatché pour envoyer
    | les données de paiement vers l'ERP Sage X3.
    */
        if ($isFermeture) {

            if (!$record->date_ouverture_raf) {
                return response()->json([
                    'message' => "Aucune session RAF ouverte à fermer pour cette date."
                ], 409);
            }

            if ($record->date_fermeture_raf) {
                return response()->json([
                    'message' => "Cette session RAF est déjà fermée."
                ], 409);
            }

            // Total des paiements de la caisse pour la date : rien à envoyer à X3 si 0
            $totalPaiements = (float) Paiement::whereDate('created_at', $dateOperation)
                ->where('caisse_id', $request->id_caisse)
                ->sum('montant');

            DB::beginTransaction();

            try {

                if ($totalPaiements > 0) {
                    // 🚀 Dispatch JOB X3 (asynchrone, retry, logs)
                    SendPaiementsToX3Job::dispatch(
                        $dateOperation,
                        $request->id_caisse
                    );
                } else {
                    // Aucun paiement (ou montant total à 0) : on ferme sans déverser 0 dans X3
                    Log::info('Fermeture RAF : aucun paiement, envoi Sage X3 ignoré.', [
                        'caisse_id' => $request->id_caisse,
                        'date_operation' => $dateOperation,
                    ]);
                }

                // ✅ Clôture RAF
                $record->update([
                    'montant_controlleur' => $request->montant_controlleur,
                    'date_fermeture_raf' => now(),
                    'status_raf' => 0,
                ]);

                DB::commit();

                return response()->json([
                    'message' => $totalPaiements > 0
                        ? 'Fermeture RAF enregistrée. Envoi vers Sage X3 lancé.'
                        : 'Fermeture RAF enregistrée. Aucun montant à envoyer vers Sage X3.',
                    'type' => 'fermeture_raf',
                    'controlleur_caisse' => $record->fresh(),
                ]);
            } catch (\Throwable $e) {

                DB::rollBack();

                return response()->json([
                    'message' => 'Erreur lors de la fermeture RAF.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json(['message' => 'Action invalide.'], 400);
    }



    public function getRafPaiementDataStat(Request $request)
    {
        $caisseId = $request->query('caisse_id');
        $siteId = $request->query('site_id');
        $date = $request->query('date', now()->toDateString());

        $query = \App\Models\Paiement::query()
            ->whereDate('created_at', $date);

        // Filtre par caisse si elle est choisie
        if (!empty($caisseId)) {
            $query->where('caisse_id', $caisseId);
        }
        // Sinon, filtre par site si un site est sélectionné
        elseif (!empty($siteId)) {
            $caisseIds = Caisse::query()
                ->where('site_id', $siteId)
                ->pluck('id');

            $query->whereIn('caisse_id', $caisseIds);
        }

        $paiements = $query->with([
            'dossier.r_dossier_vehicule',
            'dossier.r_dossier_user',
            'dossier.r_dossier_client'
        ])->get();

        return response()->json($paiements);
    }
}
