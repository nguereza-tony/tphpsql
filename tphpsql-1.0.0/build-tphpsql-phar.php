<?php
	$phar_name = "tphpsql.phar";
	$phar_alias = "tphpsql.phar";
	$version = '1.0.0';
	$phar_build_dir = "tphpsql-$version/tphpsql-source-$version";
	
	$phar = new Phar(__DIR__."/$phar_name", 0, $phar_alias);
	
	$phar->startBuffering();
	$stub = null;
	if(stripos(PHP_OS, 'linux') !== false){
		$stub .= "#!/usr/bin/env php\n";
	}
	$stub .= $phar->createDefaultStub('index.php');
	$phar->setStub($stub);
	$phar->buildFromDirectory(__DIR__."/$phar_build_dir", "/^.*$/");
	$phar->stopBuffering();
?>
