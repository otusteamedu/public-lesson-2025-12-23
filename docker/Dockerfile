FROM php:8.3-fpm-trixie

COPY --from=composer:2.8.1 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y --allow-unauthenticated --no-install-recommends curl \
    apt-utils \
    aptitude \
    git \
    iputils-ping \
    libzip-dev \
    zip \
    vim \
    unzip \
    libicu-dev \
    libpq-dev \
    libgmp-dev \
    libffi-dev \
    libssl-dev \
    cron \
    libpng-dev \
    libfreetype6-dev \
    librabbitmq-dev \
    gcc \
    g++ \
    make \
    memcached

RUN docker-php-ext-configure intl && docker-php-ext-install intl
RUN docker-php-ext-install \
    bcmath \
    gd \
    gmp \
    ffi \
    opcache \
    pcntl \
    pdo \
    pdo_pgsql \
    sockets \
    zip

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

RUN install-php-extensions xdebug
RUN echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.trigger_value=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN apt-get autoremove --purge -y && apt-get autoclean -y && apt-get clean -y

