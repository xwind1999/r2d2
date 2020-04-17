#!/bin/sh
ln -s /etc/sv/crond /service/cron

/sbin/runsvdir /service
