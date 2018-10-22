<?php
namespace FwBD\DBConect\Drives;

use PDO;
use PDOException;

class SQLITE implements iDB
{
    public function getConBD(array $configDB)
    {

        try {

            $pdo = new PDO('sqlite:' . $configDB['DBHost'] );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ); # [PDO::FETCH_ASSOC]

            return $pdo;

        } catch (PDOException $e) {
            // die('Error Connect DB-SQLITE: ').$e->getMessage();
            // pp($e->getMessage());
            return $e;
        }

    }




    /**
     * Methods HELPERS
     */



}