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

# Start Apache
apache2-foreground