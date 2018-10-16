<?php
namespace FwBD\Filter;

use FwBD\Filter\BaseFilter as BaseFilter;
use FwBD\DBConect\DBConect as Conn;

class SetupSystemFilter extends BaseFilter
{
    public function Filter($value='')
    {

        if ( get_class( Conn::getCon() ) === 'PDOException' ) {

            // echo "con error";
            // redirect('/settings');

            /*$pathPage = 'settings';
            $dataPage = [
                'title' => 'Settings Systems',
                'header' => 'Error 404 - Page Not Found',
            ];
            \FwBD\View\View::directView($pathPage, $dataPage);*/

        }else{
            echo "con ok";
        }

    }

}