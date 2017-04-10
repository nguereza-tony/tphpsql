<?php
	//constants
	define('PLATEFORM', PHP_OS);
	define('APP_NAME', 'tphpsql');
	define('APP_BIN', 'tphpsql');
	define('APP_CONFIG_BIN', 'tphpsql-conf');
	define('APP_VERSION', '2.0.0');
	define('APP_BUILD_DATE', '07/04/2017');
	define('APP_AUTHOR', 'Tony NGUEREZA <nguerezatony@gmail.com>');
	define('APP_CONFIG_FILE', "/etc/".APP_NAME."/".APP_NAME.".conf");
	define('APP_PHP_REQUIRE_VERSION', '5.3');

	//the PDO instance
	$pdo = null;
	 //check if the user run this app in cli mode
	 if(stripos(php_sapi_name(), 'cli') === false){
	 	die("You must run this application in cli mode not in ".php_sapi_name());
	 }

	//including the file containing some functions
	require('helpers/functions.php');

	//including the file config file
	require('config.php');


	 require('helpers/database.php');
	 require('helpers/langage.php');
