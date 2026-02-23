<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Entite;
use App\Models\Paiement;
use App\Models\Service;
use App\Models\Vehicule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

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
            $montant_dossier_taxe = $montant_dossier_ht * 0.18;
            $montant_dossier_ttc = $montant_dossier_ht + $montant_dossier_taxe;

            // Pour dossier lié
            $montant_dossier_lier_ht = 0;
            if (!empty($validated['detailTypeServices_lier'])) {
                foreach ($validated['detailTypeServices_lier'] as $item) {
                    $montant_dossier_lier_ht += $item['montant'];
                }
            }
            //gère ici has_changement_plaque
            $montant_dossier_lier_taxe = $montant_dossier_lier_ht * 0.18;
            $montant_dossier_lier_ttc = $montant_dossier_lier_ht + $montant_dossier_lier_taxe;


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
                'message' => 'Paiements enregistrés avec taxe 18%.',
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
        ]);

        // 2️⃣ Récupération du dossier principal
        $dossier = Dossier::where('num_chrono', $validated['chrono'])->first();
        if (!$dossier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dossier introuvable.',
            ], 404);
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
                //si nb_plaque == 1 
                $changementPlaqueData = null;
                if ($nb_plaque == 1) {
                    $changementPlaqueData = DB::table('autre_facturation')
                        ->where('id', 2) // ID fixe pour "Changement de plaque"
                        ->where('status', 1) // Actif
                        ->first();
                } else {
                    $changementPlaqueData = DB::table('autre_facturation')
                        ->where('id', 1) // ID fixe pour "Changement de plaque"
                        ->where('status', 1) // Actif
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
                    'montant' => (int)$changementPlaqueData->montant, //convertir en int si besoin
                    "id_entite" => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'id_service' => $dossier->id_service,
                    "id_type_services" => 13,
                    "element_facturation" => "Changement de plaques",
                ];
            }

            $montant_dossier_taxe = $montant_dossier_ht * 0.18;
            $montant_dossier_ttc = $montant_dossier_ht + $montant_dossier_taxe;
            // dd($montant_dossier_ttc, $detailServicesPrincipal);

            // Pour dossier lié (sans changement de plaque)
            $montant_dossier_lier_ht = 0;
            if (!empty($validated['detailTypeServices_lier'])) {
                foreach ($validated['detailTypeServices_lier'] as $item) {
                    $montant_dossier_lier_ht += $item['montant'];
                }
            }

            $montant_dossier_lier_taxe = $montant_dossier_lier_ht * 0.18;
            $montant_dossier_lier_ttc = $montant_dossier_lier_ht + $montant_dossier_lier_taxe;


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

            return response()->json([
                'status' => 'success',
                'message' => 'Paiements enregistrés avec succès.',
                'dossier' => $dossier,
                'montant_dossier_ht' => $montant_dossier_ht,
                'montant_dossier_ttc' => $montant_dossier_ttc,
                'montant_dossier_lier_ht' => $montant_dossier_lier_ht,
                'montant_dossier_lier_ttc' => $montant_dossier_lier_ttc,
                // 'changement_plaque' => $changementPlaqueData ? [
                //     'nom' => $changementPlaqueData->nom_type_service,
                //     'montant_ht' => $changementPlaqueData->montant,
                //     'montant_ttc' => $changementPlaqueData->montant * 1.18,
                // ] : null,
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
            ->whereIn('nom_type_service', $details)
            ->pluck('id');

        $detailTypeServices = DB::table('detail_type_services')
            ->whereIn('id_type_services', $ids)
            ->where('id_site', getIdSite())
            ->get();


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
                    ->whereIn('nom_type_service', $details_lier)
                    ->pluck('id');

                $detailTypeServices_lier = DB::table('detail_type_services')
                    ->whereIn('id_type_services', $ids_lier)
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
        //si nb_plaque == 1 
        $autre_facturation = null;
        if ($nb_plaque == 1) {
            $autre_facturation = DB::table('autre_facturation')
                ->where('id', 2) // ID fixe pour "Changement de plaque"
                ->where('status', 1) // Actif
                ->first();
        } else {
            $autre_facturation = DB::table('autre_facturation')
                ->where('id', 1) // ID fixe pour "Changement de plaque"
                ->where('status', 1) // Actif
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ Retour vers Inertia
        |--------------------------------------------------------------------------
        */

        return inertia('Caisse/components/Receipt', [
            'chrono'                     => $chrono,
            'dossier'                    => $dossier,
            'detailTypeServices'         => $detailTypeServices,
            'caissier'                   => $caissier,

            // --- Dossier lié ---
            'dossier_lier'               => $dossier_lier,
            'detailTypeServices_lier'    => $detailTypeServices_lier,

            'autre_facturation' => $autre_facturation
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

        $paiements = $query->get();

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
}
