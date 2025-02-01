#!/bin/bash
set -e

echo "Checking if timezone tables need to be populated..."

# Run MySQL query to check if the timezone table is empty
COUNT=$(mysql -u root --password="$MYSQL_ROOT_PASSWORD" -e "SELECT COUNT(*) FROM mysql.time_zone;" | tail -n 1)

if [ "$COUNT" -eq 0 ]; then
    echo "Populating timezone tables..."
    mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root --password="$MYSQL_ROOT_PASSWORD" mysql
    echo "Timezone tables populated successfully."
else
    echo "Timezone tables already populated."
fi