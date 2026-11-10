<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\DemandeModification;
use App\Models\Entreprise;
use App\Support\ModificationFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DemandeModificationController extends Controller
{
    /**
     * Enregistre une demande de modification depuis la caisse.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dossier_id' => 'required|integer|exists:dossiers,id',
            'champs_vehicule' => 'nullable|array',
            'champs_client' => 'nullable|array',
            'champs_entreprise' => 'nullable|array',
            'message' => 'nullable|string|max:2000',
        ]);

        $champsVehicule = ModificationFields::cleanKeys(
            ModificationFields::SECTION_VEHICULE,
            $validated['champs_vehicule'] ?? []
        );
        $champsClient = ModificationFields::cleanKeys(
            ModificationFields::SECTION_CLIENT,
            $validated['champs_client'] ?? []
        );
        $champsEntreprise = ModificationFields::cleanKeys(
            ModificationFields::SECTION_ENTREPRISE,
            $validated['champs_entreprise'] ?? []
        );

        if (empty($champsVehicule) && empty($champsClient) && empty($champsEntreprise)) {
            return response()->json([
                'message' => 'Veuillez sélectionner au moins un champ à modifier.',
            ], 422);
        }

        $demande = DemandeModification::create([
            'dossier_id' => $validated['dossier_id'],
            'user_id' => getConnectedUserId(),
            'site_id' => getIdSite(),
            'statut' => 1,
            'champs_vehicule' => $champsVehicule,
            'champs_client' => $champsClient,
            'champs_entreprise' => $champsEntreprise,
            'message' => $validated['message'] ?? null,
        ]);

        return response()->json([
            'message' => 'Demande de modification enregistrée avec succès.',
            'demande' => $demande,
        ], 201);
    }

    /**
     * Page d'administration listant les demandes de modification.
     */
    public function indexAdmin()
    {
        return Inertia::render('Admin/DemandesModification/Index');
    }

    /**
     * Liste paginée des demandes pour l'administration.
     */
    public function dataAdmin(Request $request)
    {
        $query = DemandeModification::with([
            'dossier.r_dossier_vehicule',
            'dossier.r_dossier_client',
            'user',
            'site',
        ]);

        $search = trim((string) $request->input('search_data'));

        if ($search !== '') {
            $query->whereHas('dossier', function ($dossierQuery) use ($search) {
                $dossierQuery->where('num_chrono', 'like', "%{$search}%")
                    ->orWhereHas('r_dossier_vehicule', function ($vehiculeQuery) use ($search) {
                        $vehiculeQuery->where('vin', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', (int) $request->input('statut'));
        }

        $demandes = $query->latest('id')
            ->paginate((int) $request->input('filtre_per_page', 10))
            ->through(function (DemandeModification $demande) {
                return [
                    'id' => $demande->id,
                    'statut' => (int) $demande->statut,
                    'statut_label' => $demande->statut_label,
                    'message' => $demande->message,
                    'date_creation' => optional($demande->created_at)->format('d/m/Y H:i'),
                    'vin' => $demande->dossier?->r_dossier_vehicule?->vin,
                    'num_chrono' => $demande->dossier?->num_chrono,
                    'marque' => $demande->dossier?->r_dossier_vehicule?->marque,
                    'client' => trim(($demande->dossier?->r_dossier_client?->nom ?? '') . ' ' . ($demande->dossier?->r_dossier_client?->prenom ?? '')),
                    'demandeur' => trim(($demande->user?->nom ?? '') . ' ' . ($demande->user?->prenom ?? '')) ?: '—',
                    'site' => $demande->site?->nom_site,
                    'nb_champs' => count($demande->champs_vehicule ?? [])
                        + count($demande->champs_client ?? [])
                        + count($demande->champs_entreprise ?? []),
                ];
            });

        return response()->json(['demandes' => $demandes]);
    }

    /**
     * Onglet PDC listant les demandes validées à appliquer.
     */
    public function indexPdc()
    {
        return Inertia::render('Pdc/DemandeModification/Index');
    }

    /**
     * Liste paginée des demandes pour le PDC (validées et appliquées).
     */
    public function dataPdc(Request $request)
    {
        $query = DemandeModification::with([
            'dossier.r_dossier_vehicule',
            'dossier.r_dossier_client',
            'user',
            'traitePar',
        ]);

        $search = trim((string) $request->input('search_data'));

        if ($search !== '') {
            $query->whereHas('dossier', function ($dossierQuery) use ($search) {
                $dossierQuery->where('num_chrono', 'like', "%{$search}%")
                    ->orWhereHas('r_dossier_vehicule', function ($vehiculeQuery) use ($search) {
                        $vehiculeQuery->where('vin', 'like', "%{$search}%");
                    });
            });
        }

        $statut = $request->input('statut');
        if ($request->has('statut')) {
            if ($statut !== null && $statut !== '' && (int) $statut !== 0) {
                $query->where('statut', (int) $statut);
            }
            // statut = 0 : toutes les demandes.
        } else {
            // Par défaut, on affiche les demandes validées à traiter.
            $query->where('statut', 2);
        }

        $demandes = $query->latest('id')
            ->paginate((int) $request->input('filtre_per_page', 10))
            ->through(function (DemandeModification $demande) {
                return [
                    'id' => $demande->id,
                    'dossier_id' => $demande->dossier_id,
                    'statut' => (int) $demande->statut,
                    'statut_label' => $demande->statut_label,
                    'date_validation' => optional($demande->traite_le)->format('d/m/Y H:i'),
                    'date_application' => optional($demande->applique_le)->format('d/m/Y H:i'),
                    'vin' => $demande->dossier?->r_dossier_vehicule?->vin,
                    'num_chrono' => $demande->dossier?->num_chrono,
                    'marque' => $demande->dossier?->r_dossier_vehicule?->marque,
                    'client' => trim(($demande->dossier?->r_dossier_client?->nom ?? '') . ' ' . ($demande->dossier?->r_dossier_client?->prenom ?? '')),
                    'valide_par' => trim(($demande->traitePar?->nom ?? '') . ' ' . ($demande->traitePar?->prenom ?? '')) ?: '—',
                    'nb_champs' => count($demande->champs_vehicule ?? [])
                        + count($demande->champs_client ?? [])
                        + count($demande->champs_entreprise ?? []),
                ];
            });

        return response()->json(['demandes' => $demandes]);
    }

    /**
     * Détail d'une demande pour l'administration.
     */
    public function showAdmin($id)
    {
        $demande = DemandeModification::with([
            'dossier.r_dossier_vehicule.r_vehicule_entreprise',
            'dossier.r_dossier_client',
            'dossier.r_dossier_services.r_service_types',
            'user',
            'site',
            'traitePar',
            'appliquePar',
        ])->findOrFail($id);

        return response()->json([
            'demande' => $demande,
            'sections' => ModificationFields::sections(),
            'entreprise' => $demande->dossier?->r_dossier_vehicule?->r_vehicule_entreprise,
        ]);
    }

    /**
     * Validation de la demande par l'administration. La demande devient
     * éligible à l'action "Modifier" côté PDC.
     */
    public function validerAdmin($id)
    {
        $demande = DemandeModification::findOrFail($id);

        if ((int) $demande->statut === 4) {
            return response()->json(['message' => 'Cette demande a déjà été appliquée.'], 400);
        }

        if ((int) $demande->statut !== 1) {
            return response()->json(['message' => 'Seule une demande en attente peut être validée.'], 400);
        }

        $demande->update([
            'statut' => 2,
            'traite_par' => getConnectedUserId(),
            'traite_le' => now(),
            'motif_rejet' => null,
        ]);

        return response()->json([
            'message' => 'Demande validée avec succès.',
            'demande' => $demande->fresh(),
        ]);
    }

    /**
     * Rejet de la demande par l'administration.
     */
    public function rejeterAdmin(Request $request, $id)
    {
        $validated = $request->validate([
            'motif_rejet' => 'nullable|string|max:2000',
        ]);

        $demande = DemandeModification::findOrFail($id);

        if ((int) $demande->statut !== 1) {
            return response()->json(['message' => 'Seule une demande en attente peut être rejetée.'], 400);
        }

        $demande->update([
            'statut' => 3,
            'traite_par' => getConnectedUserId(),
            'traite_le' => now(),
            'motif_rejet' => $validated['motif_rejet'] ?? null,
        ]);

        return response()->json([
            'message' => 'Demande rejetée.',
            'demande' => $demande->fresh(),
        ]);
    }

    /**
     * Formulaire PDC d'édition des champs concernés par une demande validée.
     */
    public function showEditPdc($dossierId)
    {
        $demande = DemandeModification::with([
            'dossier.r_dossier_vehicule.r_vehicule_entreprise',
            'dossier.r_dossier_client',
            'dossier.r_dossier_services.r_service_types',
        ])
            ->where('dossier_id', $dossierId)
            ->where('statut', 2)
            ->latest('id')
            ->first();

        if (!$demande) {
            abort(404, 'Aucune demande de modification validée pour ce dossier.');
        }

        return Inertia::render('Pdc/DemandeModification/Edit', [
            'demande' => $demande,
            'sections' => ModificationFields::sections(),
            'entreprise' => $demande->dossier?->r_dossier_vehicule?->r_vehicule_entreprise,
            'listeRoute' => $this->listeRouteForService($demande->dossier?->id_service),
        ]);
    }

    /**
     * Applique les modifications validées sur le véhicule, le client et/ou
     * l'entreprise puis marque la demande comme appliquée.
     */
    public function updatePdc(Request $request, $dossierId)
    {
        $demande = DemandeModification::with('dossier.r_dossier_vehicule.r_vehicule_entreprise', 'dossier.r_dossier_client')
            ->where('dossier_id', $dossierId)
            ->where('statut', 2)
            ->latest('id')
            ->first();

        if (!$demande) {
            return response()->json(['message' => 'Aucune demande de modification validée pour ce dossier.'], 404);
        }

        $validated = $request->validate([
            'values' => 'required|array',
        ]);

        $values = $validated['values'];
        $vehicule = $demande->dossier?->r_dossier_vehicule;
        $client = $demande->dossier?->r_dossier_client;

        if (!$vehicule) {
            return response()->json(['message' => 'Les informations du véhicule sont introuvables.'], 422);
        }

        $champsVehicule = ModificationFields::cleanKeys(
            ModificationFields::SECTION_VEHICULE,
            $demande->champs_vehicule ?? []
        );
        $champsClient = ModificationFields::cleanKeys(
            ModificationFields::SECTION_CLIENT,
            $demande->champs_client ?? []
        );
        $champsEntreprise = ModificationFields::cleanKeys(
            ModificationFields::SECTION_ENTREPRISE,
            $demande->champs_entreprise ?? []
        );

        if (!empty($champsClient) && !$client) {
            return response()->json(['message' => 'Les informations du propriétaire sont introuvables.'], 422);
        }

        // Champs obligatoires : on empêche d'enregistrer une valeur vide.
        $obligatoires = [
            ModificationFields::SECTION_VEHICULE => ['vin'],
            ModificationFields::SECTION_CLIENT => ['nom', 'prenom'],
        ];

        foreach ($obligatoires as $section => $keys) {
            $selected = match ($section) {
                ModificationFields::SECTION_VEHICULE => $champsVehicule,
                ModificationFields::SECTION_CLIENT => $champsClient,
            };

            foreach ($keys as $key) {
                if (in_array($key, $selected, true) && trim((string) ($values[$key] ?? '')) === '') {
                    return response()->json([
                        'message' => "Le champ {$key} est obligatoire.",
                        'errors' => [$key => ["Le champ {$key} est obligatoire."]],
                    ], 422);
                }
            }
        }

        $integerFields = ModificationFields::integerFields();

        try {
            DB::beginTransaction();

            /* ------------------------------ Véhicule ------------------------------ */
            $vehiculeData = [];
            foreach ($champsVehicule as $key) {
                if (!array_key_exists($key, $values)) {
                    continue;
                }

                $value = $values[$key];
                if (in_array($key, $integerFields, true)) {
                    $value = ($value === null || $value === '') ? null : (int) $value;
                }

                $vehiculeData[$key] = $value;
            }

            if (!empty($vehiculeData)) {
                LogHelper::enregistrer('vehicule', $vehicule, $vehiculeData, getConnectedUserId());
                $vehicule->update($vehiculeData);
            }

            /* ------------------------------- Client ------------------------------- */
            $clientData = [];
            foreach ($champsClient as $key) {
                if (array_key_exists($key, $values)) {
                    $clientData[$key] = $values[$key];
                }
            }

            if (!empty($clientData)) {
                LogHelper::enregistrer('client', $client, $clientData, getConnectedUserId());
                $client->update($clientData);
            }

            /* ----------------------------- Entreprise ----------------------------- */
            if (!empty($champsEntreprise)) {
                $entreprise = $vehicule->r_vehicule_entreprise;

                $entrepriseData = [];
                foreach ($champsEntreprise as $key) {
                    if (array_key_exists($key, $values)) {
                        $entrepriseData[$key] = $values[$key];
                    }
                }

                if (!$entreprise) {
                    $entreprise = Entreprise::create($entrepriseData);
                    $vehicule->update(['entreprise_id' => $entreprise->id]);
                    if ($client && !$client->entreprise_id) {
                        $client->update(['entreprise_id' => $entreprise->id]);
                    }
                    LogHelper::enregistrer('entreprise', $entreprise, $entrepriseData, getConnectedUserId(), []);
                } else {
                    LogHelper::enregistrer('entreprise', $entreprise, $entrepriseData, getConnectedUserId());
                    $entreprise->update($entrepriseData);
                }
            }

            $demande->update([
                'statut' => 4,
                'applique_par' => getConnectedUserId(),
                'applique_le' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Les modifications ont été enregistrées avec succès.',
                'liste_route' => $this->listeRouteForService($demande->dossier?->id_service),
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'enregistrement des modifications.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Route de listing correspondant au service du dossier.
     */
    private function listeRouteForService($idService): string
    {
        return match ((int) $idService) {
            1 => 'show.pdc.immatriculation',
            2 => 'show.pdc.re.immatriculation',
            3 => 'show.pdc.post.immatriculation',
            4 => 'show.pdc.duplicata',
            default => 'show.pdc.immatriculation',
        };
    }
}
