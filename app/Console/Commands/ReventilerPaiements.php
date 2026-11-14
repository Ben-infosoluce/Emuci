<?php

namespace App\Console\Commands;

use App\Models\BillingArticle;
use App\Services\VentilationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

                    $lines = $this->buildLines($items, $dossier, $genre, $ventilation);

                    if (empty($lines)) {
                        $this->verboseSkip($dryRun, $paiement, 'aucune ligne identifiable');
                        $skipped++;
                        continue;
                    }

                    $lines = $this->rescaleToCollected($lines, $paiement);

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

    /**
     * Construit les lignes "théoriques" (avant ajustement sur l'encaissé).
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildLines(array $items, object $dossier, ?string $genre, VentilationService $ventilation): array
    {
        // PRIMO-ESPECE : les anciens paiements ont une description synthétique.
        // On ventile directement selon PRIM1 / PRIM2 / PRIM3.
        if ((int) ($dossier->id_service ?? 0) === 6 || ($dossier->type ?? null) === 'PRIMO-ESPECE') {
            $catalog = $this->primoCatalog($dossier, $genre, $ventilation);
            if ($catalog->isNotEmpty()) {
                return $this->catalogToLines($catalog);
            }
        }

        $lines = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $detailId = is_numeric($item['id'] ?? null) ? (int) $item['id'] : null;
            $catalog = $this->catalogForDetail($detailId, $dossier, $genre, $ventilation);

            if ($catalog->isNotEmpty()) {
                array_push($lines, ...$this->catalogToLines($catalog));
                continue;
            }

            $label = (string) ($item['element_facturation'] ?? '');
            $ttc = round((float) ($item['montant'] ?? 0), 2);

            if ($ttc <= 0) {
                continue;
            }

            $lines[] = [
                'article_code' => $this->legacyCode($detailId, $dossier, $genre, $label, $ventilation),
                'libelle' => $label !== '' ? $label : null,
                'fait_generateur' => null,
                'montant_ht' => round($ttc / 1.18, 2),
                'montant_ttc' => $ttc,
                'compte_x3' => null,
                'parent_detail_type_service_id' => $detailId,
            ];
        }

        if (!empty($lines)) {
            return $lines;
        }

        // Description vide : on se rabat sur le détail du dossier.
        $detailIds = json_decode($dossier->detail ?? '[]', true);
        if (!is_array($detailIds)) {
            return [];
        }

        foreach ($detailIds as $detailId) {
            if (!is_numeric($detailId)) {
                continue;
            }

            $detailId = (int) $detailId;
            $catalog = $this->catalogForDetail($detailId, $dossier, $genre, $ventilation);

            if ($catalog->isNotEmpty()) {
                array_push($lines, ...$this->catalogToLines($catalog));
            }
        }

        return $lines;
    }

    /**
     * Répartit l'encaissé réel sur les lignes théoriques (prorata).
     */
    private function rescaleToCollected(array $lines, object $paiement): array
    {
        $collected = round((float) $paiement->montant - (float) $paiement->timbre, 2);

        if ($collected <= 0) {
            return [];
        }

        $weight = array_sum(array_map(fn ($line) => (float) $line['montant_ttc'], $lines));

        if ($weight <= 0) {
            return [];
        }

        $factor = $collected / $weight;
        $isCatalogExact = abs($factor - 1) < 0.000001;
        $sum = 0.0;
        $last = count($lines) - 1;

        foreach ($lines as $index => &$line) {
            if ($index === $last) {
                $line['montant_ttc'] = round($collected - $sum, 2);
            } else {
                $line['montant_ttc'] = round((float) $line['montant_ttc'] * $factor, 2);
                $sum += $line['montant_ttc'];
            }

            $line['montant_ht'] = $isCatalogExact && isset($line['source_ht'])
                ? round((float) $line['source_ht'], 2)
                : round($line['montant_ttc'] / 1.18, 2);

            unset($line['source_ht']);
        }

        return array_values(array_filter($lines, fn ($line) => $line['montant_ttc'] > 0));
    }

    private function catalogToLines($catalog): array
    {
        return $catalog->map(function (BillingArticle $article) {
            return [
                'article_code' => $article->code,
                'libelle' => $article->libelle,
                'fait_generateur' => $article->fait_generateur,
                'montant_ht' => round((float) $article->montant_ht, 2),
                'montant_ttc' => round((float) $article->montant_ttc, 2),
                'compte_x3' => $article->compte_x3,
                'parent_detail_type_service_id' => $article->detail_type_service_id,
                'source_ht' => (float) $article->montant_ht,
            ];
        })->all();
    }

    private function catalogForDetail(?int $detailId, object $dossier, ?string $genre, VentilationService $ventilation)
    {
        if (!$detailId) {
            return collect();
        }

        // Anciens dossiers : 46 = Ré-immatriculation moto (service 2) ou RELICA (service 5).
        if ($detailId === 46 && (int) ($dossier->id_service ?? 0) === 2) {
            return $ventilation->catalogFor($this->serviceId('REI02'));
        }

        // Anciens dossiers : 47 = PRIMO-ESPECE.
        if ($detailId === 47 && (int) ($dossier->id_service ?? 0) === 6) {
            return $this->primoCatalog($dossier, $genre, $ventilation);
        }

        return $ventilation->catalogFor($detailId);
    }

    private function primoCatalog(object $dossier, ?string $genre, VentilationService $ventilation)
    {
        $code = match ($this->genreCategory($genre)) {
            'remorque' => 'PRIM3',
            'moto' => 'PRIM2',
            default => 'PRIM1',
        };

        return $ventilation->catalogFor($this->serviceId($code));
    }

    private function legacyCode(?int $detailId, object $dossier, ?string $genre, string $label, VentilationService $ventilation): string
    {
        $idService = (int) ($dossier->id_service ?? 0);

        // RELICA-PRIMO (service 5) : la description contient une ligne
        // synthétique (id null). L'ancien code X3 est REL01/02/03 selon le genre.
        if ($idService === 5 || ($dossier->type ?? null) === 'RELICA-PRIMO') {
            return $ventilation->legacyArticleCode(46, $genre, $label);
        }

        // PRIMO-ESPECE sans catalogue (migration non exécutée) : PREM01/02/03.
        if ($idService === 6 || ($dossier->type ?? null) === 'PRIMO-ESPECE') {
            return $ventilation->legacyArticleCode(47, $genre, $label);
        }

        return $ventilation->legacyArticleCode($detailId, $genre, $label);
    }

    private function serviceId(string $code): ?int
    {
        $id = DB::table('detail_type_services')->where('code', $code)->value('id');

        return $id ? (int) $id : null;
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

    private function genreCategory(?string $genre): string
    {
        $genre = Str::upper((string) $genre);

        if (Str::contains($genre, 'REMORQUE')) {
            return 'remorque';
        }

        if (Str::contains($genre, 'MOTO')) {
            return 'moto';
        }

        return 'auto';
    }
}
