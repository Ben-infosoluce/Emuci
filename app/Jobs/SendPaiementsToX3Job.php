<?php

namespace App\Jobs;

use App\Models\Paiement;
use App\Services\X3\X3FactureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Job pour l'envoi des paiements vers Sage X3
 * 
 * Ce job est responsable de la récupération des paiements pour une date et une caisse donnée,
 * de leur transformation en factures X3 et de leur envoi vers l'API Sage X3.
 * 
 * Le traitement inclut :
 * - Le regroupement des paiements par dossier
 * - La création d'en-têtes de facture
 * - La création de lignes de facture pour chaque service
 * - La gestion des erreurs et des logs
 */
class SendPaiementsToX3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Nombre de tentatives en cas d'échec */
    public int $tries = 5;

    /** @var int Délai d'expiration du job en secondes */
    public int $timeout = 120;

    /** @var string Date de l'opération au format YYYY-MM-DD */
    protected string $dateOperation;

    /** @var int ID de la caisse concernée */
    protected int $caisseId;

    /**
     * Crée une nouvelle instance du job
     *
     * @param string $dateOperation Date de l'opération au format YYYY-MM-DD
     * @param int $caisseId ID de la caisse concernée
     */
    public function __construct(string $dateOperation, int $caisseId)
    {
        $this->dateOperation = $dateOperation;
        $this->caisseId = $caisseId;
    }

    /**
     * Exécute le job
     *
     * @param X3FactureService $x3 Service pour l'interaction avec l'API Sage X3
     * @return void
     * @throws Exception Si aucun paiement n'est trouvé
     */
    public function handle(X3FactureService $x3)
    {
        // Récupération des paiements pour la date et la caisse spécifiées
        $query = Paiement::with('dossier')
            ->whereDate('created_at', $this->dateOperation);

        if ($this->caisseId) {
            $query->where('caisse_id', $this->caisseId);
        }

        $paiements = $query->get();

        // Rien à envoyer : aucun paiement ou montant total à 0 (on n'envoie pas de 0 dans X3)
        if ($paiements->isEmpty() || (float) $paiements->sum('montant') <= 0) {
            Log::info('Envoi Sage X3 ignoré : aucun paiement ou montant total à 0.', [
                'date_operation' => $this->dateOperation,
                'caisse_id' => $this->caisseId,
            ]);
            return;
        }

        // Grouper les paiements par dossier (num_chrono) pour créer une facture par dossier
        $groupedPaiements = $paiements->groupBy('dossier.num_chrono');
        $facturesAtraiter = [];
        $factureHeaderDossiers = [];

        foreach ($groupedPaiements as $numFacture => $paiementsDuDossier) {
            $firstPaiement = $paiementsDuDossier->first();
            $dossier = $firstPaiement ? $firstPaiement->dossier : null;

            if (!$dossier) {
                Log::warning('Dossier introuvable pour le groupe de paiements', [
                    'num_facture_groupe' => $numFacture,
                    'paiements' => $paiementsDuDossier->pluck('id')->all()
                ]);
                continue;
            }

            // Par défaut, la facture cible est elle-même
            $targetNumFacture = $numFacture;
            $headerDossier = $dossier;

            // Est-ce un dossier principal de type Duplicata (id_service === 4) contenant un lien vers un autre dossier ?
            $isDuplicateWithLink = ((int) ($dossier->id_service ?? 0) === 4) && !empty($dossier->id_dossier_lier);

            if ($isDuplicateWithLink) {
                // Pour le duplicata (dossier principal), la facture cible reste le duplicata lui-même
                $targetNumFacture = $dossier->num_chrono;
                $headerDossier = $dossier;
            } else {
                // Pour tout autre dossier (comme le Post), on vérifie s'il existe un duplicata principal qui le lie
                $duplicateParent = \App\Models\Dossier::where('id_service', 4)
                    ->where('id_dossier_lier', $dossier->id)
                    ->first();

                if ($duplicateParent) {
                    // Si un duplicata lie ce dossier, on redirige les paiements vers la facture du duplicata
                    $targetNumFacture = $duplicateParent->num_chrono;
                    $headerDossier = $duplicateParent;

                    Log::info('Dossier enfant/lié détecté, fusion vers facture parent Duplicata', [
                        'dossier_enfant_id' => $dossier->id,
                        'num_chrono_enfant' => $dossier->num_chrono,
                        'dossier_parent_id' => $duplicateParent->id,
                        'num_chrono_parent' => $duplicateParent->num_chrono,
                    ]);
                }
            }

            if (!isset($facturesAtraiter[$targetNumFacture])) {
                $facturesAtraiter[$targetNumFacture] = collect();
            }

            $facturesAtraiter[$targetNumFacture] = $facturesAtraiter[$targetNumFacture]->concat($paiementsDuDossier);
            
            // On s'assure que le dossier d'en-tête soit le duplicata (s'il y en a un dans la fusion)
            if (!isset($factureHeaderDossiers[$targetNumFacture]) || ((int) ($headerDossier->id_service ?? 0) === 4)) {
                $factureHeaderDossiers[$targetNumFacture] = $headerDossier;
            }
        }

        foreach ($facturesAtraiter as $numFacture => $paiementsDuDossier) {
            try {
                Log::info('Début traitement facture X3', ['num_facture' => $numFacture]);

                $dossier = $factureHeaderDossiers[$numFacture] ?? ($paiementsDuDossier->first() ? $paiementsDuDossier->first()->dossier : null);
                if (!$dossier) {
                    throw new Exception("Dossier manquant ou introuvable pour la facture [NUM: $numFacture]");
                }
                Log::info('dossier', $dossier->toArray());
                $client = $dossier->client;

                // ÉTAPE 1 : Préparation et calcul des lignes en mémoire
                $lignesAEnvoyer = [];
                $linCounter = 1000;
                $totalHT = 0;
                $totalTTC = 0;
                
                // Le montant total des timbres est la somme réelle des timbres de chaque paiement
                $totalTimbre = $paiementsDuDossier->sum('timbre');

                $services_par_id = [
                    "2" => "REI01",
                    "7" => "OPS01",
                    "10" => "POI01",
                    "16" => "POI02",
                    "19" => "POI03",
                    "22" => "POI04",
                    "25" => "POI05",
                    "28" => "POI06",
                    "31" => "POI07",
                    "40" => "DUP01",
                    "41" => "DUP02",
                    "42" => "DUP03",
                    "43" => "DUP04",
                    "44" => "DUP05",
                    "45" => "OPS02",
                ];

                foreach ($paiementsDuDossier as $paiement) {
                    // 1. Récupérer le breakdown des montants depuis la description du paiement
                    $description = json_decode($paiement->description ?? '[]', true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning('Description JSON invalide pour le paiement', [
                            'paiement_id' => $paiement->id,
                            'reference' => $paiement->reference,
                            'description_brute' => $paiement->description
                        ]);
                    }
                    $breakdown = collect(is_array($description) ? $description : []);

                    // 2. Identifier les services à envoyer
                    $rawDetail = $paiement->dossier->detail;
                    $serviceIds = is_array($rawDetail) ? $rawDetail : (json_decode($rawDetail ?? '', true) ?? []);

                    $servicesAEnvoyer = [];
                    foreach ($serviceIds as $serviceId) {
                        if (isset($services_par_id[$serviceId]) || $serviceId == 46 || $serviceId == 47) {
                            $detailService['montant'] = 0;
                            // Pour le Relica Primo et Primo Caisse, on prend directement le montant du paiement moins le timbre réel
                            if ($serviceId == 46 || $serviceId == 47) {
                                $detailService['montant'] = $paiement->montant - $paiement->timbre;

                                $genre = strtoupper($paiement->dossier->vehicule->genre_vehicule ?? '');

                                $isRemorque = str_contains($genre, 'REMORQUE');
                                $isMoto = str_contains($genre, 'MOTO');

                                if ($serviceId == 46) {
                                    // RELICA-PRIMO
                                    if ($isRemorque) {
                                        $itmref = 'REL03';
                                    } elseif ($isMoto) {
                                        $itmref = 'REL02';
                                    } else {
                                        $itmref = 'REL01';
                                    }
                                } else {
                                    // PRIMO CAISSE
                                    if ($isRemorque) {
                                        $itmref = 'PREM03';
                                    } elseif ($isMoto) {
                                        $itmref = 'PREM02';
                                    } else {
                                        $itmref = 'PREM01';
                                    }
                                }
                            } else {
                                $itmref = $services_par_id[$serviceId];
                                // Chercher le montant réel dans le breakdown
                                $detailService = $breakdown->firstWhere('id', $serviceId);

                                if (!$detailService) {
                                    Log::error("Montant introuvable pour le service dans le breakdown du paiement", [
                                        'service_id' => $serviceId,
                                        'paiement_ref' => $paiement->reference,
                                        'breakdown' => $breakdown->toArray()
                                    ]);
                                    throw new Exception("Montant introuvable pour le service [ID: $serviceId] dans le paiement [REF: {$paiement->reference}]. Échec de l'envoi pour éviter une division erronée.");
                                }
                            }
                            $servicesAEnvoyer[] = [
                                'itmref' => $itmref,
                                'montant_ttc' => (float) $detailService['montant']
                            ];
                        }
                    }

                    // Condition spéciale PLQ : uniquement pour les dossiers Post-immatriculation (id_service=3)
                    // en dehors de ceux qui sont fusionnés dans une facture Duplicata
                    // (si le header de la facture est un Duplicata id_service=4, on ne passe pas ici)
                    if ((int) ($dossier->id_service ?? 0) === 3 && (int) ($paiement->dossier->id_service ?? 0) === 3) {
                        $triggerIds = [10, 28, 25];
                        $serviceIdsInt = array_map('intval', $serviceIds);
                        $hasTriggerService = !empty(array_intersect($serviceIdsInt, $triggerIds));

                        if ($hasTriggerService) {
                            // Chercher l'entrée "Changement de plaques" dans le breakdown du paiement
                            $plqItem = $breakdown->first(function ($item) {
                                return isset($item['element_facturation']) &&
                                    stripos($item['element_facturation'], 'Changement de plaques') !== false;
                            });

                            if ($plqItem && isset($plqItem['montant'])) {
                                $servicesAEnvoyer[] = [
                                    'itmref' => 'PLQ',
                                    'montant_ttc' => (float) $plqItem['montant']
                                ];
                            } else {
                                Log::warning('Élément "Changement de plaques" introuvable dans le breakdown pour le service PLQ', [
                                    'paiement_ref' => $paiement->reference,
                                    'num_chrono' => $paiement->dossier->num_chrono,
                                    'breakdown' => $breakdown->toArray()
                                ]);
                            }
                        }
                    }

                    // Fallback si aucun service mappé n'est trouvé dans le dossier
                    if (empty($servicesAEnvoyer)) {
                        Log::warning("Aucun service mappé trouvé pour le dossier, utilisation du fallback ARTICLE01", [
                            'num_chrono' => $paiement->dossier->num_chrono ?? $numFacture,
                            'detail_dossier' => $rawDetail,
                            'mapping_services' => $services_par_id
                        ]);
                        $servicesAEnvoyer[] = [
                            'itmref' => "ARTICLE01",
                            'montant_ttc' => $paiement->montant - $paiement->timbre
                        ];
                    }

                    $vinVehicule = $paiement->dossier->vehicule->vin ?? $numFacture;

                    // 4. Préparation des lignes
                    foreach ($servicesAEnvoyer as $service) {
                        $mtHT = $service['montant_ttc'] / 1.18;
                        $mtTTC = $service['montant_ttc'];

                        $totalHT += (int) round($mtHT);
                        $totalTTC += (int) round($mtTTC);

                        $lignesAEnvoyer[] = [
                            'LIN' => $linCounter,
                            'ITMREF' => $service['itmref'],
                            'CHRONO' => $paiement->dossier->num_chrono,
                            'VINVEHICULE' => $vinVehicule,
                            'QTYUS' => 1,
                            'NETPRIX' => (int) round($mtHT),
                            'MONTANTHTLN' => (int) round($mtHT),
                            'MONTANTTTCLN' => (int) round($mtTTC),
                        ];
                        $linCounter += 1000;
                    }
                }

                // ÉTAPE 2 : Création de l'en-tête de la facture dans Sage X3
                $headerTTC = $totalTTC + $totalTimbre;
                $tva = $totalTTC - $totalHT;

                $enteteData = [
                    'NUM' => $numFacture,
                    'DATINV' => $this->dateOperation, // Format YYYY-MM-DD
                    'BPCINV' => 'C000016', // Code client
                    'BPRNAM' => ($client ? $client->nom . ' ' . $client->prenom : 'Client'), // Nom client
                    'TIMBRE' => $totalTimbre,
                    'TVA' => $tva,
                    'TOTALHT' => $totalHT, // Format entier
                    'TOTALTTC' => $headerTTC, // Format entier
                    'SITE' => $dossier->id_site,
                    'CUR' => 'XOF'
                ];

                Log::info('Création entête facture X3', $enteteData);
                $x3->createEntete($enteteData);

                // ÉTAPE 3 : Création des lignes de la facture dans Sage X3
                foreach ($lignesAEnvoyer as $ligne) {
                    $ligneData = array_merge(['NUM' => $numFacture], $ligne);
                    Log::info('Création ligne facture X3 (Montant Réel)', $ligneData);
                    $x3->createLigne($ligneData);
                }

                Log::info('Facture X3 traitée avec succès', [
                    'num_facture' => $numFacture,
                    'nombre_lignes' => count($lignesAEnvoyer)
                ]);
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('Erreur de requête API Sage X3', [
                    'num_facture' => $numFacture,
                    'error' => $e->getMessage()
                ]);
                if ($e->response) {
                    Log::error("=== REPONSE COMPLETE X3 POUR $numFacture ===");
                    Log::error($e->response->body());
                }
                continue;
            } catch (Exception $e) {
                Log::error('Erreur traitement facture X3', [
                    'num_facture' => $numFacture,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continuer avec la facture suivante
                continue;
            }
        }

        Log::info('Tous les paiements envoyés à X3', [
            'date' => $this->dateOperation,
            'nombre_factures' => count($facturesAtraiter),
            'total_paiements' => $paiements->count()
        ]);
    }

    // En cas d'échec du job après toutes les tentatives, cette méthode sera appelée
    public function failed(\Throwable $exception)
    {
        // Journalisation critique en cas d'échec du job
        Log::critical('JOB X3 FAILED', [
            'date' => $this->dateOperation,
            'caisse_id' => $this->caisseId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
