<?php

class Googlefont_Filter {
	
	/**
	 *	Return only fonts having subset specified in option 'googlefont_subset'
	 *
	 *	@param $font_list array Font list retrieved from google api
	 *	@param $subset string 'latin', 'latin-ext', 'cyrillic', ...
	 *	@return array filtered fonts
	 */
	public static function by_subset( $font_list , $subset = false ) {
		if ( $subset || ($subset = get_option( 'googlefont_subset' )) ) {
			$new_font_list = array();
			foreach ( $font_list as $font )
				if ( self::font_has_subset( $font , $subset ) )
					$new_font_list[] = $font;
		} else {
			$new_font_list = $font_list;
		}
		return $new_font_list;
	}
	
	/**
	 *	Return only fonts having regular and bold style
	 *
	 *	@param $font_list array Font list retrieved from google api
	 *	@return array filtered fonts
	 */
	public static function by_b( $font_list ) {
		$new_list = self::by_styles( $font_list , array( 'regular' , '700' ) );
		return $new_list;
	}
	/**
	 *	Return only fonts having regular and bold and italic style
	 *
	 *	@param $font_list array Font list retrieved from google api
	 *	@return array filtered fonts
	 */
	public static function by_bi( $font_list ) {
		return self::by_styles( $font_list , array( 'regular' , '700' , 'italic' ) );
	}
	/**
	 *	Return only fonts having regular and bold and italic and BoldItalic style
	 *
	 *	@param $font_list array Font list retrieved from google api
	 *	@return array filtered fonts
	 */
	public static function by_bibi( $font_list ) {
		return self::by_styles( $font_list , array( 'regular' , '700' , 'italic' , '700italic') );
	}
	/**
	 *	Return only fonts having all $styles
	 *
	 *	@param $font_list array Font list retrieved from google api
	 *	@param $styles Array with stylenames:  'regular' , '700' , 'italic' , '700italic'
	 *	@return array filtered fonts
	 */
	public static function by_styles( $font_list , $styles ) {
		$new_font_list = array();
		foreach ($font_list as $font)
			if ( self::font_has_styles( $font , $styles ) )
				$new_font_list[] = $font;
		return $new_font_list;
	}
	
	
	
	/**
	 *	Return if font has $subset
	 *
	 *	@param $font Font Object retrieved from googlefont api
	 *	@param $subset string 'latin', 'latin-ext', 'cyrillic', ...
	 *	@return bool
	 */
	static function font_has_subset( $font , $subset) {
		return in_array( $subset , $font->subsets );
	}
	/**
	 *	Return if font has all $styles
	 *
	 *	@param $font Font Object retrieved from googlefont api
	 *	@param $required_styles Array with stylenames:  'regular' , '700' , 'italic' , '700italic'
	 *	@return bool
	 */
	static function font_has_styles( $font , $required_styles ) {
		return array_intersect( $required_styles , $font->variants ) == $required_styles;
	}
	/**
	 *	Return if font has only one style
	 *
	 *	@param $font Font Object retrieved from googlefont api
	 *	@return bool
	 */
	static function font_has_no_styles( $font ) {
		return count($font->variants) == 1; 
	}
	
	
}
