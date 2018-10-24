<?php

/**
 * Configurações do nosso banco de dados
 * Options Drive DataBase (mysql, sqlite)
 * [ Configuração para versão do php > 7 ]
 */


$connectionsJson = getJsonDBConfig(PATH_STORAGE . 'database' .DS);
$connections = [
            'sqlite'    => [
                            'host'       => PATH_DATABASE . DB_NAME . '.db',
                            'name'       => DB_NAME . '.db',
                            'user'       => 'PDO::ATTR_ERRMODE',
                            'password'   => 'PDO::ERRMODE_EXCEPTION',
                            'charset'    => 'PDO::ATTR_DEFAULT_FETCH_MODE',
                            'collation'  => 'PDO::FETCH_OBJ'
                        ],

            'mysql'     => [
                            'host'       => 'localhost',
                            'name'       => DB_NAME,
                            'user'       => 'root',
                            'password'   => 'beca',
                            'charset'    => 'utf8',
                            'collation'  => 'utf8_unicode_ci'
                        ],

            'oracle'    => [
                            'ost'        => 'localhost',
                            'ame'        => DB_NAME,
                            'user'       => 'root',
                            'password'   => 'beca',
                            'charset'    => 'utf8',
                            'collation'  => 'utf8_unicode_ci'
                        ],

            'postgres'  => [
                            'ost'        => 'localhost',
                            'ame'        => DB_NAME,
                            'user'       => 'root',
                            'password'   => 'beca',
                            'charset'    => 'utf8',
                            'collation'  => 'utf8_unicode_ci'
                        ],

            'sqlserver'     => [
                            'ost'        => 'localhost',
                            'ame'        => DB_NAME,
                            'user'       => 'root',
                            'password'   => 'beca',
                            'charset'    => 'utf8',
                            'collation'  => 'utf8_unicode_ci'
                        ],
        ];


define('DB_CONNECTIONS', $connections);

if ( $connectionsJson )
	define('DB_CONFIG', $connectionsJson);



/*
$confgSQlite = [
	'DBHost' 		=> PATH_DATABASE . DB_NAME . '.db',
	'DBName' 		=> DB_NAME . '.db',
	'DBUser' 		=> '--',
	'DBPass' 		=> '--',
	'DBCharset'   	=> '--',
	'DBCollation'	=> '--'
];

$confgMysql = [
	'DBHost' 		=> 'localhost',
	'DBName' 		=> DB_NAME,
	'DBUser' 		=> 'root',
	'DBPass' 		=> 'beca',
	'DBCharset'   	=> 'utf8',
	'DBCollation'	=> 'utf8_unicode_ci'
];
*/

