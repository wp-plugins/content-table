<?php
/*
Core SedLex Plugin
VersionInclude : 2.1
*/ 


/** =*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*
* This PHP class regroups a few useful method to manage directory, string, ... 
*/
if (!class_exists("Utils")) {
	class Utils {
	
		/** ====================================================================================================================================================
		* Compute the size of a directory (reccursively or not)
		* 
		* @param string $path the path of the directory to scan 
		* @param boolean $recursive set to FALSE if you do NOT want to reccurse in the folder of the directory
		* @return integer the size of the directory
		*/
		
		static function dirSize($path , $recursive=TRUE){
			$result = 0 ;
			if(!is_dir($path) || !is_readable($path)) {
				return 0;
			}
			$fd = dir($path);
			while($file = $fd->read()){
				if(($file != ".") && ($file != "..")){
					if(@is_dir($path.'/'.$file)) {
						$result += $recursive?Utils::dirSize($path.'/'.$file):0;
					} else {
						$result += filesize($path.'/'.$file);
					}
				}
			}
			$fd->close();
			return $result;
		}
		
		/** ====================================================================================================================================================
		* Test if the argument is really an integer (even if string)
		* For instance : 
		* <code>is_really_int(5)</code> will return TRUE.
		* <code>is_really_int("5")</code> will return TRUE.
		* <code>is_really_int(5.2)</code> will return FALSE.
		* <code>is_really_int(array(5))</code> will return FALSE.
		*
		* @param mixed $int the integer, float, string, ... to check 
		* @return boolean TRUE if it is an integer, FALSE otherwise
		*/
		
		static function is_really_int($int){
			if(is_numeric($int) === TRUE){
				// It's a number, but it has to be an integer
				if((int)$int == $int){
					return TRUE;
				// It's a number, but not an integer, so we fail
				}else{
					return FALSE;
				}
			// Not a number
			}else{
				return FALSE;
			}
		}
		
		/** ====================================================================================================================================================
		* Randomize a string
		* For instance, <code>rand_str(5, "0123456789")</code> will return a string of length 5 characters comprising only numbers 
		* 
		* @param integer $length the length of the randomized result string
		* @param string $chars the available characters for the randomized result string
		* @return string the randomized result string
		*/
		static function rand_str($length, $chars) {
			// Length of character list
			$chars_length = (strlen($chars) - 1);
			// Start our string
			$string = $chars{rand(0, $chars_length)};
			// Generate random string
			for ($i = 1; $i < $length; $i = strlen($string)) {
				// Grab a random character from our list
				$r = $chars{rand(0, $chars_length)};
				$string .=  $r;
			}
			// Return the string
			return $string;
		}
		
		/** ====================================================================================================================================================
		* Create an simple identifier from a given string. It removes all non alphanumeric characters and strip spaces
		* For instance : 
		* <code>create_identifier("Hello World 007")</code> will return "Hello_World_007".
		* <code>create_identifier("It's time !")</code> will return "Its_time_".
		* <code>create_identifier("4L car")</code> will return "L_car".
		* 
		* @param string $text the text to be sanitized
		* @return string the sanitized string (identifier)
		*/
		
		static public function create_identifier($text) {		
			// Pas d'espace
			$n = str_replace(" ", "_", strip_tags($text));
			// L'identifiant ne doit contenir que des caracteres alpha-numérique et des underscores...
			$n = preg_replace("#[^A-Za-z0-9_]#", "", $n);
			// l'identifiant doit commencer par un caractere "alpha"
			$n = preg_replace("#^[^A-Za-z]*?([A-Za-z])#", "$1", $n);
			return $n;
		}
		
		/** ====================================================================================================================================================
		* Convert an integer into a string which represent a  size in a computer format (ie. MB, KB, GB, etc.)
		* 
		* @param integer $bytes the number to convert into a byte-format (ie. MB, KB, GB, etc.)
		* @return string the size with a byte-format at the end (ie. MB, KB, GB, etc.)
		*/
		
		static function byteSize($bytes)  {
			$size = $bytes / 1024;
			if($size < 1024) {
				$size = number_format($size, 2);
				$size .= ' KB';
			} else {
				if($size / 1024 < 1024)  {
					$size = number_format($size / 1024, 2);
					$size .= ' MB';
				} else if ($size / 1024 / 1024 < 1024)  {
					$size = number_format($size / 1024 / 1024, 2);
					$size .= ' GB';
				} 
			}
			return $size;
		} 	
 
		/** ====================================================================================================================================================
		* Sort a table against the n-th column
		* 
		* @param array $data the table (i.e. array of array) to be sorted
		* @param integer $num the n-th column to be considered in order to sort the table 
		* @return array the sorted table
		*/

		static function multicolumn_sort($data,$num){
 			$col_uniq = array() ; 
 			
			// List As Columns
  			foreach ($data as $row) {
				$ligne = $row[$num] ;
				$cnt = 0 ; 
				foreach ($row as $c) {
					if ($cnt!=$num) {
						$ligne .= ",".$row[$cnt] ; 
					}
					$cnt ++ ; 

				}
				$col_uniq[] = $ligne ; 

			}
    		
			// We sort
			asort($col_uniq) ; 
			$result = array() ; 
			foreach ($col_uniq as $l) {
				$result[] = explode(",",$l) ; 
			}
			
			return $result;
		} 
		
		/** ====================================================================================================================================================
		* Copy a file or a directory (recursively)
		* 
		* @param string $source the source directory
		* @param string $destination the destination directory
		* @return void
		*/

		static function copy_rec( $source, $destination ) {
			if ( is_dir( $source ) ) {
				@mkdir( $destination );
				$directory = dir( $source );
				while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
					if ( $readdirectory == '.' || $readdirectory == '..' ) {
						continue;
					}
					$PathDir = $source . '/' . $readdirectory; 
					if ( is_dir( $PathDir ) ) {
						Utils::copy_rec( $PathDir, $destination . '/' . $readdirectory );
						continue;
					}
					copy( $PathDir, $destination . '/' . $readdirectory );
				}
		 
				$directory->close();
			} else {
				copy( $source, $destination );
			}
		}
		
		/** ====================================================================================================================================================
		* Delete a file or a directory (recursively)
		* 
		* @param string $path the path to delete
		* @return void
		*/

		static function rm_rec($path) {
			if (is_dir($path)) {
				$objects = scandir($path);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						if (filetype($path."/".$object) == "dir") 
							Utils::rm_rec($path."/".$object); 
						else 
							unlink($path."/".$object);
					}
				}
				reset($objects);
				rmdir($path);
			} else {
				unlink($path) ; 
			}
		}

	} 
}

?>