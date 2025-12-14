FROM php:8.2-apache

# Fix MPM module conflicts and install PHP extensions
RUN set -eux; \
    # Disable conflicting MPM modules
    a2dismod mpm_event mpm_worker 2>/dev/null || true; \
    # Drop leftover MPM symlinks to ensure only prefork loads
    rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf; \
    # Enable prefork and rewrite modules
    a2enmod mpm_prefork rewrite; \
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
