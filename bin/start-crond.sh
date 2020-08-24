#!/bin/sh

/app/bin/start-newrelic.sh

ln -s /etc/sv/crond /service/cron

cat /app/config/crontab | crontab -

/app/bin/warm-up.sh

/sbin/runsvdir /service
