ARG PHP_VERSION
ARG PHP_FROM="php:$PHP_VERSION-fpm"

FROM $PHP_FROM

ARG APP_ENV
ARG PROJECT_ROOT
ARG TIMEZONE

ENV APP_ENV=$APP_ENV

# Install dependencies & php extensions
RUN apt-get update && apt-get install -y
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        nano \
        unzip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libjpeg-dev \
        libicu-dev \
        libsodium-dev \
        libxslt-dev \
        zlib1g-dev \
        libssl-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        librabbitmq-dev \
        pkg-config \
        supervisor \
        cron \
        openssl \
    && docker-php-ext-install \
        gettext \
        gd \
        intl \
        opcache \
        pcntl \
		pdo pgsql pdo_pgsql \
		sodium \
        xsl \
        zip \
    && pecl install \
        amqp \
        apcu \
        xdebug \
        redis \
    && docker-php-ext-enable \
        amqp \
        apcu \
        opcache \
        xdebug \
        redis

# Copy php ini file
COPY ./env/$APP_ENV/php.ini /usr/local/etc/php/php.ini
RUN sed -i -e "s#TIMEZONE#$TIMEZONE#g" /usr/local/etc/php/php.ini

# Copy Xdebug
COPY ./debug/xdebug.ini /tmp/
RUN cat /tmp/xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN rm /tmp/xdebug.ini

# Composer install
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# or
# RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1

# create composer cache directory
RUN mkdir -p /var/www/.composer && chown -R www-data /var/www/.composer

# Composer parallel install plugin
#RUN composer global require hirak/prestissimo

# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
	composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
	composer clear-cache

# add supervisor
#RUN mkdir -p /var/log/supervisor
#COPY --chown=root:root ./supervisord/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
#COPY --chown=root:root ./docker/other/cron /var/spool/cron/crontabs/root
#RUN chmod 0600 /var/spool/cron/crontabs/root

# Node.js 13 && Yarn install
RUN apt-get update && \
    apt-get install -y --no-install-recommends gnupg && \
    curl -sL https://deb.nodesource.com/setup_13.x | bash - && \
    apt-get update && \
    apt-get install -y --no-install-recommends nodejs && \
#    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
#    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
#    apt-get update && \
#    apt-get install -y --no-install-recommends yarn && \
    npm install -g npm

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install -y yarn --no-install-recommends

RUN usermod -u 1000 www-data

WORKDIR $PROJECT_ROOT

