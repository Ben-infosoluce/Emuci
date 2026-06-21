<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Entite;
use App\Models\Paiement;
use App\Models\Service;
use App\Models\Vehicule;
use App\Models\ReplicaPrimo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Cast\Double;

class PaiementController extends Controller
{

    //update statut paiement
    public function updateStatutPaiements(Request $request)
    {
        // dd(getIdCaisse());
        // 1️⃣ Validation
        $validated = $request->validate([
            'chrono' => 'required|string',
            'chrono_lier' => 'nullable|string',
            'statut_paiement' => 'required|integer|in:1,2,3',
            'montant_total' => 'nullable|numeric',
            'caisse_ouverture_id' => 'nullable|integer',
            'detailTypeServices' => 'nullable|array',
            'detailTypeServices_lier' => 'nullable|array',
        ]);

        // 2️⃣ Récupération du dossier principal
        $dossier = Dossier::where('num_chrono', $validated['chrono'])->first();
        if (!$dossier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dossier introuvable.',
            ], 404);
        }

        // Vérifier si le dossier est déjà payé
        if ($dossier->statut_paiement == 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce dossier a déjà été payé.',
            ], 422);
        }

        // 3️⃣ Vérification du rôle
        if (Auth::user()->id_role != 4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous n’êtes pas autorisé à valider ce paiement.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            /*
             |--------------------------------------------------------------------------
             | CALCUL DES MONTANTS + TAXE 18%
             |--------------------------------------------------------------------------
             */

            // Pour dossier principal
            $montant_dossier_ht = 0;
            if (!empty($validated['detailTypeServices'])) {
                foreach ($validated['detailTypeServices'] as $item) {
                    $montant_dossier_ht += $item['montant'];
                }
            }
            // Suppression de la TVA
            $montant_dossier_taxe = 0;
            $montant_dossier_ttc = $montant_dossier_ht + 100; // Ajout du Timbre de 100F

            // Pour dossier lié
            $montant_dossier_lier_ht = 0;
            if (!empty($validated['detailTypeServices_lier'])) {
                foreach ($validated['detailTypeServices_lier'] as $item) {
                    $montant_dossier_lier_ht += $item['montant'];
                }
            }
            //gère ici has_changement_plaque, suppression TVA
            $montant_dossier_lier_taxe = 0;
            $montant_dossier_lier_ttc = $montant_dossier_lier_ht + 100; // Ajout du Timbre de 100F


            /*
             |--------------------------------------------------------------------------
             | GESTION DU DOSSIER LIÉ
             |--------------------------------------------------------------------------
             */
            if (!empty($validated['chrono_lier'])) {

                $dossier_lier = Dossier::where('num_chrono', $validated['chrono_lier'])->first();

                if (!$dossier_lier) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Dossier lié introuvable.',
                    ], 404);
                }

                // Mise à jour du dossier lié
                $dossier_lier->update([
                    'statut_paiement' => $validated['statut_paiement'],
                    'paiement_validated_by' => Auth::id(),
                    'date_paiement' => now(),
                ]);

                // Paiement dossier lié : montant TTC
                DB::table('paiements')->insert([
                    'id_dossier' => $dossier_lier->id,
                    'montant' => $montant_dossier_lier_ttc,
                    'mode_paiement' => 'Cash',
                    'id_service' => $dossier_lier->id_service,
                    'id_vehicule' => $dossier_lier->id_vehicule,
                    'user_id' => Auth::id(),
                    'description' => json_encode($validated['detailTypeServices_lier']),
                    'reference' => 'PAI' . strtoupper(uniqid()),
                    'caisse_ouverture_id' => $validated['caisse_ouverture_id'],
                    'id_site' => getIdSite(),
                    'caisse_id' => getIdCaisse(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }


            /*
             |--------------------------------------------------------------------------
             | GESTION DU DOSSIER PRINCIPAL
             |--------------------------------------------------------------------------
             */

            $dossier->update([
                'statut_paiement' => $validated['statut_paiement'],
                'paiement_validated_by' => Auth::id(),
                'date_paiement' => now(),
            ]);

            // Paiement dossier principal : montant TTC
            DB::table('paiements')->insert([
                'id_dossier' => $dossier->id,
                'montant' => $montant_dossier_ttc,
                'mode_paiement' => 'Cash',
                'id_service' => $dossier->id_service,
                'id_vehicule' => $dossier->id_vehicule,
                'user_id' => Auth::id(),
                'description' => json_encode($validated['detailTypeServices']),
                'reference' => 'PAI' . strtoupper(uniqid()),
                'caisse_ouverture_id' => $validated['caisse_ouverture_id'],
                'id_site' => getIdSite(),
                'caisse_id' => getIdCaisse(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Paiements enregistrés avec timbre de 100F.',
                'dossier' => $dossier,
                'montant_dossier_ht' => $montant_dossier_ht,
                'montant_dossier_ttc' => $montant_dossier_ttc,
                'montant_dossier_lier_ht' => $montant_dossier_lier_ht,
                'montant_dossier_lier_ttc' => $montant_dossier_lier_ttc,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du paiement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    //update statut paiement with changement de plaque
    public function updateStatutPaiement(Request $request)
    {
        // dd($request->all());
        // 1️⃣ Validation
        $validated = $request->validate([
            'chrono' => 'required|string',
            'chrono_lier' => 'nullable|string',
            'statut_paiement' => 'required|integer|in:1,2,3',
            'montant_total' => 'nullable|numeric',
            'caisse_ouverture_id' => 'nullable|integer',
            'detailTypeServices' => 'nullable|array',
            'detailTypeServices_lier' => 'nullable|array',
            'has_changement_plaque' => 'nullable|boolean',
            // Demandeur info
            'demandeur_nom' => 'nullable|string',
            'demandeur_prenom' => 'nullable|string',
            'demandeur_telephone' => 'nullable|string',
            'demandeur_type_piece' => 'nullable|string',
            'demandeur_numero_piece' => 'nullable|string',
        ]);

        // 2️⃣ Récupération du dossier principal
        $dossier = Dossier::where('num_chrono', $validated['chrono'])->first();
        if (!$dossier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dossier introuvable.',
            ], 404);
        }

        // Vérifier si le dossier est déjà payé
        if ($dossier->statut_paiement == 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce dossier a déjà été payé.',
            ], 422);
        }

        // 3️⃣ Vérification du rôle
        if (Auth::user()->id_role != 4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous n\'êtes pas autorisé à valider ce paiement.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            /*
             |--------------------------------------------------------------------------
             | RECUPERATION CHANGEMENT DE PLAQUE (si applicable)
             |--------------------------------------------------------------------------
             */
            $changementPlaqueData = null;
            $hasChangementPlaque = $validated['has_changement_plaque'] ?? false;

            if ($hasChangementPlaque) {
                // Récupération depuis la table autre_facturation (ID 20 = Changement de plaque)
                //récupéérer de nb_plaque 
                $nb_plaque = $dossier->r_dossier_vehicule->nb_plaque;
                $genre = $dossier->r_dossier_vehicule->genre_vehicule;
                //si nb_plaque == 1 
                // dd($genre, $nb_plaque);
                $changementPlaqueData = null;
                if (stripos($genre, "REMORQUE") !== false) {
                    $changementPlaqueData = DB::table('autre_facturation')
                        ->where('id', 3) // ID pour "REMORQUE"
                        ->where('status', 1)
                        ->first();
                } else if ($nb_plaque == 1) {
                    $changementPlaqueData = DB::table('autre_facturation')
                        ->where('id', 2) // ID pour "1 plaque et non REMORQUE"
                        ->where('status', 1)
                        ->first();
                } else {
                    $changementPlaqueData = DB::table('autre_facturation')
                        ->where('id', 1) // ID par défaut
                        ->where('status', 1)
                        ->first();
                }


                if (!$changementPlaqueData) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Configuration "Changement de plaque" introuvable dans la table de facturation.',
                    ], 404);
                }
            }

            /*
             |--------------------------------------------------------------------------
             | CALCUL DES MONTANTS + TAXE 18%
             |--------------------------------------------------------------------------
             */
            // dd($validated['detailTypeServices'], $changementPlaqueData);

            // Pour dossier principal
            $montant_dossier_ht = 0;
            $detailServicesPrincipal = $validated['detailTypeServices'] ?? [];

            if (!empty($detailServicesPrincipal)) {
                foreach ($detailServicesPrincipal as $item) {
                    $montant_dossier_ht += $item['montant'];
                }
            }

            // dd($montant_dossier_ht, $detailServicesPrincipal);

            // Ajout du montant changement de plaque au total HT
            if ($changementPlaqueData) {
                $montant_dossier_ht += $changementPlaqueData->montant;

                // Ajout dans le détail pour la description JSON uniquement
                $detailServicesPrincipal[] = [
                    'id' => $changementPlaqueData->id,
                    "statut" => 1,
                    "id_site" => 2,
                    'montant' => (float) $changementPlaqueData->montant, //convertir en int si besoin
                    "id_entite" => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'id_service' => $dossier->id_service,
                    "id_type_services" => 13,
                    "element_facturation" => "Changement de plaques",
                ];
            }

            $montant_dossier_taxe = 0;
            $montant_dossier_ttc = $montant_dossier_ht + 100; // Ajout Timbre
            // dd($montant_dossier_ttc, $detailServicesPrincipal);

            // Pour dossier lié (sans changement de plaque)
            $montant_dossier_lier_ht = 0;
            if (!empty($validated['detailTypeServices_lier'])) {
                foreach ($validated['detailTypeServices_lier'] as $item) {
                    $montant_dossier_lier_ht += $item['montant'];
                }
            }

            $montant_dossier_lier_taxe = 0;
            $montant_dossier_lier_ttc = $montant_dossier_lier_ht + 100; // Ajout Timbre


            /*
             |--------------------------------------------------------------------------
             | GESTION DU DOSSIER LIÉ
             |--------------------------------------------------------------------------
             */
            if (!empty($validated['chrono_lier'])) {

                $dossier_lier = Dossier::where('num_chrono', $validated['chrono_lier'])->first();

                if (!$dossier_lier) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Dossier lié introuvable.',
                    ], 404);
                }

                // Mise à jour du dossier lié
                $dossier_lier->update([
                    'statut_paiement' => $validated['statut_paiement'],
                    'paiement_validated_by' => Auth::id(),
                    'date_paiement' => now(),
                ]);

                // Paiement dossier lié (sans changement de plaque dans la description)
                DB::table('paiements')->insert([
                    'id_dossier' => $dossier_lier->id,
                    'montant' => $montant_dossier_lier_ttc,
                    'mode_paiement' => 'Cash',
                    'id_service' => $dossier_lier->id_service,
                    'id_vehicule' => $dossier_lier->id_vehicule,
                    'user_id' => Auth::id(),
                    'description' => json_encode($validated['detailTypeServices_lier'] ?? []),
                    'reference' => 'PAI' . strtoupper(uniqid()),
                    'caisse_ouverture_id' => $validated['caisse_ouverture_id'],
                    'id_site' => getIdSite(),
                    'caisse_id' => getIdCaisse(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }


            /*
             |--------------------------------------------------------------------------
             | GESTION DU DOSSIER PRINCIPAL
             |--------------------------------------------------------------------------
             */

            $dossier->update([
                'statut_paiement' => $validated['statut_paiement'],
                'paiement_validated_by' => Auth::id(),
                'date_paiement' => now(),
                'id_site' => ($dossier->type == 'FDS') ? getIdSite() : $dossier->id_site,
                'demandeur_nom' => $validated['demandeur_nom'],
                'demandeur_prenom' => $validated['demandeur_prenom'],
                'demandeur_telephone' => $validated['demandeur_telephone'],
                'demandeur_type_piece' => $validated['demandeur_type_piece'],
                'demandeur_numero_piece' => $validated['demandeur_numero_piece'],
            ]);

            // Paiement dossier principal : montant TTC (avec changement de plaque inclus dans montant ET description)
            DB::table('paiements')->insert([
                'id_dossier' => $dossier->id,
                'montant' => $montant_dossier_ttc,
                'mode_paiement' => 'Cash',
                'id_service' => $dossier->id_service,
                'id_vehicule' => $dossier->id_vehicule,
                'user_id' => Auth::id(),
                'description' => json_encode($detailServicesPrincipal), // Contient maintenant le changement de plaque
                'reference' => 'PAI' . strtoupper(uniqid()),
                'caisse_ouverture_id' => $validated['caisse_ouverture_id'],
                'id_site' => getIdSite(),
                'caisse_id' => getIdCaisse(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du paiement.',
                'error' => $e->getMessage(),
            ], 500);
        }

        // --- APPELS EXTERNES (hors transaction) ---
        // Exécutés après le commit : une erreur ici ne rollback pas le paiement.

        // update numérisation site_id uniquement pour FDS
        if ($dossier->type == 'FDS') {
            updateFdsSite($dossier->num_chrono, getIdSite());
        }

        // --- NOTIFICATION EXTERNE DU PAIEMENT ---
        $this->notifierPaiementExterne($dossier->num_chrono);

        return response()->json([
            'status' => 'success',
            'message' => 'Paiements enregistrés avec succès.',
            'dossier' => $dossier,
            'montant_dossier_ht' => $montant_dossier_ht,
            'montant_dossier_ttc' => $montant_dossier_ttc,
            'montant_dossier_lier_ht' => $montant_dossier_lier_ht,
            'montant_dossier_lier_ttc' => $montant_dossier_lier_ttc,
        ]);
    }

    //generate receipt
    public function recu($id)
    {
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->findOrFail($id);

        return inertia('Caisse/components/Facture', [
            'chrono' => $dossier->num_chrono,
            'dossier' => $dossier,
        ]);
    }


    public function receipt($chrono)
    {
        // 1️⃣ Récupérer le dossier principal
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->where('num_chrono', $chrono)->first();

        if (!$dossier) {
            abort(404, 'Dossier non trouvé');
        }

        /*
         |--------------------------------------------------------------------------
         | 2️⃣ Récupérer les services du dossier principal
         |--------------------------------------------------------------------------
         */
        $details = json_decode($dossier->detail); // tableau de noms

        $ids = DB::table('type_services')
            ->whereIn('id', $details)
            ->pluck('id');

        if ($dossier->type == 'FDS') {
            $detailTypeServices = DB::table('detail_type_services')
                ->whereIn('id', $details)
                // ->where('id_site', getIdSite())
                ->get();
        } else {
            $detailTypeServices = DB::table('detail_type_services')
                ->whereIn('id', $details)
                ->where('id_site', getIdSite())
                ->get();
        }


        /*
         |--------------------------------------------------------------------------
         | 3️⃣ Récupérer le dossier lié via id_dossier_lier
         |--------------------------------------------------------------------------
         */

        $dossier_lier = null;
        $detailTypeServices_lier = null;
        $caissier_lier = null;

        if (!empty($dossier->id_dossier_lier)) {

            // 🔥 On récupère directement via ID
            $dossier_lier = Dossier::with([
                'r_dossier_vehicule',
                'r_dossier_user',
                'r_dossier_client',
                'r_dossier_documents',
                'r_dossier_services',
                'r_dossier_services.r_service_types',
                'r_dossier_transactions'
            ])->where('id', $dossier->id_dossier_lier)->first();

            if ($dossier_lier) {

                // Récupérer ses services
                $details_lier = json_decode($dossier_lier->detail);

                $ids_lier = DB::table('type_services')
                    ->whereIn('id', $details_lier)
                    ->pluck('id');

                $detailTypeServices_lier = DB::table('detail_type_services')
                    ->whereIn('id', $details_lier)
                    ->where('id_site', getIdSite())
                    ->get();
            }
        }


        /*
         |--------------------------------------------------------------------------
         | 4️⃣ Caissier du dossier principal
         |--------------------------------------------------------------------------
         */
        $caissier = DB::table('users')
            ->select('id', 'nom', 'prenom', 'id_site')
            ->where('id', $dossier->paiement_validated_by)
            ->first();

        //récupéérer de nb_plaque 
        $nb_plaque = $dossier->r_dossier_vehicule->nb_plaque;
        $genre = $dossier->r_dossier_vehicule->genre_vehicule;
        //si nb_plaque == 1 
        $autre_facturation = null;
        if ($dossier->id_service != '4') {
            if (stripos($genre, "REMORQUE") !== false) {
                $autre_facturation = DB::table('autre_facturation')
                    ->where('id', 3) // ID pour "REMORQUE"
                    ->where('status', 1)
                    ->first();
            } else if ($nb_plaque == 1) {
                $autre_facturation = DB::table('autre_facturation')
                    ->where('id', 2) // ID pour "1 plaque et non REMORQUE"
                    ->where('status', 1)
                    ->first();
            } else {
                $autre_facturation = DB::table('autre_facturation')
                    ->where('id', 1) // ID par défaut
                    ->where('status', 1)
                    ->first();
            }
        }

        // dd($autre_facturation);

        // ── RELICA-PRIMO : récupérer mt_total_cil depuis la table relica_primo ──
        $relicaPrimo = null;
        if ($dossier->type === 'RELICA-PRIMO') {
            $relicaPrimo = ReplicaPrimo::where('chrono', $dossier->num_chrono)->first();
        }

        /*
         |--------------------------------------------------------------------------
         | 5️⃣ Retour vers Inertia
         |--------------------------------------------------------------------------
         */

        return inertia('Caisse/components/Receipt', [
            'chrono' => $chrono,
            'dossier' => $dossier,
            'detailTypeServices' => $detailTypeServices,
            'caissier' => $caissier,

            // --- Dossier lié ---
            'dossier_lier' => $dossier_lier,
            'detailTypeServices_lier' => $detailTypeServices_lier,

            'autre_facturation' => $autre_facturation,
            'relicaPrimo' => $relicaPrimo,
        ]);
    }


    //get paiement data for dashboard
    public function getPaiementDataForStat(Request $request)
    {
        $date = $request->query('date'); // ex: "2025-10-15"

        // 🔹 Récupération automatique de la caisse
        $caisseId = getIdCaisse();

        $query = Paiement::query();

        // Filtre par date
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        // Filtre par caisse (obligatoire maintenant)
        if ($caisseId) {
            $query->where('caisse_id', $caisseId);
        } else {
            return response()->json([
                'message' => 'Aucune caisse active trouvée pour cet utilisateur'
            ], 403);
        }

        $paiements = $query->with([
            'dossier.r_dossier_vehicule',
            'dossier.r_dossier_user',
            'dossier.r_dossier_client'
        ])->get();

        return response()->json($paiements);
    }


    public function getPaiementData(Request $request)
    {
        $query = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ]);

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
            )
        ]);
    }


    public function getActiveEntites()
    {
        $entites = Entite::where('statut', 1)
            ->select('id', 'nom')
            ->get();
        return response()->json($entites);
    }

    /**
     * Enregistre un paiement en espèces pour PRIMO-ESPECE
     */
    public function enregistrerPaiementEspece(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'NUMCHRONOCIL' => 'required|string',
            'NUMEROCHASISIS' => 'required|string',
            'IDENTITE_CLIENT' => 'required|string',
            'MONTANT_TOTAL' => 'required|numeric|min:0',
            'DATE_PAIEMENT' => 'required|date',
            'DEVISE' => 'required|string|size:3',
            'MODE_PAIEMENT' => 'required|string',
            'REFERENCE_PAIEMENT' => 'required|string',
            'OBSERVATIONS' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Vérifier si le dossier existe
            $dossier = Dossier::where('num_chrono', $validated['NUMCHRONOCIL'])->first();
            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé'
                ], 404);
            }

            // Vérifier si le dossier est déjà payé
            if ($dossier->statut_paiement == 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce dossier a déjà été payé'
                ], 422);
            }

            // Mettre à jour le statut de paiement du dossier
            $dossier->update([
                'statut_paiement' => 2, // Statut payé
                'paiement_validated_by' => 1, // ID de l'utilisateur système
                'date_paiement' => now()
            ]);

            // Enregistrer le paiement
            DB::table('paiements')->insert([
                'id_dossier' => $dossier->id,
                'montant' => $validated['MONTANT_TOTAL'],
                'mode_paiement' => 'ESPECES',
                'id_service' => $dossier->id_service,
                'id_vehicule' => $dossier->id_vehicule,
                'user_id' => 1, // ID de l'utilisateur système
                'description' => json_encode([
                    'reference' => $validated['REFERENCE_PAIEMENT'],
                    'observations' => $validated['OBSERVATIONS'] ?? '',
                    'flux' => 'PRIMO-ESPECE'
                ]),
                'reference' => $validated['REFERENCE_PAIEMENT'],
                'caisse_ouverture_id' => null, // À adapter selon la gestion de caisse
                'id_site' => $dossier->id_site,
                'caisse_id' => null, // À adapter selon la gestion de caisse
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // Notifier le système externe
            $this->notifierPaiementExterne($dossier->num_chrono);

            return response()->json([
                'success' => true,
                'message' => 'Paiement en espèces enregistré avec succès',
                'dossier' => $dossier->num_chrono,
                'montant' => $validated['MONTANT_TOTAL'],
                'reference' => $validated['REFERENCE_PAIEMENT']
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Erreur lors de l'enregistrement du paiement PRIMO-ESPECE: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function notifierPaiementExterne($chrono)
    {
        try {
            // En mode local uniquement pour le moment
            // $loginUrl = 'https://demo.dgttc.ci/recensementww/API/Auth/login.php';
            // $notifyUrl = 'https://demo.dgttc.ci/recensementww/API/notification_paiement_caisse_emuci.php';
            // $email = 'userinterco@dgttc.ci';
            // $password = 'interconnection@auth';

            // //PRODUCTION
            $loginUrl = 'https://recensementww.dgttc.ci/API/Auth/login.php';
            $notifyUrl = 'https://recensementww.dgttc.ci/API/notification_paiement_caisse_emuci.php';
            $email = 'userinterco_prod@dgttc.ci';
            $password = 'interconnection@auth';

            \Illuminate\Support\Facades\Log::info("[notifierPaiementExterne] Début - chrono: {$chrono}");

            $authResponse = \Illuminate\Support\Facades\Http::post($loginUrl, [
                'email' => $email,
                'password' => $password,
            ]);

            \Illuminate\Support\Facades\Log::info("[notifierPaiementExterne] Réponse login", [
                'status' => $authResponse->status(),
                'body' => $authResponse->body(),
            ]);

            $token = $authResponse->json('token');

            if (!$token) {
                \Illuminate\Support\Facades\Log::error("[notifierPaiementExterne] Token absent ou null - impossible d'envoyer la notification", [
                    'chrono' => $chrono,
                    'login_status' => $authResponse->status(),
                    'login_body' => $authResponse->json(),
                ]);
                return;
            }

            \Illuminate\Support\Facades\Log::info("[notifierPaiementExterne] Token obtenu, envoi de la notification pour chrono: {$chrono}");

            $notifyResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'X-Token' => $token,
                'Content-Type' => 'application/json'
            ])->post($notifyUrl, [
                'numChronoCil' => $chrono
            ]);

            \Illuminate\Support\Facades\Log::info("[notifierPaiementExterne] Réponse notification", [
                'chrono' => $chrono,
                'status' => $notifyResponse->status(),
                'body' => $notifyResponse->json(),
            ]);

            if (!$notifyResponse->successful()) {
                \Illuminate\Support\Facades\Log::error("[notifierPaiementExterne] Échec de la notification externe", [
                    'chrono' => $chrono,
                    'status' => $notifyResponse->status(),
                    'body' => $notifyResponse->body(),
                ]);
            } else {
                \Illuminate\Support\Facades\Log::info("[notifierPaiementExterne] Notification envoyée avec succès pour le chrono: {$chrono}");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("[notifierPaiementExterne] Exception: " . $e->getMessage(), [
                'chrono' => $chrono,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
