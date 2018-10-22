<?php

/**
 * Configurações do nosso banco de dados
 * Options Drive DataBase (mysql, sqlite)
 * [ Configuração para versão do php > 7 ]
 */

if ( !defined('DB_DRIVE') ) {
	define('DB_DRIVE', 'sqlite');
}


# Set paths sqlite3 database
const DB_DIRECTORY  = PATH_STORAGE . 'database' .DS. DB_NAME .'.db';

public function getDBParm($value='')
{
	$dbJson = getJsonDBConfig(PATH_STORAGE . 'database' .DS);
	// pp($dbJson);

	if ( $dbJson ) {
		$data = $dbJson;
		/*[
			'DBHost'      => $dbJson['DBHost'],
	        'DBName'      => $dbJson['DBName'],
	        'DBUser'      => $dbJson['DBUser'],
	        'DBPass'      => $dbJson['DBPass'],
	        'DBCharset'   => $dbJson['DBCharset'],
	        'DBCollation' => $dbJson['DBCollation']
		];*/
	}else{
		$host = (DB_DRIVE === 'sqlite')? DB_DIRECTORY : DB_NAME

		switch (DB_DRIVE) {
			case 'sqlite': return DB_DIRECTORY; break;
			case 'mysql':  return DB_DIRECTORY; break;

			default:
				return DB_DIRECTORY; break;
		}

		$data = [
			'DBHost'      => ()?? 'localhost',
	        'DBName'      => DB_NAME,
	        'DBUser'      => ()?? 'root',
	        'DBPass'      => ()?? 'beca',
	        'DBCharset'   => ()?? 'utf8',
	        'DBCollation' => ()?? 'utf8_unicode_ci'
		];
	}


	return $data;
}

switch (DB_DRIVE) {

	case 'sqlite':

		createDBConfig( $data );
		// createDBConfig( ['DBHost' => DB_DIRECTORY] );
		break;

	case 'mysql':
		$data = [
			'HOST' 		=> ($dbJson['DBHost'])?? 'localhost',
			'DBS' 		=> ($dbJson['DBName'])?? DB_NAME,
			'USER' 		=> ($dbJson['DBUser'])?? 'root',
			'PASS' 		=> ($dbJson['DBPass'])?? 'beca',
			'CHARSET' 	=> ($dbJson['DBCharset'])?? 'utf8',
			'COLLATION' => ($dbJson['DBCollation'])?? 'utf8_unicode_ci'
		];
		createDBConfig( $data );
		break;

	case 'oracle':
		#...
		break;
	case 'postgres':
		#...
		break;

	default:
		createDBConfig( ['HOST' => DB_DIRECTORY] );
		break;
}



# Cria e set a constante DB_CONFIG com as config. do banco de dados
/*function createDBConfig(array $data)
{
    return define('DB_CONFIG', $data);
}*/