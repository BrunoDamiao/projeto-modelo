<?php
namespace FwBD\DBConect\Drives;

use PDO;
use PDOException;

class SQLITE implements iDB
{
    public function getConBD()
    {
        // pp(CONFIG_DB);
        try {

            $pdo = new PDO('sqlite:' . CONFIG_DB['PATH_SQLITE'] );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            // $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;

        } catch (PDOException $e) {
            // die('Error Connect DB-SQLITE: ').$e->getMessage();
            pp($e->getMessage());
            return $e;
        }

    }


}