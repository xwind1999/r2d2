FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base8.0:latest

RUN apk --no-cache add \
    openjdk8-jre \
    font-noto

RUN curl -L https://github.com/schemaspy/schemaspy/releases/download/v6.1.0/schemaspy-6.1.0.jar -o /schemaspy.jar

RUN curl -Ls https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-java-8.0.20.tar.gz \
    | tar -xvz --directory /tmp mysql-connector-java-8.0.20/mysql-connector-java-8.0.20.jar \
    && mkdir -p /mysql \
    && mv /tmp/mysql-connector-java-8.0.20/mysql-connector-java-8.0.20.jar /mysql
