#!/bin/sh
ln -s /etc/sv/php-fpm /service/php-fpm
ln -s /etc/sv/nginx /service/nginx

/sbin/runsvdir /service
