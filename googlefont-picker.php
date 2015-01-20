<?php
/**
* @package Googlefont_Picker
* @version 1.1.0
*/ 

/*
Plugin Name: WP GoogleFont Picker
Plugin URI: https://github.com/mcguffin/wp-googlefont-picker
Description: Pick some GoogleFonts to pretty up your website. The Plugin hooks into WP Theme Customizer.
Author: Joern Lund
Version: 0.0.5
Author URI: https://github.com/mcguffin/

Text Domain: googlefont
*/

/*
ToDo:
	- please-configure-message
	- options: 
		selector building
		reload font list every [___] days. (Relaod Now)
	- theme compatibility (collect some theme <-> selector relation)
	- translate
	- use WP caching system rather than 
*/

/**
 * Autoload GFPicker Classes
 *
 * @param string $classname
 */
function googlefont_autoload( $classname ) {
	$class_path = dirname(__FILE__). sprintf('/include/class-%s.php' , strtolower( $classname ) ) ; 
	if ( file_exists($class_path) )
		require_once $class_path;
}
spl_autoload_register( 'googlefont_autoload' );




if ( ! function_exists( 'googlefont_loaded' ) ) :
function googlefont_loaded() {
	load_plugin_textdomain( 'googlefont' , false , dirname(plugin_basename( __FILE__ )) . '/languages');
}
add_action('plugins_loaded','googlefont_loaded');
endif; // function_exists('googlefont_loaded')


// setup
add_action( 'after_setup_theme' , array( Googlefont_Picker , 'instance' ) );
add_action( 'customize_register' , array( Googlefont_Picker , 'instance' ) , 1 );

// backend setup
if ( is_admin() ) {
	Googlefont_Admin::instance();
}



if ( ! function_exists( 'googlefont_ajax_init' ) ) :
function googlefont_ajax_init(){
// 	if ( is_admin() && defined('DOING_AJAX') && DOING_AJAX && isset( $_REQUEST['action']) && strpos( $_REQUEST['action'] , 'googlefont' ) !== false  )
// 		googlefont_customize_register();
}
add_action('init','googlefont_ajax_init');
endif; // function_exists('googlefont_ajax_init')







//
//	Installation
//

if ( ! function_exists( 'googlefont_activate' )) :
function googlefont_activate() {
	Googlefont_Admin::instance()->add_options();
}
register_activation_hook( __FILE__ , 'googlefont_activate' );
endif; // function_exists('googlefont_activate')

if ( ! function_exists( 'googlefont_uninstall' )) :
function googlefont_uninstall() {
	Googlefont_Admin::instance()->remove_options();
}
/*
register_deactivation_hook( __FILE__ , 'googlefont_uninstall' );
/*/
register_uninstall_hook( __FILE__ , 'googlefont_uninstall' );
//*/
endif; // function_exists('googlefont_uninstall')

include_once plugin_dir_path( __FILE__ ).'/include/googlefont-cron.php';

// 

