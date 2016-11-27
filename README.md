# mtg-comparator

Install:
1. php7
2. mysql5
3. load dump backup/schema.sql
4. create mysql user mtg with priviledges for 1915239_mtg
5. php composer.phar install
6. append to /etc/apache2/apache2.conf
SetEnv MTG_MYSQL_HOST localhost
SetEnv MTG_MYSQL_DB 1915239_mtg
SetEnv MTG_MYSQL_USER mtg
SetEnv MTG_MYSQL_PASS ***

SetEnv MTG_RABBITMQ_HOST localhost
SetEnv MTG_RABBITMQ_USER guest
SetEnv MTG_RABBITMQ_PASS guest
7. sudo apt-get install rabbitmq-server
8. sudo service rabbitmq-server start
9. sudo rabbitmq-plugins enable rabbitmq_management

Run
src/Fetch_and_parse/download.sh