<?php
namespace FwBD\DBConect\Drives;

use PDO;
use PDOException;


class MYSQL implements iDB
{

    public function getConBD()
    {

        $host       = CONFIG_DB['HOST'];
        $db         = CONFIG_DB['DBS'];
        $user       = CONFIG_DB['USER'];
        $pass       = CONFIG_DB['PASS'];
        $charset    = CONFIG_DB['CHARSET'];
        $collation  = CONFIG_DB['COLLATION'];

        try {

            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES '$charset' COLLATE '$collation'");
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            return $pdo;

        } catch (PDOException $e) {

            pp($e->getMessage());
            return $e;

        }

    }


}

