#!/bin/bash

# Durcissement php2shell (CVE-2026-109618 / CVE-2026-109619).
# Appliqué en ligne de commande car ce fichier est le point d'entrée Nixpacks :
# il fonctionne même si aucun php.ini n'est déployé avec l'image.
PHP_FLAGS="-d session.upload_progress.enabled=0 -d fatal_error_backtraces=0"

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
php artisan config:clear
php artisan cache:clear

# 3. Lancement du worker en arrière-plan (&)
# On redirige la sortie vers les logs pour pouvoir débugger plus tard
php $PHP_FLAGS artisan queue:work --daemon --tries=3 > storage/logs/worker.log 2>&1 &

# 4. Lancement du serveur (RESTE AU PREMIER PLAN)
# On reprend exactement votre commande actuelle
php $PHP_FLAGS artisan serve --host=0.0.0.0 --port=80