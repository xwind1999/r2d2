FROM docker-registry.production.smartbox.com/millenium-falcon/r2d2-base:latest

ENV DB_DATA_PATH="/var/lib/mysql"
ENV DB_ROOT_PASS="admin123"

RUN apk --no-cache add \
    mysql \
    mysql-client \
    openjdk8-jre \
    font-noto

RUN cd /tmp; mysql_install_db --user=mysql --datadir=${DB_DATA_PATH}; \
    sed -i "s/#bind-address/bind-address/"  /etc/my.cnf.d/mariadb-server.cnf; sed -i "s/skip-networking/ /"  /etc/my.cnf.d/mariadb-server.cnf; \
    nohup mysqld_safe & sleep 3 && mysqladmin -u root password "${DB_ROOT_PASS}"

RUN curl -L https://github.com/schemaspy/schemaspy/releases/download/v6.1.0/schemaspy-6.1.0.jar -o /schemaspy.jar

RUN curl -Ls https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-java-5.1.6.tar.gz \
    | tar -xz --directory /tmp mysql-connector-java-5.1.6/mysql-connector-java-5.1.6-bin.jar \
    && mkdir -p /mysql \
    && mv /tmp/mysql-connector-java-5.1.6/mysql-connector-java-5.1.6-bin.jar /mysql
