#!/bin/sh
ln -s /etc/sv/crond /service/cron

/app/bin/warm-up.sh

/sbin/runsvdir /service
