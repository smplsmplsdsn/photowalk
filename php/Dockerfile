FROM php:8.2-fpm

# 必要なライブラリをインストール
RUN apt-get update && apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        unzip \
        curl \
        libmagickwand-dev \
        imagemagick \
        libheif-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli mbstring gd zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-enable pdo_mysql \
    && rm -rf /var/lib/apt/lists/*