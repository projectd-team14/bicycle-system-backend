FROM phpswoole/swoole:5.0.1-php8.2

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    gettext-base \
    libz-dev \
    libpq-dev \
    libjpeg-dev \
    libpng-dev \
    libssl-dev \
    libzip-dev \
    unzip \
    zip  \
  && apt-get clean

RUN docker-php-ext-configure zip \
  && docker-php-ext-install \
    bcmath \
    gd \
    exif \
    opcache \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    pcntl \
    zip \
  && docker-php-ext-enable gd \
  && docker-php-ext-enable redis \
  && docker-php-ext-configure gd \
    --with-jpeg=/usr/include/ \
  && rm -rf /var/lib/apt/lists/*;

ENV COMPOSER_ALLOW_SUPERUSER=1 \
  COMPOSER_HOME=/composer

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

EXPOSE 8000