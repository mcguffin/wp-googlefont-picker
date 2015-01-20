<?php


/*
- button: back to default values
*/

if ( ! class_exists('Googlefont_Admin') ) :

class Googlefont_Admin {
	private $tabs = array();
	
	/**
	 *	Holding the singleton instance
	 */
	private static $_instance = null;

	/**
	 *	@return WP_reCaptcha_Options The options manager instance
	 */
	public function instance() {
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
		$this->tabs = array(
			'selectors' => __( 'Font Pickers' , 'googlefont' ),
			'api_access' => __( 'Google API Access' , 'googlefont' ),
		);
		add_action('admin_init', array( &$this ,'admin_init') );
		add_action('admin_menu', array( &$this ,'add_options_page') );
	}
	
	private function _current_tab(){
		$tabnames = array_keys($this->tabs);
		$current_tab = array_shift($tabnames);
		if ( isset($_REQUEST['tab']) && array_key_exists($_REQUEST['tab'],$this->tabs) )
			$current_tab = $_REQUEST['tab'];
		return $current_tab;
	}
	
	
	public function admin_init() {
		$this->add_options();
		
		add_action('load-settings_page_googlefont',array( &$this ,'enqueue_styles'));
		
		switch ( $this->_current_tab() ) {
			case 'selectors':
				register_setting( 'googlefont_options', 'googlefont_selectors', array( &$this ,'validate_selector') );
				register_setting( 'googlefont_options', 'googlefont_subset', array( &$this ,'validate_subset') );
				
				add_settings_section('googlefont_selectors', __( 'Font Pickers' , 'googlefont' ), array( &$this ,'explain_fontpickers'), 'googlefont_set_selectors');
				add_settings_field('googlefont_subset', __('Subset','googlefont'), array( &$this ,'select_subset'), 'googlefont_set_selectors', 'googlefont_selectors');
				add_settings_field('googlefont_selectors', __('Selectors','googlefont'), array( &$this ,'configure_selectors'), 'googlefont_set_selectors', 'googlefont_selectors');
				break;
			case 'api_access':
				register_setting( 'googlefont_options', 'googlefont_api_key', array( &$this ,'validate_api_key') );
				register_setting( 'googlefont_options', 'googlefont_refresh_period', array( &$this ,'validate_refresh_period') );
		
				add_settings_section('googlefont_api_access', __( 'Google API Access' , 'googlefont' ), array( &$this ,'explain_api_access'), 'googlefont_set_api_access');
				add_settings_field('googlefont_api_key', __('Google API Key','googlefont'), array( &$this ,'input_api_key'), 'googlefont_set_api_access', 'googlefont_api_access');
				add_settings_field('googlefont_refresh_period', __('Refresh period','googlefont'), array( &$this ,'select_refresh_period'), 'googlefont_set_api_access', 'googlefont_api_access');
				break;
		}
		
		// add ajax refresh
		add_action( 'wp_ajax_googlefont_refresh_fontlist', array(  &$this  , 'ajax_googlefont_refresh' ) );
		// add cron
		add_action('update_option_googlefont_refresh_period' , array( &$this ,'set_refresh_cron'),10,2);
		add_action('update_option_googlefont_selectors' , array( &$this ,'unset_cached_css'),10,2);
	}

	public function ajax_googlefont_refresh() {
		if ( wp_verify_nonce(@$_POST['_wp_ajax_nonce'] , 'googlefont_refresh' ) && current_user_can( 'manage_options' ) ) {
			// refresh font list
			$fonts_before = count(json_decode(get_option( '_googlefont_fontlist' ))->items);
			header( 'Content-Type: application/json' );
			$api = Googlefont_Api::instance();
			$fonts_after = count(json_decode(get_option( '_googlefont_fontlist' ))->items);
			echo json_encode( (object) array(
				'success' 	=> $api->refresh( get_option( 'googlefont_api_key' ) ),
				'before'	=> $fonts_before,
				'after'		=> $fonts_after,
				'added'		=> $fonts_after-$fonts_before,
				'message'	=> sprintf(_n( 'Got one new font. %2$d Fonts overall.' , 'Got %1$d new fonts. %2$d Fonts overall.' , $fonts_after-$fonts_before , 'googlefont' ),$fonts_after-$fonts_before , $fonts_after ),
			) );
		}
		die();
	}
	
	public function enqueue_styles() {
		wp_enqueue_script( 'googlefont-admin', plugins_url( '/js/googlefont-admin.js' , dirname(__FILE__) ) , array('jquery','jquery-ui-sortable') ) ;
		wp_enqueue_style( 'googlefont-admin', plugins_url( '/css/googlefont-admin.css' , dirname(__FILE__) )  );
	}
	
	public function add_options_page() {
		add_options_page( 
			__('Googlefont Options','googlefont'), __('Googlefonts','googlefont'), 
			'manage_options', 'googlefont', 
			array( &$this ,'render_options_page')
		);
	}
	
	private function _nav_tabs() {
		$current_tab = 'selectors';
		?><h2 class="nav-tab-wrapper"><?php /*icon*/ 
			foreach ( $this->tabs as $tab => $label ) {
				$href = add_query_arg('tab',$tab);
				?><a href="<?php echo $href ?>" class="nav-tab<?php echo $tab==$this->_current_tab() ? ' nav-tab-active' : ''; ?>"><?php echo $label ?></a><?php
			}
		?></h2><?php
	}
	
	public function render_options_page() {
		?><div class="wrap"><?php
			$this->_nav_tabs();
		/*	?><p><?php _e( '...' , 'googlefont' ); ?></p><?php */
			?><form action="options.php" method="post"><?php
				?><input type="hidden" name="tab" value="<?php echo $this->_current_tab() ?>" /><?php
				settings_fields( 'googlefont_options' );
				do_settings_sections( 'googlefont_set_'.$this->_current_tab() ); 
				?><input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /><?php
			?></form><?php
		?></div><?php
	}
	
	
	// Section rendering
	public function explain_fontpickers( ) {
		?><p class="description"><?php 
			_e('In this section You can configure, which Font controls a User will be avaliable during theme customization.','googlefont');
			?><br /><?php
			_e('The CSS Selectors very much depend on Your Theme, so the default values won’t always work. You better come up with some css knowledge, or at least find somebody who can help you out.','googlefont'); 
		?></p><?php
	}
	
	// Section rendering
	public function explain_api_access( ) {
		?><p class="description"><?php 
			_e('To stay tuned to the latest avaliable Google Web Fonts you need an API Key. <a href="https://code.google.com/apis/console/">Click here to get one.</a>','googlefont');
			?><br /><?php
			_e('In the <a href="https://code.google.com/apis/console/">APIs console</a> click on “Services” and enable Web Fonts Developer API.','googlefont'); 
		?></p><?php
		?><p class="description"><?php 
			_e( '<a href="http://code.garyjones.co.uk/google-developer-api-key">Detailed help required?</a>' , 'googlefont' );
		?></p><?php
	}
	
	
	// The CSS Selectors very much depend on Your Theme, so the default values won't work every time. You better come up with a little css knowledge, or at least find somebody who can help you out.
	
	
	public function input_api_key() {
		$api_key = get_option('googlefont_api_key');
		?><input type="text" name="googlefont_api_key" value="<?php echo $api_key ?>" /><?php
	}
	public function select_refresh_period() {
		
		$options = array(
			'manual' => __('Manual', 'googlefont'),
			'weekly'  => __('Weekly', 'googlefont'),
			'monthly' => __('Monthly', 'googlefont'),
			'yearly'  => __( 'Once a Year', 'googlefont' ),
		);
		$refresh_period = get_option('googlefont_refresh_period');
		?><select name="googlefont_refresh_period"><?php
			foreach ( $options as $value => $label ) {
				?><option <?php selected( $value , $refresh_period , true) ?> value="<?php echo $value ?>"><?php echo $label ?></option><?php
			}
		?></select><?php
		if ( $api_key = get_option('googlefont_api_key') ) {
			wp_nonce_field( 'googlefont_refresh' , '_wp_ajax_nonce' );
			?><input name="googlefont[refresh]" id="googlefont-refresh-now" class="hide-if-no-js button button-secondary" type="submit" value="<?php esc_attr_e('Refresh now','googlefont'); ?>" /><?php
		}
	}
	public function select_subset() {
		$subsets = Googlefont_API::instance()->get_available_subsets();
		$subset = get_option('googlefont_subset');
		
		?><select name="googlefont_subset"><?php
			foreach ( $subsets as $value ) {
				$label = ucwords(str_replace('-',' ',$value));
				?><option <?php selected( $subset , $value , true) ?> value="<?php echo $value ?>"><?php echo $label ?></option><?php
			}
		?></select><?php
	}
	public function configure_selectors() {
		$selectors = get_option('googlefont_selectors');
		
		// add dummy picker to clone
		
		?><div id="googlefont-selectors" class="googlefont-selectors metabox-holder meta-box-sortables"><?php
		foreach ($selectors as $i => $selector )
			$this->print_selector( $selector , $i );
		?></div><?php
		?><div id="googlefont-dummy-container" class="googlefont-dummy-container"><?php
			$this->print_selector();
		?></div><?php
		?><p class="submit"><a href="#" id="googlefont-add-selector" class="button"><?php _e('Add Font Picker','googlefont') ?></a></p><?php
		?><script type="text/javascript">
			
		</script><?php
	}
	
	private function print_selector( $selector = array() , $i = '__DUMMY__' ) {
		$selector = wp_parse_args($selector , array(
			'name' => 'font-picker-'.$i,
			'label' => __('New Font Picker'),
			'css_selector' => 'cite,blockquote',
			'description' => '',
			'filter' => false,
			'active' => true,
			'show_styles' => true,
		));
		extract($selector);
		$cb =  $filter ? $filter->callback[1] : false;

		?><div id="googlefont-selector-<?php echo $i ?>" class="postbox googlefont-selector-item ui-sortable closed"><?php
			?><div class="handlediv" title="<?php esc_attr_e('Click to toggle') ?>"><br /></div><?php
		
			?><h3 class="hndle"><?php 
				?><span class="label selector-label"><?php echo $label ?></span><?php 
				?><input placeholder="<?php _ex('Title','selector','googlefont') ?>" class="selector-label" type="hidden" name="googlefont_selectors[<?php echo $i ?>][label]" value="<?php echo $label ?>"  /><?php
				?> <small class="label">(<?php _e('Applies to:' , 'googlefont') ?> <code class="label"><?php echo $css_selector ?></code>)</small><?php 
			?></h3><?php
			?><div class="inside"><?php
				
				?><table class="form-table"><?php
					// active
					?><tr><?php
						?><th><?php
							?><label for="selector-active-<?php echo $i ?>"><?php
								_ex('Active','selector','googlefont');
							?></label><?php
						?></th><?php
						?><td><?php
							?><input type="hidden" name="googlefont_selectors[<?php echo $i ?>][active]" value="0" /><?php
							?><input id="selector-active-<?php echo $i ?>" type="checkbox" name="googlefont_selectors[<?php echo $i ?>][active]" <?php checked($active) ?> value="1" /><?php
						?></td><?php
					?></tr><?php
					
					// name
					?><tr><?php
						?><th><?php
							?><label for="selector-name-<?php echo $i ?>"><?php
								_ex('Name','selector','googlefont') ;
							?></label><?php
						?></th><?php
						?><td><?php
							?><input for="selector-name-<?php echo $i ?>" type="text" name="googlefont_selectors[<?php echo $i ?>][name]" value="<?php echo $name ?>" /><?php
						?></td><?php
					?></tr><?php
					
					// Description
					?><tr><?php
						?><th><?php
							?><label for="selector-description-<?php echo $i ?>"><?php
								_ex('Description','selector','googlefont') ;
							?></label><?php
						?></th><?php
						?><td><?php
							?><textarea id="selector-description-<?php echo $i ?>" type="text" class="large-text" name="googlefont_selectors[<?php echo $i ?>][description]" ><?php echo $description ?></textarea><?php
						?></td><?php
					?></tr><?php
					
					// CSS
					?><tr><?php
						?><th><?php
							?><label for="selector-css-selector-<?php echo $i ?>"><?php
								_e('CSS-Selector','googlefont') 
							?></label><?php
						?></th><?php
						?><td><?php
							?><input id="selector-css-selector-<?php echo $i ?>" type="text" class="large-text code" name="googlefont_selectors[<?php echo $i ?>][css_selector]" value="<?php echo $css_selector ?>" /><?php
						?></td><?php
					?></tr><?php
					
					// active
					?><tr><?php
						?><th><?php
							?><label for="selector-show-styles-<?php echo $i ?>"><?php
								_ex('Show Styles','selector','googlefont');
							?></label><?php
						?></th><?php
						?><td><?php
							?><input type="hidden" name="googlefont_selectors[<?php echo $i ?>][show_styles]" value="0" /><?php
							?><input id="selector-show-styles-<?php echo $i ?>" type="checkbox" name="googlefont_selectors[<?php echo $i ?>][show_styles]" <?php checked($show_styles) ?> value="1" /><?php
							?><label for="selector-show-styles-<?php echo $i ?>"><?php
								_ex('Will show a style selection','selector','googlefont');
							?></label><?php
						?></td><?php
					?></tr><?php
					
					// Filter
					?><tr><?php
						?><th><?php
							?><label><?php
								_e('Filter Font list','googlefont') 
							?></label><?php
						?></th><?php
						?><td><?php
							?><ul class="googlefont-filter-variants"><?php
								?><li><label><input type="radio" name="googlefont_selectors[<?php echo $i ?>][filter_variants]" <?php checked( $cb, false ,true ); ?> value="none" /><?php _e('Show all fonts','googlefont') ?></label></li><?php
								?><li><label><input type="radio" name="googlefont_selectors[<?php echo $i ?>][filter_variants]" <?php checked( $cb,'by_b',true ); ?> value="by_b" /><?php _e('Fonts with Regular &amp; <b>Bold</b> styles','googlefont') ?></label></li><?php
								?><li><label><input type="radio" name="googlefont_selectors[<?php echo $i ?>][filter_variants]" <?php checked( $cb,'by_bi',true ); ?> value="by_bi" /><?php _e('Fonts with Regular, <b>Bold</b> &amp; <i>Italic</i> styles','googlefont') ?></label></li><?php
								?><li><label><input type="radio" name="googlefont_selectors[<?php echo $i ?>][filter_variants]" <?php checked( $cb,'by_bibi',true ); ?> value="by_bibi" /><?php _e('Fonts with Regular, <b>Bold</b>, <i>Italic</i> &amp; <b><i>BoldItalic</i></b> styles','googlefont') ?></label></li><?php
							?></ul><?php
						?></td><?php
					?></tr><?php
					
					
					
				?></table><?php
				
				?><p><label><?php 
					
					?></label></p><?php
				
				?><div class="googlefont-selector-options"><?php
				?></div><?php
				?><p class="submit"><a href="#" class="googlefont-remove-selector button"><?php _e('Remove Item','googlefont') ?></a></p><?php
			?></div><?php
		?></div><?php
	}
	
	
	
	public function add_options() {
		$default_selectors = array(
			array(
				'name'=>'base_font',
				'label'=>__('Base Font','googlefont'),
				'css_selector'=>'html,body,article,button,input,select,textarea', 
				'description'=>__('Used in plain Text','googlefont'),
				'filter' => (object) array(
					'callback' => array('Googlefont_Filter','by_bibi'),
				),
				'show_styles' => false,
				'auto_embed_styles' => array( 'regular' , 'italic' , '700' , '700italic'),
				'active' => true,
			),
			array(
				'name'=>'accent_font' , // accent font pattern
				'label'=>__('Accent font','googlefont'),
				'css_selector'=>'h1,h2,h3,h4,h5,h6,.entry-title,#site-title,.widget .widget-title,.comment-reply-title,.comments-title,#main-nav select' , // travelify selector
				'description'=>__('Font for Headlines, page-header.','googlefont'),
				'show_styles' => true,
				'active' => true,
			),
		);
		add_option( 'googlefont_api_key' , '' , '' , false );
		add_option( 'googlefont_selectors' , $default_selectors , '' , false );
		add_option( 'googlefont_subset' , 'latin' , '' , false );
		add_option( 'googlefont_refresh_period' , 'weekly' , '' , false );
		if ( ($file = plugin_dir_path( dirname(__FILE__) ).'data/google_webfonts.json') && file_exists( $file ) )
			add_option( '_googlefont_fontlist' , file_get_contents( $file ) , '' , false );
		
		// add option
	}
	public function remove_options() {
		delete_option( 'googlefont_api_key' );
		delete_option( 'googlefont_selectors' );
		delete_option( 'googlefont_subset' );
		delete_option( 'googlefont_refresh_period' );
		delete_option( '_googlefont_fontlist' );
		// frontend opts!
	}
	
	public function validate_api_key( $input ) {
		if ( ! empty($input) ) {
			if (preg_match( '/[^a-zA-Z0-9-_]/' , $input)) {
				// put some error message: Malformed API-Key
				add_settings_error( 'googlefont_api_key', 1, __( 'Invalid API-Key.','googlefont' ), 'error' );
				return get_option('googlefont_api_key');
			}
			if ( ! Googlefont_Api::instance()->refresh( $input ) ) {
				add_settings_error( 'googlefont_api_key', 2, __( 'API-Key was not accepted by Google.','googlefont' ), 'error' );
				return get_option('googlefont_api_key');
			}
		}
		return $input;
	}
	public function validate_selector( $input ) {
		$okay = true;
		$style_filters = array(
			'by_b'		=> array( 'regular' , 'italic' ),
			'by_bi'		=> array( 'regular' , 'italic' , '700'),
			'by_bibi'	=> array( 'regular' , 'italic' , '700' , '700italic'),
		);
		$defaults = array(
			'name' => '',
			'label' => '',
			'css_selector' => '',
			'description' => '',
			'filter' => false,
			'auto_embed_styles' => false,
			'show_styles' => false,
			'active' => false,
		);
		$return = array();
		
		foreach ( $input as $key => $selector) {
			if ( ! is_numeric($key) ) {
				unset($input[$key]);
				continue;
			}
			$okay &= 	is_array($selector) &&
						isset($selector['name']) &&
						isset($selector['label']) &&
						isset($selector['css_selector']);
			if ( ! $okay )
				return false;

			if ( ! $selector['label'] )
				$selectors[$i]['label'] = __( "Font-Picker #{$i}" , 'googlefont' );

			if ( ! $selector['name'] )
				$selector['name'] = sanitize_title( $selector['label'] );
			
			$selector['active'] = (bool) $selector['active'];

			$selector = wp_parse_args( $selector , $defaults );

			if ( isset( $selector['filter_variants'] ) && isset( $style_filters[$selector['filter_variants']] ) ) {
				$selector['filter'] 			= (object) array('callback' => array('Googlefont_Filter' , $selector['filter_variants'] ) );
				$selector['auto_embed_styles']	= $style_filters[ $selector['filter_variants'] ];
				$selector['show_styles'] 		= false;
//				unset( $selector['filter_variants'] );
			} else {
				$selector['filter'] = false;
			}
			
			foreach ( array_keys($selector) as $k )
				if ( ! array_key_exists( $k , $defaults) )
					unset($selector[$k]);
			
			$return[] = $selector;
		}
		return $return;
	}
	
	public function validate_subset( $input ) {
		$subsets = Googlefont_API::instance()->get_available_subsets();
		if ( ! in_array( $input , $subsets ) ) {
			add_settings_error( 'googlefont_subset', 3, __( 'Invalid subset.','googlefont' ), 'error' );
			$subset = get_option('googlefont_subset');
		} else {
			$subset = $input;
		}
		return $subset;
	}
	public function validate_refresh_period( $input ) {
		if ( ! in_array( $input , array( 'manual' ,'monthly','weekly','yearly' ) ) ) {
			add_settings_error( 'googlefont_api_key', 3, __( 'Invalid refresh period. How did you manage that?','googlefont' ), 'error' );
			return get_option('googlefont_refresh_period');
		}
		
		// set cron
		
		return $input;
	}
	
	public function set_refresh_cron( $old_value , $new_value ) {
		// set new cron, clear the old one.
		$old_cron_task_hook = "calendar_cron_{$old_value}";
		$new_cron_task_hook = "calendar_cron_{$new_value}";
		
		if ( wp_next_scheduled( $old_cron_task_hook ) )
			wp_clear_scheduled_hook( $old_cron_task_hook );

		$res = wp_schedule_event( time(), $new_value , $new_cron_task_hook );
	}
	public function unset_cached_css( $old_value , $new_value ) {
		delete_option( sprintf( 'googlefont_%s_css' , get_option('stylesheet') ) );
		delete_option( sprintf( 'googlefont_%s_fonturl' , get_option('stylesheet') ) );
	}
	
}
Googlefont_Admin::instance();

endif;

?>