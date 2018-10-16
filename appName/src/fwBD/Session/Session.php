<?php
namespace FwBD\Session;

class Session implements iSession
{

    public static function set($name, $valor)
    {
        $_SESSION[$name] = (array) $valor;
    }

    public static function get($name)
    {
        // return (object) $_SESSION[$name];
        return $_SESSION[$name];
    }

    public static function all()
    {
        return $_SESSION;
    }

    public static function has($name)
    {
        return (!empty($_SESSION[$name]))? true : false;
    }

    public static function delete($name)
    {
        if ($name != '')
            unset($_SESSION[$name]);
        else
            session_destroy();
    }

    public static function regenerate()
    {
        session_regenerate_id();
    }



    public static function setFlash($name, $valor){
        $_SESSION['SESSION_FLASH'][$name] = $valor;
        // print_r($_SESSION['SESSION_FLASH']);
    }

    public static function getFlash($name){
        $messager = $_SESSION['SESSION_FLASH'][$name];
        unset($_SESSION['SESSION_FLASH'][$name]);
        return $messager;
    }

    public static function hasFlash($name)
    {
        return ( isset($_SESSION['SESSION_FLASH'][$name]) )? true : false;
    }


}