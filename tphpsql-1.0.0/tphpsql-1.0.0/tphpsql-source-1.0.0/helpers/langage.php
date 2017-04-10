<?php

function check_environment(){
	 if(version_compare(phpversion(), APP_PHP_REQUIRE_VERSION,'<')){
		terror("your PHP ".phpversion()." is not compatible with this application.<br>Please install PHP ".APP_PHP_REQUIRE_VERSION." before using this application !<br>");
	}
}



function check($bool, $success = 'OK', $error = 'ERROR'){
	return $bool ? $success : $error;
}

/**
* for remplace char name
* 
* @example foo_bar => foobar
*/
function replace($str){
	$temp = preg_replace('#([^a-z0-9]+)#i',' ',$str);
	$tab = explode(' ',$temp);
	$val = null;
	for($i=0; $i<count($tab);$i++){
		if($i != 0){
			$val .= ucfirst($tab[$i]);
		}
		else{
			$val .= $tab[$i];
		}
	}
	return $val;
}

function get_class_base_dir(){
    return 'source/';
}

function get_class_lib_dir(){
    return 'lib/';
}


function create_autoload($path = '.', $lib_dir){
	global $options;
	$lib_dir = rtrim($lib_dir, '/').'/';
	$str = '<?php';
	/*if(!empty($options['licence']) && file_exists($options['licence'])){
		$str .= file_get_contents($options['licence']);
	}*/
	$str .= '
	/**
	* class autoloader bootstrap for all class
	*
	* @file autoload.php 
	* @date '.date('d/m/Y').' 
	* @author '.(!empty($options['author']) ? $options['author'] : gethostname()).'
	*/
	 class Autoloader{
	 	
	 	/**
	 	* the autoloader method
	    *
	    * @param string $className the class to load
	 	*/
	 	static public function loader($className){
	 		$filename = "'.$lib_dir.'".str_replace("\\\", DIRECTORY_SEPARATOR, $className).".php";
	 		if(file_exists($filename)){
	 			//require it
	 			require($filename);
	 		}
	 	}
	 }
	 
	 spl_autoload_register("Autoloader::loader"); 
?>';

	$path = rtrim($path, '/').'/';
	!is_dir($path) && @mkdir($path) ;
	$file = $path.'autoload.php';
	$fp = @fopen($file,'w+');
	$return = @fwrite($fp, $str);
	@fclose($fp);
	return $return;
}

function create_class_manager_file($table, $path = '.'){
	global $options;
	
	$class = class_name($table).(!empty($options['manager_suffix']) ? class_name($options['manager_suffix']) : 'Manager');
	
	$str = null;
	$str .= class_manager_file_docblock($table);
	$str .= class_file_start($class);
	$str .= class_manager_const_table($table);
	$str .= class_manager_pdo_instance();
	$str .= class_manager_constructor();
	$str .= class_manager_add($table);
	$str .= class_manager_update($table);
	$str .= class_manager_delete($table);
	$str .= class_manager_exists($table);
	$str .= class_manager_count($table);
	$str .= class_manager_get($table);
	$str .= class_manager_getList($table);
	$str .= class_file_end();
	
	$path = rtrim($path, '/').'/';
	 !is_dir($path) && @mkdir($path) ;
     $file = $path.$class.'.php';
	 $fp = @fopen($file,'w+');
	$return = @fwrite($fp, $str);
	@fclose($fp);
	return $return;
}

function create_class_file($table, $path = '.'){
	 $cols = table_cols($table);
	 $str = null;
	 $class = class_name($table);
	 $str.= class_file_docblock($table);
	 $str.= class_file_start($class);

	//properties
	for($i = 0; $i<count($cols);$i++){
		$field = table_field($cols, $i);
		$type = table_type($cols, $i);
		$type = class_type($type);
		$default = table_default($cols, $i);
		$str.= class_private_member($field, $type, $default);
	}

	//constructor
	$str.= class_constructor($class, $cols);

	//getters
	for($i = 0; $i<count($cols);$i++){
		$field = table_field($cols, $i);
		$field = replace($field);
		$type = table_type($cols, $i);
		$type = class_type($type);
	
		$str.= class_getter_docblock($field,$type);
		$str.= class_getter($field);
	}

	//setters
	for($i = 0; $i<count($cols);$i++){
		$field = table_field($cols, $i);
		$field = replace($field);
		$type = table_type($cols, $i);
		$type = class_type($type);
	
		$str.= class_setter_docblock($field,$type);
		$str.= class_setter($field);
	}
	$str.= class_file_end();

	 $path = rtrim($path, '/').'/';
	 !is_dir($path) && @mkdir($path);
     $file = $path.$class.'.php';
	 $fp = @fopen($file,'w+');
	$return = @fwrite($fp, $str);
	@fclose($fp);
	return $return;
}

function class_file_docblock($name){
	global $options;
	
	return '<?php
	
	/**
	* class for '.$name.' entity
	*
	* @file '.class_name($name).'.php 
	* @date '.date('d/m/Y').'
	* @author '.(!empty($options['author']) ? $options['author'] : gethostname()).'
	*/
	';
}

function class_name($str){
	return ucfirst(replace($str));
}

function class_file_start($name){
	return '
	class '.$name.'{
			';
}

function class_file_end(){
	return '
	}
?>
';
}


function class_constructor($classe, $cols = array()){
	$return = '
		/**
		* create new '.$classe.'
		*
		* @param array $data the '.$classe.' data
		* @access public
		* @return null
		*/
		public function __construct(array $data = array()){
			foreach($data as $key => $value){
				switch($key){';
				 	for($i = 0; $i<count($cols);$i++){
	$champs_table = table_field($cols, $i);
	$setteur = '$this->set'. class_name(replace($champs_table)).'($value);';
				$return .= '
				case "'.$champs_table.'":
					'.$setteur.'
					break;';
					}
					$return .= '
				}
			}
		}
	';
	return $return;
}


function class_type($str){
	$type = 'string';
	if(preg_match('#((big|small|tiny)?int)#i',$str)){
		$type = 'int';
	}
	return $type;
}

function class_getter($str){
	return '
		public function get'.ucfirst($str).'(){
			return $this->'.$str.';
		}
	';
}

function class_getter_docblock($str, $type = 'string'){
	return '
		/**
		* get the '.$str.' value
		*
		* @access public
		* @return '.$type.' the '.$str.' value
		*/';
}


function class_setter_docblock($str, $type = 'string'){
	return '
		/**
		* set the new '.$str.' value
		*
		* @param '.$type.' $'.$str.' the new '.$str.' value to set
		* @access public
		* @return null
		*/';
}

function class_setter($str){
	return '
		public function set'.ucfirst($str).'($'.$str.'){
			$this->'.$str.' = $'.$str.';
		}
	';
}

function class_member($name, $access = 'private', $type ="string", $default = null){
	if(is_numeric($default)){
		$default = (int)$default;
	}
     else if(is_string($default)){
	    $default =  '"'.$default.'"';
     }
	else{
		$default = 'null';
	}
	
	return '
		/**
		* for '.$name.' attribute
		*
		* @var '.$type.'
		* @access '.$access.'
		*/
		'.$access.' $'.replace($name).' = '.$default.';
	';
}

function class_public_member($name, $type, $default = null){
	return class_member($name, 'public', $type, $default);
}

function class_private_member($name, $type , $default = null){
	return class_member($name, 'private', $type, $default);
}

function class_protected_member($name, $type, $default = null){
	return class_member($name, 'protected', $type, $default);
}

/************************* manager ************************************/

function class_manager_file_docblock($name){
	global $options;
	return '<?php
	
	/**
	* class manager for '.$name.' entity
	*
	* @file '.class_name($name).(!empty($options['manager_suffix']) ? class_name($options['manager_suffix']) : 'Manager').'.php 
	* @date '.date('d/m/Y').'
	* @author '.(!empty($options['author']) ? $options['author'] : gethostname()).'
	*/
	';
}

function class_manager_const_table($table){
return '
		/**
		* the '.$table.' table name
		*
		* @const string
		*/
		const TABLE = "'.$table.'";
		';
}

function class_manager_pdo_instance(){
return '
		/**
		* the instance of PDO
		*
		* @var object the PDO instance
		* @access public
		*/
		public $pdo = null;
		';
}

function class_manager_constructor(){
	return '
		/**
		* construct an manager
		* 
		* @param $pdo the PDO instance
		*/
		public function __construct(PDO $pdo = null){
			$this->pdo = $pdo;
		}
	';
}

function class_manager_add($table){
	$return = '
		/**
		* add new '.$table.' to the database
		*
		* @param object $'.replace($table).'
		*
		* @return int
		*/
		public function add('.class_name($table).' $'.replace($table).'){
			return $this->pdo->exec("INSERT INTO ".self::TABLE."(';
		$cols = table_cols($table);
		for($i = 0; $i<count($cols);$i++){
            $field = table_field($cols, $i);
            $return .= $field;
            if($i != (count($cols) - 1)){
            	$return .= ', ';
            }
		}
		$return .= ') VALUES(';
		for($i = 0; $i<count($cols);$i++){
            $field = table_field($cols, $i);
            $getteur = '$'.replace($table).'->get'. class_name($field).'()';
            $return .= '\'".'.$getteur.'."\'';
            if($i != (count($cols) - 1)){
            	$return .= ', ';
            }
		}
		$return .= ')");
		}
	';
	return $return;
}

function class_manager_update($table){
	$return = '
		/**
		* update '.$table.' informations in the database
		*
		* @param object $'.replace($table).'
		*
		* @return int
		*/
		public function update('.class_name($table).' $'.replace($table).'){
	';
		$primary = null;
		$str = null;
		$cols = table_cols($table);
		for($i = 0; $i<count($cols);$i++){
            $field = table_field($cols, $i);
            $getteur = '$'.replace($table).'->get'. class_name($field).'()';
            $key = table_key($cols, $i);
            if($key){
            	if($primary){
            		$primary .= 'AND '.$field.' = \'".'.$getteur.'."\'';
            	}
            	else{
            		$primary .= 'WHERE '.$field.' = \'".'.$getteur.'."\'';
            	}
            }
            else{
            	$str .= $field.' = \'".'.$getteur.'."\'';
            	if($i != (count($cols) - 1)){
            		$str .= ', ';
            	}
            }	            
		}
		if($str && $primary){
			$return .= '		return $this->pdo->exec("UPDATE ".self::TABLE." SET '.$str. ' '.$primary.'");';
		}
		else{
			$return .= '		return 0;';
		}
$return .= '
		}
		';
			
			return $return;
}

function class_manager_delete($table){
	$return = '
		/**
		* delete unique '.replace($table).'
		*
		* @param mixed $id the '.replace($table).' identifier
		*
		* @return int
		*/
		public function delete($id = null){
	';
		$primary = null;
		$str = null;
		$cols = table_cols($table);
		for($i = 0; $i<count($cols);$i++){
            $key = table_key($cols, $i);
            if($key){
         		$primary = table_field($cols, $i);
            }
		}
		if($primary){
			$return .= '		return $this->pdo->exec("DELETE FROM ".self::TABLE." WHERE '.$primary.' = \'".$id."\'");';
		} 
		else{
			$return .= '		return 0;';
		}
	$return .= '
		}
	';
	return $return;
}

function class_manager_get($table){
	$return = '
		/**
		* get unique '.replace($table).' information store in database
		*
		* @param $id the '.replace($table).' identifier to fetch
		* @return object '.class_name($table).'
		*/
		public function get($id = null){
			$data = null;
	';
		$primary = null;
		$str = null;
		$cols = table_cols($table);
		for($i = 0; $i<count($cols);$i++){
            $key = table_key($cols, $i);
            if($key){
         		$primary = table_field($cols, $i);
            }
		}
		if($primary){
			$return .= '		$return = $this->pdo->query("SELECT * FROM ".self::TABLE." WHERE '.$primary.' = \'".$id."\'");
			$data = $return->fetch();
			$return->closeCursor();';
		} 
	$return .= '
			return $data ? new '.class_name($table).'($data) : new '.class_name($table).'(array());
		}
	';
	return $return;
}

function class_manager_getList($table){
	return '
		/**
		* get the list of the '.replace($table).' store in database
		*
		* @param $start the ligne we start to fetch
		* @param $count the number to return. Default 10
		* 
		* @return array '.class_name($table).'
		*/
		public function getList($count = 10, $start = 0){
			$query = "SELECT * FROM  ".self::TABLE;
			if($start > 0){
				$query .= " LIMIT ".$start." , ".$count;
			}
			else{
				$query .= " LIMIT ".$count;
			}
			$'.replace($table).' = array();
			$return = $this->pdo->query($query);
			while($data = $return->fetch()){
				$'.replace($table).'[] = new '.class_name($table).'($data);
			}
			$return->closeCursor();
			return $'.replace($table).';
		}
	';
}

function class_manager_count($table){
	return '
		/**
		* count the number of the '.replace($table).' in the database			
		* 
		* @return int
		*/
		public function count(){
			$count = 0;
			$return = $this->pdo->query("SELECT COUNT(*) FROM ".self::TABLE);
			$count = $return->fetchColumn();
			$return->closeCursor();
			return $count;
		}
	';
}


function class_manager_exists($table){
	$return = '
		/**
		* verify if the '.replace($table).' with id passed in argument exists in the database
		*
		* @param $id the '.replace($table).' id to fetch
		* @return int 1 or more if exists 0 or not
		*/
		public function exists($id = null){
			$count = 0;
	';
		$primary = null;
		$str = null;
		$cols = table_cols($table);
		for($i = 0; $i<count($cols);$i++){
            $key = table_key($cols, $i);
            if($key){
         		$primary = table_field($cols, $i);
            }
		}
		if($primary){
			$return .= '		$return = $this->pdo->query("SELECT COUNT(*) FROM ".self::TABLE." WHERE '.$primary.' = \'".$id."\'");
			$count = $return->fetchColumn();
			$return->closeCursor();';
	}
$return .= '
			return $count;
		}
		';
	return $return;
}

//checking the environment do not remove
 check_environment();
