FROM php:8.2-apache

# PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Apache rewrite (Slim)
RUN a2enmod rewrite

# Копіюємо код
COPY . /var/www/html/
WORKDIR /var/www/html

EXPOSE 80
