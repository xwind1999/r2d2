FROM php:8.0-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer

RUN apk add nginx; mkdir -p /run/nginx

RUN sed -i '/xfs/ s/33/82/g' /etc/passwd;\
    sed -i '/www-data/ s/82/33/g' /etc/passwd; \
    sed -i '/xfs/ s/33/82/g' /etc/group;\
    sed -i '/www-data/ s/82/33/g' /etc/group; \
    mkdir /app; \
    chown 33.33 /app -R;\
    chown 33.33 /home/www-data -R;

RUN apk add sudo libxml2-dev icu-dev runit openssh-client curl git bash rabbitmq-c-dev libmemcached libmemcached-dev libzip-dev \
    && apk add --virtual purgable-deps $PHPIZE_DEPS

RUN wget https://github.com/FriendsOfPHP/pickle/releases/download/v0.6.0/pickle.phar \
    && mv pickle.phar /usr/local/bin/pickle \
    && chmod +x /usr/local/bin/pickle;

RUN docker-php-ext-install pcntl soap pdo_mysql sockets intl opcache zip;

# downloading and compiling amqp
# instaling the correct version when PHP 8 is released
RUN git clone --branch issue-php8 https://github.com/remicollet/php-amqp.git /tmp/amqp \
    && cd /tmp/amqp \
    && phpize \
    && ./configure \
    # maybe we need some seds
    # && find /tmp/amqp/ -type f -print0 | xargs -0 sed -i -e 's/TSRMLS_CC/ /' \
    # && find /tmp/amqp/ -type f -print0 | xargs -0 sed -i -e 's/TSRMLS_DC/ /' \
    # && find /tmp/amqp/ -type f -print0 | xargs -0 sed -i -e 's/TSRMLS_C/ /' \
    && make \
    && make install \
    && docker-php-ext-enable amqp;

RUN pickle install igbinary;

RUN docker-php-ext-enable igbinary;

RUN git clone --branch master https://github.com/php-memcached-dev/php-memcached.git /tmp/memcached \
    && cd /tmp/memcached \
    && phpize \
    && ./configure --enable-memcached-igbinary \
    && make -j$(nproc) \
    && make install \
    && docker-php-ext-enable memcached \
    && apk del purgable-deps;

RUN curl -s -f -L -o /tmp/composer-setup.php https://getcomposer.org/installer \
 && php -r " \
    \$signature = trim(file_get_contents('https://composer.github.io/installer.sig')); \
    \$hash = hash_file('sha384', '/tmp/composer-setup.php'); \
    if (\$signature !== \$hash) { \
        unlink('/tmp/composer-setup.php'); \
        echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
        exit(1); \
    }" \
 && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/bin --filename=composer \
 && composer --ansi --version --no-interaction \
 && rm -rf /tmp/* /tmp/.htaccess

RUN mkdir -p /etc/php8; \
    mkdir -p /etc/php8/php-fpm.d; \
    mkdir -p /etc/nginx/; \
    mkdir -p /etc/nginx/conf.d; \
    chown www-data.www-data /var/lib/nginx -R;

COPY ./runit/sv /etc/sv
COPY ./runit/runit /etc/runit

RUN mkdir /service; \
    chmod 777 /service

COPY ./nginx/default.conf /etc/nginx/conf.d
COPY ./nginx/nginx.conf /etc/nginx
COPY ./php/php-fpm.conf /etc/php8
COPY ./php/www.conf /etc/php8/php-fpm.d
COPY ./load-key.sh /

WORKDIR /app
