-- ============================================================================
-- Ventilation des services (REI01/02/03, OPS01/02, PRIM1/2/3)
-- Création des tables et de la colonne `code` — production
-- ----------------------------------------------------------------------------
-- À exécuter AVANT le seed du catalogue :
--   database/sql/2026_11_14_ventilation_seed.sql
--
-- Alternative recommandée (Laravel gère tout, y compris le seed) :
--   php artisan migrate --path=database/migrations/2026_11_14_000001_create_billing_articles_table.php
--   php artisan migrate --path=database/migrations/2026_11_14_000002_create_paiement_lignes_table.php
--   php artisan migrate --path=database/migrations/2026_11_14_000003_add_code_to_detail_type_services_table.php
--   php artisan migrate --path=database/migrations/2026_11_14_000004_seed_ventilation_catalog.php
--
-- Ne pas exécuter ce fichier si les migrations ont déjà été passées.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. Catalogue de ventilation comptable des services
--    Ex. REI01 -> REI01_1, REI01_2, REI01_3
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `billing_articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `detail_type_service_id` bigint unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fait_generateur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_ht` decimal(14,2) NOT NULL,
  `montant_ttc` decimal(14,2) NOT NULL,
  `compte_x3` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_articles_service_code_unique` (`detail_type_service_id`, `code`),
  KEY `billing_articles_code_index` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Lignes réelles d'un paiement (ventilation figée à l'encaissement)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `paiement_lignes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paiement_id` bigint unsigned NOT NULL,
  `article_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fait_generateur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_ht` decimal(14,2) NOT NULL DEFAULT '0.00',
  `montant_ttc` decimal(14,2) NOT NULL DEFAULT '0.00',
  `compte_x3` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_detail_type_service_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paiement_lignes_paiement_id_index` (`paiement_id`),
  KEY `paiement_lignes_article_code_index` (`article_code`),
  KEY `paiement_lignes_parent_detail_type_service_id_index` (`parent_detail_type_service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Code métier stable d'un service (REI01, OPS01, PRIM1, ...)
--    À exécuter une seule fois. Vérifier d'abord que la colonne n'existe pas :
--      SHOW COLUMNS FROM `detail_type_services` LIKE 'code';
-- ----------------------------------------------------------------------------
ALTER TABLE `detail_type_services`
  ADD COLUMN `code` varchar(30) DEFAULT NULL AFTER `element_facturation`,
  ADD UNIQUE KEY `detail_type_services_code_unique` (`code`);

-- ----------------------------------------------------------------------------
-- Contrôles après exécution
-- ----------------------------------------------------------------------------
-- SHOW CREATE TABLE `billing_articles`;
-- SHOW CREATE TABLE `paiement_lignes`;
-- SHOW COLUMNS FROM `detail_type_services` LIKE 'code';
