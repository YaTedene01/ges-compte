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

# Ensure `public/swagger.json` exists at container start.
# If the file was generated during the image build it will already be present.
# If not, copy it from storage (it may have been generated during build/publish),
# otherwise do nothing here to avoid heavy startup work.
if [ -f "/var/www/html/storage/api-docs/swagger.json" ] && [ ! -f "/var/www/html/public/swagger.json" ]; then
	echo "Copying generated swagger.json to public folder"
	cp /var/www/html/storage/api-docs/swagger.json /var/www/html/public/swagger.json || true
	chown www-data:www-data /var/www/html/public/swagger.json || true
fi

# Clear all caches after generating docs
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Start Apache
apache2-foreground