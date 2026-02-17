#!/bin/bash

set -e  # Detiene el script si algo falla

echo "Payment API - Instalación"
echo "=============================="

if [ ! -f .env ]; then
    cp .env.example .env
    echo "Archivo .env creado"
fi

if [ ! -f .env.testing ]; then
    cat <<EOL > .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
EOL
    echo "Archivo .env.testing creado"
fi

echo "Levantando contenedores..."
docker compose up -d --build

echo "Esperando MySQL..."
until docker compose exec -T mysql mysqladmin ping -h"localhost" --silent; do
    sleep 2
done

echo "Instalando dependencias..."
docker compose exec -T app composer install

echo "Generando APP_KEY..."
docker compose exec -T app php artisan key:generate

echo "Ejecutando migraciones..."
docker compose exec -T app php artisan migrate --force

echo "Cargando seeders..."
docker compose exec -T app php artisan db:seed --force

echo ""
echo "Instalación completa!"
echo "API disponible en: http://localhost:8000"
