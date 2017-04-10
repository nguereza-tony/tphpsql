<?php
	 require('init.php');
	
	//required options values
	$required_opts_values = array('-d','--database','-s','--source-dir', '-l','--lib-dir', '--author', '--manager-suffix');
	 
	//available options	 
	 $opts = array_merge($required_opts_values, array('--help','-h','--show-db', '--version', '-v', '--config'));
	
	//options to add in the files generated
	$options = array();
	
	/******************** default values *****************************/
	//database to use
	$database = null;
	
	//destination directory
	$source_dir = null;	
	
	//lib directory contains all classes generated
	$lib_dir = get_class_lib_dir();	
	
	/****************************************************************/
	
	if($argc == 1){
		usage();
	}

	for($i=1; $i< $argc; $i++){
		$val = $argv[$i];
		if(is_option($val)){
			if(in_array($val,$opts)){
				$value = (isset($argv[$i+1]) && !is_option($argv[$i+1])) ? $argv[$i+1] : null;
				if(empty($value) && in_array($val,$required_opts_values)){
					terror("option '".$val."' require an value.<br>Type ".APP_BIN." --help to display help.<br>");
				}
				
				if($val == '-d' || $val == '--database'){
					$database = $value;
				}
				
				if($val == '-s' || $val == '--source-dir'){
					if(preg_match('#^([a-z0-9-_/.]+)$#i',$value)){
						$source_dir = $value;
					}
					else{
						terror("option '".$val."' require an valid path<br>");
					}
				}
				
				
				if($val == '-l' || $val == '--lib-dir'){
					if(preg_match('#^([a-z0-9-_\.]+)$#i',$value)){
						$lib_dir = $value;
					}
					else{
						terror("option '".$val."' require an valid directory name<br>");
					}
				}
				
				if($val == '--author'){
					$options['author'] = $value;
				}
				
				if($val == '--manager-suffix'){
					$options['manager_suffix'] = $value;
				}
				
				if($val == '-h' || $val == '--help'){
					usage();
				}
				
				if($val == '-v' || $val == '--version'){
					message(APP_BIN.' '.APP_VERSION." in ".PHP_OS."<br>by ".APP_AUTHOR."<br>Build ".APP_BUILD_DATE."<br>");
					exit(0);
				}
				
				if($val == '--config'){
					if(function_exists('system')){
						system(APP_CONFIG_BIN);
					}
					exit(0);
				}
			
				
				if($val == '--show-db'){
					db_connect();
					echo html_to_shell("<span style = \"color : yellow;\">List of Databases available for <b>".$config['sql_user']."@".$config['sql_host']."</b></span><br>");
					foreach(db_list() as $db){
						echo html_to_shell("<span style = \"color : blue;\"> + $db</span><br>");
					}
					db_close();
					exit(0);
				}
			}
			else{
				terror("unknown option '".$val."'.<br>Type ".APP_BIN." --help to display help.<br>");
			}
		}
	}
	
	
	
	//prossessing
	
	if(empty($database)){
		terror("missing database.<br>Type ".APP_BIN." -d database_name to select the database to use.<br>");
	}
	db_connect();
	db_select($database);
	
	if(empty($source_dir)){
		$source_dir = $database;
	}
	
	$source_dir = rtrim($source_dir, '/').'/';
	!is_dir($source_dir) && @mkdir($source_dir);
	
	foreach(db_table_list($database) as $table){
		tinfo("creating class [".class_name($table)."] to [$source_dir$lib_dir".class_name($table).".php] ");	
		$result = check(create_class_file($table, $source_dir.$lib_dir));
		 message("$result !<br>");
		
		tinfo("creating class manager for table $table [".class_name($table).(!empty($options['manager_suffix']) ? class_name($options['manager_suffix']) : 'Manager')."] ");
		$result = check(create_class_manager_file($table, $source_dir.$lib_dir));
		message("$result !<br>");
	}
	tinfo("creating the autoload file to [".$source_dir."autoload.php] ");
	$result = check(create_autoload($source_dir, $lib_dir));
	message("$result !<br>");
	
	echo "------------------------------------------------------------------------------\n";
	tsuccess("All files are generated to ".$source_dir."<br>");
	tsuccess("All classes are generated to : ".$source_dir.$lib_dir."<br>");
	tsuccess("Autoload path : ".$source_dir."autoload.php<br>");
	echo "------------------------------------------------------------------------------\n";
	db_close();	
?>
