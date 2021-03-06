<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit66570e7455767c706a734217a734d4aa
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'F' => 
        array (
            'FwBD\\' => 5,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'FwBD\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/fwBD',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit66570e7455767c706a734217a734d4aa::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit66570e7455767c706a734217a734d4aa::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
