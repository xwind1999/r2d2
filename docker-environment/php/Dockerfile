FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:0.0.11

COPY load-key.sh /
RUN chmod 777 /etc/passwd; chmod 777 /etc/group

RUN rm -rf /service

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
COPY ./toggle-xdebug.sh /
RUN chmod 777 /usr/local/etc/php/conf.d/ -R
RUN rm -rf /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
