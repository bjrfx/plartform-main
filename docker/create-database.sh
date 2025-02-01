#!/bin/bash
echo "Checking if database '${DB_DATABASE}' exists..."

if [ -n "${DB_DATABASE}" ]; then
    echo "Creating database '${DB_DATABASE}' if it does not exist..."
    mysql --user=root --password="${MYSQL_ROOT_PASSWORD}" --execute="CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\`;"
fi