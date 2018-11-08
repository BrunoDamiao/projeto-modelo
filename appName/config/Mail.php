<?php

/**
 * Get file de config. Json
 */
$sisTitle = getJsonDBConfig(PATH_DATABASE)['proj_title'];
/**
 * File de config. de E-mails do AppFwBD
 */
define('CONFIG_EMAIL', [

    'HOST'          => 'smtp.gmail.com',
    'SMTP_AUTH'     => true,
    'USERNAME'      => 'devbrunodamiao@gmail.com',
    'PASSWORD'      => 'brhenry13',
    'SMTP_SECURE'   => 'tls',
    'PORT'          => 587,
    'EMPRESA'       => !empty($sisTitle)? strtoupper($sisTitle) : strtoupper(APP_TITLE),
    'REPRESENTANTE' => !empty($sisTitle)? strtoupper($sisTitle) : strtoupper(APP_TITLE),
    'MAIL'          => 'suporte@brunodamiao.com.br',
    'SITE'          => 'www.brunodamiao.com.br',
    'CONTATO'       => '(22)9 9724-0936',

]);



