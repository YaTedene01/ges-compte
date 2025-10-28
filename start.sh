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
# Swagger documentation is generated at build time and shipped in public/swagger.json
# (See Dockerfile). Avoid generating docs at runtime to keep startup fast and deterministic.

# Clear all caches after generating docs
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Start Apache
apache2-foreground