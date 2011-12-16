<?php
/**
Plugin Name: Table of content
Description: <p>Create a table of content in you posts. </p><p>You only have to insert the shortcode <code>[toc]</code> in your post to display the table of content. </p><p>Please note that you can also configure a text to be inserted before the title of you post such as <code>Chapter</code> or <code>Section</code> with numbers. </p><p>It is stressed that the first level taken in account is "Title 2". </p><p>Plugin developped from the orginal plugin <a href="http://wordpress.org/extend/plugins/toc-for-wordpress/">Toc for Wordpress</a>. </p><p>This plugin is under GPL licence. </p>
Version: 1.2.5
Author: SedLex
Author Email: sedlex@sedlex.fr
Framework Email: sedlex@sedlex.fr
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
		$this->table_name = $wpdb->prefix . "pluginSL_" . get_class() ; 
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
		add_action('wp_print_styles', array($this,'header_init'));
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
			case 'html' 	: return "*<div class='toc tableofcontent'>
   <h2>%title%</h2>
   %toc%
</div>" 	; break ; 
			case 'css' 	: return "*.tableofcontent {
	border: 1px solid #AAAAAA;
	padding: 5px;
	padding-left: 20px;
	padding-right: 20px;
	padding-bottom: 10px;
	font-size: 0.95em;
	min-width:200px;
	float:left ; 
}" ;break ; 
			case 'css_title' 	: return "*.tableofcontent h2 {
	text-align: center;
	font-size: 1em;
	font-weight: bold;
	margin : 3px ; 
	padding-top : 5px ;
	padding-bottom : 5px ;
}" 	; break ; 
			case 'padding' 	: return 20 	; break ; 
			case 'title' 	: return "Table Of Content" 	; break ; 
			case 'h2' 		: return "Chapter #2." 			; break ; 
			case 'h3' 		: return "Section #3." 			; break ; 
			case 'h4' 		: return "#4)" 					; break ; 
			case 'h5' 		: return "#4.#5." 				; break ; 
			case 'h6' 		: return "" 					; break ; 
			case 'style_h2' 		: return "" 			; break ; 
			case 'style_h3' 		: return "" 			; break ; 
			case 'style_h4' 		: return "" 					; break ; 
			case 'style_h5' 		: return "" 				; break ; 
			case 'style_h6' 		: return "" 					; break ; 
			case 'entry_max_font_size' 		: return 14 					; break ; 
			case 'entry_min_font_size' 		: return 10 					; break ; 
			case 'entry_max_color' 		: return "#000000" 					; break ; 
			case 'entry_min_color' 		: return "#555555" 					; break ; 
		}
		return null ;
	}

	/** ====================================================================================================================================================
	* Load the configuration of the javascript in the header
	* 
	* @return variant of the option
	*/
	function header_init() {
		$css = $this->get_param('css') ; 
		$css .= "\r\n".$this->get_param('css_title') ; 
		$this->add_inline_css($css) ; 
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
		</div>
		<div style="padding:20px;">
				<?php echo $this->signature ; ?>
			<p><?php echo sprintf(__("If you want that the table of content appears in your post, just type: %s, that is all!", $this->pluginID)," <i>[toc]</i>") ?></p>
		<?php
	
			$this->check_folder_rights( array() ) ; 
			
			//==========================================================================================
			//
			// Mise en place du systeme d'onglet
			//
			//==========================================================================================

			$tabs = new adminTabs() ; 
			
			ob_start() ; 
				$params = new parametersSedLex($this, "tab-parameters") ; 
				$params->add_title(__('What is the title to be displayed in the table of content?',$this->pluginID)) ; 
				$params->add_param('title', __('Title of the table of content:',$this->pluginID)) ; 
				$params->add_title(__('Add prefix in your title:',$this->pluginID)) ; 
				$params->add_comment(__('If you leave the field blank, nothing will be added!',$this->pluginID).'<br/>'.sprintf(__('Note that if you want to display the number of level 2, just write %s ...',$this->pluginID),"<i>#2</i>")) ; 
				$params->add_param('h2', sprintf(__('Prefix of the level %s:',$this->pluginID), "2")) ; 
				$params->add_param('h3', sprintf(__('Prefix of the level %s:',$this->pluginID), "3")) ; 
				$params->add_param('h4', sprintf(__('Prefix of the level %s:',$this->pluginID), "4")) ; 
				$params->add_param('h5', sprintf(__('Prefix of the level %s:',$this->pluginID), "5")) ; 
				$params->add_param('h6', sprintf(__('Prefix of the level %s:',$this->pluginID), "6")) ; 
				$params->add_title(__('Customize the global visual appearance:',$this->pluginID)) ; 
				$params->add_param('html', __('The HTML:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('The default HTML is: %s',$this->pluginID), "<br/><code>&lt;div class='toc tableofcontent'&gt;<br/>
   &lt;h2&gt;%title%&lt;/h2&gt;<br/>
   %toc%<br/>
&lt;/div&gt;</code><br/>").
sprintf(__('Please note that %s will be replaced with the given title of the table of content and %s will be replaced with the current chapter/section/etc. title', $this->pluginID) , "<code>%title%</code>", "<code>%toc%</code>") ) ; 
				$params->add_param('css', __('The CSS:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('The default CSS is: %s',$this->pluginID), "<br/><code>.tableofcontent {<br/>
&nbsp; &nbsp;border: 1px solid #AAAAAA;<br/>
&nbsp; &nbsp;padding: 5px;<br/>
&nbsp; &nbsp;padding-left: 20px;<br/>
&nbsp; &nbsp;padding-right: 20px;<br/>
&nbsp; &nbsp;padding-bottom: 10px;<br/>
&nbsp; &nbsp;font-size: 0.95em;<br/>
&nbsp; &nbsp;min-width:200px;<br/>
&nbsp; &nbsp;float:left ; <br/>
}</code><br/>")) ; 
				$params->add_title(__('Customize the visual appearance of the title:',$this->pluginID)) ; 
				$params->add_param('css_title', __('The CSS:',$this->pluginID)) ; 
				$params->add_comment(sprintf(__('The default CSS is: %s',$this->pluginID), "<br/><code>.tableofcontent h2 {<br/>
&nbsp; &nbsp;text-align: center;<br/>
&nbsp; &nbsp;font-size: 1em;<br/>
&nbsp; &nbsp;font-weight: bold;<br/>
&nbsp; &nbsp;margin : 3px ; <br/>
&nbsp; &nbsp;padding-top : 5px ;<br/>
&nbsp; &nbsp;padding-bottom : 5px ;<br/>
}</code><br/>")) ; 
				$params->add_title(__('Customize the visual appearance of each entry in the TOC:',$this->pluginID)) ; 
				$params->add_param('padding', __('The indentation of the TOC (in pixels):',$this->pluginID)) ; 
				$params->add_param('entry_max_font_size', __('The max font size:',$this->pluginID)) ; 
				$params->add_param('entry_min_font_size', __('The max font size:',$this->pluginID)) ; 
				$params->add_param('entry_max_color', __('The color of the upper level:',$this->pluginID)) ; 
				$params->add_param('entry_min_color', __('The color of the lower level:',$this->pluginID)) ; 
				$params->add_comment(__('The color of entry will be a transition color between these two colors (depending of their levels).', $this->pluginID)."<br/> ".sprintf(__('Please add the # character before the code. If you do not know what code to use, please visit this website: %s',$this->pluginID),"<a href='http://html-color-codes.info/'>http://html-color-codes.info/</a>")) ; 
				$params->add_title(__('Customize the visual appearance of each entry in the TOC (for Experts):',$this->pluginID)) ; 
				$params->add_param('style_h2', sprintf(__('The CSS style of the level %s:',$this->pluginID),"2")) ; 
				$params->add_comment(sprintf(__('For instance, %s',$this->pluginID),"<code>font-weight:bold; size:12px</code>")) ; 
				$params->add_param('style_h3', sprintf(__('The CSS style of the level %s:',$this->pluginID),"3")) ; 
				$params->add_param('style_h4', sprintf(__('The CSS style of the level %s:',$this->pluginID),"4")) ; 
				$params->add_param('style_h5', sprintf(__('The CSS style of the level %s:',$this->pluginID),"5")) ; 
				$params->add_param('style_h6', sprintf(__('The CSS style of the level %s:',$this->pluginID),"6")) ; 
				
				$params->flush() ; 
			$tabs->add_tab(__('Parameters',  $this->pluginID), ob_get_clean() , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_param.png") ; 	
			
			
			ob_start() ; 
				$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
				$trans = new translationSL($this->pluginID, $plugin) ; 
				$trans->enable_translation() ; 
			$tabs->add_tab(__('Manage translations',  $this->pluginID), ob_get_clean() , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_trad.png") ; 	

			ob_start() ; 
				$plugin = str_replace("/","",str_replace(basename(__FILE__),"",plugin_basename( __FILE__))) ; 
				$trans = new feedbackSL($plugin, $this->pluginID) ; 
				$trans->enable_feedback() ; 
			$tabs->add_tab(__('Give feedback',  $this->pluginID), ob_get_clean() , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_mail.png") ; 	
			
			ob_start() ; 
				$trans = new otherPlugins("sedLex", array('wp-pirates-search')) ; 
				$trans->list_plugins() ; 
			$tabs->add_tab(__('Other plugins',  $this->pluginID), ob_get_clean() , WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__))."core/img/tab_plug.png") ; 	
			
			echo $tabs->flush() ; 
			
			echo $this->signature ; ?>
		</div>
		<?php
	}

	/** ====================================================================================================================================================
	* Generate a unique name from a header
	* 
	* @return string the unique name
	*/	
	
	public function get_unique_name($heading) {		
		$n = $heading ; 

		$n = str_replace(" ", "_", strip_tags($n));
		$n = str_replace("'", "_", strip_tags($n));
		$n = str_replace("\"", "_", strip_tags($n));
		$n = preg_replace("/^[0-9]*?([A-Za-z0-9\-_]*)$/u", "$1", $n);
		
		return $n;
	}

	/** ====================================================================================================================================================
	* Call when meet "[toc]" in an article
	* 
	* @return string the replacement string
	*/	
	function shortcode_toc($attribs) {	
		$out = "</p>" ; 
		$out .= $this->get_param('html') ; 
		$out .= "<div class='tableofcontent-end'> </div><p>" ; 
		
		$out = str_replace('%title%', $this->get_param('title'), $out);
		
		//Ré-initialisation
		$this->niv2 = 1 ; 
		$this->niv3 = 1 ; 
		$this->niv4 = 1 ; 
		$this->niv5 = 1 ; 
		$this->niv6 = 1 ; 
		
		$out_toc = "" ; 
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
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2-1,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
				$this->niv3 ++ ; 
				$this->niv4 = 1 ; 
				$this->niv5 = 1 ; 
				$this->niv6 = 1 ; 
			} else if ($heading['level']==4) {
				$add = $this->get_param('h4')." " ; 
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2-1,$this->niv3-1,$this->niv4,$this->niv5,$this->niv6), $add) ; 
				$this->niv4 ++ ; 
				$this->niv5 = 1 ; 
				$this->niv6 = 1 ; 
			} else if ($heading['level']==5) {
				$add = $this->get_param('h5')." " ; 
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2-1,$this->niv3-1,$this->niv4-1,$this->niv5,$this->niv6), $add) ; 
				$this->niv5 ++ ; 
				$this->niv6 = 1 ; 
			} else if ($heading['level']==6) {
				$add = $this->get_param('h6')." " ; 
				$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2-1,$this->niv3-1,$this->niv4-1,$this->niv5-1,$this->niv6), $add) ; 
				$this->niv6 ++ ; 
			}
			$min = $this->get_param('entry_min_font_size') ; 
			$max = $this->get_param('entry_max_font_size') ; 
			$font_size = floor($max-($max-$min)/4*($heading['level']-2)) ; 
			
			$r2 = hexdec(substr($this->get_param('entry_min_color'), 1, 2)) ; 
  			$g2 = hexdec(substr($this->get_param('entry_min_color'), 3, 2)) ; 
  			$b2 = hexdec(substr($this->get_param('entry_min_color'), 5, 2)) ; 
  			
			$r1 = hexdec(substr($this->get_param('entry_max_color'), 1, 2)) ; 
  			$g1 = hexdec(substr($this->get_param('entry_max_color'), 3, 2)) ; 
  			$b1 = hexdec(substr($this->get_param('entry_max_color'), 5, 2)) ; 
			
			$r3 = floor($r1 - ($r1-$r2)/4*($heading['level']-2) ) ; 
			$g3 = floor($g1 - ($g1-$g2)/4*($heading['level']-2) ) ; 
			$b3 = floor($b1 - ($b1-$b2)/4*($heading['level']-2) ) ; 
			$color = "#".str_pad(dechex($r3), 2, '0', STR_PAD_LEFT).str_pad(dechex($g3), 2, '0', STR_PAD_LEFT).str_pad(dechex($b3), 2, '0', STR_PAD_LEFT);
		
			
			$out_toc .= "<p style='font-size:".$font_size."px; line-height:".$font_size."px; padding-left:".($this->get_param('padding')*($heading['level']-2))."px;".$this->get_param('style_h'.$heading['level'])."'><a style='color:".$color." ;' href=\"#" . $i. "\">" .trim($add. $heading['value']) . "</a></p>\n";
		}
		
		$out = str_replace('%toc%', $out_toc , $out) ; 
		
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
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2-1,$this->niv3,$this->niv4,$this->niv5,$this->niv6), $add) ; 
			$this->niv3 ++ ; 
			$this->niv4 = 1 ; 
			$this->niv5 = 1 ; 
			$this->niv6 = 1 ; 
		} else if ($match[1]=="4") {
			$add = $this->get_param('h4')." " ; 
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2-1,$this->niv3-1,$this->niv4,$this->niv5,$this->niv6), $add) ; 
			$this->niv4 ++ ; 
			$this->niv5 = 1 ; 
			$this->niv6 = 1 ; 
		} else if ($match[1]=="5") {
			$add = $this->get_param('h5')." " ; 
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2-1,$this->niv3-1,$this->niv4-1,$this->niv5,$this->niv6), $add) ; 
			$this->niv5 ++ ; 
			$this->niv6 = 1 ; 
		} else if ($match[1]=="6") {
			$add = $this->get_param('h6')." " ; 
			$add = preg_replace(array("/#2/","/#3/","/#4/","/#5/","/#6/"), array($this->niv2-1,$this->niv3-1,$this->niv4-1,$this->niv5-1,$this->niv6), $add) ; 
			$this->niv6 ++ ; 
		}
		return '<h'.$match[1].' id="' . $name . '">' . trim($add . $match[2]) . '</h'.$match[1].'>';
	}

	/** ====================================================================================================================================================
	* Called when the content is printed
	* 
	* @return void
	*/	
	
	function the_content($content) {	
	
		//Ré-initialisation
		$this->niv2 = 1 ; 
		$this->niv3 = 1 ; 
		$this->niv4 = 1 ; 
		$this->niv5 = 1 ; 
		$this->niv6 = 1 ; 
		
		$this->used_names = array();
		$out = preg_replace_callback("#<h([2-6])>(.*?)</h[2-6]>#iu", array($this,"heading_anchor"), $content);
		
		//Ré-initialisation
		$this->niv2 = 1 ; 
		$this->niv3 = 1 ; 
		$this->niv4 = 1 ; 
		$this->niv5 = 1 ; 
		$this->niv6 = 1 ; 
		
		return $out;
	}
	
}

$tableofcontent = tableofcontent::getInstance();

?>