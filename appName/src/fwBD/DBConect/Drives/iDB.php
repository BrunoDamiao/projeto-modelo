<?php
namespace FwBD\DBConect\Drives;

interface iDB
{
    /**
     * interface responsável por realizar a conexão com database via PDO
     * @param array $configDB, parametros de configuração
     * @return instancia do PDO ou PDOException
     */
    public function getConBD(array $configDB);
    // public static function createDbSystems();
}