<?php
/*
Core SedLex Plugin
VersionInclude : 2.1
*/ 
/** ====================================================================================================================================================
* Configuration panel - Parameters Class
* 
* @return void
*/
if (!class_exists("phpDoc")) {
	class phpDoc {
	
		var $file ;
		var $content ;
		
		/** ====================================================================================================================================================
		* Constructor
		* 
		* @return void
		*/
		function phpDoc($file) {
			$this->file = $file ; 
			
			$handle = fopen($this->file, "r");
			$length = filesize($this->file);
			$this->content = fread($handle, $length);
			fclose($handle);
		}

		/** ====================================================================================================================================================
		* Get the classes name in the file
		* 
		* @return array
		*/
		public function parse() {
		
		
		
			$tokens = token_get_all($this->content);
			$class_token = false;
			foreach ($tokens as $token) {
				if (is_array($token)) {
					if ($token[0] == T_CLASS) {
						$class_token = true;
					} else if ($class_token && $token[0] == T_STRING) {
						$class_token = false;
						//FOUND
						$matches[] = $token[1] ; 
					}
				}       
			}
			
			$c = array() ; 

			foreach($matches as $id => $cl){
				
				$methods = get_class_methods($cl) ;  
				$reflector = new ReflectionClass($cl);
				
				$desc = $reflector->getDocComment();
				
				$m = array() ; 
				
				foreach ($methods as $method) {
					$gm = $reflector->getMethod($method) ; 
					
					$parameters = $gm->getParameters();
					$comment = $gm->getDocComment();
					
					
					$d = $this->parseComments($comment) ; 
					$d = $this->parseParameters($d, $parameters) ; 
					
					$m = array_merge($m, array($method => $d)) ; 
				}
				
				$c = array_merge($c, array($cl => array('methods'=>$m, 'description' => $this->parseComments($desc) ))) ; 
			}
			
			return $c ;
		}
		
		
		/** ====================================================================================================================================================
		* Parse comments 
		* 
		* @return void
		*/
		private function parseComments($comment) {
			$comment = str_replace("\r","" ,$comment) ; 
			$lignes = explode("\n", $comment) ; 
			$result = array( 	"comment" => "",
			
						"abstract" => "",
						"access" => "",
						"author" => "",
						"category" => "",
						"copyright" => "",
						"deprecated" => "",
						"example" => "",
						"final" => "",
						"filesource" => "",
						"global" => "",
						"ignore" => "",
						"internal" => "",
						"license" => "",
						"link" => "",
						"method" => "",
						"name" => "",
						"package" => "",
						"param" => "",
						"property" => "",
						"return" => "",
						"see" => "",
						"since" => "",
						"static" => "",
						"staticvar" => "",
						"subpackage" => "",
						"todo" => "",
						"tutorial" => "",
						"uses" => "",
						"var" => "",
						"version" => "") ; 
			foreach ($lignes as $l) {
				if (preg_match("/^\s*\*([^\/].*)$/",trim($l), $matches)) {
					$l = trim($matches[1]) ; 
					$found = false ; 
					foreach ($result as $n => $r) {
						if (preg_match("/^\s*@".$n."\s*(.*)$/",$l, $matches)) {
							$found = true ; 
							$m = htmlentities($matches[1]) ; 
							if ($r!="") {
								if (is_array($result[$n])){
									$result[$n] = array_merge(array($m), $r) ; 
								} else {
									$result[$n] = array($m, $r) ; 
								}
							} else {
								$result[$n] = $m ; 
							}
						}
					}
					if (!$found) {
						$result['comment'] .= htmlentities($l)."\n" ; 
					}
				}
			}
			return $result ; 
		} 
		
		/** ====================================================================================================================================================
		* add Missing Parameters to the parsed Comment
		* 
		* @return void
		*/
		private function parseParameters($parsedComment, $params) {
			
			$array_params = array() ; 
			
			foreach ($params as $p) {
				if ($p->isOptional()) {
					$array_params[] = array(	'name' => $p->getName(), 
										'default' => $p->getDefaultValue(), 
										'position' => $p->getPosition(), 
										'description' => '??', 
										'type' => '??' ) ; 
				} else {
					$array_params[] = array(	'name' => $p->getName(), 
										'position' => $p->getPosition(), 
										'description' => '??', 
										'type' => '??' ) ; 
				
				}
			}
			
			$desc = $parsedComment['param'] ; 
			
			if ($desc != "") {
				if (!is_array($desc)) $desc = array($desc) ; 
				
				foreach ($desc as $d) {
					$de = explode(" ", trim($d), 3) ; 
					foreach ($array_params as $i => $ch) {
						if ($de[1]=="$".$ch['name']) {
							$array_params[$i]['type'] = $de[0] ;
							$array_params[$i]['description'] = $de[2] ;
						}
					}
				}
				
			}
	
	
			
			$parsedComment['param'] = $array_params  ; 
			
			
			
			return $parsedComment ; 
			
		} 
	}
}

?>