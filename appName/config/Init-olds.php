<?php
/**
 * File de config. de inicialização do AppName
 */

// define('APP_NAME', 'app-fwBd');
define('APP_DEBUG', true);
define('APP_KEY', '@AppFwBd$2018%');
// define('DRIVE', 'mysql');
// define('DRIVE', 'sqlite');


/**
 * Settings Systems
 * 1. APP_HEADER     => define title default
 * 2. APP_SUBHEAD    => define subtitle default
 * 3. APP_PAGINATOR  => define paginator default [limit show register data]
 * 4. APP_DBNAME     => define database name default [sqlite3-mysql]
 */
const APP_NAME  	= 'Projeto AppBruno';
const APP_SLOGAN 	= 'FrameWorks Bruno Damião';
const APP_PAGINATOR = 12;

const DB_DRIVE     = 'mysql';
const DB_DBNAME    = 'appModelo';
// const APP_DBNAME    = 'appBruno';

/**
 * Sets PATH: root e separator;
 */
define('ROOT', $_SERVER['DOCUMENT_ROOT']);
define('DS', DIRECTORY_SEPARATOR);

/**
 * Sets absolute path in the system;
 * 1. PATH_ROOT     => path apontando para appName/
 * 2. PATH_VIEWS    => path apontando para appName/app/Views/
 * 3. DIRECTORY_DB  => path apontando para appName/storage/database/
 */
const PATH_ROOT    = ROOT .DS. '..' .DS;
const PATH_VIEWS   = PATH_ROOT . 'app' .DS. 'Views' .DS;
const DIRECTORY_DB = PATH_ROOT .'storage' .DS. 'database' .DS;


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
