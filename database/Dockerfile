FROM mysql:8.0

ENV MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-root_password}
ENV MYSQL_DATABASE=${MYSQL_DATABASE:-parking_db}
ENV MYSQL_USER=${MYSQL_USER:-parking_user}
ENV MYSQL_PASSWORD=${MYSQL_PASSWORD:-parking_password}

COPY my.cnf /etc/mysql/conf.d/my.cnf

COPY init/ /docker-entrypoint-initdb.d/

EXPOSE 3306

HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=5 \
    CMD mysqladmin ping -h localhost -u root -p${MYSQL_ROOT_PASSWORD} || exit 1
