<?php

class Googlefont_Picker {
	// on 'admin_init'

	public function __construct( ) {
		global $googlefont;
		
		add_action( 'customize_register', array( &$this , 'customize_register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( &$this , 'enqueue_customize_scripts' ) );
		add_action( 'admin_print_styles-appearance_page_custom-header', array( &$this , 'enqueue_frontend_style') ); // fct: dequeue twentythirteen-fonts, enqueue own fonts

		add_action('wp_ajax_googlefont_add_favorite',array( &$this , 'ajax_add_to_favorites' ));
		add_action('customize_controls_print_scripts',array(  &$this , 'print_ajax_url' ));

		add_action('customize_save_after' , array( &$this , 'save_options') );
	}
	
	// -------------------------------------------
	//	Ajax
	// -------------------------------------------
	public function ajax_add_to_favorites($a) {
//		header( 'Content-Type: text/plain' );
		$font = $_POST['font'];
		$favs = get_user_option('googlefont_favorites');
		if ( !$favs )
			$favs = array();
		
		if ($pos = array_search($font,$favs))
			unset($favs[ $pos ]);
		else 
			$favs[] = $font;
		$favs = array_unique($favs);
		
		update_user_option( get_current_user_id() , 'googlefont_favorites',$favs);
		header( 'Content-Type: application/json' );
		echo json_encode( $pos );
		die;
	}
	public function print_ajax_url() {
		?><script type="text/javascript">var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';</script><?php
	}
	
	// -------------------------------------------
	//	Scripts
	// -------------------------------------------
	public function enqueue_customize_scripts( ) {
		wp_enqueue_script( 'googlefont-theme-customizer', plugins_url( '/js/googlefont-customizer.js' , dirname(__FILE__) ) , array('jquery') ) ;
		wp_enqueue_style( 'googlefont-theme-customizer', plugins_url( '/css/googlefont-customizer.css' , dirname(__FILE__) )  );
	}

	// -------------------------------------------
	//	Customizer init
	// -------------------------------------------
	public function customize_register( $wp_customize ) {
		global $googlefont;
		include_once plugin_dir_path( __FILE__ ).'/class-customize_fontpicker_control.php';
		$selectors = $googlefont->get_selectors();

		if ( empty( $selectors ) ) 
			return;
		
		$wp_customize->add_section( 'googlefont_settings' , array(
			'title' => __( 'Google Webfonts' , 'googlefont'),
			'priority' => 50,
		) );

		foreach ( $selectors as $name => $obj ) {
			if ( $obj->active ) {
				$wp_customize->add_setting( $obj->name , array('default' => '',));
				$ctrl = new Customize_Fontpicker_Control( $wp_customize , $obj->name , array(
					'label'=>$obj->label,
					'section' => 'googlefont_settings',
					'type' => 'text',
					'description'=>$obj->description,
					'options'=>$obj,
				) );
				$wp_customize->add_control( $ctrl );
			}
		}
	}
	
	
	// -------------------------------------------
	//	Flush to options for quick access to settings
	// -------------------------------------------
	public function save_options() {
		global $googlefont;
		$css = $googlefont->get_css( );
		$google_font_url = $googlefont->get_googlefont_url( );
		update_option( sprintf( 'googlefont_%s_css' , get_option('stylesheet') ) , $css );
		update_option( sprintf( 'googlefont_%s_fonturl' , get_option('stylesheet') ) , $google_font_url );
	}
}
$googlefont_picker = new Googlefont_Picker();
