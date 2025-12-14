FROM php:8.2-apache

RUN a2enmod rewrite \
 && docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/
WORKDIR /var/www/html

EXPOSE 80
