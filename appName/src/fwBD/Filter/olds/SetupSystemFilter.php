<?php
namespace FwBD\Filter;

use FwBD\Filter\BaseFilter as BaseFilter;
use FwBD\DBConect\DBConect as Conn;
use FwBD\DI\Container;

class SetupSystemFilter extends BaseFilter
{
    public function Filter($value='')
    {

        switch (DRIVE) {
            case 'mysql':
                return ( get_class( Conn::getCon() ) === 'PDOException' )? redirect('/setup'): '' ;
                break;
            case 'oracle':
                #...
                break;
            case 'postgres':
                #...
                break;
            case 'sqlite':
                $sql = \FwBD\Model\BaseModel::exec('SELECT * FROM sqlite_master WHERE 1');
                return (!$sql)? redirect('/setup') : '';
                break;
        }

    }

}