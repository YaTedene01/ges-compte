#!/bin/bash

# Set permissions for storage directory
chmod -R 775 /var/www/html/storage

# Generate application key if not set
php artisan key:generate --force

# Run migrations and seeders
php artisan migrate --seed

# Clear config cache
php artisan config:clear

# Generate Swagger documentation
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --force
php artisan l5-swagger:generate

# Copy Swagger UI assets to public directory
mkdir -p public/vendor/swagger-api/swagger-ui/dist
cp -r vendor/swagger-api/swagger-ui/dist/* public/vendor/swagger-api/swagger-ui/dist/ 2>/dev/null || true

# Clear all caches after generating docs
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Start Apache
apache2-foreground