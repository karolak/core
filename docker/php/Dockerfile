ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-alpine
WORKDIR /var/www

RUN apk --no-cache --update add ${PHPIZE_DEPS} \
    && pecl install pcov && docker-php-ext-enable pcov \
    && apk del ${PHPIZE_DEPS} \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer