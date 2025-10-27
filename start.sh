#!/bin/bash

# Set permissions for storage directory
chmod -R 775 /var/www/html/storage

# Generate application key if not set
php artisan key:generate --force

# Run migrations and seeders
php artisan migrate --seed

# Clear config cache
php artisan config:clear

# Start Apache
apache2-foreground