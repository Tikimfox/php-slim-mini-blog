FROM php:8.2-apache

# Disable conflicting MPM modules and ensure only mpm_prefork is loaded
RUN a2dismod mpm_event mpm_worker || true \
 && a2enmod mpm_prefork rewrite \
 && docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/
WORKDIR /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

EXPOSE 80
