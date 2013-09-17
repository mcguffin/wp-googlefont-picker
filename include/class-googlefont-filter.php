<?php

class Googlefont_Filter {
	static function apply_filters( ) {
	}
	
	public static function by_subset( $font_list ) {
		if ( $subset = get_option( 'googlefont_subset' ) ) {
			$new_font_list = array();
			foreach ( $font_list as $font )
				if ( self::font_has_subset( $font , $subset ) )
					$new_font_list[] = $font;
		} else {
			$new_font_list = $font_list;
		}
		return $new_font_list;
	}
	
	// regular, bold
	static function by_b( $font_list ) {
		$new_list = self::by_styles( $font_list , array( 'regular' , '700' ) );
		return $new_list;
	}
	// regular, bold, italic, bolditalic
	static function by_bi( $font_list ) {
		return self::by_styles( $font_list , array( 'regular' , '700' , 'italic' ) );
	}
	static function by_bibi( $font_list ) {
		return self::by_styles( $font_list , array( 'regular' , '700' , 'italic' , '700italic') );
	}
	public static function by_styles( $font_list , $styles ) {
		$new_font_list = array();
		foreach ($font_list as $font)
			if ( self::font_has_styles( $font , $styles ) )
				$new_font_list[] = $font;
		return $new_font_list;
	}
	
	
	
	static function font_has_subset( $font , $subset) {
		return in_array( $subset , $font->subsets );
	}
	static function font_has_styles( $font , $required_styles ) {
		return array_intersect( $required_styles , $font->variants ) == $required_styles;
	}
	static function font_has_no_styles( $font_list ) {
		return count($font->variants) == 1; // array_intersect( $required_styles ,  ) == $required_styles;
	}
	
	
}
