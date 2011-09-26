<?php
/*
Core SedLex Plugin
VersionInclude : 3.0
*/ 
/** =*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*
* This PHP class enables the svn management of the plugin with the wordpress.org repository
*/
if (!class_exists("svnAdmin")) {
	class svnAdmin {
		
		var $isCompatible ; 
		var $reasonForIncompatibilities ; 
		var $cmd ; 
		
		/** ====================================================================================================================================================
		* Constructor of the class
		* 
		* @return svnAdmin the box object
		*/
		
		function svnAdmin() {
			$this->reasonForIncompatibilities = "" ; 
			
			// We test if we can use the exec function 
			$disabled = explode(', ', ini_get('disable_functions'));
			if(!in_array('exec', $disabled)){
				// We test if the svn function is available
				exec("svn info 2>&1", $out, $value) ; 
				if ($value==0) {
					$this->cmd = "svn" ; 
					$this->isCompatible = true ; 
					$this->reasonForIncompatibilities = "" ; 
				} else {
					// If no svn function is available we test on which system we are
					if (strtoupper(substr(php_uname(), 0, 3)) != 'WIN') {
						
						// We create a sh file to launch
						$path = WP_CONTENT_DIR."/sedlex/svn" ; 
						$rep_exists = true ; 
						if (!is_dir($path)) {
							$rep_exists = @mkdir($path, 0755, true) ; 
						}
						if ($rep_exists) {
							$path = $path."/svn.sh" ; 
							if (!is_file($path)) {
								$text = '#!/bin/sh'."\n" ; 
								$text .= 'LD_LIBRARY_PATH='.WP_PLUGIN_DIR.'/'.str_replace(basename(  __FILE__),"",plugin_basename( __FILE__)).'svn_bin:/usr/lib'."\n" ; 
								$text .= 'PATH='.WP_PLUGIN_DIR.'/'.str_replace(basename(  __FILE__),"",plugin_basename( __FILE__)).'svn_bin:$PATH'."\n" ; 
								$text .= 'export LD_LIBRARY_PATH PATH'."\n" ; 
								$text .= 'exec svn "$@"' ; 
								@file_put_contents($path, $text);		
								@chmod($path,0777); 								
							} 
							if (!is_file($path)) {
								$this->isCompatible = false ; 
								$this->reasonForIncompatibilities =  sprintf(__("The file %s cannot be created. Therefore, no svn script can be created. Please make the folder writable or visit %s to install a SubVersion package on your server.", 'SL_framework'), "<code>".$path."</code>", "<a href='http://subversion.apache.org/packages.html'>Apache SubVersion</a>") ; 
							} else {
								$svnpath = WP_PLUGIN_DIR.'/'.str_replace(basename(  __FILE__),"",plugin_basename( __FILE__)).'bin/svn' ;
								@chmod($svnpath,0777); 
								$this->cmd = $path ; 
								$this->isCompatible = true ; 
								$this->reasonForIncompatibilities = "" ;
							}
						} else {
							$this->isCompatible = false ; 
							$this->reasonForIncompatibilities =  sprintf(__("The folder %s does not exists and cannot be created. Therefore, no svn script can be created in that folder. Please create this folder (and make it writable) or visit %s to install a SubVersion package on your server.", 'SL_framework'), "<code>".$path."</code>", "<a href='http://subversion.apache.org/packages.html'>Apache SubVersion</a>") ; 
						}
					} else {
						exec("svn.exe info 2>&1", $out, $value) ; 
						if ($value==0) {
							$this->cmd = "svn.exe" ; 
							$this->isCompatible = true ; 
							$this->reasonForIncompatibilities = "" ; 
						} else {
							$this->isCompatible = false ; 
							$this->reasonForIncompatibilities =  sprintf(__("The operating system of your server is %s. No installation of SVN has been found. Please visit %s to install a SubVersion package on your server.", 'SL_framework'), "<code>".php_uname()."</code>", "<a href='http://subversion.apache.org/packages.html'>Apache SubVersion</a>") ; 
						}
					}
				}
				
			} else {
				$this->isCompatible = false ; 
				$this->reasonForIncompatibilities =  __("The exec function is disabled on your installation. This function is mandatory to be able to use SVN.", 'SL_framework') ; 
			}
		}
		
		/** ====================================================================================================================================================
		* Update or Checkout the following local cache
		* 
		* @return 
		*/
		function update_checkout($root, $repository, $print=true) {
			if  (is_dir($root."/.svn")) {
				echo "<p>".__('SVN command:', 'SL_framework')." <code>update</code></p>\n" ; 
				$return_value = $this->update($root) ; 					
			} else {
				echo "<p>".__('SVN command:', 'SL_framework')." <code>checkout $root $repository</code></p>\n" ; 
				$return_value = $this->checkout($root, $repository) ; 
			}
			return $return_value ; 
		}
		
		/** ====================================================================================================================================================
		* Update the following local cache
		* 
		* @return 
		*/
		function update($root, $print=true) {
			$value = 99 ; 
			
			chdir($root) ; 
			
			exec($this->cmd." cleanup 2>&1", $out, $value) ; 
			exec($this->cmd." revert --recursive . 2>&1", $out, $value) ; 
			exec($this->cmd." update 2>&1", $out, $value) ; 
			
			// On affiche
			if ($print) {
				echo "<p class='console'>\n" ; 
				echo sprintf(__('%s returns the following code: %s', 'SL_framework'), "*Update*", "<b>".$value."</b><br/>")."\n" ; 
				foreach ($out as $l) {
					echo $l."<br/>\n" ; 
				}
				echo "</p>\n" ; 
			}
			
			return $value ; 
		}
		
		/** ====================================================================================================================================================
		* Checkout
		* 
		* @return void
		*/
		function checkout($root, $repository, $print=true) {
			$value = 99 ; 
			
			// we delete the repository if exist
			if (is_dir($root)) {
				Utils::rm_rec($root) ; 
			}
			// we create the root file
			if (!is_dir($root)) {
				@mkdir($root, 0777, true) ; 
			}
			
			chdir($root) ; 
			
			exec($this->cmd." checkout ".$repository." ".$root." 2>&1", $out, $value) ; 
			
			// On affiche
			if ($print) {
				echo "<p class='console'>\n" ; 
				echo sprintf(__('%s returns the following code: %s ', 'SL_framework'), "*Checkout*", "<b>".$value."</b>")."<br/>\n" ; 
				if ($value == 0) {
					foreach ($out as $l) {
						echo $l."<br/>\n" ; 
					}
				} else {
					echo __('Checkout has failed! Please retry ...', 'SL_framework')."<br/>\n" ; 
					echo __('Indeed, it is known that the Checkout command have some difficulties to work. You may have to re-test several times (1-20 times) to finally succeed.', 'SL_framework')."<br/>\n" ; 
					echo __('Do not panic: once the checkout have worked one time, the update command will be used and it is far more robust!', 'SL_framework')."<br/>\n" ; 
					if (count($out)>0) {
						echo "<br/>".__('NOTE: The command outputs the following information:', 'SL_framework')."<br/>\n" ; 
						$isBeginSync = false ; 
						foreach ($out as $l) {
							if(strpos($l, $root) === FALSE) {
								echo $l."<br/>\n" ; 
							} else {
								if (!$isBeginSync) {
									$isBeginSync = true ; 
									echo __('The checkout have begun but have been interrupted without any reason!', 'SL_framework')."<br/>\n" ; 
								}
							}
						}
					} else {
						echo "<br/>".__('NOTE: The command does not output any information.', 'SL_framework')."<br/>\n" ; 
					}
				} 
				echo "</p>\n" ; 
			}
			
			
			// If it is unsuccessuful,  we delete the local cache
			if ($value!=0) {
				if (is_dir($root)) {
					Utils::rm_rec($root) ; 
				}
			}
			
			return $value ; 
		}
		
		
		/** ====================================================================================================================================================
		* Add
		* 
		* @return void
		*/
		function add($root, $file) {
			$value = 99 ; 
			
			$f = str_replace($root,"",$file) ; 
			$added = false ; 
			
			// Change directory
			chdir($root) ; 
			// Clean the SVN stuff (to remove any lock)
			exec($this->cmd." cleanup 2>&1", $out) ; 
	
			// We first check that the directory are SVN compliant :)
			$ad = explode("/",str_replace(basename($f),"",$f)) ; 
			$list_f = $root ; 
			foreach($ad as $d) {
				$list_f .= $d."/" ; 
				if (!is_dir($list_f)) {
					@mkdir($list_f, 0777, true) ; 
				}
				if (!is_dir($list_f.".svn/")) {
					$added = true ; 
					exec($this->cmd." add ".str_replace($root,'',$list_f)." 2>&1", $out) ; 
				}
			}
			
			// We add the file
			if (!$added) {
				exec($this->cmd." add ".$f." 2>&1", $out, $value) ; 
			}
			echo "<p class='console'>\n" ; 
			echo sprintf(__('%s returns the following code: %s', 'SL_framework'), "*Add*", "<b>".$value."</b><br/>")."\n" ; 
			foreach ($out as $l) {
				echo $l."<br/>\n" ; 
			}
			echo "</p>\n" ; 
			
			return $value ; 
		}
		
		
		/** ====================================================================================================================================================
		* SVN delete
		* 
		* @return void
		*/
		function delete($root, $file) {
			
			$value = 99 ; 
			
			chdir($root) ; 
			
			exec($this->cmd." cleanup 2>&1", $out, $value) ; 
			exec($this->cmd." delete ".$file." 2>&1", $out, $value) ; 
			
			// On affiche
			if ($print) {
				echo "<p class='console'>\n" ; 
				echo sprintf(__('%s returns the following code: %s', 'SL_framework'), "*Delete*", "<b>".$value."</b><br/>")."\n" ; 
				foreach ($out as $l) {
					echo $l."<br/>\n" ; 
				}
				echo "</p>\n" ; 
			}
			
			return $value ; 
		}
		/** ====================================================================================================================================================
		* Commit
		* 
		* @return void
		*/
		function commit($root, $login, $pass, $comment) {
			$value = 99 ; 
			// Change directory
			chdir($root) ; 
								
			// Clean the SVN stuff (to remove any lock)
			exec($this->cmd." cleanup 2>&1", $out) ; 
			
			// We commit the change
			exec($this->cmd." commit ".$f." --username ".$login." --password ".$pass." --message \"".$comment."\" 2>&1", $out, $value) ; 
	
			echo "<p class='console'>\n" ; 
				echo sprintf(__('%s returns the following code: %s', 'SL_framework'), "*Commit*", "<b>".$value."</b><br/>")."\n" ; 
			foreach ($out as $l) {
				echo $l."<br/>\n" ; 
			}
			echo "</p>\n" ; 
			
			return $value ; 
		}	
	}
}

?>