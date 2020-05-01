FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:latest

COPY .ci/docker/app/warm-start.sh /

COPY . /app

RUN mkdir /app/var; chown -R 33.33 /app

CMD ["/warm-start.sh", "/start.sh"]
