#!/bin/sh

/app/bin/start-newrelic.sh

ln -s /etc/sv/php-fpm /service/php-fpm
ln -s /etc/sv/nginx /service/nginx

/app/bin/warm-up.sh

/sbin/runsvdir /service
