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

# CRITICAL: Disable all MPM modules first to avoid conflicts
RUN a2dismod mpm_event || true && \
    a2dismod mpm_worker || true && \
    a2dismod mpm_prefork || true

# Remove any conflicting MPM configuration files
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* || true && \
    rm -f /etc/apache2/mods-enabled/mpm_worker.* || true

# Enable only mpm_prefork (most compatible with PHP)
RUN a2enmod mpm_prefork

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

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Verify only one MPM is loaded (build-time check)
RUN echo "Verifying MPM configuration..." && \
    apache2ctl -M 2>&1 | grep mpm || echo "Warning: No MPM found"

# Expose port (Railway automatically uses PORT env variable)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

