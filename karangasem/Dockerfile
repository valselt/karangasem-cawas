FROM php:8.2-fpm

# 1. Instal dependencies sistem & library PHP yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev libzip-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd mysqli zip

# 2. Instal Composer secara global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Set Working Directory
WORKDIR /var/www/html

# 4. Instal AWS SDK for PHP menggunakan Composer
# Kita jalankan ini agar folder 'vendor' terbentuk di dalam image
RUN composer require aws/aws-sdk-php

# 5. Copy custom php.ini (seperti sebelumnya)
COPY php.ini /usr/local/etc/php/php.ini