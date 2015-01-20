<?php




class Googlefont_Api {
// 	const METADATA_URL = 'http://googlefontdirectory.googlecode.com/hg/%s/%s/METADATA.json';
	const WEBFONTS_LIST_URL = 'https://www.googleapis.com/webfonts/v1/webfonts?key=%s';

	private static $_instance;
	
	private $_data		= array();
	private $_items 	= array();
	private $_variants	= array();
	private $_subsets	= array();
	
	
	public static function &instance( $filter = false ) {
		if ( ! isset( self::$_instance ) )
			self::$_instance = new Googlefont_Api();
		
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
		$this->init();
	}
	private function init( $json_data = null ) {
		if ( is_null( $json_data ) )
			$json_data = get_option( '_googlefont_fontlist' );
		
		$this->_data = json_decode( $json_data );
		$variants = array();
		$subsets  = array();
		
		foreach ($this->_data->items as $i=>$item) {
			$this->_items[$item->family] = &$this->_data->items[$i];
			foreach ( $item->variants as $variant ) 
				$variants[$variant] = true;
			foreach ( $item->subsets as $subset ) 
				$subsets[$subset] = true;
		}
		$this->_subsets = array_keys($subsets);
		$this->_variants = array_keys($variants);
		natsort($this->_subsets);
		natsort($this->_variants);
	}
	public function refresh( $google_api_key = null ) {
		if ( ! is_null( $google_api_key ) || ($google_api_key = get_option( 'googlefont_api_key' )) ) {
			$url = sprintf( self::WEBFONTS_LIST_URL , $google_api_key );
			$response = wp_remote_get( $url );
			
			if ( is_wp_error( $response ) || $response['response']['code'] !== 200 )
				return false; // err!
			else 
				$json_data = $response['body'];
			update_option( '_googlefont_fontlist' , $json_data );
			$this->init( $json_data );
			return true;
		} else {
			return false;
			// doesn't work. No API Access. Bugger!
		}
	}
	public function get_font( $font_name ) {
		if ( isset($this->_items[$font_name]) )
			return $this->_items[$font_name];
		return false;
	}
	public function get_items() {
		return apply_filters( 'googlefont_list' , $this->_items );
	}
	public function __get( $key ) {
		switch ($key) {
			case 'items':
				return $this->_data->items;
		}
	}
	
	public function get_available_variants() {
		return $this->_variants;
	}
	public function get_available_subsets() {
		return $this->_subsets;
	}
	
}