#!/bin/bash
set -e

# Render binds dynamic port via $PORT
PORT="${PORT:-80}"
echo "--> Configuring Apache to listen on port ${PORT} (0.0.0.0)..."
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/*:80/*:${PORT}/g" /etc/apache2/sites-available/*.conf

# Fix permissions
echo "--> Setting permissions for storage and bootstrap/cache..."
mkdir -p /var/www/html/database
if [ "$DB_CONNECTION" = "sqlite" ]; then
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate key if not set
if [ -z "$APP_KEY" ]; then
    echo "--> Generating application key..."
    php artisan key:generate --force
fi

# Run Database Migrations in production
if [ "$RUN_MIGRATIONS" = "true" ] || [ "$APP_ENV" = "production" ]; then
    echo "--> Running database migrations..."
    php artisan migrate --force --isolated || true
fi

# Seed Database if requested
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

