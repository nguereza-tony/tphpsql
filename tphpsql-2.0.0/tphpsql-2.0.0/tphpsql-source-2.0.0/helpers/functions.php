<?php
function usage(){
	echo html_to_shell(
"<span style = \"color : yellow;\">PHP SQL Table Class generator</span>.
This app is used to create the PHP class (OOP) for every MySQL table and their
manager.

<span style = \"color : blue;\">Author  : ".APP_AUTHOR."</span>
Version : ".APP_VERSION."
Build date : ".APP_BUILD_DATE."

<span style = \"color : green;\">Usage : ".APP_BIN." [OPTIONS] [ARGS...]</span>

OPTIONS are:

	<b>--author</b>          the author to use in class file for tag @author
	                  by default this is your hostname [".gethostname()."].
	<b>--config</b>          to configure ".APP_BIN."
	<b>-d, --database</b>    the database to use.
	<b>--config-file</b>	 the absolute path to config ini file used to
	                  define some param. example author, manager suffix, etc
	<b>-h, --help</b>        to display this help.
	<b>-l, --lib-dir</b>     the lib directory that contains all PHP classes.
	                  by default this directory is ".get_class_lib_dir().".
	<b>--manager-suffix</b>  the manager suffix to add after the class name
	                  by default this value is Manager.
	<b>--show-db</b>         to display all databases.
	<b>-s, --source-dir</b>  the path that contains all files
	                  by default this is the database name in relative path.
	<b>-v, --version</b>     to show the app version.
"
);
exit(0);
}

function is_option($option){
	return preg_match('/^(-|--)([a-z-]+)$/i',$option);
}

function html_to_shell($str){
	$patterns = array();
	$replacements = array();

	$styles = array(
					 1 => 'b',
					 4 => 'u'
					 );


	$fg = array(
				30 => 'black',
				31 => 'red',
				32 => 'green',
				33 => 'yellow',
				34 => 'blue',
				35 => 'rose',
				36 => 'cyan',
				37 => 'gray'
				);

	$bg = array(
				40 => 'black',
				41 => 'red',
				42 => 'green',
				43 => 'yellow',
				44 => 'blue',
				45 => 'rose',
				46 => 'cyan',
				47 => 'gray'
				);


	foreach($fg as $shell => $html){
		$patterns[] = '#<span style = "color : '.$html.';">(.*?)</span>#';
		$replacements[] = "\033[".$shell."m\${1}\033[00m";
	}

	foreach($bg as $shell => $html){
		$patterns[] = '#<span style = "background-color : '.$html.';">(.*?)</span>#';
		$replacements[] = "\033[".$shell."m\${1}\033[00m";
	}

	foreach($styles as $shell => $html){
		$patterns[] = '#<'.$html.'>(.*?)</'.$html.'>#';
		$replacements[] = "\033[".$shell."m\${1}\033[00m";
	}

	$str = preg_replace($patterns, $replacements, $str);
	$str = preg_replace('#<br>#', "\n", $str);
	return ($str);
}

function message($str, $type = "normal", $exit = 0){
	switch(strtolower($type)){
		case 'error':
			$str = '<span style = "color : red;">'.$str.'</span>';
		break;
		case 'warning':
			$str = '<span style = "color : yellow;">'.$str.'</span>';
		break;
		case 'success':
			$str = '<span style = "color : green;">'.$str.'</span>';
		break;
		case 'info':
			$str = '<span style = "color : blue;">'.$str.'</span>';
		break;
	}
	echo html_to_shell('<b>'.$str.'</b>');
	if($exit){
		exit($exit);
	}
}

function terror($str, $exit = 1){
	message('Error : '.$str, 'error', $exit);
}

function twarning($str, $exit = 0){
	message('Warning : '.$str, 'warning', $exit);
}

function tinfo($str, $exit = 0){
	message('Info : '.$str, 'info', $exit);
}

function tsuccess($str, $exit = 0){
	message($str, 'success', $exit);
}
