#!/bin/sh

if [ ! -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]; then
  echo 'Enabling XDEBUG'
  echo 'zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20190902/xdebug.so' > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
  echo "xdebug.remote_host=$(/sbin/ip route|awk '/default/ { print $3 }')" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

else
  echo 'Disabling XDEBUG'
  unlink /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
fi
kill -s USR2 `ps -o pid,args | grep php-fpm | grep master | awk '{ print $1 }'`