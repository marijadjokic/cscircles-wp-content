<?php
/*
		Plugin Name: jQuery Vertical Mega Menu
		Plugin URI: http://www.designchemical.com/blog/index.php/wordpress-plugins/wordpress-plugin-jquery-vertical-mega-menu-widget/
		Tags: jquery, flyout, mega, menu, vertical, animated, css, navigation, widget
		Description: Creates a widget, which allows you to add vertical mega menus to your side columns using any Wordpress custom menu.
		Author: Lee Chestnutt
		Version: 1.3.3
		Author URI: http://www.designchemical.com
*/

global $registered_skins;

class dc_jqverticalmegamenu {

	function dc_jqverticalmegamenu(){
		global $registered_skins;
	
		if(!is_admin()){
			// Header styles
			add_action( 'wp_head', array('dc_jqverticalmegamenu', 'header') );
			
		}
		add_action( 'wp_footer', array('dc_jqverticalmegamenu', 'footer') );
		
		$registered_skins = array();
	}

	function header(){
		echo "\n\t<link rel=\"stylesheet\" type=\"text/css\" href=\"".dc_jqverticalmegamenu::get_plugin_directory()."/css/dcverticalmegamenu.css\" media=\"screen\" />";
		
		// Scripts
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jqueryhoverintent', dc_jqverticalmegamenu::get_plugin_directory() . '/js/jquery.hoverIntent.minified.js', array('jquery') );
			wp_enqueue_script( 'dcjqverticalmegamenu', dc_jqverticalmegamenu::get_plugin_directory() . '/js/jquery.dcverticalmegamenu.1.3.js', array('jquery') );
			
	}
	
	function footer(){
		//echo "\n\t";
	}
	
	function options(){}

	function get_plugin_directory(){
		return WP_PLUGIN_URL . '/jquery-vertical-mega-menu';	
	}

};

// Include the widget
include_once('dcwp_jquery_vertical_mega_menu_widget.php');

// Initialize the plugin.
$dcjqverticalmegamenu = new dc_jqverticalmegamenu();

// Register the widget
add_action('widgets_init', create_function('', 'return register_widget("dc_jqverticalmegamenu_widget");'));

?>