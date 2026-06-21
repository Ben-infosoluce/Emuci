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

        if ($paiements->isEmpty()) {
            throw new Exception('Aucun paiement à envoyer');
        }

        // Grouper les paiements par dossier (num_chrono) pour créer une facture par dossier
        $groupedPaiements = $paiements->groupBy('dossier.num_chrono');

        foreach ($groupedPaiements as $numFacture => $paiementsDuDossier) {
            try {
                Log::info('Début traitement facture X3', ['num_facture' => $numFacture]);

                // ÉTAPE 1 : Calcul des totaux et création de l'en-tête de la facture dans Sage X3
                $totalHT = 0;
                $totalTTC = 0;

                foreach ($paiementsDuDossier as $paiement) {
                    // Calcul du montant HT (TVA 18%)
                    $montantHT = $paiement->montant / 1.18;
                    $totalHT += $montantHT;
                    $totalTTC += $paiement->montant;
                }

                // Récupérer les informations du client depuis le dossier
                $firstPaiement = $paiementsDuDossier->first();
                $dossier = $firstPaiement ? $firstPaiement->dossier : null;
                if (!$dossier) {
                    throw new Exception("Dossier manquant ou introuvable pour la facture [NUM: $numFacture]");
                }
                Log::info('dossier', $dossier->toArray());
                $client = $dossier->client;

                $enteteData = [
                    'NUM' => $numFacture,
                    'DATINV' => $this->dateOperation, // Format YYYY-MM-DD
                    'BPCINV' => 'C000016', // Code client
                    'BPRNAM' => ($client ? $client->nom . ' ' . $client->prenom : 'Client'), // Nom client
                    'TIMBRE' => 100,
                    'TVA' => (int) round($totalTTC) - (int) round($totalHT),
                    'TOTALHT' => (int) round($totalHT), // Format entier
                    'TOTALTTC' => (int) round($totalTTC), // Format entier
                    'SITE' => $dossier->id_site,
                    'CUR' => 'XOF'
                ];
                Log::info('Création entête facture X3', $enteteData);
                $x3->createEntete($enteteData);
                // ÉTAPE 2 : Création des lignes de la facture dans Sage X3
                // Chaque ligne correspond à un service spécifique lié au dossier
                $linCounter = 1000;
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
                //pour RELICA-PRIMO et PRIMO CAISSE, il faut : regarder 2 choses :
                // 1. RELICA-PRIMO :
                // REL01 RELICA-PRIMO  Auto
                // REL02 RELICA-PRIMO  Moto
                // REL03 RELICA-PRIMO  Semi-remorque

                // 2. PRIMO CAISSE :
                // PREM01 PRIMO-CAISSE  Auto
                // PREM02 PRIMO-CAISSE  Moto
                // PREM03 PRIMO-CAISSE  Semi-remorque


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
                            //pour le Relica Primo et Primo Caisse, on prend directement le montant du paiement car il n'est pas détaillé dans le breakdown
                            if ($serviceId == 46 or $serviceId == 47) {
                                $detailService['montant'] = $paiement->montant;

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

                    // Fallback si aucun service mappé n'est trouvé dans le dossier
                    if (empty($servicesAEnvoyer)) {
                        Log::warning("Aucun service mappé trouvé pour le dossier, utilisation du fallback ARTICLE01", [
                            'num_chrono' => $paiement->dossier->num_chrono ?? $numFacture,
                            'detail_dossier' => $rawDetail,
                            'mapping_services' => $services_par_id
                        ]);
                        $servicesAEnvoyer[] = [
                            'itmref' => "ARTICLE01",
                            'montant_ttc' => $paiement->montant - 100
                        ];
                    }

                    $vinVehicule = $paiement->dossier->vehicule->vin ?? $numFacture;

                    // 4. Création des lignes
                    foreach ($servicesAEnvoyer as $service) {
                        $mtHT = $service['montant_ttc'] / 1.18;

                        $ligneData = [
                            'NUM' => $numFacture,
                            'LIN' => $linCounter,
                            "ITMREF" => $service['itmref'],
                            'CHRONO' => $paiement->dossier->num_chrono,
                            'VINVEHICULE' => $vinVehicule,
                            'QTYUS' => 1,
                            'NETPRIX' => (int) round($mtHT),
                            'MONTANTHTLN' => (int) round($mtHT),
                            'MONTANTTTCLN' => (int) round($service['montant_ttc']),
                        ];

                        Log::info('Création ligne facture X3 (Montant Réel)', $ligneData);
                        $x3->createLigne($ligneData);
                        $linCounter += 1000;
                    }
                }

                Log::info('Facture X3 traitée avec succès', [
                    'num_facture' => $numFacture,
                    'nombre_lignes' => $paiementsDuDossier->count()
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
            'nombre_factures' => $groupedPaiements->count(),
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
