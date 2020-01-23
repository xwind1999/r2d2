#!/usr/bin/env sh
nr_install() {
 cd /tmp
 curl -O https://opex-team.s3-eu-west-1.amazonaws.com/files/newrelic-php5-8.7.0.242-linux-musl.tar.gz
 tar xvfz newrelic-php5-8.7.0.242-linux-musl.tar.gz
 rm newrelic-php5-8.7.0.242-linux-musl.tar.gz
 cp newrelic-php5-8.7.0.242-linux-musl/agent/x64/newrelic-20160303.so  /usr/local/lib/php/extensions/no-debug-non-zts-20160303/
 cp newrelic-php5-8.7.0.242-linux-musl/daemon/newrelic-daemon.x64 /usr/bin/
 ln /usr/local/lib/php/extensions/no-debug-non-zts-20160303/newrelic-20160303.so  /usr/local/lib/php/extensions/no-debug-non-zts-20160303/newrelic.so
 # ln /usr/local/lib/php/extensions/no-debug-non-zts-20160303/newrelic-20160303.so /usr/lib/php7/modules/newrelic.so

 mkdir -p /etc/newrelic
 mkdir -p /var/log/newrelic

cat << EOF > /usr/local/etc/php/conf.d/newrelic.ini
extension = "newrelic.so"
[newrelic]
newrelic.license = "37123137d53e1a7de31e14c72428f9cd7963018b"
newrelic.logfile = "/var/log/newrelic/php_agent.log"
newrelic.daemon.logfile = "/var/log/newrelic/newrelic-daemon.log"
newrelic.transaction_tracer.enabled = true
newrelic.distributed_tracing_enabled = true
EOF

cat <<EOF > /etc/newrelic/newrelic.cfg
pidfile=/var/run/newrelic-daemon.pid
logfile=/var/log/newrelic/newrelic-daemon.log
#loglevel=info
port="/tmp/.newrelic.sock"
#auditlog=/var/log/newrelic/audit.log
#utilization.detect_docker=true
#app_timeout=10m
EOF
}

nr_install
