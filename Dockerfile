FROM php:8.2-apache

# Fix MPM module conflicts and install PHP extensions
RUN set -eux; \
    # Disable conflicting MPM modules
    a2dismod mpm_event mpm_worker 2>/dev/null || true; \
    # Enable mpm_prefork
    a2enmod mpm_prefork; \
    # Enable rewrite module
    a2enmod rewrite; \
    # Install PHP extensions
    docker-php-ext-install pdo pdo_mysql; \
    # Set ServerName to suppress warning
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY . /var/www/html/
WORKDIR /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]

