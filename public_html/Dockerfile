FROM php:8.2-apache
WORKDIR /var/www/html
COPY . .
RUN apt-get update \
    && apt-get install -y \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        pdo_mysql \
        zip
