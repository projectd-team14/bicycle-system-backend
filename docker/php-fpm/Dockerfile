FROM php:8.2-fpm

COPY php.ini /usr/local/etc/php/

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
  && apt-get install -y zlib1g-dev mariadb-client git zip unzip vim libzip-dev \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-install pdo_mysql