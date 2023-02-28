FROM composer AS composer
FROM php:8.1-cli

ARG DEBIAN_FRONTEND=noninteractive

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y git
RUN apt-get install -y libmagickwand-dev  &&pecl install imagick && docker-php-ext-enable imagick

COPY . /app
WORKDIR /app

RUN composer install --prefer-dist --no-scripts --no-dev

CMD [ "sh", "run/scheduler.sh" ]
