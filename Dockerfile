# Stage 1: PHP dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN php scripts/check-php-leading-output.php
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Frontend assets (skip if you have no Vite/Mix build)
FROM node:24-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# Stage 3: Run the application
FROM php:8.5-apache AS runner

RUN apt-get update \
    && apt-get install -y \
        libicu-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        pdo_pgsql \
        pgsql \
        zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Point Apache at Laravel's public/ folder and allow .htaccess
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    AllowOverride All\n</Directory>\n' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html
COPY --from=vendor /app ./
COPY --from=assets /app/public/build ./public/build
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80