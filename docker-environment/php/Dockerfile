FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:0.0.16

ENV HOME "/tmp"

RUN apk add libcap && setcap CAP_NET_BIND_SERVICE=+eip /usr/sbin/nginx

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN ln -s /tmp/.ssh /root/.ssh; \
    chown www-data.www-data /app -R; \
    chown www-data.www-data /home/www-data -R; \
    chmod 777 /etc/passwd; \
    chmod 777 /etc/sv -R; \
    chmod 777 /etc/group; \
    chmod 777 /var/log -R; \
    chmod 777 /var/lib/nginx -R; \
    chmod 777 /var/run -R; \
    chmod 777 /usr/local/etc/php/conf.d/ -R; \
    rm -rf /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;


COPY ./toggle-xdebug.sh ./load-key.sh /

