<?php
namespace FwBD\Session;


interface iSession
{

    public static function set($name, $valor);
    public static function get($name);
    public static function all();
    public static function has($name);
    public static function delete($name);
    public static function regenerate();

    # Funções FLASH
    public static function setFlash($name, $valor);
    public static function getFlash($name);
    public static function hasFlash($name);

}