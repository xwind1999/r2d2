#!/bin/sh
ln -s /etc/sv/php-fpm /service/php-fpm

exec /sbin/runsvdir /service
