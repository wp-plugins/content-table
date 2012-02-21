=== Table of content ===

Author: SedLex
Contributors: SedLex
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/extend/plugins/content-table/
Tags: plugin, table of content, toc, content
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: trunk

Insert a *table of content* in your posts. 

== Description ==

Insert a *table of content* in your posts. 

You only have to insert the shortcode [toc] in your post to display the table of content. 

Please note that you can also configure a text to be inserted before the title of you post such as Chapter or Section with numbers. 

It is stressed that the first level taken in account is "Title 2". 

Plugin developped from the orginal plugin Toc for Wordpress. 

This plugin is under GPL licence. 

= Localization =

* English (United States), default language
* French (France) translation provided by SedLex
* Italian (Italy) translation provided by jkappa
* Russian (Russia) translation provided by Limych

= Features of the framework =

This plugin uses the SL framework. This framework eases the creation of new plugins by providing incredible tools and frames.

For instance, a new created plugin comes with

* A translation interface to simplify the localization of the text of the plugin ; 
* An embedded SVN client (subversion) to easily commit/update the plugin in wordpress.org repository ; 
* A detailled documentation of all available classes and methodes ; 
* etc.

Have fun !

== Installation ==

1. Upload this folder to your plugin directory (for instance '/wp-content/plugins/')
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the 'SL plugins' box
4. All plugins developed with the SL core will be listed in this box
5. Enjoy !

== Screenshots ==

1. The configuration page of the plugin
2. An example of a table of content

== Changelog ==

= 1.3.2 =
* Update of the framework

= 1.3.1 =
* Update translations

= 1.3.0 =
* Major update of the framework

= 1.2.5 =
* Bug correction (open_basedir restriction)
* Change the creation of the link id ...

= 1.2.4 =
* Improve the English text thanks to Rene 

= 1.2.3 =
* Each level may be stylized with custom CSS

= 1.2.2 =
* The HTML/CSS may now be modified to fit your need
* The size of the entries may be configured
* The color of the entries may be configured

= 1.2.1 =
* Improve the information interface of the Core
* Add translations for the core

= 1.2.0 =
* SVN support for committing changes

= 1.1.6 =
* Russian translation (by Limych)
* Italian translation (by Jkappa)

= 1.1.5 =
* Major update of the core (beta SVN support)

= 1.1.4 =
* Fix a bug in computing the hash of the plugin and the core to determine which one is the most up-to-date

= 1.1.3 =
* Update of the core plugin

= 1.1.2 =
* Correction bug for the numbering in the RSS feed
* ZipArchive class has been suppressed and pclzip is used instead

= 1.1.1 =
* Ensure that folders and files permissions are correct for an adequate behavior

= 1.0.8 =
* Thanks to Vincent (http://www.vincent.mabillot.net) a bug in the numbering have been corrected
* Feedback have been improved to show the configuration of the submitters
* Update of the framework

= 1.0.6 and 1.0.7 =
* Correction of a micro-bug (nothing to worry about)

= 1.0.5 =
* Enhance the internationalization
* Improve stability

= 1.0.4 =
* Correction of the bug when a plurality of plugin use the same framework

= 1.0.3 =
* Correction of a bug in the load-style.php which change dynamically the url of the image contained in the CSS file
* Enable the translation of the plugin (modification in the framework, thus all your plugin developped with this framework can enable this feature easily)
* Add the email of the author in the header of the file to be able to send email to him
* Enhance the localization of the plugin
* The javascript function to be called for table cell can have now complex parameters (instead of just the id of the line)
* Add the French localization
* Add a form to send feedback to the author

= 1.0.2 =
* Update the framework with a new version

= 1.0.1 =
* First release in the wild web (have fun)

== Frequently Asked Questions ==

* Where can I read more?

Visit http://www.sedlex.fr/cote_geek/
 
 
InfoVersion:366283c1b28d736855bfd0a0f37b75cb