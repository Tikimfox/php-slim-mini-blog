FROM php:8.2-apache

RUN a2dismod mpm_event || true && \
    a2dismod mpm_worker || true && \
    a2dismod mpm_prefork || true

# Вмикаємо тільки prefork
RUN a2enmod mpm_prefork

# PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Rewrite для Slim
RUN a2enmod rewrite

COPY . /var/www/html/
WORKDIR /var/www/html

EXPOSE 80
