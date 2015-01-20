<?php

/*
 * This class does the frontend job. Enqueing Googlefont-css and printing custom styles.
 */

if ( ! class_exists( 'Googlefont' ) ) :
class Googlefont {

	private $_selectors = array();
	
	/**
	 *	Holding the singleton instance
	 */
	private static $_instance = null;

	/**
	 *	@return WP_reCaptcha_Options The options manager instance
	 */
	public static function instance(){
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 *	Prevent from creating more than one instance
	 */
	private function __clone() {
	}
	/**
	 *	Prevent from creating more than one instance
	 */
	private function __construct() {
		if ( $selectors = get_option( 'googlefont_selectors' ) )
			foreach ( $selectors as $selector_args )
				Googlefont::register_font_selector( $selector_args );
		
		add_action( 'wp_enqueue_scripts' , array(&$this,'enqueue_font_css') , 1 );
		add_action( 'admin_print_styles-appearance_page_custom-header', array(&$this,'googlefont_dequeue_gfont') , 99 ); // fct: dequeue twentythirteen-fonts, enqueue own fonts
		add_action( 'wp_enqueue_scripts',  array(&$this,'googlefont_dequeue_gfont') , 99 );
		
		do_action( 'googlefont_init' , $this );
	}
	
	// -------------------------------------------------------
	// get registered selectors
	// -------------------------------------------------------
	public function get_selectors() {
		return $this->_selectors;
	}
	
	// -------------------------------------------------------
	// Add another selector
	// -------------------------------------------------------
	public function register_font_selector( $args ){
		// $name , $label, $css_selector = 'html,body'  , $description = '' , $options = array()

		$selector = (object) wp_parse_args( $args , array(
			'name' => '', // whoo.... find some good default, aye?!?
			'label' => __( 'New Font Picker' , 'googlefont' ),
			'css_selector' => 'html,body',
			'description' => __( 'Affects body text.' , 'googlefont' ),
			'filter' => false,
			'show_styles' => true,
			'auto_embed_styles' => array( ),
			'active' => true,
		));
		$this->_selectors[$selector->name] = $selector;
		
		add_filter( "googlefont_list" , array( 'Googlefont_Filter' , 'by_subset') );
		if ( isset($selector->filter) &&  $selector->filter && is_callable($selector->filter->callback) ) 
			add_filter( "googlefont_{$selector->name}_list" , $selector->filter->callback );
	}
	
	public function googlefont_dequeue_gfont() {
		global $wp_styles;

		if ( is_a( $wp_styles, 'WP_Styles' ) ) {
	
			foreach( $wp_styles->registered as $key => $dep ) {
				if ( $key !== 'googlefont' && preg_match( '/\/\/fonts\.googleapis\.com\/css/' , $dep->src ) ) {
					wp_dequeue_style( $key );
				}
			}
		}
	}
	
	// -------------------------------------------------------
	// get style names from URL parameters
	// -------------------------------------------------------
	public function get_gfont_styles_name( $url_param ) {
		require_once 'class-googlefont-api.php';
		$font_list = Googlefont_Api::instance( );
		$fonts = explode('|',$url_param);
		$ret = array();
		foreach ( $fonts as $font ) {
			@list($font_name , $styles) = explode(':',$font);
			$font_obj = $font_list->get_font($font_name);
			$ret[ str_replace('+',' ',$font_name) ] = !$styles && $font_obj ? $font_obj->variants :  explode(',',$styles);
		}
		return $ret;
	}
	// -------------------------------------------------------
	// get URL parameters for google font load
	// -------------------------------------------------------
	public function get_gfont_url_param() {
		require_once 'class-googlefont-api.php';
		
		$args = func_get_args();
		if ( is_array($args[0]) )
			$args = $args[0];
		$ret = array();
		$ret2 = array();
		foreach ($args as $style) {
			@list($family,$sty) = explode(':',$style);
			$fam = str_replace(' ','+',$family);
			if (!$fam)
				continue;
			if ( $family && ! isset( $ret[ $fam ] ) ) {
				$font_obj = Googlefont_Api::instance( )->get_font($family);
				if ( $font_obj )
					$ret[$fam] = $font_obj->variants; // sometimes fonts are only available in italic. We need to explicitly add a stylename here.
				else
					$ret[$fam] = '';
			}
			if ( $sty )
				$ret[$fam][] = $sty;
		}
		foreach($ret as $fam=>$stys) {
			if ( $stys )
				$fam .= ':'.implode(',',$stys);
			$ret2[] = $fam;
		}
		return implode('|',$ret2);
	}
	
	
	// -------------------------------------------------------
	//	load googlefont stylesheet. Print custom stylesheets
	// -------------------------------------------------------
	public function enqueue_font_css() {
		// need some way to a) cache it, b) make sure css and font list match
		$css = $this->get_css();
		$google_font_url = $this->get_googlefont_url( );

		if ( $this->is_valid_font_url( $google_font_url ) && ! empty( $css ) ) {
			wp_enqueue_style( 'googlefont', $google_font_url );
			add_action('wp_head',create_function('','echo "<style type=\"text/css\">'. $css .'</style>";'));
		}
	}
	
	
	

	// -------------------------------------------------------
	//	get css for all selectors
	// -------------------------------------------------------
	public function get_css( ) {
		// alt: serif, sans-serif, cursive, fantasy, monospace
		$ret = '';
		foreach (array_keys($this->_selectors) as $name )
			$ret .= $this->get_selector_css( $name );
		if ( $ret )
			$ret = '/* generate by plugin wp googlefont picker */'.$ret;
		return $ret;
	}
	// -------------------------------------------------------
	//	get googlefont url to load css from
	// -------------------------------------------------------
	public function get_googlefont_url( ) {
		$mods	= array();
		foreach ( array_keys($this->_selectors) as $mod_name )
			if ($mod = get_theme_mod( $mod_name ))
				$mods[] = $mod;
	
		$upar = $this->get_gfont_url_param( $mods );
		if ( ! empty( $upar ) )
			$google_font_url = '//fonts.googleapis.com/css?family='.$upar ;
		else
			$google_font_url = false;
		return $google_font_url;
	}



	// -------------------------------------------------------
	//	get css for a specific selectors
	// -------------------------------------------------------
	private function get_selector_css( $selector_name ) {
		$selector = $this->_selectors[$selector_name];
		$ret = '';
		if ( $selector->active ) {
		
			$theme_mod = get_theme_mod( $selector_name );
			if ( ! $theme_mod )
				return '';
			$font_style = $this->get_gfont_styles_name( $theme_mod );
			foreach ($font_style as $font_name => $styles ) {
			
				$ret .= "\n" . $selector->css_selector.'{ font-family: \''.$font_name.'\';';
				if ( count($styles) == 1 && $styles[0] != 'regular' ) {
					if ( intval( $styles[0] ) )
						$ret .= 'font-weight: '.intval($styles[0]).';';
					if ( strpos($styles[0],'italic') !== false )
						$ret .= 'font-style: italic;';
				}
				$ret .= "}";
			}
		}
		return $ret;
	}
	private function is_valid_font_url( $font_url ) {
		return (bool) preg_match( '/\/\/fonts\.googleapis\.com\/css\?family=(\w+)/' , $font_url );
	}
}
Googlefont::instance();

endif;