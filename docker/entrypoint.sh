#!/bin/bash
set -e

# Render binds dynamic port via $PORT
PORT="${PORT:-80}"
echo "--> Configuring Apache to listen on port ${PORT} (0.0.0.0)..."
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/*:80/*:${PORT}/g" /etc/apache2/sites-available/*.conf

# Ensure .env exists in container
if [ ! -f /var/www/html/.env ]; then
    echo "--> Copying .env.example to .env..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure storage, bootstrap/cache, and database directories exist and have full write permissions
echo "--> Setting permissions for storage, cache, and database..."
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/temp_imports \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

if [ "$DB_CONNECTION" = "sqlite" ]; then
    touch /var/www/html/database/database.sqlite
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "--> Generating application key..."
    php artisan key:generate --force
fi

# Clear old cached files first to prevent stale references
echo "--> Clearing stale caches and discovering packages..."
php artisan config:clear || true
php artisan view:clear || true
php artisan route:clear || true
php artisan package:discover --ansi || true

# Run Database Migrations in production
if [ "$RUN_MIGRATIONS" = "true" ] || [ "$APP_ENV" = "production" ]; then
    echo "--> Running database migrations..."
    php artisan migrate --force --isolated || true
    php artisan migrate --force || true
fi

# Seed Database if requested
# Seed Database if requested or if users table is empty
if [ "$SEED_DATABASE" = "true" ]; then
    echo "--> Seeding database with initial data..."
    php artisan db:seed --force || true
fi

# Cache configurations for optimal production speed
if [ "$APP_ENV" = "production" ]; then
    echo "--> Caching configuration, routes, and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "--> Khosamphi Hospital Analytics is ready on port ${PORT}!"
exec apache2-foreground

