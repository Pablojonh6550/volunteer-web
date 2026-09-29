FROM php:8.4-fpm-alpine

WORKDIR /var/www

RUN apk add --no-cache \
        bash \
        git \
        curl \
        nodejs \
        npm \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        libxml2-dev \
        oniguruma-dev \
        icu-dev \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && apk del linux-headers

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 www && adduser -G www -g www -s /bin/bash -D www

USER www

EXPOSE 9000
CMD ["php-fpm"]
