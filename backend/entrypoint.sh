#!/bin/bash
set -e

echo "============================================"
echo "Soft ERP Backend - Starting..."
echo "============================================"

echo "Waiting for MySQL to be ready..."
until mysql -h"${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; do
    echo "MySQL is unavailable - sleeping"
    sleep 3
done

echo "MySQL is up and ready!"

if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader --no-dev
else
    echo "Composer dependencies already installed"
fi

if [ ! -f ".env" ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo ".env file created"
else
    echo ".env file already exists"
fi

if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --ansi
    echo "Application key generated"
else
    echo "Application key already exists"
fi

echo "Optimizing Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Running database migrations and seeders..."
php artisan migrate --force --seed

php artisan config:cache
php artisan route:cache

echo "============================================"
echo "Backend is ready!"
echo "Starting Laravel development server..."
echo "============================================"

exec php artisan serve --host=0.0.0.0 --port=8000
