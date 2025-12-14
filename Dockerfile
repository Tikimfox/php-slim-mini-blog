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

# Enable required Apache modules (don't touch MPM - base image has correct config)
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create storage directory and set permissions
RUN mkdir -p /var/www/html/storage && \
    chown -R www-data:www-data /var/www/html/storage && \
    chmod -R 775 /var/www/html/storage

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Create startup script for Railway PORT support
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Use Railway PORT or default to 80\n\
PORT=${PORT:-80}\n\
\n\
# Update Apache ports configuration\n\
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf\n\
sed -i "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/*.conf\n\
\n\
# Start Apache in foreground\n\
exec apache2-foreground\n\
' > /usr/local/bin/start-apache.sh && chmod +x /usr/local/bin/start-apache.sh

# Copy custom Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Expose port (Railway uses PORT env variable)
EXPOSE 80

# Start Apache with Railway PORT support
CMD ["/bin/bash", "/usr/local/bin/start-apache.sh"]

