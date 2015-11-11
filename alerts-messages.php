<?php
/**
 * Plugin Name: Alerts & Messages
 * Description: Displays alerts and messages on your site, including a countdown timer
 * Version: 0.0.5
 * Author: Big Boom Design
 * Author URI: http://bigboomdesign.com
 */

require_once alm_dir("lib/class-alm.php");
# Admin routines
if(is_admin()){	
	# define sections and fields for options page
	add_action('admin_init', array('Alm_Options', 'register_settings'));
	
	# styles and scripts
	add_action('admin_enqueue_scripts', array('Alm','admin_enqueue'));
	
	# plugin options page
	add_action('admin_menu', 'alm_settings_page');
	function alm_settings_page() {
		add_options_page('Alerts & Messages', 'Alerts & Messages', 'manage_options', 'alm_settings', array('Alm_Options','settings_page'));
	}
	
	# Action links on main Plugins screen
	$plugin = plugin_basename(__FILE__);
	add_filter("plugin_action_links_$plugin", 'alm_plugin_actions' );
	function alm_plugin_actions($links){
		$settings_link = '<a href="options-general.php?page=alm_settings">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}	
} #end: admin routines

# Front end routines
else{
	# Default message
	add_action('wp', array('Alm','default_message'));

	# Shortcodes
	## alert
	add_shortcode('alm_alert', array('Alm', 'do_alert'));
	## countdown
	add_shortcode('alm_countdown', array('Alm', 'do_countdown'));

} # end: front end routines
#end main routine

###
# Helper functions
###
# paths
function alm_url($s){ return plugins_url($s, __FILE__); }
function alm_dir($s){ return plugin_dir_path(__FILE__) . $s; }