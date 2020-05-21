FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:latest

COPY . /app

RUN mkdir /app/var; chown -R 33.33 /app

CMD ["/app/bin/start.sh"]
