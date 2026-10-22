#!/bin/bash

# Durcissement php2shell (CVE-2026-109618 / CVE-2026-109619).
# Appliqué en ligne de commande car ce fichier est le point d'entrée Nixpacks :
# il fonctionne même si aucun php.ini n'est déployé avec l'image.
PHP_FLAGS="-d session.upload_progress.enabled=0 -d fatal_error_backtraces=0 -d expose_php=0 -d display_errors=0 -d display_startup_errors=0 -d log_errors=1 -d error_log=/dev/stderr"

# Garde-fou : trace la version réellement servie et alerte si elle est vulnérable.
if [ -f scripts/php2shell-check.sh ]; then
  bash scripts/php2shell-check.sh --no-containers || \
    echo "!!! ATTENTION : PHP vulnérable à CVE-2026-109618 / CVE-2026-109619 — voir SECURITY-php2shell.md"
fi

# 1. On s'assure que les dossiers de cache et logs existent sur le serveur
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache

# 2. Nettoyage du cache (indispensable pour oublier les chemins "Macbook")
php $PHP_FLAGS artisan config:clear
php $PHP_FLAGS artisan cache:clear

# 3. Lancement du worker en arrière-plan (&)
# On redirige la sortie vers les logs pour pouvoir débugger plus tard
php $PHP_FLAGS artisan queue:work --daemon --tries=3 > storage/logs/worker.log 2>&1 &

# 4. Lancement du serveur (RESTE AU PREMIER PLAN)
# On lance php -S directement : artisan serve ne transmet pas les flags -d
# au processus enfant, donc le durcissement ne serait pas appliqué aux requêtes.
exec php $PHP_FLAGS -S 0.0.0.0:80 -t public server.php
