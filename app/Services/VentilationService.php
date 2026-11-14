<?php

namespace App\Services;

use App\Models\BillingArticle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ventilation des services en articles de facturation.
 *
 * Deux cas sont gérés :
 *  - le service possède un catalogue `billing_articles` (REI01, OPS01, PRIM1…) :
 *    on génère une ligne par article du catalogue ;
 *  - le service n'est pas ventilé (POI, DUP, plaques, historique 46/47…) :
 *    on conserve l'ancien mapping d'articles Sage X3.
 */
class VentilationService
{
    /**
     * Ancien mapping service (detail_type_services.id) -> article X3.
     * Il reste utilisé pour les services non ventilés et les paiements historiques.
     */
    public const LEGACY_ARTICLE_BY_DETAIL = [
        2 => 'REI01',
        7 => 'OPS01',
        10 => 'POI01',
        16 => 'POI02',
        19 => 'POI03',
        22 => 'POI04',
        25 => 'POI05',
        28 => 'POI06',
        31 => 'POI07',
        40 => 'DUP01',
        41 => 'DUP02',
        42 => 'DUP03',
        43 => 'DUP04',
        44 => 'DUP05',
        45 => 'OPS02',
    ];

    /**
     * Catalogue actif d'un service parent, indexé par code.
     */
    public function catalogFor(?int $detailTypeServiceId): Collection
    {
        if (!$detailTypeServiceId) {
            return collect();
        }

        return BillingArticle::query()
            ->where('detail_type_service_id', $detailTypeServiceId)
            ->where('actif', true)
            ->orderBy('id')
            ->get();
    }

    /**
     * Transforme un élément de `detailTypeServices` en lignes de paiement.
     *
     * @return array<int, array<string, mixed>>
     */
    public function linesForItem(array $item, ?string $genre = null): array
    {
        $label = (string) ($item['element_facturation'] ?? '');
        $detailId = $this->detailIdFromItem($item);

        // La ligne « Changement de plaques » est une ligne technique ajoutée par la
        // caisse : son `id` provient de `autre_facturation` et peut coïncider avec
        // un id de `detail_type_services`. On la traite donc avant tout catalogue.
        if (stripos($label, 'Changement de plaques') !== false) {
            $montantTtc = round((float) ($item['montant'] ?? 0), 2);

            return [[
                'article_code' => 'PLQ',
                'libelle' => $label,
                'fait_generateur' => null,
                'montant_ht' => round($montantTtc / 1.18, 2),
                'montant_ttc' => $montantTtc,
                'compte_x3' => null,
                'parent_detail_type_service_id' => null,
            ]];
        }

        $catalog = $this->catalogFor($detailId);

        if ($catalog->isNotEmpty()) {
            return $catalog->map(function (BillingArticle $article) use ($detailId) {
                return [
                    'article_code' => $article->code,
                    'libelle' => $article->libelle,
                    'fait_generateur' => $article->fait_generateur,
                    'montant_ht' => round((float) $article->montant_ht, 2),
                    'montant_ttc' => round((float) $article->montant_ttc, 2),
                    'compte_x3' => $article->compte_x3,
                    'parent_detail_type_service_id' => $detailId,
                ];
            })->all();
        }

        $montantTtc = round((float) ($item['montant'] ?? 0), 2);

        return [[
            'article_code' => $this->legacyArticleCode($detailId, $genre, $label),
            'libelle' => $label !== '' ? $label : null,
            'fait_generateur' => null,
            'montant_ht' => round($montantTtc / 1.18, 2),
            'montant_ttc' => $montantTtc,
            'compte_x3' => null,
            'parent_detail_type_service_id' => $detailId,
        ]];
    }

    /**
     * Total TTC d'un élément (ventilé ou non).
     */
    public function totalTtc(array $item, ?string $genre = null): float
    {
        return collect($this->linesForItem($item, $genre))->sum('montant_ttc');
    }

    /**
     * Enregistre les lignes d'un paiement.
     *
     * @return array<int, array<string, mixed>> Lignes créées.
     */
    public function createForPaiement(int $paiementId, array $items, ?string $genre = null): array
    {
        $created = [];
        $now = now();

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            foreach ($this->linesForItem($item, $genre) as $line) {
                $line['paiement_id'] = $paiementId;
                $line['created_at'] = $now;
                $line['updated_at'] = $now;

                DB::table('paiement_lignes')->insert($line);
                $created[] = $line;
            }
        }

        return $created;
    }

    /**
     * Ajoute la ventilation du catalogue à une liste d'éléments destinée à l'UI
     * (caisse / reçu). Les éléments non ventilés sont retournés tels quels.
     */
    public function attachCatalog(array $items): array
    {
        return array_map(function ($item) {
            $wasObject = is_object($item);
            $item = (array) $item;

            if (stripos((string) ($item['element_facturation'] ?? ''), 'Changement de plaques') !== false) {
                return $wasObject ? (object) $item : $item;
            }

            $detailId = $this->detailIdFromItem($item);
            $catalog = $this->catalogFor($detailId);

            if ($catalog->isNotEmpty()) {
                $item['ventilation'] = $catalog->map(fn (BillingArticle $article) => [
                    'code' => $article->code,
                    'libelle' => $article->libelle,
                    'fait_generateur' => $article->fait_generateur,
                    'montant_ht' => (float) $article->montant_ht,
                    'montant_ttc' => (float) $article->montant_ttc,
                ])->all();
            }

            return $wasObject ? (object) $item : $item;
        }, $items);
    }

    /**
     * Code article X3 d'un service non ventilé.
     */
    public function legacyArticleCode(?int $detailId, ?string $genre = null, string $label = ''): string
    {
        if (stripos($label, 'Changement de plaques') !== false) {
            return 'PLQ';
        }

        if ($detailId === 46 || $detailId === 47) {
            $genre = strtoupper((string) $genre);
            $isRemorque = str_contains($genre, 'REMORQUE');
            $isMoto = str_contains($genre, 'MOTO');

            if ($detailId === 46) {
                return $isRemorque ? 'REL03' : ($isMoto ? 'REL02' : 'REL01');
            }

            return $isRemorque ? 'PREM03' : ($isMoto ? 'PREM02' : 'PREM01');
        }

        return self::LEGACY_ARTICLE_BY_DETAIL[$detailId] ?? 'ARTICLE01';
    }

    private function detailIdFromItem(array $item): ?int
    {
        $id = $item['id'] ?? null;

        return (is_numeric($id)) ? (int) $id : null;
    }
}
