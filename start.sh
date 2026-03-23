#!/bin/bash

# 1. On s'assure que les dossiers de cache et logs existent sur le serveur
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache

# 2. Nettoyage du cache (indispensable pour oublier les chemins "Macbook")
php artisan config:clear
php artisan cache:clear

# 3. Lancement du worker en arrière-plan (&)
# On redirige la sortie vers les logs pour pouvoir débugger plus tard
php artisan queue:work --daemon --tries=3 > storage/logs/worker.log 2>&1 &

# 4. Lancement du serveur (RESTE AU PREMIER PLAN)
# On reprend exactement votre commande actuelle
php artisan serve --host=0.0.0.0 --port=80