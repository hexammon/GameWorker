ARG BASE_DOCKER_IMAGE

FROM ${BASE_DOCKER_IMAGE}

# get composer and required tools
RUN curl -OL https://getcomposer.org/download/1.5.2/composer.phar \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer \
    && apk update \
    && apk add git \
    && apk add zlib-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/cache/apk/*

# enable debug
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug.so

