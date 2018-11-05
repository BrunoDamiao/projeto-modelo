<?php
/**
 * Set init systems
 */
define('APP_DEBUG', true);
define('APP_KEY', '@AppName$2018%');
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('DS', DIRECTORY_SEPARATOR);

/**
 * Set headers systems
 */
const APP_TYPE      = 'CMS';
const APP_TITLE     = 'Projeto AppName';
const APP_SLOGAN    = 'Um FrameWorks por Bruno Damião';
const APP_PAGINATOR = 12;
/**
 * Set suport
 */
const SP_LEVEL     = 'admin';
const SP_NAME      = 'Bruno Damião';
const SP_EMAIL     = 'brunodamiao13@gmail.com';
const SP_PASS      = 'suporte';

/**
 * Set database systems
 */
const DB_NAME       = 'appModelo';

/**
 * Set absolute path in the system;
 */
const PATH_ROOT     = APP_ROOT .DS. '..' .DS;
const PATH_VIEWS    = PATH_ROOT . 'app' .DS. 'Views' .DS;
# paths databases
const PATH_STORAGE  = PATH_ROOT . 'storage' .DS;
const PATH_DATABASE = PATH_STORAGE . 'database' .DS;
# paths midias
const PATH_MIDIAS   = 'assets'. DS .'midias'. DS;
const PATH_FAVICON  = PATH_MIDIAS .'icon.png'; # logo Principal
const PATH_LOGO     = PATH_MIDIAS .'logo.png'; # logo Principal
const PATH_LOGO2    = PATH_MIDIAS .'logo2.png'; # logo Secundaria
const PATH_MESCLE   = PATH_MIDIAS .'marca-dagua.gif'; # marca D´água
const PATH_LOADER   = PATH_MIDIAS .'loader-face.gif'; # loader.gif
const PATH_AVATAR   = PATH_MIDIAS .'avatar-b.png';

/**
 *  Set Errors
 */
if (APP_DEBUG == true) {
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
}


/**
 *  Set Location
 */
date_default_timezone_set('America/Sao_Paulo');
