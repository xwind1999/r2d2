FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:latest

ENV APP_ENV prod

COPY . /app

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY ./.ci/docker/app/php-optimized.ini /usr/local/etc/php/conf.d/php-optimized.ini

RUN cd /app && \
    echo '<?php' > vendor/smartbox/canonical-data-model/legacyClassAliases.php && \
    echo '<?php' > vendor/smartbox/canonical-data-model/classAliases.php && \
    composer install --no-dev && \
    composer dump-autoload --no-dev --classmap-authoritative

RUN chown -R 33.33 /app

CMD ["/app/bin/start.sh"]
