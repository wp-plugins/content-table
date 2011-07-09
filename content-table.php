<?php
/*
Plugin Name: Table of content
Description: <p>Create a table of content in you posts. </p><p>You only have to insert the shortcode <code>[toc]</code> in your post to display the table of content. </p><p>Please note that you can also configure a text to be inserted before the title of you post such as <code>Chapter</code> or <code>Section</code> with numbers. </p><p>It is stressed that the first level taken in account is "Title 2". </p><p>Plugin developped from the orginal plugin <a href="http://wordpress.org/extend/plugins/toc-for-wordpress/">Toc for Wordpress</a>. </p><p>This plugin is under GPL licence. </p>
Version: 1.0.1
Author: SedLex
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/extend/plugins/content-table/
License: GPL3
*/

require_once('core.php') ; 


class tableofcontent extends pluginSedLex {
	
	var $tableofcontent_used_names ;
	var $niv2 ;
	var $niv3 ;
	var $niv4 ;
	var $niv5 ;
	var $niv6 ;
	

	/** ====================================================================================================================================================
	* Initialisation du plugin
	* 
	* @return void
	*/
	static $instance = false;

	protected function _init() {
		// Configuration
		$this->pluginName = 'Table of content' ; 
		$this->table_sql = '' ; 
		$this->path = __FILE__ ; 
		$this->pluginID = get_class() ; 
		
		//Init et des-init
		register_activation_hook(__FILE__, array($this,'install'));
		register_deactivation_hook(__FILE__, array($this,'uninstall'));

		//Paramètres supplementaires
		$this->tableofcontent_used_names = array();
		$this->niv2 = 1 ; 
		$this->niv3 = 1 ; 
		$this->niv4 = 1 ; 
		$this->niv5 = 1 ; 
		$this->niv6 = 1 ; 
		
		add_shortcode( "toc", array($this,"shortcode_toc") );
		add_action( "the_content", array($this,"the_content") );
	}
	/**
		 * Function to instantiate our class and make it a singleton
		 */
		public static function getInstance() {
			if ( !self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	
	/** ====================================================================================================================================================
	* Define the default option value of the plugin
	* 
	* @return variant of the option
	*/
	public function get_default_option($option) {
		switch ($option) {
			case 'title' 	: return "Table Of Content" 	; break ; 
			case 'h2' 		: return "Chapter #2." 			; break ; 
			case 'h3' 		: return "Section #3." 			; break ; 
			case 'h4' 		: return "#4)" 					; break ; 
			case 'h5' 		: return "#4.#5." 				; break ; 
			case 'h6' 		: return "" 					; break ; 
		}
		return null ;
	}

	/** ====================================================================================================================================================
	* The configuration page
	* 
	* @return void
	*/
	public function configuration_page() {
		global $wpdb;
	
		?>
		<div class="wrap">
			<div id="icon-themes" class="icon32"><br></div>
			<h2><?php echo $this->pluginName ?></h2>
			<?php echo $this->signature ; ?>
			<p>If you want that the table of content appears in your post, just type <i>[toc]</i>, that is all !</p>
			<!--debut de personnalisation-->
		<?php
	
			
			//==========================================================================================
			//
			// Mise en place du systeme d'onglet
			//		(bien mettre a jour les liens contenu dans les <li> qui suivent)
			//
			//==========================================================================================
		?>		
			<script>jQuery(function($){ $('#tabs').tabs(); }) ; </script>		
			<div id="tabs">
				<ul class="hide-if-no-js">
					<li><a href="#tab-parameters"><? echo __('Parameters',$this->pluginID) ?></a></li>					
				</ul>
				<?php
				//==========================================================================================
				//
				// Premier Onglet 
				//		(bien verifier que id du 1er div correspond a celui indique dans la mise en 
				//			place des onglets)
				//
				//==========================================================================================
				?>
				<div id="tab-parameters" class="blc-section">
				
					<h3 class="hide-if-js"><? echo __('Parameters',$this->pluginID) ?></h3>
					<p><?php echo __('Here is the parameters of the plugin. Please modify them at your convenience.',$this->pluginID) ; ?> </p>			
					
					<?php
					$params = new parametersSedLex($this, "tab-parameters") ; 
					$params->add_title(__('What is the title of the TOC?',$this->pluginID)) ; 
					$params->add_param('title', __('Main title:',$this->pluginID)) ; 
					$params->add_title(__('Do you want to modify the content of your titles by adding numbers or text before them? If you leave the field blank, nothing will be added !<br/>Note that if you want to display the number of level 2, just write <i>#2</i> ...',$this->pluginID)) ; 
					$params->add_param('h2', __('For level 2 title:',$this->pluginID)) ; 
					$params->add_param('h3', __('For level 3 title:',$this->pluginID)) ; 
					$params->add_param('h4', __('For level 4 title:',$this->pluginID)) ; 
					$params->add_param('h5', __('For level 5 title:',$this->pluginID)) ; 
					$params->add_param('h6', __('For level 6 title:',$this->pluginID)) ; 
					
					$params->flush() ; 
					
					?>
				</div>
			</div>
			<!--fin de personnalisation-->
			<?php echo $this->signature ; ?>
		</div>
		<?php
	}

	/** ====================================================================================================================================================
	* Generate a unique name from a header
	* 
	* @return string the unique name
	*/	
	
	public function get_unique_name($heading) {		
		$n = str_replace(" ", "_", strip_tags($heading));
		$n = preg_replace("#[^A-Za-z0-9\-_]#", "", $n);
		$n = preg_replace("#^[^A-Za-z]*?([A-Za-z])#", "$1", $n);
		return $n;
	}

	/** ====================================================================================================================================================
	* Call when meet "[toc]" in an article
	* 
	* @return string the replacement string
	*/	
	function shortcode_toc($attribs) {	
		$out = "</p><div class='toc tableofcontent'>\n";
		$out .= "<h2>".$this->get_param('title')."</h2>\n";
		
		//Ré-initialisation
		$this->niv2 = 1 ; 
		$this->niv3 = 1 ; 
		$this->niv4 = 1 ; 
		$this->niv5 = 1 ; 
		$this->niv6 = 1 ; 

		// headings...
		foreach($this->used_names as $i => $heading) {
			// We check if we have to add something here
			$add = "" ; 
			if ($heading['level']==2) {
				$add = $this->get_param('h2')." " ; 
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
				$this->niv2 ++ ; 
				$this->niv3 = 1 ; 
				$this->niv4 = 1 ; 
				$this->niv5 = 1 ; 
				$this->niv6 = 1 ; 
			} else if ($heading['level']==3) {
				$add = $this->get_param('h3')." " ; 
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
				$this->niv3 ++ ; 
				$this->niv4 = 1 ; 
				$this->niv5 = 1 ; 
				$this->niv6 = 1 ; 
			} else if ($heading['level']==4) {
				$add = $this->get_param('h4')." " ; 
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
				$this->niv4 ++ ; 
				$this->niv5 = 1 ; 
				$this->niv6 = 1 ; 
			} else if ($heading['level']==5) {
				$add = $this->get_param('h5')." " ; 
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
				$this->niv5 ++ ; 
				$this->niv6 = 1 ; 
			} else if ($heading['level']==6) {
				$add = $this->get_param('h6')." " ; 
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
				$this->niv6 ++ ; 
			}
		
			$out .= "<p style='text-indent:".(0.5*($heading['level']-2))."cm;'><a href=\"#" . esc_attr($i). "\">" .trim($add. $heading['value']) . "</a></p>\n";
		}
		
		$out .= "</div><div class='fix'></div>\n";
		$out .= "<p>\n";
		
		//Ré-initialisation
		$this->niv2 = 1 ; 
		$this->niv3 = 1 ; 
		$this->niv4 = 1 ; 
		$this->niv5 = 1 ; 
		$this->niv6 = 1 ; 
		 
		return $out;
	}

	/** ====================================================================================================================================================
	* Callback pour modifier les titres dans le content
	* 
	* @return void
	*/	
	
	
	function heading_anchor($match) {		
		$name = $this->get_unique_name($match[2]);
		
		if (isset($this->used_names[$name])) {
			$name = $name.rand(0,10000000) ; 
		}
		$this->used_names[$name] = array() ;
		
		$this->used_names[$name]['level'] = $match[1] ; 
		$this->used_names[$name]['value'] = $match[2] ; 
		
		// We check if we have to add something here
		$add = "" ; 
		if ($match[1]=="2") {
			$add = $this->get_param('h2')." " ; 
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
			$this->niv2 ++ ; 
			$this->niv3 = 1 ; 
			$this->niv4 = 1 ; 
			$this->niv5 = 1 ; 
			$this->niv6 = 1 ; 
		} else if ($match[1]=="3") {
			$add = $this->get_param('h3')." " ; 
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
			$this->niv3 ++ ; 
			$this->niv4 = 1 ; 
			$this->niv5 = 1 ; 
			$this->niv6 = 1 ; 
		} else if ($match[1]=="4") {
			$add = $this->get_param('h4')." " ; 
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
			$this->niv4 ++ ; 
			$this->niv5 = 1 ; 
			$this->niv6 = 1 ; 
		} else if ($match[1]=="5") {
			$add = $this->get_param('h5')." " ; 
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
			$this->niv5 ++ ; 
			$this->niv6 = 1 ; 
		} else if ($match[1]=="6") {
			$add = $this->get_param('h6')." " ; 
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
			$this->niv6 ++ ; 
		}
		
		return '<h'.$match[1].' id="' . esc_attr($name) . '">' . trim($add . $match[2]) . '</h'.$match[1].'>';
	}

	/** ====================================================================================================================================================
	* Called when the content is printed
	* 
	* @return void
	*/	
	
	function the_content($content) {		
		$this->used_names = array();
		$out = preg_replace_callback("#<h([2-6])>(.*?)</h[2-6]>#i", array($this,"heading_anchor"), $content);
		return $out;
	}
}

$efficientRelatedPosts = tableofcontent::getInstance();

?>