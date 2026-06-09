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

class SendPaiementsToX3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 120;

    protected string $dateOperation;
    protected int $caisseId;

    public function __construct(string $dateOperation, int $caisseId)
    {
        $this->dateOperation = $dateOperation;
        $this->caisseId = $caisseId;
    }

    public function handle(X3FactureService $x3)
    {
        $query = Paiement::with('dossier')
            ->whereDate('created_at', $this->dateOperation);

        if ($this->caisseId) {
            $query->where('caisse_id', $this->caisseId);
        }

        $paiements = $query->get();

        if ($paiements->isEmpty()) {
            throw new Exception('Aucun paiement à envoyer');
        }

        // Grouper les paiements par dossier (num_chrono)
        $groupedPaiements = $paiements->groupBy('dossier.num_chrono');

        foreach ($groupedPaiements as $numFacture => $paiementsDuDossier) {
            try {
                Log::info('Début traitement facture X3', ['num_facture' => $numFacture]);

                // ÉTAPE 1 : Créer l'en-tête de la facture
                $totalHT = 0;
                $totalTTC = 0;

                foreach ($paiementsDuDossier as $paiement) {
                    $montantHT = $paiement->montant / 1.18; // À vérifier : 18% ou 25% ?
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
                // ÉTAPE 2 : Créer les lignes de la facture
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
                    "45" => "OPS02"
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
                        if (isset($services_par_id[$serviceId])) {
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

                            $servicesAEnvoyer[] = [
                                'itmref' => $services_par_id[$serviceId],
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

    public function failed(\Throwable $exception)
    {
        Log::critical('JOB X3 FAILED', [
            'date' => $this->dateOperation,
            'caisse_id' => $this->caisseId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
