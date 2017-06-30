<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/4/25
 * Time: 0:52
 */

return [
	'default' => [
		'dsn' => '',
		'host' => '127.0.0.1',
		'port' => 3306,
		'username' => 'root',
		'password' => '0000',
		'database' => 'test',
		'table_prefix' => '',
		'type' => 'mysql',
		'driver' => 'mysqli',
		'pconnect' => false,
		'charset' => 'utf8',
		'collate' => 'utf8_general_ci',
		'debug' => true
	],
	'sqlite' => [
		'filename' => BASE_PATH.'/public/test.db',
		'driver' => 'sqlite3',
		/**
		 * Optional flags used to determine how to open the SQLite database
		 * default is SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE
		 *
		 * @var int
		 */
		'flags' => null, //see SQLite::__consturct() $flags
		/**
		 * An optional encryption key used when encrypting and decrypting an SQLite database.
		 * If the SQLite encryption module is not installed, this parameter will have no effect.
		 *
		 * @var string|null
		 */
		'encryption_key' => null,
		'debug' => true
	]
];
