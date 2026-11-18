-- ============================================================================
-- Annulation d'un paiement avec traçabilité — production
-- ----------------------------------------------------------------------------
-- Colonnes ajoutées à la table `paiements` :
--   statut             1 = actif, 2 = annulé (défaut 1)
--   annule_at          date/heure de l'annulation
--   annule_par         id de l'utilisateur qui a annulé
--   motif_annulation   motif saisi (facultatif)
--
-- Alternative recommandée si le code est déployé :
--   php artisan migrate --path=database/migrations/2026_11_17_000001_add_annulation_to_paiements_table.php
-- ============================================================================

ALTER TABLE `paiements`
  ADD COLUMN `statut` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '1 = actif, 2 = annulé' AFTER `mode_paiement`,
  ADD COLUMN `annule_at` timestamp NULL DEFAULT NULL AFTER `statut`,
  ADD COLUMN `annule_par` bigint unsigned NULL DEFAULT NULL AFTER `annule_at`,
  ADD COLUMN `motif_annulation` varchar(255) NULL DEFAULT NULL AFTER `annule_par`,
  ADD KEY `paiements_statut_index` (`statut`);

-- Contrôle
-- SHOW COLUMNS FROM `paiements` LIKE 'statut';
-- SELECT statut, COUNT(*) FROM paiements GROUP BY statut;
