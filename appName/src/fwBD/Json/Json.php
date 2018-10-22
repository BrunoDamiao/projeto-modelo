<?php
namespace FwBD\Json;

use FwBD\DI\Container;

class Json
{

    private static $path = __DIR__ . '/lastSession.json';


    public static function create(array $datasJson, $path='')
    {

        $dir = ( empty($path) )? self::$path : $path ;
        // $dir = self::$path;

        // pp( self::$path );
        // pp($dir,1);

        // $session = $datasJson;
        $getJson = self::get();

        $datax = [];

        if (file_exists($dir) && !empty($getJson))
            $datax = $getJson;


        array_push($datax, $datasJson);
        file_put_contents($dir, json_encode($datax));

    }

    public static function update(array $datasJson, $path='')
    {

        $dir = ( empty($path) )? self::$path : $path ;
        // $dir = self::$path;
        $getJson = self::get();

        if (file_exists($dir) && !empty($getJson)) {

            foreach ($getJson as $key => $value) {

                foreach ($value as $k => $v):

                    $datay[$k] = $v;
                    if ( $k == 'session_timeEnd' &&
                         $value['session_key'] == $datasJson['session_key'] &&
                         $value['session_user'] == $datasJson['session_user']
                     )
                        $datay[$k] = $datasJson['session_timeEnd'];

                endforeach;

                $datax[$key] = $datay;

            }

            file_put_contents($dir, json_encode($datax));

        }

    }


    public static function delete($session_user, $path='')
    {

        $dir = ( empty($path) )? self::$path : $path ;
        // $dir = self::$path;

        $session = $session_user;
        $getJson = self::get();
        // pp($getJson);

        if (file_exists($dir) && !empty($getJson)) {
            foreach ($getJson as $key => $json) {
                if ($json['session_user'] == $session) {
                    unset($getJson[$key]);
                }
            }
        }

        // pp($getJson,1);
        file_put_contents($dir, json_encode($getJson));
        return true;
    }


    public static function get($path='')
    {
        $dir = ( empty($path) )? self::$path : $path ;
        // $dir = self::$path;

        if (file_exists($dir) && !empty($dir)) {
            $json_file = file_get_contents($dir);
            return json_decode($json_file, true);
        }
        return false;
    }


    public static function has($path='')
    {
        $dir = ( empty($path) )? self::$path : $path ;
        // $dir = self::$path;

        if (file_exists($dir) && !empty($dir) && file_get_contents($dir) && file_get_contents($dir) != '[]')
            return true;

        return false;
    }


# ================================================================================= #

    public static function createJson(array $dataJson, $path)
    {
        if (file_exists($path) && !empty($getJson))
            $dataJson = self::get($path);

        if ( file_put_contents($path, json_encode($dataJson)) )
            return true;

        return false;
    }

    public static function deleteJson($file)
    {
        if(is_file($file))
        {
            if ( unlink($file) )
                return true;

            return false;
        }
    }



}

/*
Baixar o PHOTOSHOP

https://www.youtube.com/watch?v=cUkvrIh8Z2c
https://mega.nz/#!ZJgkgDob!aX7bXvuX7aO5SpHK0wrt1qWPNIybyP5kWs8rZqeO_5Q

*/