#!/bin/bash

# Run migrations and seeders
php artisan migrate --seed

# Start Apache
apache2-foreground