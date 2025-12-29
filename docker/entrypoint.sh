#!/bin/bash
set -e

echo "Starting Laravel application initialization..."

# Create necessary directories if they don't exist
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/sessions

# Ensure proper permissions
chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Wait for database if needed
if [ ! -z "$DATABASE_URL" ]; then
    echo "Database URL configured, waiting for database..."
    sleep 5
fi

# Clear any stale cache
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Generate optimized cache with runtime env vars
echo "Generating Laravel config and route cache..."
php artisan config:cache || {
    echo "Config cache failed, clearing and continuing..."
    php artisan config:clear || true
}
php artisan route:cache || {
    echo "Route cache failed, clearing and continuing..."
    php artisan route:clear || true
}

# Run migrations (--force needed in production, idempotent)
echo "Running database migrations..."
php artisan migrate --force || {
    echo "Migration failed, continuing..."
    sleep 2
}

# Run seeders (--force needed in production, idempotent)
echo "Running database seeders..."
php artisan db:seed --force || {
    echo "Seeders already run or skipped, continuing..."
    sleep 2
}

echo "Laravel initialization complete. Starting services..."

# Execute the original start script from the base image
exec /start.sh
