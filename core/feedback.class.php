<?php
/*
Core SedLex Plugin
VersionInclude : 3.0
*/ 

/** =*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*=*
* This PHP class enables the translation of the plugin using the framework
*/
if (!class_exists("feedbackSL")) {
	class feedbackSL {

		/** ====================================================================================================================================================
		* Constructor of the class
		* 
		* @param string $plugin the name of the plugin (probably <code>str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__)))</code>)
		* @return feedbackSL the feedbackSL object
		*/
		function feedbackSL($plugin) {
			$this->plugin = $plugin ; 
		}
		
		/** ====================================================================================================================================================
		* Display the feedback form
		* Please note that the users will send you their comments/feedback at the email used is in the header of the main file of your plugin <code>Author Email : xxx@xxx.com</code>
		* 
		* @return void
		*/

		public function enable_feedback() {
			
			echo "<a name='top_feedback'></a><div id='form_feedback_info'></div><div id='form_feedback'>" ; 
			$_POST['plugin'] = $this->plugin ; 
			
			$info_file = pluginSedLex::get_plugins_data(WP_PLUGIN_DIR."/".$this->plugin."/".$this->plugin.".php") ; 
			if (preg_match("#^[a-z0-9-_.]+@[a-z0-9-_.]{2,}\.[a-z]{2,4}$#",$info_file['Email'])) {
				echo "<p>".__('Your name:', 'SL_framework')." <input id='feedback_name' type='text' name='feedback_name' value='' /></p>" ; 
				echo "<p>".__('Your email (for response):', 'SL_framework')." <input id='feedback_mail' type='text' name='feedback_mail' value='' /></p>" ; 
				echo "<p>".__('Your comments:', 'SL_framework')." </p>" ; 
				echo "<p><textarea id='feedback_comment' style='width:500px;height:400px;'></textarea></p>" ; 
				echo "<p id='feedback_submit'><input type='submit' name='add' class='button-primary validButton' onclick='send_feedback(\"".$this->plugin."\");return false;' value='".__('Send feedback','SL_framework')."' /></p>" ; 
				
				$x = WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__)) ; 
				echo "<img id='wait_feedback' src='".$x."/img/ajax-loader.gif' style='display:none;'>" ; 

			} else {
				echo "<p>".__('No email have been provided for the author of this plugin. Therefore, the feedback is impossible', 'SL_framework')."</p>" ; 
			}
			echo "</div>" ; 
			
		}
		
		/** ====================================================================================================================================================
		* Send the feedback form
		* 
		* @access private
		* @return void
		*/
		public function send_feedback() {
			// We sanitize the entries
			$plugin = preg_replace("/[^a-zA-Z0-9_-]/","",$_POST['plugin']) ; 
			$name = strip_tags($_POST['name']) ; 
			$mail = preg_replace("/[^:\/a-z0-9@A-Z_.-]/","",$_POST['mail']) ; 
			$comment = strip_tags($_POST['comment']) ; 
			
			$info_file = pluginSedLex::get_plugins_data(WP_PLUGIN_DIR."/".$plugin."/".$plugin.".php") ; 
			
			$to = $info_file['Email'] ; 
			
			
			
			$subject = "[".ucfirst($plugin)."] Feedback of ".$name ; 
			
			$message = "" ; 
			$message .= "From $name (".$mail.")\n\n\n" ; 
			$message .= $comment ; 
			
			$headers = "" ; 
			if (preg_match("#^[a-z0-9-_.]+@[a-z0-9-_.]{2,}\.[a-z]{2,4}$#",$mail)) {
			echo "coucou" ; 
				$headers = "Reply-To: $mail\n".
						"Return-Path: $mail" ; 
			}
			
			$attachments = array();
			
			// send the email
			if (wp_mail( $to, $subject, $message, $headers, $attachments )) {
				echo "<div class='updated  fade'>" ; 
				echo "<p>".__("The feedback has been sent", 'SL_framework')."</p>" ; 
				echo "</div>" ; 
			} else {
				echo "<div class='error  fade'>" ; 
				echo "<p>".__("An error occured sending the email.", 'SL_framework')."</p><p>".__("Make sure that your wordpress is able to send email.", 'SL_framework')."</p>" ; 
				echo "</div>" ; 			
			}

			//Die in order to avoid the 0 character to be printed at the end
			die() ;

		}
		
	}
}

?>