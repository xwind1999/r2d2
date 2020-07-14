FROM php:7.4-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
ENV COMPOSER_VERSION 1.10.5

RUN apk add nginx; mkdir -p /run/nginx

RUN sed -i '/xfs/ s/33/82/g' /etc/passwd;\
    sed -i '/www-data/ s/82/33/g' /etc/passwd; \
    sed -i '/xfs/ s/33/82/g' /etc/group;\
    sed -i '/www-data/ s/82/33/g' /etc/group; \
    mkdir /app; \
    chown 33.33 /app -R;\
    chown 33.33 /home/www-data -R;

RUN apk add sudo libxml2-dev icu-dev runit openssh-client curl git bash rabbitmq-c-dev && apk add --virtual purgable-deps $PHPIZE_DEPS

RUN docker-php-ext-install pcntl soap pdo_mysql sockets intl opcache && pecl install amqp && docker-php-ext-enable amqp && apk del purgable-deps

COPY ./newrelic_install.sh /
RUN /newrelic_install.sh

RUN curl -s -f -L -o /tmp/composer-setup.php https://getcomposer.org/installer \
 && php -r " \
    \$signature = trim(file_get_contents('https://composer.github.io/installer.sig')); \
    \$hash = hash_file('sha384', '/tmp/composer-setup.php'); \
    if (\$signature !== \$hash) { \
        unlink('/tmp/composer-setup.php'); \
        echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
        exit(1); \
    }" \
 && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/bin --filename=composer --version=${COMPOSER_VERSION} \
 && composer --ansi --version --no-interaction \
 && rm -rf /tmp/* /tmp/.htaccess

RUN mkdir -p /etc/php7; \
    mkdir -p /etc/php7/php-fpm.d; \
    mkdir -p /etc/nginx/; \
    mkdir -p /etc/nginx/conf.d

COPY ./runit/sv /etc/sv
COPY ./runit/runit /etc/runit

RUN mkdir /service; \
    chmod 777 /service

COPY ./nginx/default.conf /etc/nginx/conf.d
COPY ./nginx/nginx.conf /etc/nginx
COPY ./php/php-fpm.conf /etc/php7
COPY ./php/www.conf /etc/php7/php-fpm.d
COPY ./load-key.sh /

WORKDIR /app
