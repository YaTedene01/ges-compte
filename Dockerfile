# Use the official PHP image with Apache
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Generate Swagger documentation at build time and include it in the image
# This ensures the deployed image serves the current API docs without relying on runtime generation.
RUN php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --force || true && \
    php artisan l5-swagger:generate || true && \
    mkdir -p public/vendor/swagger-api/swagger-ui/dist && \
    cp -r vendor/swagger-api/swagger-ui/dist/* public/vendor/swagger-api/swagger-ui/dist/ 2>/dev/null || true && \
    cp storage/api-docs/swagger.json public/swagger.json || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod +x start.sh

# Copy custom Apache configuration
COPY apache2.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite and the site
RUN a2enmod rewrite && a2ensite 000-default

# Expose port 80
EXPOSE 80

# Start with custom script
CMD ["./start.sh"]