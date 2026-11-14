<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Catalogue métier validé (feuille « Proposition IT »).
     *
     * 8 services ventilés en 3 articles chacun :
     *   REI01 / REI02 / REI03  (Ré-immatriculation Auto / Moto / Semi-remorque)
     *   OPS01 / OPS02          (Opération Spéciale Moto / Auto)
     *   PRIM1 / PRIM2 / PRIM3  (Primo-Espèce Auto / Moto / Semi-remorque)
     */
    private array $groups = [
        'REI01' => [
            'montant' => 22600,
            'existant' => 2,
            'match' => [
                'id_service' => 2,
                'id_type_services' => 1,
                'element_facturation' => 'Ré-immatriculation Ordinaire',
            ],
            'service' => 2,
            'type' => 1,
            'site' => 2,
            'entite' => 1,
            'libelle' => 'Ré-immatriculation Auto',
            'lignes' => [
                ['code' => 'REI01_1', 'libelle' => 'Auto-Sécurisation plaque vierge', 'fait' => 'A la génération de la 1ère immatriculation', 'ht' => 2491.5254237288136, 'ttc' => 2940],
                ['code' => 'REI01_2', 'libelle' => 'Auto-Pose sécurisation QR code', 'fait' => 'A la pose de la plaque', 'ht' => 4745.762711864407, 'ttc' => 5600],
                ['code' => 'REI01_3', 'libelle' => 'Auto-Plaques', 'fait' => 'A la pose de la plaque', 'ht' => 11915.254237288136, 'ttc' => 14060],
            ],
        ],
        'REI02' => [
            'montant' => 14100,
            'existant' => null,
            'new_id' => 1001,
            'service' => 2,
            'type' => 1,
            'site' => 2,
            'entite' => 1,
            'libelle' => 'Ré-immatriculation Moto',
            'lignes' => [
                ['code' => 'REI02_1', 'libelle' => 'Moto-Sécurisation plaque vierge', 'fait' => 'A la génération de la 1ère immatriculation', 'ht' => 2966.1016949152545, 'ttc' => 3500],
                ['code' => 'REI02_2', 'libelle' => 'Moto-Pose sécurisation QR code', 'fait' => 'A la pose de la plaque', 'ht' => 4745.762711864407, 'ttc' => 5600],
                ['code' => 'REI02_3', 'libelle' => 'Moto-Plaques', 'fait' => 'A la pose de la plaque', 'ht' => 4237.28813559322, 'ttc' => 5000],
            ],
        ],
        'REI03' => [
            'montant' => 15570,
            'existant' => null,
            'new_id' => 1002,
            'service' => 2,
            'type' => 1,
            'site' => 2,
            'entite' => 1,
            'libelle' => 'Ré-immatriculation Semi-Remorque',
            'lignes' => [
                ['code' => 'REI03_1', 'libelle' => 'Semi-remorque-Sécurisation plaque vierge', 'fait' => 'A la génération de la 1ère immatriculation', 'ht' => 2491.5254237288136, 'ttc' => 2940],
                ['code' => 'REI03_2', 'libelle' => 'Semi-remorque-Pose sécurisation QR code', 'fait' => 'A la pose de la plaque', 'ht' => 4745.762711864407, 'ttc' => 5600],
                ['code' => 'REI03_3', 'libelle' => 'Semi-remorque-Plaques', 'fait' => 'A la pose de la plaque', 'ht' => 5957.627118644068, 'ttc' => 7030],
            ],
        ],
        'OPS01' => [
            'montant' => 6300,
            'existant' => 7,
            'match' => [
                'id_service' => 1,
                'id_type_services' => 3,
                'element_facturation' => 'Opération Spéciale - Moto',
            ],
            'service' => 1,
            'type' => 3,
            'site' => 1,
            'entite' => 1,
            'libelle' => 'Operation Spéciale moto',
            'lignes' => [
                ['code' => 'OPS01_1', 'libelle' => 'OPS Moto-Sécurisation plaque vierge', 'fait' => 'A la génération de la 1ère immatriculation', 'ht' => 847.4576271186442, 'ttc' => 1000],
                ['code' => 'OPS01_2', 'libelle' => 'OPS Moto-Pose sécurisation QR code', 'fait' => 'A la pose de la plaque', 'ht' => 1949.1525423728815, 'ttc' => 2300],
                ['code' => 'OPS01_3', 'libelle' => 'OPS Moto-Plaques', 'fait' => 'A la pose de la plaque', 'ht' => 2542.3728813559323, 'ttc' => 3000],
            ],
        ],
        'OPS02' => [
            'montant' => 22600,
            'existant' => 45,
            'match' => [
                'id_service' => 1,
                'id_type_services' => 15,
                'element_facturation' => 'Fabrication et pose de plaque',
            ],
            'service' => 1,
            'type' => 15,
            'site' => 1,
            'entite' => 1,
            'libelle' => 'Operation Spéciale auto',
            'lignes' => [
                ['code' => 'OPS02_1', 'libelle' => 'OPS Auto-Sécurisation plaque vierge', 'fait' => 'A la génération de la 1ère immatriculation', 'ht' => 2491.5254237288136, 'ttc' => 2940],
                ['code' => 'OPS02_2', 'libelle' => 'OPS Auto-Pose sécurisation QR code', 'fait' => 'A la pose de la plaque', 'ht' => 4745.762711864407, 'ttc' => 5600],
                ['code' => 'OPS02_3', 'libelle' => 'OPS Auto-Plaques', 'fait' => 'A la pose de la plaque', 'ht' => 11915.254237288136, 'ttc' => 14060],
            ],
        ],
        'PRIM1' => [
            'montant' => 22600,
            'existant' => null,
            'new_id' => 1003,
            'service' => 6,
            'type' => 19,
            'site' => 2,
            'entite' => 1,
            'libelle' => 'Espece Auto BOUAKE & KORHOGO',
            'lignes' => [
                ['code' => 'PRIM1_1', 'libelle' => 'Auto-Sécurisation plaque vierge', 'fait' => 'A la génération de la 1ère immatriculation', 'ht' => 2491.5254237288136, 'ttc' => 2940],
                ['code' => 'PRIM1_2', 'libelle' => 'Auto-Pose sécurisation QR code', 'fait' => 'A la pose de la plaque', 'ht' => 4745.762711864407, 'ttc' => 5600],
                ['code' => 'PRIM1_3', 'libelle' => 'Auto-Plaques', 'fait' => 'A la pose de la plaque', 'ht' => 11915.254237288136, 'ttc' => 14060],
            ],
        ],
        'PRIM2' => [
            'montant' => 14100,
            'existant' => null,
            'new_id' => 1004,
            'service' => 6,
            'type' => 19,
            'site' => 2,
            'entite' => 1,
            'libelle' => 'Espece Moto BOUAKE & KORHOGO',
            'lignes' => [
                ['code' => 'PRIM2_1', 'libelle' => 'Moto-Sécurisation plaque vierge', 'fait' => 'A la génération de la 1ère immatriculation', 'ht' => 2966.1016949152545, 'ttc' => 3500],
                ['code' => 'PRIM2_2', 'libelle' => 'Moto-Pose sécurisation QR code', 'fait' => 'A la pose de la plaque', 'ht' => 4745.762711864407, 'ttc' => 5600],
                ['code' => 'PRIM2_3', 'libelle' => 'Moto-Plaques', 'fait' => 'A la pose de la plaque', 'ht' => 4237.28813559322, 'ttc' => 5000],
            ],
        ],
        'PRIM3' => [
            'montant' => 15570,
            'existant' => null,
            'new_id' => 1005,
            'service' => 6,
            'type' => 19,
            'site' => 2,
            'entite' => 1,
            'libelle' => 'Espece Semi-Rem BOUAKE & KORHOGO',
            'lignes' => [
                ['code' => 'PRIM3_1', 'libelle' => 'Semi-remorque-Sécurisation plaque vierge', 'fait' => 'A la génération de la 1ère immatriculation', 'ht' => 2491.5254237288136, 'ttc' => 2940],
                ['code' => 'PRIM3_2', 'libelle' => 'Semi-remorque-Pose sécurisation QR code', 'fait' => 'A la pose de la plaque', 'ht' => 4745.762711864407, 'ttc' => 5600],
                ['code' => 'PRIM3_3', 'libelle' => 'Semi-remorque-Plaques', 'fait' => 'A la pose de la plaque', 'ht' => 5957.627118644068, 'ttc' => 7030],
            ],
        ],
    ];

    public function up(): void
    {
        $now = now();

        // 1. Rattacher les codes métier aux services déjà existants.
        //
        // On privilégie le code (migration déjà exécutée), puis une recherche
        // par caractéristiques métier, et seulement en dernier recours l'ID
        // historique. Cela évite de casser la ventilation si les IDs diffèrent
        // entre les environnements (dev / prod).
        foreach ($this->groups as $code => $group) {
            $service = DB::table('detail_type_services')->where('code', $code)->first();

            if (!$service && !empty($group['match'])) {
                $service = DB::table('detail_type_services')->where($group['match'])->first();
            }

            if (!$service && !empty($group['existant'])) {
                $service = DB::table('detail_type_services')->where('id', $group['existant'])->first();
            }

            if ($service) {
                DB::table('detail_type_services')
                    ->where('id', $service->id)
                    ->update(['code' => $code, 'updated_at' => $now]);
            }
        }

        // 2. Créer les services manquants (REI02, REI03, PRIM1, PRIM2, PRIM3).
        foreach ($this->groups as $code => $group) {
            $service = DB::table('detail_type_services')->where('code', $code)->first();

            if (!$service) {
                // On force un ID élevé pour ne jamais réutiliser les valeurs
                // sentinelles 46 (RELICA-PRIMO) et 47 (PRIMO-ESPECE) présentes
                // dans d'anciens dossiers. Si l'ID prévu est déjà occupé dans
                // cet environnement, on prend le prochain ID libre : le code
                // reste la référence stable, jamais l'ID.
                $id = $group['new_id'] ?? null;

                if ($id && DB::table('detail_type_services')->where('id', $id)->exists()) {
                    $id = null;
                }

                if (!$id) {
                    $id = max(1001, (int) DB::table('detail_type_services')->max('id') + 1);
                }

                DB::table('detail_type_services')->insert([
                    'id' => $id,
                    'id_type_services' => $group['type'],
                    'id_service' => $group['service'],
                    'id_site' => $group['site'],
                    'id_entite' => $group['entite'],
                    'element_facturation' => $group['libelle'],
                    'code' => $code,
                    'montant' => $group['montant'],
                    'statut' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $id = $service->id;
            }

            // 3. Les nouveaux tarifs TTC remplacent les anciens montants.
            DB::table('detail_type_services')->where('id', $id)->update([
                'montant' => $group['montant'],
                'updated_at' => $now,
            ]);

            $this->groups[$code]['id'] = (int) $id;
        }

        // 4. Alimenter le catalogue de ventilation.
        foreach ($this->groups as $group) {
            foreach ($group['lignes'] as $ligne) {
                DB::table('billing_articles')->updateOrInsert(
                    [
                        'detail_type_service_id' => $group['id'],
                        'code' => $ligne['code'],
                    ],
                    [
                        'libelle' => $ligne['libelle'],
                        'fait_generateur' => $ligne['fait'],
                        'montant_ht' => round($ligne['ht'], 2),
                        'montant_ttc' => $ligne['ttc'],
                        'compte_x3' => null,
                        'actif' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $codes = array_keys($this->groups);

        DB::table('billing_articles')
            ->whereIn('code', collect($this->groups)->flatMap(fn ($g) => collect($g['lignes'])->pluck('code'))->all())
            ->delete();

        DB::table('detail_type_services')
            ->whereIn('code', ['REI02', 'REI03', 'PRIM1', 'PRIM2', 'PRIM3'])
            ->delete();

        DB::table('detail_type_services')
            ->whereIn('code', $codes)
            ->update(['code' => null]);
    }
};
