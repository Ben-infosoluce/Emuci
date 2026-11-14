<?php

namespace App\Services;

use App\Models\BillingArticle;
use App\Models\Paiement;
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
     * Lignes théoriques d'un paiement historique (avant `paiement_lignes`).
     *
     * On repart de la description enregistrée à l'époque, puis on développe
     * chaque service ventilé en ses articles de catalogue. Les anciens
     * dossiers PRIMO-ESPECE (sentinelle 47) sont rattachés à PRIM1/2/3 selon
     * le genre du véhicule.
     *
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    public function historicalLines(array $items, object $dossier, ?string $genre = null): array
    {
        // PRIMO-ESPECE : la description historique est souvent synthétique
        // (une seule ligne « Reliquat... »), on ventile donc par le catalogue.
        if ((int) ($dossier->id_service ?? 0) === 6 || ($dossier->type ?? null) === 'PRIMO-ESPECE') {
            $catalog = $this->primoCatalog($genre);

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
            $catalog = $this->catalogForDetail($detailId, $dossier, $genre);

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
                'article_code' => $this->legacyCode($detailId, $dossier, $genre, $label),
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

        // Description vide ou inexploitable : on se rabat sur le détail du dossier.
        $detailIds = json_decode($dossier->detail ?? '[]', true);

        if (!is_array($detailIds)) {
            return [];
        }

        foreach ($detailIds as $detailId) {
            if (!is_numeric($detailId)) {
                continue;
            }

            $catalog = $this->catalogForDetail((int) $detailId, $dossier, $genre);

            if ($catalog->isNotEmpty()) {
                array_push($lines, ...$this->catalogToLines($catalog));
            }
        }

        return $lines;
    }

    /**
     * Répartit le montant réellement encaissé sur les lignes théoriques.
     *
     * Option A : la somme des lignes TTC est toujours exactement
     * `montant - timbre`, y compris pour les exceptions tarifaires.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    public function rescaleToCollected(array $lines, float $montant, float $timbre): array
    {
        $collected = round($montant - $timbre, 2);

        if ($collected <= 0) {
            return [];
        }

        $weight = array_sum(array_map(fn ($line) => (float) $line['montant_ttc'], $lines));

        if ($weight <= 0) {
            return [];
        }

        $factor = $collected / $weight;
        $isCatalogExact = abs($factor - 1) < 0.000001;
        // Les montants FCFA sont entiers : on arrondit chaque ligne à l'unité
        // pour que la somme affichée sur le reçu corresponde exactement au total.
        $wholeUnits = abs($collected - round($collected)) < 0.005;
        $sum = 0.0;
        $last = count($lines) - 1;

        foreach ($lines as $index => &$line) {
            if ($index === $last) {
                $line['montant_ttc'] = round($collected - $sum, 2);
            } else {
                $montant = (float) $line['montant_ttc'] * $factor;
                $line['montant_ttc'] = $wholeUnits ? round($montant) : round($montant, 2);
                $sum += $line['montant_ttc'];
            }

            $line['montant_ht'] = $isCatalogExact && isset($line['source_ht'])
                ? round((float) $line['source_ht'], 2)
                : round($line['montant_ttc'] / 1.18, 2);

            unset($line['source_ht']);
        }

        return array_values(array_filter($lines, fn ($line) => $line['montant_ttc'] > 0));
    }

    /**
     * Lignes de ventilation à afficher pour un paiement.
     *
     * On privilégie les lignes réellement enregistrées (`paiement_lignes`).
     * Pour un paiement historique qui n'a pas encore été re-ventilé, on les
     * recalcule et on les répartit sur le montant encaissé, comme sur le reçu.
     *
     * @return array<int, array<string, mixed>>
     */
    public function linesForPaiement(Paiement $paiement): array
    {
        $lignes = $paiement->relationLoaded('lignes')
            ? $paiement->lignes
            : $paiement->lignes()->get();

        if ($lignes->isNotEmpty()) {
            return $lignes->map(fn ($ligne) => [
                'article_code' => $ligne->article_code,
                'libelle' => $ligne->libelle,
                'fait_generateur' => $ligne->fait_generateur,
                'montant_ht' => (float) $ligne->montant_ht,
                'montant_ttc' => (float) $ligne->montant_ttc,
                'parent_detail_type_service_id' => $ligne->parent_detail_type_service_id,
            ])->values()->all();
        }

        $dossier = $paiement->dossier;

        if (!$dossier) {
            return [];
        }

        $description = json_decode($paiement->description ?? '[]', true);

        return $this->rescaleToCollected(
            $this->historicalLines(
                is_array($description) ? $description : [],
                $dossier,
                $dossier->r_dossier_vehicule->genre_vehicule ?? null
            ),
            (float) $paiement->montant,
            (float) $paiement->timbre
        );
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function catalogToLines(Collection $catalog): array
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
                // Conservé uniquement pour restituer le HT exact quand le
                // montant encaissé correspond au tarif du catalogue.
                'source_ht' => (float) $article->montant_ht,
            ];
        })->all();
    }

    private function catalogForDetail(?int $detailId, object $dossier, ?string $genre): Collection
    {
        if (!$detailId) {
            return collect();
        }

        // Anciens dossiers : 46 = Ré-immatriculation moto (service 2).
        if ($detailId === 46 && (int) ($dossier->id_service ?? 0) === 2) {
            return $this->catalogFor($this->serviceId('REI02'));
        }

        // Anciens dossiers : 47 = PRIMO-ESPECE.
        if ($detailId === 47 && (int) ($dossier->id_service ?? 0) === 6) {
            return $this->primoCatalog($genre);
        }

        return $this->catalogFor($detailId);
    }

    private function primoCatalog(?string $genre): Collection
    {
        $code = match ($this->genreCategory($genre)) {
            'remorque' => 'PRIM3',
            'moto' => 'PRIM2',
            default => 'PRIM1',
        };

        return $this->catalogFor($this->serviceId($code));
    }

    private function legacyCode(?int $detailId, object $dossier, ?string $genre, string $label): string
    {
        $idService = (int) ($dossier->id_service ?? 0);

        // RELICA-PRIMO (service 5) : la description contient une ligne
        // synthétique (id null). L'ancien code X3 dépend du genre.
        if ($idService === 5 || ($dossier->type ?? null) === 'RELICA-PRIMO') {
            return $this->legacyArticleCode(46, $genre, $label);
        }

        // PRIMO-ESPECE sans catalogue : PREM01/02/03.
        if ($idService === 6 || ($dossier->type ?? null) === 'PRIMO-ESPECE') {
            return $this->legacyArticleCode(47, $genre, $label);
        }

        return $this->legacyArticleCode($detailId, $genre, $label);
    }

    private function serviceId(string $code): ?int
    {
        $id = DB::table('detail_type_services')->where('code', $code)->value('id');

        return $id ? (int) $id : null;
    }

    private function genreCategory(?string $genre): string
    {
        $genre = strtoupper((string) $genre);

        if (str_contains($genre, 'REMORQUE')) {
            return 'remorque';
        }

        if (str_contains($genre, 'MOTO')) {
            return 'moto';
        }

        return 'auto';
    }

    private function detailIdFromItem(array $item): ?int
    {
        $id = $item['id'] ?? null;

        return (is_numeric($id)) ? (int) $id : null;
    }
}
