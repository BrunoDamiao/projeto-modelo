<?php

/**
 * Configurações do nosso banco de dados
 * Options Drive DataBase (mysql, sqlite)
 * [ Configuração para versão do php > 7 ]
 */


$connectionsJson = getJsonDBConfig(PATH_STORAGE . 'database' .DS);
$connections = [
            'sqlite'    => [
                            'DBHost'        => PATH_DATABASE . DB_NAME . '.db',
                            'DBName'        => DB_NAME . '.db'
                        ],

            'mysql'     => [
                            'DBHost'        => 'localhost',
                            'DBName'        => DB_NAME,
                            'DBUser'        => 'root',
                            'DBPass'        => 'beca',
                            'DBCharset'     => 'utf8',
                            'DBCollation'   => 'utf8_unicode_ci'
                        ],

            'oracle'    => [
                            'DBHost'        => 'localhost',
                            'DBName'        => DB_NAME,
                            'DBUser'        => 'root',
                            'DBPass'        => 'beca',
                            'DBCharset'     => 'utf8',
                            'DBCollation'   => 'utf8_unicode_ci'
                        ],

            'postgres'  => [
                            'DBHost'        => 'localhost',
                            'DBName'        => DB_NAME,
                            'DBUser'        => 'root',
                            'DBPass'        => 'beca',
                            'DBCharset'     => 'utf8',
                            'DBCollation'   => 'utf8_unicode_ci'
                        ],

            'sqlserver'     => [
                            'DBHost'        => 'localhost',
                            'DBName'        => DB_NAME,
                            'DBUser'        => 'root',
                            'DBPass'        => 'beca',
                            'DBCharset'     => 'utf8',
                            'DBCollation'   => 'utf8_unicode_ci'
                        ],
        ];


define('DB_CONNECTIONS', $connections);

if ( $connectionsJson )
	define('DB_CONFIG', $connectionsJson);
else
	define('DB_CONFIG', []);



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

