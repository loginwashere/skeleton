#!/bin/bash

echo "Setting up database"

mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e 'CREATE DATABASE $MYSQL_DATABASE;' && \

    mysql -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE < /tmp/app/structure.ddl && \
    mysql -uroot -p"$MYSQL_ROOT_PASSWORD" $MYSQL_DATABASE < /tmp/app/dump.sql
