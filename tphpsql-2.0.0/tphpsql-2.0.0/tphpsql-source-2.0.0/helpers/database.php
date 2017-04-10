<?php

function db_connect(){
	global $config;
	extract($config);
	$host = !empty($sql_host)?$sql_host:'localhost';
	$user = !empty($sql_user)?$sql_user:'root';
	$password = !empty($sql_password)?$sql_password:null;
	$port = (!empty($sql_port))?$sql_port:3306;
	$cnx = @mysqli_connect($host.':'.$port , $user , $password) or terror("failure to connect to MySQL server : <$sql_host><br>");
	return $cnx;
}

function db_select($database, $cnx){
	mysqli_select_db($cnx, $database) or terror("the database <$database> does not exist.<br>Type ".APP_BIN." --show-db to display all databases.<br>");
}

function db_list(){
	$cnx = db_connect();
	$databases = array();
	$sql = mysqli_query($cnx, "SHOW DATABASES");
	while($db = mysqli_fetch_assoc($sql)){
		$databases[] = $db['Database'];
	}
	return $databases;
}

function db_close(){
	@mysqli_close();
}

function db_table_list($database){
	$cnx = db_connect();
	$tables = array();
	db_select($database, $cnx);
	$sql = mysqli_query($cnx, "SHOW TABLE STATUS") or die(mysqli_error($cnx));
	while($table = mysqli_fetch_assoc($sql)){
		$tables[] = $table['Name'];
	}
	return $tables;
}



function table_cols($table){
	global $database;
	$cnx = db_connect();
	db_select($database, $cnx);
	$columns = array();
	$sql = mysqli_query($cnx, "SHOW COLUMNS FROM $table") or die(mysqli_error($cnx));//or terror("the table <$table> does not exist in this database<br>");
	while($field = mysqli_fetch_assoc($sql)){
		$columns[] = $field;
	}
	return $columns;
}


function table_info($col, $info = 'field', $index = 0){
	$info = ucfirst($info);
	return isset($col[$index][$info]) ? $col[$index][$info] : null;
}

function table_field($col,$index = 0){
	return table_info($col, 'field', $index);
}

function table_type($col,$index = 0){
	return table_info($col, 'type', $index);
}

function table_key($col,$index = 0){
	return table_info($col, 'key', $index);
}

function table_null($col,$index = 0){
	return table_info($col, 'null', $index);
}

function table_extra($col,$index = 0){
	return table_info($col, 'extra', $index);
}

function table_default($col,$index = 0){
	return table_info($col, 'default', $index);
}

function table_length($type){
     $match = array();
	preg_match('#([0-9]+)#i', $type, $match);
	return $match ? $match[0] : 0 ;
}
