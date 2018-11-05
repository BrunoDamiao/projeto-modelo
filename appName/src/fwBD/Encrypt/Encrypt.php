<?php
namespace FwBD\Encrypt;

class Encrypt implements iEncrypt
{

    private static $key;

    private static function getAppKey()
    {
        self::$key = getJsonDBConfig(PATH_DATABASE)['proj_key'];
        // return !empty(self::$key)? self::$key : APP_KEY;
        return self::$key;
    }

    public static function encrypt($val, $key='')
    {
        $init = substr($val, 0, 2);
        $middle = substr($val, 2, -2);
        $end = substr($val, -2);

        $encrypt = base64_encode($middle.self::getAppKey().$end.$key.$init);

        return $encrypt;
    }

    public static function decrypt($val, $key='')
    {
        $decrypt = base64_decode($val);
        $decrypt = str_replace(self::getAppKey(), '', $decrypt);
        $decrypt = str_replace($key, '', $decrypt);

        $init = substr($decrypt, -2);
        $middle = substr($decrypt, 0, 2);
        $end = substr($decrypt, 2, -2);

        $decrypt = $init.$middle.$end;

        return $decrypt;
    }

    public static function hashCode($val)
    {
        $encrypt = self::encrypt($val);
        $encrypt = md5($encrypt);
        $encrypt = crypt($val, $encrypt);
        $encrypt = hash('sha512', $encrypt);

        return $encrypt;
    }
}