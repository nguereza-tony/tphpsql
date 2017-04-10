<?php
	//checking config file
	 if(!file_exists(APP_CONFIG_FILE)){
	 	terror("missing config file [".APP_CONFIG_FILE."].<br>Type ".APP_CONFIG_BIN." to reconfigure<br>");
	 }
	 else{
	 	$config = parse_ini_file(APP_CONFIG_FILE);
	 	if(!$config){
			 terror("invalid config file [".APP_CONFIG_FILE."]. Type ".APP_CONFIG_BIN." to reconfigure<br>");
		}
		else{
		 	if(empty($config['sql_host'])){
		 		twarning("invalid or missing sql server host configuration in config file [".APP_CONFIG_FILE."] trying to use localhost.<br>Type ".APP_CONFIG_BIN." to reconfigure<br>");
		 		$config['sql_host'] = 'localhost';
		 	}
		 	
		 	if(empty($config['sql_user'])){
		 		twarning("invalid or missing sql server username configuration in config file [".APP_CONFIG_FILE."] trying to use root.<br>Type ".APP_CONFIG_BIN." to reconfigure<br>");
		 		$config['sql_user'] = 'root';
		 	}
		 	
		 	if(empty($config['sql_password'])){
		 		$config['sql_password'] = null;
		 	}
		 	
		 	if(empty($config['sql_port'])){
		 		$config['sql_port'] = 3306;
		 	}
	 	}
	 }
