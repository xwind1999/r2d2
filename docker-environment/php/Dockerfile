FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:0.0.12

COPY load-key.sh /
RUN chmod 777 /etc/passwd; chmod 777 /etc/group

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
COPY ./toggle-xdebug.sh /
RUN chmod 777 /usr/local/etc/php/conf.d/ -R
RUN rm -rf /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN apk --no-cache --update add rabbitmq-c-dev \
   && pecl install amqp \
   && docker-php-ext-enable amqp

RUN rm -rf /service; mkdir /service; chmod 777 /service
COPY ./start.sh /
COPY ./start-command.sh /
COPY ./command-runner /
