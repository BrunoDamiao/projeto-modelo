<?php
namespace FwBD\Encrypt;

class Encrypt implements iEncrypt
{

    private static function getJsonKey()
    {
        // self::$key = getJsonDBConfig(PATH_DATABASE)['proj_key'];
        /*$k = getJsonDBConfig(PATH_DATABASE)['proj_key'] ;
        self::$key = !empty($prmkey)? $prmkey : $k ;
        // return self::$key;*/

        return getJsonDBConfig(PATH_DATABASE)['proj_key'];
    }

    public static function encrypt($val, $key='')
    {
        $init = substr($val, 0, 2);
        $middle = substr($val, 2, -2);
        $end = substr($val, -2);

        $encrypt = base64_encode($middle.$key.$end.$key.$init);

        return $encrypt;
    }

    public static function decrypt($val, $key='')
    {
        $decrypt = base64_decode($val);
        // $decrypt = str_replace($key, '', $decrypt);
        $decrypt = str_replace($key, '', $decrypt);

        $init = substr($decrypt, -2);
        $middle = substr($decrypt, 0, 2);
        $end = substr($decrypt, 2, -2);

        $decrypt = $init.$middle.$end;

        return $decrypt;
    }

    public static function hashCode($val, $key='')
    {
        $appKey = !empty($key)? $key : getJsonDBConfig(PATH_DATABASE)['proj_key'];

        $encrypt = self::encrypt($val,$appKey);
        $encrypt = md5($encrypt);
        $encrypt = crypt($val, $encrypt);
        $encrypt = hash('sha512', $encrypt);

        return $encrypt;
    }
}