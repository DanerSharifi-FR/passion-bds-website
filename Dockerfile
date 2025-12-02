# syntax=docker/dockerfile:1.6

FROM php:8.4-fpm-alpine AS dev

WORKDIR /var/www/html

RUN apk add --no-cache bash

COPY . .

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000
