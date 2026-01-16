# syntax=docker/dockerfile:1.6

FROM php:8.4-fpm-alpine AS dev

WORKDIR /var/www/html

# Outils de base + extensions PHP nécessaires pour Laravel
RUN apk add --no-cache bash sqlite-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring

# Le code sera monté par le volume (./:/var/www/html),
# mais on copie quand même pour les builds CI éventuels
COPY . .

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000
