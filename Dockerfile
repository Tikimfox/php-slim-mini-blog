# Use official PHP with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Fix MPM issue - ensure only mpm_prefork is loaded
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true

# Enable required Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . /var/www/html

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create storage directory and set permissions
RUN mkdir -p /var/www/html/storage && \
    chown -R www-data:www-data /var/www/html/storage && \
    chmod -R 775 /var/www/html/storage

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Create startup script for Railway PORT support
RUN printf '#!/bin/bash\nset -e\nPORT=${PORT:-80}\nsed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf\nsed -i "s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/*.conf\nexec apache2-foreground\n' > /usr/local/bin/start-apache.sh && \
    chmod +x /usr/local/bin/start-apache.sh

# Expose port (Railway uses PORT env variable)
EXPOSE 80

# Start Apache with Railway PORT support
CMD ["/usr/local/bin/start-apache.sh"]

