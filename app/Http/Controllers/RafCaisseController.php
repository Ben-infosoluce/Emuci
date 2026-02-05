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
use App\Jobs\SendPaiementsToX3Job;



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
        $date = $request->query('date', now()->toDateString());

        // 1) Session RAF NON FERMÉE (status_raf = 1) pour cette caisse
        $ouvertureRafNonFermee = ControlleurCaisse::where('caisse_id', $caisseId)
            ->whereNotNull('date_ouverture_raf')
            ->where('status_raf', 1)
            ->orderByDesc('date_ouverture_raf')
            ->first();

        // 2) Ouverture RAF pour la date cible
        $ouvertureRafDuJour = ControlleurCaisse::where('caisse_id', $caisseId)
            ->whereDate('date', $date)
            ->whereNotNull('date_ouverture_raf')
            ->first();

        return response()->json([
            'caisse_id' => $caisseId,
            'date_cible' => $date,
            'ouverture_raf_du_jour' => $ouvertureRafDuJour,
            'ouverture_raf_non_fermee' => $ouvertureRafNonFermee,
            'status_raf' => $ouvertureRafNonFermee ? 'ouverte' : 'fermée',
        ]);
    }






    public function validateRafControlleurMontant(Request $request)
    {
        // dd($request->all());
        // ✅ Validation
        $request->validate([
            'id_caisse' => 'required|integer|exists:caisse_ouvertures,id',
            'montant_controlleur' => 'nullable|numeric|min:0',
            'is_fermeture' => 'required|boolean',
            'code_caisse' => 'nullable|string',
            'id_site' => 'nullable|integer',
            'date_operation' => 'required|date',
        ]);

        // ✅ Vérifier la caisse
        $caisseOuverture = CaisseOuverture::find($request->id_caisse);
        if (!$caisseOuverture) {
            return response()->json(['message' => 'Caisse introuvable.'], 404);
        }

        // ✅ Vérification date (J-5)
        $dateOperation = Carbon::parse($request->date_operation)->toDateString();
        $today = Carbon::now()->toDateString();
        $fiveDaysAgo = Carbon::now()->subDays(5)->toDateString();

        if ($dateOperation < $fiveDaysAgo || $dateOperation > $today) {
            return response()->json([
                'message' => "La date de l'opération doit être comprise dans les 5 derniers jours."
            ], 400);
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

            DB::beginTransaction();

            try {
                // 🚀 Dispatch JOB X3 (asynchrone, retry, logs)
                SendPaiementsToX3Job::dispatch(
                    $dateOperation,
                    $request->id_caisse
                );

                // ✅ Clôture RAF
                $record->update([
                    'montant_controlleur' => $request->montant_controlleur,
                    'date_fermeture_raf' => now(),
                    'status_raf' => 0,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Fermeture RAF enregistrée. Envoi vers Sage X3 lancé.',
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
        $date = $request->query('date', now()->toDateString());

        $query = DB::table('paiements')
            ->whereDate('created_at', $date);

        // Si caisseId existe, on applique le filtre
        if (!empty($caisseId)) {
            $query->where('caisse_id', $caisseId);
        }

        return response()->json($query->get());
    }
}
