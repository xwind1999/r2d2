#!/bin/sh

if [[ ! -z "$NR_APPNAME" ]]; then
    echo '' > /usr/local/etc/php/conf.d/newrelic.ini
    echo "extension = newrelic.so" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "[newrelic]" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "newrelic.logfile = /var/log/newrelic/php_agent.log" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "newrelic.appname = \"$NR_APPNAME\"" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "newrelic.license = \"$NR_LICENSE\"" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "newrelic.daemon.pidfile = /var/run/newrelic-daemon.pid" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "newrelic.daemon.logfile = /var/log/newrelic/newrelic-daemon.log" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "newrelic.daemon.address = /tmp/.newrelic.sock" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "newrelic.daemon.location = /usr/bin/newrelic-daemon.x64" >> /usr/local/etc/php/conf.d/newrelic.ini
    echo "newrelic.process_host.display_name = \"$NR_HOST-$HOSTNAME\"" >> /usr/local/etc/php/conf.d/newrelic.ini
fi
