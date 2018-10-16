<?php

/**
 * Configurações do nosso banco de dados
 * Options Drive DataBase (mysql, sqlite)
 * [ Configuração para versão do php > 7 ]
 */

/*if ( defined(DRIVE) )
	define('DRIVE', 'sqlite');
	*/

if ( !defined('DRIVE') )
	define('DRIVE', 'sqlite');

switch (DRIVE) {

	case 'mysql':
		define('CONFIG_DB', [
			'HOST' 		=> 'localhost',
			'DBS' 		=> DBS,
			'USER' 		=> 'root',
			'PASS' 		=> 'beca',
			'CHARSET' 	=> 'utf8',
			'COLLATION' => 'utf8_unicode_ci'
		]);

		break;

	case 'oracle':
		#...
		break;
	case 'postgres':
		#...
		break;
	case 'sqlite':
		define('CONFIG_DB', DIRECTORY_SQLITE.DBS);
		break;

	default:
		define('CONFIG_DB', DIRECTORY_SQLITE.DBS);
		break;
}




