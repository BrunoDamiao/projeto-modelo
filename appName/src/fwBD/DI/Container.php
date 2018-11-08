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

    public static function getView($path='', array $data=[])
    {
        if (!class_exists('\FwBD\View\View'))
            throw new \Exception("Error: Ao instaciar a class View!");

        // pp($path,1);
        // pp($data);
        $render = new View;
        $render->setPathViews($path);
        $render->setTitle($data['title']);
        // pp($render);

        unset($data['title']);

        $render->setData($data);
        $render->run();
    }

    /**
     * get Services: Cria instacia do objeto passado pelos parametros;
     * @param string $services: nome da class a ser instaciada
     * @param string $dependecie: conexão com banco [via PDO]
     * @return objeto instanciado
     */
    public static function getServices($services, $dependecie='')
    {
        $service = ucfirst($services);
        $str_Class = $service;

        if ( stristr($str_Class, 'Model') || stristr($str_Class, 'Models') )
            # retorna string de conexão db [obj PDO]
            $dependecie = self::getPDO();

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

    /**
     * Responsável por provê a conexão com db;
     * @param array com params de configuração da conexão com db;
     * @return instance PDO;
     */
    public static function getPDO(array $db_config=[])
    {
        Container::setFilter(['SetupIn']);

        $config = !empty($db_config)? $db_config : DB_CONFIG;
        return \FwBD\DBConect\DBConect::getCon($config);
    }


}