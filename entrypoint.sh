#!/bin/bash
set -e

# Roda as migrations e seeders em segundo plano sem bloquear a inicialização da porta 80 do Apache
(sleep 2 && php artisan migrate --force --no-interaction && php artisan db:seed --force --no-interaction) &

# Inicia o Apache em primeiro plano na porta 80
exec apache2-foreground
