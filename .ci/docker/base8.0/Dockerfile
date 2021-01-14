FROM composer:2 AS composer

###################################################
### Building all extensions in a separate stage ###
###################################################

FROM php:8.0-fpm-alpine as build-exts

RUN apk add sudo libxml2-dev icu-dev runit openssh-client curl git bash rabbitmq-c-dev libmemcached libmemcached-dev $PHPIZE_DEPS

RUN pecl install igbinary && \
    docker-php-ext-enable igbinary

RUN mkdir -p /usr/src/php/ext/amqp && \
    curl -L https://github.com/php-amqp/php-amqp/archive/master.tar.gz | tar -xzC /usr/src/php/ext/amqp --strip-components=1 && \
    docker-php-ext-install amqp

RUN mkdir -p /usr/src/php/ext/memcached && \
    curl -L https://github.com/php-memcached-dev/php-memcached/archive/master.tar.gz | tar -xzC /usr/src/php/ext/memcached --strip-components=1 && \
    docker-php-ext-configure memcached --enable-memcached-igbinary && \
    docker-php-ext-install memcached

RUN docker-php-ext-install pcntl soap pdo_mysql sockets intl opcache

#COPY ./newrelic_install.sh /
#RUN /newrelic_install.sh

###############################
### Building the base image ###
###############################

FROM php:8.0-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer

RUN apk add nginx sudo runit openssh-client curl git bash libmemcached icu rabbitmq-c

RUN sed -i '/xfs/ s/33/82/g' /etc/passwd;\
    sed -i '/www-data/ s/82/33/g' /etc/passwd; \
    sed -i '/xfs/ s/33/82/g' /etc/group;\
    sed -i '/www-data/ s/82/33/g' /etc/group; \
    mkdir -p /run/nginx; \
    mkdir /app; \
    chown 33.33 /app -R;\
    chown 33.33 /home/www-data -R; \
    mkdir -p /etc/php8; \
    mkdir -p /etc/php8/php-fpm.d; \
    mkdir -p /etc/nginx/; \
    mkdir -p /etc/nginx/conf.d; \
    chown www-data.www-data /var/lib/nginx -R; \
    mkdir /service; \
    chmod 777 /service

COPY ./runit/sv /etc/sv
COPY ./runit/runit /etc/runit

COPY --from=build-exts /usr/local/lib/php/extensions/no-debug-non-zts-20200930/* /usr/local/lib/php/extensions/no-debug-non-zts-20200930/
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-enable pcntl soap pdo_mysql sockets intl opcache amqp igbinary memcached

COPY ./nginx/default.conf /etc/nginx/conf.d
COPY ./nginx/nginx.conf /etc/nginx
COPY ./php/php-fpm.conf /etc/php8
COPY ./php/www.conf /etc/php8/php-fpm.d
COPY ./load-key.sh /

WORKDIR /app
