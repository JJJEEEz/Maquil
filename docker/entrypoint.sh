#!/bin/bash
set -e

echo "Starting Laravel application initialization..."

# Wait for dependencies if needed
if [ ! -z "$DATABASE_URL" ]; then
    echo "Database URL configured"
fi

# Clear any stale cache
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Generate optimized cache with runtime env vars
php artisan config:cache
php artisan route:cache

# Run migrations (--force needed in production)
echo "Running database migrations..."
php artisan migrate --force

# Run seeders
echo "Running database seeders..."
php artisan db:seed --force

# Ensure permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "Laravel initialization complete. Starting services..."

# Execute the original start script from the base image
exec /start.sh
