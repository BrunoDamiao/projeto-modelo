<?php
namespace FwBD\DBConect;

use FwBD\Filter\BaseFilter as BaseFilter;

/**
 * Classe Retorna objeto de Conexão com DB:
 * Name Metodo de retorno => connect()
 */
class DBConect
{

    public static $PDO;

    public static function getCon( array $DBConfig )
    {
        # Conexão com PDO via Sington
        if (!isset(Self::$PDO))
            Self::fabicaConDB($DBConfig['drive']);

        return Self::$PDO->getConBD($DBConfig);
    }

    public static function getConInit( array $DBConfig )
    {
        # Conexão com PDO via Sington
        if (!isset(Self::$PDO))
            Self::fabicaConDB($DBConfig['drive']);

        return Self::$PDO->getConInitBD($DBConfig);
    }

    # Pattern Method Factor
    private static function fabicaConDB(string $drive)
    {
        switch ($drive) {
            case 'sqlite':   Self::$PDO = new \FwBD\DBConect\Drives\SQLITE();   break;
            case 'mysql':    Self::$PDO = new \FwBD\DBConect\Drives\MYSQL();    break;
            case 'postgres': Self::$PDO = new \FwBD\DBConect\Drives\POSTGRES(); break;

            default:
                Self::$PDO = null;
        }
    }

}