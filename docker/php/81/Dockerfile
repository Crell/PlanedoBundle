FROM php:8.1-cli
WORKDIR /usr/src/myapp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install zip unzip git libicu-dev -y \
    && pecl install xdebug \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && mkdir /.composer && chmod 777 /.composer
