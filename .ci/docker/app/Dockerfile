FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:0.0.11

COPY .ci/docker/app/warm-start.sh /

COPY . /app

RUN mkdir /app/var; chown -R 33.33 /app/var

CMD ["/warm-start.sh"]
