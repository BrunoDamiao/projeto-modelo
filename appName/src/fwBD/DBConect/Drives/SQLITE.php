<?php
namespace FwBD\DBConect\Drives;

use PDO;
use PDOException;

class SQLITE implements iDB
{
    public function getConBD(array $configDB)
    {

        try {
            $pdo = new PDO('sqlite:' . $configDB['host'] );

            if ($configDB['user'] === 'PDO::ATTR_ERRMODE')
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($configDB['charset'] === 'PDO::ATTR_DEFAULT_FETCH_MODE')
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            /*$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ); # [PDO::FETCH_ASSOC]*/

            return $pdo;

        } catch (PDOException $e) {
            // die('Error Connect DB-SQLITE: ').$e->getMessage();
            // pp($e->getMessage());
            return $e;
        }

    }

    public function getConInitBD(array $dbConfig)
    {
        return $this->getConBD($dbConfig);
    }




    /**
     * Methods HELPERS
     */



}