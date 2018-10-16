<?php
namespace FwBD\DBConect;

/**
 * Classe Retorna objeto de Conexão com DB:
 * Name Metodo de retorno => connect()
 */
class DBConect
{

    public static $PDO;

    # Pattern Singleton
    public static function getCon()
    {
        if (!isset(Self::$PDO))
            Self::fabicaConDB();

        return Self::$PDO->getConBD();
    }

    # Pattern Method Factor
    public static function fabicaConDB()
    {
        switch (DRIVE) {
            case 'sqlite':   Self::$PDO = new \FwBD\DBConect\Drives\SQLITE();   break;
            case 'mysql':    Self::$PDO = new \FwBD\DBConect\Drives\MYSQL();    break;
            case 'postgres': Self::$PDO = new \FwBD\DBConect\Drives\POSTGRES(); break;

            default:
                Self::$PDO = null;
        }
    }

}