#!/bin/bash
set -e

# Só roda migrations se o banco estiver configurado
# Evita que processos PHP travem esperando conexão inexistente
(sleep 3 && \
  if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" != "sqlite" ]; then
    echo "==> Rodando migrations..."
    php artisan migrate --force --no-interaction 2>/dev/null || echo "==> Migrations falharam (verifique as variáveis de DB no Render)"
  else
    echo "==> DB_HOST não configurado, pulando migrations."
  fi
) &

# Inicia o Apache em primeiro plano na porta 80
exec apache2-foreground
