FROM php:8.2-fpm

# 1. Instal dependensi sistem
# Tambahkan 'git' dan 'curl' (dibutuhkan oleh Composer)
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev libzip-dev unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd mysqli zip

# 2. Instal Composer
# Ini mengambil composer terbaru dari image resmi dan memasukkannya ke container ini
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Copy konfigurasi PHP custom
COPY php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html