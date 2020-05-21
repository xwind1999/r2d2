#!/bin/sh
set -e

cd /app
sudo -u www-data bin/console -e prod cache:warmup || true
chown -R www-data.www-data var || true
sudo -u www-data touch var/log/r2d2.log || true
sudo -u www-data touch var/log/worker/r2d2.log || true
