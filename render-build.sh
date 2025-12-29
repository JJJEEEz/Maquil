#!/usr/bin/env bash
# Salir si hay un error
set -o errexit

# Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# Instalar dependencias JavaScript y compilar assets
npm ci
npm run build

# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders para datos iniciales
php artisan db:seed

# Optimizaciones para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
