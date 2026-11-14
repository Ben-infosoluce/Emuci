<?php

namespace App\Console\Commands;

use App\Services\VentilationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Re-ventile les paiements historiques sans modifier les montants encaissés.
 *
 * Option A : la somme des lignes créées est toujours égale à
 * `paiements.montant - paiements.timbre`. Si le montant réellement encaissé
 * diffère du tarif du catalogue (exception), les lignes sont réparties au
 * prorata du catalogue puis ajustées pour retomber exactement sur l'encaissé.
 */
class ReventilerPaiements extends Command
{
    protected $signature = 'ventilation:reventiler
        {--dry-run : Simule sans écrire en base}
        {--paiement= : Ne traite qu\'un paiement (ID)}
        {--from= : Date de début (YYYY-MM-DD)}
        {--to= : Date de fin (YYYY-MM-DD)}
        {--chunk=500 : Taille des lots}';

    protected $description = 'Crée les lignes de ventilation des paiements historiques en conservant les montants encaissés';

    public function handle(VentilationService $ventilation): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));

        $query = DB::table('paiements')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('paiement_lignes')
                    ->whereColumn('paiement_lignes.paiement_id', 'paiements.id');
            })
            ->orderBy('id');

        if ($this->option('paiement')) {
            $query->where('id', (int) $this->option('paiement'));
        }
        if ($this->option('from')) {
            $query->whereDate('created_at', '>=', $this->option('from'));
        }
        if ($this->option('to')) {
            $query->whereDate('created_at', '<=', $this->option('to'));
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Aucun paiement à re-ventiler.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%s : %d paiement(s) à traiter.',
            $dryRun ? 'SIMULATION' : 'RE-VENTILATION',
            $total
        ));

        $created = 0;
        $skipped = 0;
        $errors = 0;

        $query->chunkById($chunk, function ($paiements) use ($ventilation, $dryRun, &$created, &$skipped, &$errors) {
            foreach ($paiements as $paiement) {
                try {
                    $dossier = DB::table('dossiers')->where('id', $paiement->id_dossier)->first();

                    if (!$dossier) {
                        $this->verboseSkip($dryRun, $paiement, 'dossier introuvable');
                        $skipped++;
                        continue;
                    }

                    $genre = DB::table('vehicules')
                        ->where('id', $dossier->id_vehicule)
                        ->value('genre_vehicule');

                    $description = json_decode($paiement->description ?? '[]', true);
                    $items = is_array($description) ? $description : [];

                    $lines = $ventilation->historicalLines($items, $dossier, $genre);

                    if (empty($lines)) {
                        $this->verboseSkip($dryRun, $paiement, 'aucune ligne identifiable');
                        $skipped++;
                        continue;
                    }

                    $lines = $ventilation->rescaleToCollected(
                        $lines,
                        (float) $paiement->montant,
                        (float) $paiement->timbre
                    );

                    if (empty($lines)) {
                        $this->verboseSkip($dryRun, $paiement, 'montant encaissé nul (montant - timbre <= 0)');
                        $skipped++;
                        continue;
                    }

                    if ($dryRun && $this->output->isVerbose()) {
                        $this->line(sprintf(
                            '#%d %s (montant=%s, timbre=%s, total lignes=%s)',
                            $paiement->id,
                            $paiement->reference,
                            $paiement->montant,
                            $paiement->timbre,
                            array_sum(array_column($lines, 'montant_ttc'))
                        ));

                        foreach ($lines as $line) {
                            $this->line(sprintf(
                                '   %-10s HT=%10.2f  TTC=%10.2f',
                                $line['article_code'],
                                $line['montant_ht'],
                                $line['montant_ttc']
                            ));
                        }
                    }

                    if (!$dryRun) {
                        DB::transaction(function () use ($paiement, $lines) {
                            foreach ($lines as $line) {
                                $line['paiement_id'] = $paiement->id;
                                $line['created_at'] = now();
                                $line['updated_at'] = now();

                                DB::table('paiement_lignes')->insert($line);
                            }
                        });
                    }

                    $created++;
                } catch (\Throwable $e) {
                    $errors++;
                    $this->error("Paiement #{$paiement->id} : {$e->getMessage()}");
                }
            }
        });

        $this->info(sprintf(
            'Terminé : %d paiement(s) %s, %d ignoré(s), %d erreur(s).',
            $created,
            $dryRun ? 'simulé(s)' : 'ventilé(s)',
            $skipped,
            $errors
        ));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function verboseSkip(bool $dryRun, object $paiement, string $reason): void
    {
        if ($dryRun && $this->output->isVerbose()) {
            $this->line(sprintf(
                '#%d %s ignoré : %s (montant=%s, timbre=%s)',
                $paiement->id,
                $paiement->reference,
                $reason,
                $paiement->montant,
                $paiement->timbre
            ));
        }
    }
}
