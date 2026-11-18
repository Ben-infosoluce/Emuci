# Procédure production — ventilation, annulation et rejeu X3

Date : 2026-11-18
Branche : `main` (commit `70f2f2f`)

> À exécuter **dans l'ordre**, en heure creuse. Chaque étape est idempotente ou
> contrôlée par une requête de vérification.

---

## 0. Pré-checks (lecture seule)

```sql
-- Nombre de paiements et de dossiers
SELECT COUNT(*) AS paiements FROM paiements;
SELECT COUNT(*) AS dossiers FROM dossiers;
SELECT COUNT(*) AS dossiers_payes FROM dossiers WHERE statut_paiement = 2;

-- Paiements déjà ventilés
SELECT COUNT(DISTINCT paiement_id) AS paiements_ventiles FROM paiement_lignes;

-- Colonne d'annulation déjà présente ?
SHOW COLUMNS FROM paiements LIKE 'statut';

-- Doublons de NUM X3 potentiels : plusieurs paiements pour un même dossier
SELECT id_dossier, COUNT(*) AS nb FROM paiements
GROUP BY id_dossier HAVING nb > 1;
```

Consigner les résultats (capture ou fichier) avant toute écriture.

---

## 1. Sauvegarde

```bash
cd /chemin/vers/emuci
php artisan down --retry=60        # optionnel : maintenance courte
mysqldump -h HOST -u USER -p BASE \
  paiements paiement_lignes dossiers caisses caisse_ouvertures \
  > backup_avant_ventilation_$(date +%F_%H%M).sql
```

Vérifier que le fichier n'est pas vide :

```bash
ls -lh backup_avant_ventilation_*.sql
```

---

## 2. Schéma et catalogue de ventilation

Vérifier d'abord l'état de la base :

```sql
SHOW TABLES LIKE 'billing_articles';
SHOW TABLES LIKE 'paiement_lignes';
SHOW COLUMNS FROM detail_type_services LIKE 'code';
```

**Si les tables n'existent pas encore** (code déjà déployé) :

```bash
php artisan migrate --path=database/migrations/2026_11_14_000001_create_billing_articles_table.php --force
php artisan migrate --path=database/migrations/2026_11_14_000002_create_paiement_lignes_table.php --force
php artisan migrate --path=database/migrations/2026_11_14_000003_add_code_to_detail_type_services_table.php --force
php artisan migrate --path=database/migrations/2026_11_14_000004_seed_ventilation_catalog.php --force
```

**Sinon**, exécuter uniquement le seed (idempotent) :

```bash
mysql -h HOST -u USER -p BASE < database/sql/2026_11_14_ventilation_seed.sql
```

Contrôle :

```sql
SELECT COUNT(*) FROM billing_articles;   -- attendu : 24
SELECT COUNT(*) FROM detail_type_services WHERE code IS NOT NULL;
```

---

## 3. Annulation des paiements (colonne `statut`)

Code déjà déployé : utiliser la migration.

```bash
php artisan migrate --path=database/migrations/2026_11_17_000001_add_annulation_to_paiements_table.php --force
```

Alternative SQL si la migration ne peut pas être lancée :

```bash
mysql -h HOST -u USER -p BASE < database/sql/2026_11_17_add_annulation_paiements.sql
```

Contrôle : les 100 % des paiements existants doivent être actifs.

```sql
SHOW COLUMNS FROM paiements LIKE 'statut';
SELECT statut, COUNT(*) FROM paiements GROUP BY statut;
-- attendu : statut = 1 pour tous les anciens paiements
```

---

## 4. Re-ventilation de l'historique

Toujours commencer par une simulation :

```bash
php artisan ventilation:reventiler --dry-run
```

Contrôler : `0 erreur`. Les paiements annulés sont automatiquement ignorés.

Puis exécution réelle, par lots (permet de reprendre en cas de coupure) :

```bash
php artisan ventilation:reventiler --chunk=500
```

En cas de reprise, relancer la même commande : seuls les paiements sans lignes
sont traités.

Contrôle :

```sql
-- Aucun paiement actif ne doit rester sans ventilation
SELECT COUNT(*) FROM paiements p
WHERE (p.statut IS NULL OR p.statut <> 2)
  AND NOT EXISTS (SELECT 1 FROM paiement_lignes l WHERE l.paiement_id = p.id);

-- La somme des lignes doit égaler montant - timbre (tolérance 1 F par arrondi)
SELECT p.id, p.montant, p.timbre,
       SUM(l.montant_ttc) AS total_lignes,
       ABS((p.montant - p.timbre) - SUM(l.montant_ttc)) AS ecart
FROM paiements p
JOIN paiement_lignes l ON l.paiement_id = p.id
WHERE (p.statut IS NULL OR p.statut <> 2)
GROUP BY p.id, p.montant, p.timbre
HAVING ecart > 1
LIMIT 50;
```

Attendu : la seconde requête ne retourne aucune ligne.

---

## 5. Rejeu X3

⚠️ Le job `SendPaiementsToX3Job` **n'est pas idempotent** : un double dispatch
recrée les factures. Ne le lancer qu'une fois par périmètre.

Avant lancement :

```sql
-- Paiements qui seraient envoyés
SELECT COUNT(*) FROM paiements
WHERE (statut IS NULL OR statut <> 2);

-- Dossiers avec plusieurs paiements (risque de plusieurs en-têtes même NUM)
SELECT id_dossier, COUNT(*) AS nb FROM paiements
WHERE (statut IS NULL OR statut <> 2)
GROUP BY id_dossier HAVING nb > 1;
```

Pour les dossiers multi-paiements, valider avec la comptabilité qu'un seul
en-tête X3 est attendu (ou exclure les doublons du lot).

Lancer le rejeu par périmètre (date / site / caisse), pas en une fois sur tout
l'historique, et vérifier les logs X3 après chaque lot.

---

## 6. Retour arrière

Si un problème est détecté avant le rejeu X3 :

```sql
-- Annuler la re-ventilation (les paiements restent intacts)
DELETE FROM paiement_lignes WHERE paiement_id IN (...);

-- Retirer les colonnes d'annulation (uniquement si aucun paiement annulé)
ALTER TABLE paiements
  DROP INDEX paiements_statut_index,
  DROP COLUMN motif_annulation,
  DROP COLUMN annule_par,
  DROP COLUMN annule_at,
  DROP COLUMN statut;
```

En cas de problème après envoi X3 : ne pas relancer le job. Corriger côté X3
(extourne) et documenter les références concernées.
