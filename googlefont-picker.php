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
Version: 0.0.1
Author URI: https://github.com/mcguffin/

Text Domain: googlefont
Domain Path: /lang/
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

if ( ! function_exists( 'googlefont_init' ) ) :
function googlefont_init() {
	load_plugin_textdomain( 'googlefont' , false , dirname(__FILE__).'/lang/' );
}
add_action('init','googlefont_init');
endif; // function_exists('googlefont_init')



// setup
if ( ! function_exists( 'googlefont_setup' ) ) :
function googlefont_setup() {
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-filter.php';
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont.php';
	if ( ( isset( $_POST['customized'] ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) )
		include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-api.php';
}
add_action( 'after_setup_theme', 'googlefont_setup' );
endif; // function_exists('googlefont_setup')



if ( ! function_exists( 'googlefont_customize_register' ) ) :
function googlefont_customize_register() {
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-api.php';
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-filter.php';
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-picker.php';
}
add_action('customize_register','googlefont_customize_register' , 1 );
endif; // function_exists('googlefont_customize_register')


if ( ! function_exists( 'googlefont_ajax_init' ) ) :
function googlefont_ajax_init(){
	if ( is_admin() && defined('DOING_AJAX') && DOING_AJAX && isset( $_REQUEST['action']) && strpos( $_REQUEST['action'] , 'googlefont' ) !== false  )
		googlefont_customize_register();
}
add_action('init','googlefont_ajax_init');
endif; // function_exists('googlefont_ajax_init')

if ( is_admin() ) {
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-api.php';
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-admin.php';
}
//
//	Installation
//

if ( ! function_exists( 'googlefont_activate' )) :
function googlefont_activate() {
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-admin.php';
	Googlefont_Admin::add_options();
}
register_activation_hook( __FILE__ , 'googlefont_activate' );
endif; // function_exists('googlefont_activate')

if ( ! function_exists( 'googlefont_uninstall' )) :
function googlefont_uninstall() {
	include_once plugin_dir_path( __FILE__ ).'/include/class-googlefont-admin.php';
	Googlefont_Admin::remove_options();
}
/*
register_deactivation_hook( __FILE__ , 'googlefont_uninstall' );
/*/
register_uninstall_hook( __FILE__ , 'googlefont_uninstall' );
//*/
endif; // function_exists('googlefont_uninstall')


// 

