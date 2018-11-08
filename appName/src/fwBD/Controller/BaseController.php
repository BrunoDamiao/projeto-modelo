<?php
namespace FwBD\Controller;


class BaseController
{
    protected $params;
    protected $pager;

    public function __construct(array $params)
    {
        $this->params = !empty($params[0])? $params[0] : '';
    }

    public static function getJson($path='')
    {
        $pth = !empty($path)? $path : PATH_DATABASE;
        return getJsonDBConfig($pth);
    }

    // public static function PagerNotFound()
    // {
    //     echo 'Pager 404';
    // }

}