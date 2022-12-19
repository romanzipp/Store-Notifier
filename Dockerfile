FROM composer AS composer
FROM php:8.1-cli

ARG DEBIAN_FRONTEND=noninteractive

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y git

COPY . /app
WORKDIR /app

RUN composer install --prefer-dist --no-scripts --no-dev

CMD [ "sh", "run/scheduler.sh" ]
