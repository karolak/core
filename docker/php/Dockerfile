ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-alpine
WORKDIR /var/www

RUN apk --no-cache --update add ${PHPIZE_DEPS} \
    && pecl install pcov \
    && docker-php-ext-install pdo \
    && docker-php-ext-enable pcov \
    && apk del ${PHPIZE_DEPS} \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && echo 'memory_limit = 2048M' >> ${PHP_INI_DIR}/conf.d/custom.ini