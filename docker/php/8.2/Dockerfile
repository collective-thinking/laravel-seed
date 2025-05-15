FROM php:8.2-alpine

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN apk add --no-cache $PHPIZE_DEPS linux-headers\
    && pecl install xdebug  \
    && docker-php-ext-enable xdebug
