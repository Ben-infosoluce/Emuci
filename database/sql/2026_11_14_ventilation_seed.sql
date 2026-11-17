-- ============================================================================
-- Ventilation des services — seed du catalogue (production)
-- ----------------------------------------------------------------------------
-- À exécuter APRÈS database/sql/2026_11_14_ventilation_tables.sql.
-- Script idempotent : peut être relancé sans créer de doublon.
--
-- Il reproduit la migration 2026_11_14_000004 :
--   1. rattache les codes REI01 / OPS01 / OPS02 aux services existants ;
--   2. crée les services manquants (REI02, REI03, PRIM1, PRIM2, PRIM3)
--      avec un ID >= 1001, pour ne jamais réutiliser les sentinelles 46/47 ;
--   3. met à jour les tarifs TTC validés ;
--   4. alimente billing_articles (24 articles).
-- ============================================================================

SET @now := NOW();

-- ----------------------------------------------------------------------------
-- 1. Rattacher les codes aux services déjà existants
-- ----------------------------------------------------------------------------
UPDATE `detail_type_services`
SET `code` = 'REI01', `updated_at` = @now
WHERE `code` IS NULL
  AND `id_service` = 2
  AND `id_type_services` = 1
  AND `element_facturation` = 'Ré-immatriculation Ordinaire'
ORDER BY `id`
LIMIT 1;

UPDATE `detail_type_services`
SET `code` = 'OPS01', `updated_at` = @now
WHERE `code` IS NULL
  AND `id_service` = 1
  AND `id_type_services` = 3
  AND `element_facturation` = 'Opération Spéciale - Moto'
ORDER BY `id`
LIMIT 1;

UPDATE `detail_type_services`
SET `code` = 'OPS02', `updated_at` = @now
WHERE `code` IS NULL
  AND `id_service` = 1
  AND `id_type_services` = 15
  AND `element_facturation` = 'Fabrication et pose de plaque'
ORDER BY `id`
LIMIT 1;

-- Repli par ID historique, uniquement si le code n'a pas été trouvé ci-dessus.
UPDATE `detail_type_services`
SET `code` = 'REI01', `updated_at` = @now
WHERE `code` IS NULL AND `id` = 2
LIMIT 1;

UPDATE `detail_type_services`
SET `code` = 'OPS01', `updated_at` = @now
WHERE `code` IS NULL AND `id` = 7
LIMIT 1;

UPDATE `detail_type_services`
SET `code` = 'OPS02', `updated_at` = @now
WHERE `code` IS NULL AND `id` = 45
LIMIT 1;

-- ----------------------------------------------------------------------------
-- 2. Créer les services manquants
--    ID = max(1001, MAX(id) + 1) pour éviter les valeurs 46/47.
-- ----------------------------------------------------------------------------

-- REI02 — Ré-immatriculation Moto
SET @next_id := (SELECT GREATEST(1001, COALESCE(MAX(`id`), 0) + 1) FROM `detail_type_services`);
INSERT INTO `detail_type_services`
  (`id`, `id_type_services`, `id_service`, `id_site`, `id_entite`, `element_facturation`, `code`, `montant`, `statut`, `created_at`, `updated_at`)
SELECT @next_id, 1, 2, 2, 1, 'Ré-immatriculation Moto', 'REI02', 14100, 1, @now, @now
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `detail_type_services` WHERE `code` = 'REI02');

-- REI03 — Ré-immatriculation Semi-Remorque
SET @next_id := (SELECT GREATEST(1001, COALESCE(MAX(`id`), 0) + 1) FROM `detail_type_services`);
INSERT INTO `detail_type_services`
  (`id`, `id_type_services`, `id_service`, `id_site`, `id_entite`, `element_facturation`, `code`, `montant`, `statut`, `created_at`, `updated_at`)
SELECT @next_id, 1, 2, 2, 1, 'Ré-immatriculation Semi-Remorque', 'REI03', 15570, 1, @now, @now
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `detail_type_services` WHERE `code` = 'REI03');

-- PRIM1 — Primo-Espèce Auto
SET @next_id := (SELECT GREATEST(1001, COALESCE(MAX(`id`), 0) + 1) FROM `detail_type_services`);
INSERT INTO `detail_type_services`
  (`id`, `id_type_services`, `id_service`, `id_site`, `id_entite`, `element_facturation`, `code`, `montant`, `statut`, `created_at`, `updated_at`)
SELECT @next_id, 19, 6, 2, 1, 'Espece Auto BOUAKE & KORHOGO', 'PRIM1', 22600, 1, @now, @now
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `detail_type_services` WHERE `code` = 'PRIM1');

-- PRIM2 — Primo-Espèce Moto
SET @next_id := (SELECT GREATEST(1001, COALESCE(MAX(`id`), 0) + 1) FROM `detail_type_services`);
INSERT INTO `detail_type_services`
  (`id`, `id_type_services`, `id_service`, `id_site`, `id_entite`, `element_facturation`, `code`, `montant`, `statut`, `created_at`, `updated_at`)
SELECT @next_id, 19, 6, 2, 1, 'Espece Moto BOUAKE & KORHOGO', 'PRIM2', 14100, 1, @now, @now
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `detail_type_services` WHERE `code` = 'PRIM2');

-- PRIM3 — Primo-Espèce Semi-Remorque
SET @next_id := (SELECT GREATEST(1001, COALESCE(MAX(`id`), 0) + 1) FROM `detail_type_services`);
INSERT INTO `detail_type_services`
  (`id`, `id_type_services`, `id_service`, `id_site`, `id_entite`, `element_facturation`, `code`, `montant`, `statut`, `created_at`, `updated_at`)
SELECT @next_id, 19, 6, 2, 1, 'Espece Semi-Rem BOUAKE & KORHOGO', 'PRIM3', 15570, 1, @now, @now
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `detail_type_services` WHERE `code` = 'PRIM3');

-- ----------------------------------------------------------------------------
-- 3. Tarifs TTC validés (feuille « Proposition IT »)
-- ----------------------------------------------------------------------------
UPDATE `detail_type_services` SET `montant` = 22600, `updated_at` = @now WHERE `code` = 'REI01';
UPDATE `detail_type_services` SET `montant` = 14100, `updated_at` = @now WHERE `code` = 'REI02';
UPDATE `detail_type_services` SET `montant` = 15570, `updated_at` = @now WHERE `code` = 'REI03';
UPDATE `detail_type_services` SET `montant` = 6300,  `updated_at` = @now WHERE `code` = 'OPS01';
UPDATE `detail_type_services` SET `montant` = 22600, `updated_at` = @now WHERE `code` = 'OPS02';
UPDATE `detail_type_services` SET `montant` = 22600, `updated_at` = @now WHERE `code` = 'PRIM1';
UPDATE `detail_type_services` SET `montant` = 14100, `updated_at` = @now WHERE `code` = 'PRIM2';
UPDATE `detail_type_services` SET `montant` = 15570, `updated_at` = @now WHERE `code` = 'PRIM3';

-- ----------------------------------------------------------------------------
-- 4. Catalogue de ventilation (24 articles)
-- ----------------------------------------------------------------------------

-- REI01 — Auto (22 600 F)
INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI01_1', 'Auto-Sécurisation plaque vierge', 'A la génération de la 1ère immatriculation', 2491.53, 2940, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI01'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI01_2', 'Auto-Pose sécurisation QR code', 'A la pose de la plaque', 4745.76, 5600, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI01'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI01_3', 'Auto-Plaques', 'A la pose de la plaque', 11915.25, 14060, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI01'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

-- REI02 — Moto (14 100 F)
INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI02_1', 'Moto-Sécurisation plaque vierge', 'A la génération de la 1ère immatriculation', 2966.10, 3500, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI02'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI02_2', 'Moto-Pose sécurisation QR code', 'A la pose de la plaque', 4745.76, 5600, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI02'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI02_3', 'Moto-Plaques', 'A la pose de la plaque', 4237.29, 5000, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI02'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

-- REI03 — Semi-remorque (15 570 F)
INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI03_1', 'Semi-remorque-Sécurisation plaque vierge', 'A la génération de la 1ère immatriculation', 2491.53, 2940, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI03'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI03_2', 'Semi-remorque-Pose sécurisation QR code', 'A la pose de la plaque', 4745.76, 5600, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI03'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'REI03_3', 'Semi-remorque-Plaques', 'A la pose de la plaque', 5957.63, 7030, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'REI03'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

-- OPS01 — Opération Spéciale Moto (6 300 F)
INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'OPS01_1', 'OPS Moto-Sécurisation plaque vierge', 'A la génération de la 1ère immatriculation', 847.46, 1000, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'OPS01'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'OPS01_2', 'OPS Moto-Pose sécurisation QR code', 'A la pose de la plaque', 1949.15, 2300, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'OPS01'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'OPS01_3', 'OPS Moto-Plaques', 'A la pose de la plaque', 2542.37, 3000, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'OPS01'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

-- OPS02 — Opération Spéciale Auto (22 600 F)
INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'OPS02_1', 'OPS Auto-Sécurisation plaque vierge', 'A la génération de la 1ère immatriculation', 2491.53, 2940, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'OPS02'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'OPS02_2', 'OPS Auto-Pose sécurisation QR code', 'A la pose de la plaque', 4745.76, 5600, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'OPS02'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'OPS02_3', 'OPS Auto-Plaques', 'A la pose de la plaque', 11915.25, 14060, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'OPS02'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

-- PRIM1 — Primo-Espèce Auto (22 600 F)
INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM1_1', 'Auto-Sécurisation plaque vierge', 'A la génération de la 1ère immatriculation', 2491.53, 2940, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM1'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM1_2', 'Auto-Pose sécurisation QR code', 'A la pose de la plaque', 4745.76, 5600, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM1'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM1_3', 'Auto-Plaques', 'A la pose de la plaque', 11915.25, 14060, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM1'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

-- PRIM2 — Primo-Espèce Moto (14 100 F)
INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM2_1', 'Moto-Sécurisation plaque vierge', 'A la génération de la 1ère immatriculation', 2966.10, 3500, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM2'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM2_2', 'Moto-Pose sécurisation QR code', 'A la pose de la plaque', 4745.76, 5600, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM2'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM2_3', 'Moto-Plaques', 'A la pose de la plaque', 4237.29, 5000, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM2'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

-- PRIM3 — Primo-Espèce Semi-Remorque (15 570 F)
INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM3_1', 'Semi-remorque-Sécurisation plaque vierge', 'A la génération de la 1ère immatriculation', 2491.53, 2940, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM3'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM3_2', 'Semi-remorque-Pose sécurisation QR code', 'A la pose de la plaque', 4745.76, 5600, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM3'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

INSERT INTO `billing_articles`
  (`detail_type_service_id`, `code`, `libelle`, `fait_generateur`, `montant_ht`, `montant_ttc`, `compte_x3`, `actif`, `created_at`, `updated_at`)
SELECT d.`id`, 'PRIM3_3', 'Semi-remorque-Plaques', 'A la pose de la plaque', 5957.63, 7030, NULL, 1, @now, @now
FROM `detail_type_services` d WHERE d.`code` = 'PRIM3'
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `fait_generateur` = VALUES(`fait_generateur`), `montant_ht` = VALUES(`montant_ht`), `montant_ttc` = VALUES(`montant_ttc`), `actif` = VALUES(`actif`), `updated_at` = @now;

-- ----------------------------------------------------------------------------
-- Contrôles après exécution (doivent renvoyer 8 services / 24 articles)
-- ----------------------------------------------------------------------------
-- SELECT code, id, element_facturation, montant FROM detail_type_services
--   WHERE code IN ('REI01','REI02','REI03','OPS01','OPS02','PRIM1','PRIM2','PRIM3')
--   ORDER BY code;
--
-- SELECT d.code AS service, a.code AS article, a.libelle, a.montant_ht, a.montant_ttc
--   FROM billing_articles a
--   JOIN detail_type_services d ON d.id = a.detail_type_service_id
--   ORDER BY d.code, a.code;
