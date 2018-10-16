<?php
namespace FwBD\Encrypt;

interface iEncrypt
{

    public static function encrypt($val, $key='');

    public static function decrypt($val, $key='');

    public static function hashCode($val);
}