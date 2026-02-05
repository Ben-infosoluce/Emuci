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
        $paiements = Paiement::with('dossier')
            ->whereDate('created_at', $this->dateOperation)
            ->get();

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
                $dossier = $paiementsDuDossier->first()->dossier;
                $client = $dossier->client;

                $enteteData = [
                    'NUM' => $numFacture,
                    'DATINV' => $this->dateOperation, // Format YYYY-MM-DD
                    'BPCINV' => 'C000016', // Code client
                    'BPRNAM' => ($client ? $client->nom . ' ' . $client->prenom : 'Client'), // Nom client
                    'TOTALHT' => (int) round($totalHT), // Format entier
                    'TOTALTTC' => (int) round($totalTTC), // Format entier
                    'CUR' => 'XOF'
                ];
                Log::info('Création entête facture X3', $enteteData);
                $x3->createEntete($enteteData);
                // ÉTAPE 2 : Créer les lignes de la facture
                $linCounter = 1000;
                foreach ($paiementsDuDossier as $paiement) {
                    $montantHT = $paiement->montant / 1.18;
                    // Récupérer le VIN du véhicule via la relation dossier->vehicule
                    $vinVehicule = $paiement->dossier->vehicule->vin ?? $numFacture;
                    $ligneData = [
                        'NUM' => $numFacture,
                        'LIN' => $linCounter, // 1000, 2000, 3000...
                        'CHRONO' => $paiement->dossier->num_chrono,
                        'VINVEHICULE' => $vinVehicule, // Ou un autre champ pour le véhicule
                        'QTYUS' => 1, // Entier comme dans la doc
                        'NETPRIX' => (int) round($montantHT), // Format entier
                        'MONTANTHTLN' => (int) round($montantHT), // Format entier
                        'MONTANTTTCLN' => (int) round($paiement->montant), // Format entier
                    ];
                    Log::info('Création ligne facture X3', $ligneData);
                    $x3->createLigne($ligneData);
                    $linCounter += 1000;
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

    public function failed(Exception $exception)
    {
        Log::critical('JOB X3 FAILED', [
            'date' => $this->dateOperation,
            'caisse_id' => $this->caisseId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
