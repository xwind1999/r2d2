#!/bin/sh
ln -s /etc/sv/sym /service/sym

exec /sbin/runsvdir /service
