PHP_VERSION?=8.4

update: composer-update composer-validate
test: phpstan psalm coverage
all: build composer-install test

build:
	export PHP_VERSION=${PHP_VERSION} && \
	docker-compose build
php-version:
	export PHP_VERSION=${PHP_VERSION} && \
    docker-compose run --remove-orphans php php -v
composer-install:
	export PHP_VERSION=${PHP_VERSION} && \
    docker-compose run --remove-orphans php composer install
composer-update:
	export PHP_VERSION=${PHP_VERSION} && \
    docker-compose run --remove-orphans php composer update
composer-validate:
	export PHP_VERSION=${PHP_VERSION} && \
    docker-compose run --remove-orphans php composer validate
phpstan:
	export PHP_VERSION=${PHP_VERSION} && \
    docker-compose run --remove-orphans php composer phpstan
psalm:
	export PHP_VERSION=${PHP_VERSION} && \
    docker-compose run --remove-orphans php composer psalm
coverage:
	export PHP_VERSION=${PHP_VERSION} && \
    docker-compose run --remove-orphans php composer coverage