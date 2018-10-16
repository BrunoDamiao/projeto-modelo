<?php

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
    'MAIL_EMPRESA'  => 'contato@brunodamiao.com.br',
    'REPRESENTATE'  => 'Bruno Damião',

]);


/**
 * Dados de Config. do Email - Lembrete de Senha
 */
define('EMAIL_LEMBRETE', [
    'assunto'  => "FwBD - Link da Nova Senha",
    'altBody'  => "FwBD - Nova Senha",
    'msgTitle' => "<h1> FwBD - Nova Senha </h1>",
    'msgSub'   => "<h2> Obs </h2>",
    'msgBody'  => " long text ",
    'msgAtt'   => "Att. <br /> <b>FwBD</b>"
]);

