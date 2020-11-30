#!/usr/bin/env bash
PHP_RELEASE_ID=20190902
NR_CURRENT_VERSION=9.14.0.290
NR_FOLDER=newrelic-php5-$NR_CURRENT_VERSION-linux-musl
NR_ARCHIVE=$NR_FOLDER.tar.gz

nr_install() {
 cd /tmp
 curl -O https://download.newrelic.com/php_agent/archive/$NR_CURRENT_VERSION/$NR_ARCHIVE
 tar xvfz $NR_ARCHIVE
 rm $NR_ARCHIVE
 cp $NR_FOLDER/agent/x64/newrelic-$PHP_RELEASE_ID.so  /usr/local/lib/php/extensions/no-debug-non-zts-$PHP_RELEASE_ID/
 cp $NR_FOLDER/daemon/newrelic-daemon.x64 /usr/bin/
 ln /usr/local/lib/php/extensions/no-debug-non-zts-$PHP_RELEASE_ID/newrelic-$PHP_RELEASE_ID.so  /usr/local/lib/php/extensions/no-debug-non-zts-$PHP_RELEASE_ID/newrelic.so
 ln /usr/local/lib/php/extensions/no-debug-non-zts-$PHP_RELEASE_ID/newrelic-$PHP_RELEASE_ID.so /usr/lib/php7/modules/newrelic.so

 mkdir -p /var/log/newrelic
}

nr_install
