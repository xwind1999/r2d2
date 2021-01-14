FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:latest

ENV APP_ENV prod

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY ./.ci/docker/app/php-optimized.ini /usr/local/etc/php/conf.d/php-optimized.ini

COPY . /app

RUN chown -R 33.33 /app

CMD ["/app/bin/start.sh"]
