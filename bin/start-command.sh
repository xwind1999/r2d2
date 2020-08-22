#!/bin/sh

/app/bin/start-newrelic.sh

ln -s /etc/sv/sym /service/sym

# defining a time limit for the command between 250 and 370 seconds, so the commands won't stop/start all at once
RANDOM_DURATION=$((250 + RANDOM % 120))

export APP_NAME=jarvis-r2d2-worker
export LOG_FILE=r2d2-worker.log
export COMMAND="/app/bin/console messenger:consume ${QUEUES} --time-limit=${RANDOM_DURATION}"

/app/bin/warm-up.sh

exec /sbin/runsvdir /service
