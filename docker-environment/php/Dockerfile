FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:0.0.14

ENV HOME "/tmp"
RUN ln -s /tmp/.ssh /root/.ssh

RUN mkdir /app; \
    chown www-data.www-data /app -R; \
    chown www-data.www-data /home/www-data -R; \
    chmod 777 /etc/passwd; \
    chmod 777 /etc/group

COPY load-key.sh /

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
COPY ./toggle-xdebug.sh /
RUN chmod 777 /usr/local/etc/php/conf.d/ -R
RUN rm -rf /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY ./start.sh /

