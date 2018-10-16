<?php
namespace FwBD\Encrypt;

class Encrypt implements iEncrypt
{

    public static function encrypt($val, $key='')
    {
        $init = substr($val, 0, 2);
        $middle = substr($val, 2, -2);
        $end = substr($val, -2);

        $encrypt = base64_encode($middle.APP_KEY.$end.$key.$init);

        return $encrypt;
    }

    public static function decrypt($val, $key='')
    {
        $decrypt = base64_decode($val);
        $decrypt = str_replace(APP_KEY, '', $decrypt);
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