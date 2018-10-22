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

    # Pattern Singleton
    public static function FunctionName($value='')
    {
        # code...
    }
    public static function getCon( array $DBConfig, string $redirect='/setup' )
    {

        # Filter checa se file.Json foi criado;
        BaseFilter::getFilter(['SetupDBConect']);

        $pdo = self::connect( $DBConfig['DBDrive'], $DBConfig );

        if ( get_class($pdo) === 'PDOException' ) {
            setMsgFlash('danger', 'Atenção! Error ao criar conexão com DRIVE do ' . $DBConfig['DBDrive'] . ' com DBName de ' . $DBConfig['DBName']);
            redirect('/setup');
        }else
            return $pdo;
    }

    public static function connect( string $drive, array $DBConfig )
    {
        # Conexão com PDO via Sington
        if (!isset(Self::$PDO))
            Self::fabicaConDB($drive);

        return Self::$PDO->getConBD($DBConfig);
    }

    # Pattern Method Factor
    public static function fabicaConDB(string $drive)
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