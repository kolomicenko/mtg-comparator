<?php

namespace MTG_Comparator;

class DB
{
    private static $dbh = null;

    public static function connect(){
        try {
            $host = getenv('MTG_MYSQL_HOST');
            $dbname = getenv('MTG_MYSQL_DB');
            $user = getenv('MTG_MYSQL_USER');
            $pass = getenv('MTG_MYSQL_PASS');

            self::$dbh = new \PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

            self::$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo $e->getMessage();

            exit();
        }
    }

    public static function query(){
        $args = func_get_args();

        $statement = self::$dbh->prepare(array_shift($args));
        $statement->execute($args);

        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        return $statement;
    }

    public static function lastInsertId(){
        return self::$dbh->lastInsertId();
    }
}

DB::connect();
