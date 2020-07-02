#!/bin/sh

/app/bin/start-newrelic.sh

ln -s /etc/sv/sym /service/sym

# defining a time limit for the command between 250 and 370 seconds, so the commands won't stop/start all at once
RANDOM_DURATION=$((250 + RANDOM % 120))

export COMMAND="/app/bin/console messenger:consume broadcast-listeners-partner broadcast-listeners-product broadcast-listeners-product-relationship broadcast-listeners-price-information calculate-manageable-flag --time-limit=$(RANDOM_DURATION)"

/app/bin/warm-up.sh

exec /sbin/runsvdir /service
