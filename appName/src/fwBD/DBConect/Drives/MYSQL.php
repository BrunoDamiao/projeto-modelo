<?php
namespace FwBD\DBConect\Drives;

use PDO;
use PDOException;


class MYSQL implements iDB
{

    public function getConBD(array $dbConfig)
    {

        $host       = $dbConfig['DBHost'];
        $db         = $dbConfig['DBName'];
        $user       = $dbConfig['DBUser'];
        $pass       = $dbConfig['DBPass'];
        $charset    = $dbConfig['DBCharset'];
        $collation  = $dbConfig['DBCollation'];

        try {

            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES '$charset' COLLATE '$collation'");
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            return $pdo;

        } catch (PDOException $e) {
            // pp($e->getMessage());
            // pp($e);
            return $e;
        }

    }


}

