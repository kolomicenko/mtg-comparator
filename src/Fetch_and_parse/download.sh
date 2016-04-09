#!/bin/bash

# cd to this script's location
cd $(dirname "$(readlink -f "$0")")

# before running, set error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE for php cli

# read the MYSQL connection details from apache conf and set them as env variables
grep 'SetEnv MTG_' /etc/apache2/apache2.conf > /tmp/xxx
while read line; do eval "$( echo $line | sed 's/SetEnv \([^ ]*\) /export \1=/' )"; done < /tmp/xxx
rm /tmp/xxx

# create a full DB back-up
mysqldump -u "$MTG_MYSQL_USER" -p"$MTG_MYSQL_PASS" "$MTG_MYSQL_DB" | gzip > ../../backup/data/mtg_`date '+%s'`.sql.gz

# TODO: should we clean the two rabbitmq queues?

# download the cards
#nohup php download_from_fireball_into_db.php > ../../log/nohup_fireball.out 2>&1&
nohup php download_from_rytir_into_db.php > ../../log/nohup_rytir.out 2>&1&