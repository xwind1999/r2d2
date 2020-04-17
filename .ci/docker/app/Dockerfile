FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:0.0.14

COPY .ci/docker/app/warm-start.sh /

COPY . /app

RUN mkdir /app/var; chown -R www-data.www-data /app/var

CMD ["/warm-start.sh", "/start.sh"]
