<?php
/**
 * File de config. de inicialização do AppOsHome
 */

define('APP_NAME', 'app-fwBd');
define('APP_DEBUG', true);
define('APP_KEY', '@AppFwBd$2018%');
define('DRIVE', 'mysql');
// define('DRIVE', 'sqlite');


/**
 * Settings Systems
 */
const APP_HEADER  	= 'Projeto AppBruno'; # Sets APP_HEADER: title default app
const APP_SUBHEAD 	= 'FrameWorks Bruno Damião'; # Sets APP_SUBHEAD: subtitle default app
const APP_PAGINATOR = 12; # Sets PAGINATOR: limit show register data


/**
 * Sets PATH: separator e base uri;
 */
const DS = DIRECTORY_SEPARATOR;
const PATH_HOME = 'http://localhost:8080';
/**
 * Sets PATH: path views;
 */

const PATH_VIEWS 	= __DIR__ .DS. '..' .DS. 'App' .DS. 'Views' .DS; # ../App/Views/

/**
 * Sets DB: banco de dados;
 */
const DBS = 'appModelo'; # database name [sqlite3-mysql]
// const DBS = 'appBruno'; # database name [sqlite3-mysql]
const DIRECTORY_SQLITE = '..' .DS.'storage' .DS. 'database' .DS; # path sqlite3

/**
 * Sets PATH: mídias e imagens;
 */
const PATH_MIDIAS 	= 'assets'. DS .'midias'. DS;
const PATH_FAVICON	= PATH_MIDIAS .'icon.png'; # logo Principal
const PATH_LOGO		= PATH_MIDIAS .'logo.png'; # logo Principal
const PATH_LOGO2	= PATH_MIDIAS .'logo2.png'; # logo Secundaria
const PATH_MESCLE	= PATH_MIDIAS .'marca-dagua.gif'; # marca D´água
const PATH_LOADER 	= PATH_MIDIAS .'loader-face.gif'; # loader.gif
const PATH_AVATAR 	= PATH_MIDIAS .'avatar-b.png';

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
