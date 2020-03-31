#!/bin/sh
set -e

cd /app
sudo -u www-data bin/console -e prod cache:warmup || :
chown -R 33.33 var/log || :
sudo -u www-data touch var/log/r2d2.log || :

exec /start.sh
