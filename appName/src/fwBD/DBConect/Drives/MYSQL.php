<?php
namespace FwBD\DBConect\Drives;

use PDO;
use PDOException;


class MYSQL implements iDB
{

    public function getConBD(array $dbConfig)
    {
        if (isset($dbConfig['init']))
            return $this->getConInitBD($dbConfig);

        return $this->getConNameBD($dbConfig);
    }

    private function getConInitBD(array $dbConfig)
    {
        try {
            return new PDO("mysql:host={$dbConfig['host']};", $dbConfig['user'], $dbConfig['password']);
        } catch (PDOException $e) {
            return $e;
        }
    }

    private function getConNameBD(array $dbConfig)
    {
        $host       = $dbConfig['host'];
        $db         = $dbConfig['name'];
        $user       = $dbConfig['user'];
        $pass       = $dbConfig['password'];
        $charset    = $dbConfig['charset'];
        $collation  = $dbConfig['collation'];
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES '$charset' COLLATE '$collation'");
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            return $pdo;
        } catch (PDOException $e) {
            // pp($e->getMessage());
            return $e;
        }
    }


}

