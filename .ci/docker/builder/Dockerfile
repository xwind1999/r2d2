FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:0.0.4

RUN apk --no-cache add curl git bash

RUN echo "memory_limit=-1" > "$PHP_INI_DIR/conf.d/memory-limit.ini" \
 && echo "date.timezone=${PHP_TIMEZONE:-UTC}" > "$PHP_INI_DIR/conf.d/date_timezone.ini"

COPY phpdbg-wrapper /usr/local/bin
COPY load-key.sh /

RUN chmod 777 /etc/passwd; chmod 777 /etc/group
RUN ln -s /tmp/.ssh /root/.ssh
ENV HOME="/tmp"

