#!/bin/sh
ln -s /etc/sv/sym /service/sym

export COMMAND='/app/bin/console messenger:consume broadcast-listeners-partner broadcast-listeners-product broadcast-listeners-product-relationship broadcast-listeners-price-information --limit=10 --time-limit=3600'

/app/bin/warm-up.sh

exec /sbin/runsvdir /service
