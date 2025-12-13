FROM php:8.2-apache

# Вимикаємо ВСІ MPM
RUN a2dismod mpm_event mpm_worker || true

# Вмикаємо тільки prefork
RUN a2enmod mpm_prefork

# PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Rewrite для Slim
RUN a2enmod rewrite

COPY . /var/www/html/
WORKDIR /var/www/html

EXPOSE 80
