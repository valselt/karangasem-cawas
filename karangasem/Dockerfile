FROM php:8.2-fpm

# instal extensi php
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd mysqli zip

# copy custom php.ini
COPY php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html
