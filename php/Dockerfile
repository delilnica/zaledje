FROM php:8.2-apache

COPY --from=composer:2.6 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && \
    apt-get install -y libzip-dev zip

RUN docker-php-ext-install zip mysqli
