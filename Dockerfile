FROM php:8.3-apache

# Install system dependencies, PHP extensions for PostgreSQL, and Node.js
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd
# Enable Apache mod_rewrite (required for Laravel routing)
RUN a2enmod rewrite

# Allow .htaccess to work by changing AllowOverride None to AllowOverride All
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy all application files to the container
COPY . .

# Install PHP dependencies via Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend assets (Vite)
RUN npm install
RUN npm run build

# Set required permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create a startup script that runs when the container boots
# This sets the correct port for Render, runs database migrations, and starts Apache
RUN echo '#!/bin/bash\n\
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan migrate --force\n\
apache2-foreground\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Run the startup script
CMD ["/usr/local/bin/start.sh"]
