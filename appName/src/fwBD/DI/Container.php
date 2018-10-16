<?php
namespace FwBD\DI;

use FwBD\Router\Router;
use FwBD\View\View;
use FwBD\Session\Session;
use FwBD\Image\Image;

class Container
{
    private $render;

    public static function setTemplateView($tempViews)
    {
        View::setTempViews($tempViews);
    }

    // public static function getView($path='', $title='', array $data=[])
    public static function getView($path='', array $data=[])
    {
        if (!class_exists('\FwBD\View\View'))
            throw new \Exception("Error: Ao instaciar a class View!");

        // pp($data);
        $render = new View;
        // pp($render);
        $render->setPathViews($path);
        $render->setTitle($data['title']);

        unset($data['title']);

        $render->setData($data);
        $render->run();

    }

    public static function getServices($services, $dependecie='')
    {
        $service = ucfirst($services);
        $str_Class = $service;

        if ( stristr($str_Class, 'Model') || stristr($str_Class, 'Models') )
            $dependecie = \FwBD\DBConect\DBConect::getCon();

            if (!class_exists($dependecie))
                return self::setFilter(['SetupSystem']);

        if (class_exists($str_Class)) {
            $Class = new $str_Class($dependecie);
            return $Class;

        } else
            trigger_error("#Container->getServices(), nome da classe <b>[{$service}]</b> não existe no sistema - ", E_USER_ERROR);

    }

    public static function getSession($method, array $data=[])
    {

        if ( !empty($data) && $data != '' && !empty($data[1]) ) {
            return Session::$method($data[0], $data[1]);
        }

        if ( !empty($data) && $data != '' && $data[0] ) {
            return Session::$method($data[0]);
        }

        return Session::$method();

    }

    public static function setFilter(array $middleware)
    {
        \FwBD\Filter\BaseFilter::getFilter($middleware);
    }


}