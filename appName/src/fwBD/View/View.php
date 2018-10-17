<?php
namespace FwBD\View;

class View implements iView
{
    private static $tempViews;
    private $pathViews;
    private $title;
    private $data;


    public static function directView($path='', array $data=[])
    {
        if ( file_exists( PATH_VIEWS . $path . ".phtml") )
            require_once PATH_VIEWS . $path . ".phtml";
        else
            echo "Error render(): A View <strong>{$path}</strong> não existe!";
    }


    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return isset($this->title)? $this->title : '' ;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return (object) isset($this->data)? $this->data : '' ;
    }

    public static function setTempViews($tempViews)
    {

        if (empty($tempViews))
            throw new \Exception("Error: Template View <b>{$tempViews}</b> não existe!");

        $stv = str_replace('.', '. ', $tempViews);
        Self::$tempViews = str_replace('. ', DIRECTORY_SEPARATOR, ucwords($stv));
    }

    public function setPathViews($pathViews)
    {
        if (empty($pathViews))
            throw new \Exception("Error: A View <b>{$pathViews}</b> não existe!");

        # $this->pathViews = str_replace('.', '/', ucfirst($pathViews));
        $spv  = str_replace('.', '. ', $pathViews);
        $this->pathViews = str_replace('. ', DIRECTORY_SEPARATOR, ucwords($spv));
    }

    public function run()
    {
        if (!empty(Self::$tempViews))
            return $this->renderTemplate();

        Self::$tempViews = strstr($this->pathViews, '/', true).'/template';

        if ( file_exists( PATH_VIEWS . Self::$tempViews . ".phtml") )
            return $this->renderTemplate();

        $this->render();
    }

    public function renderTemplate()
    {
        if ( file_exists( PATH_VIEWS . Self::$tempViews . ".phtml") )
            require_once PATH_VIEWS . Self::$tempViews . ".phtml";
        else
            echo "Error: Template View <strong>".Self::$tempViews."</strong> não existe!";
    }

    public function render()
    {

        if ( file_exists( PATH_VIEWS . $this->pathViews . ".phtml") )
            require_once PATH_VIEWS . $this->pathViews . ".phtml";
        else
            echo "Error render(): A View <strong>{$this->pathViews}</strong> não existe!";

    }




}