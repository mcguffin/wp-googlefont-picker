<?php


if (class_exists('WP_Customize_Control')) {
	class Customize_Fontpicker_Control extends WP_Customize_Control {
		public $options = null;
		public $description = '';
		
		public function render_content() {
			
			$val = $this->value();
			
			if ( $val )
				@list( $family , $style ) = explode(':',urldecode($val));
			else 
				$family = $style = '';
			//$favs = get_option('googlefont_favorites' , array() );
			$favs = get_user_option( 'googlefont_favorites' );
			if ( ! $favs )
				$favs = array();
			$font_items = apply_filters( "googlefont_{$this->options->name}_list" , Googlefont_Api::get_instance()->get_items() , $this->options->name );
			
			
			if ( ! empty($font_items) ) {
				$odd = true;
				$font = false;

				// set selected
				?><div class="googlefont-control">
					<label class="googlefont-picker-label">
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
						if ( $this->description ) {
							?><span class="description"><?php echo esc_html( $this->description ); ?></span><?php
						}
					?></label><?php
				
					?><div class="googlefont-picker nofavs">
						<div class="googlefont-filters">
							<input type="hidden" class="googlefont-value" <?php $this->link(); ?> value="<?php echo esc_textarea( $this->value() ); ?>" />
							<span class="filter favorite" title="<?php _e( 'Restrict view to favorites' , 'twentythirteen' ) ?>"></span>
							<input type="search" placeholder="<?php _e('Filter…') ?>" class="googlefont-filter" />
						</div>
					
						<div class="googlefont-fontpicker"><?php
						
							$item_classes = array('fav','even');
							?><label class="googlefont-item <?php echo implode(' ',$item_classes) ?>"><?php
								?><span class="googlefont-item-head"><?php
									?><input class="googlefont-pick-family" <?php checked( '' , $family , true); ?> type="radio" name="googlefont-family" value="" /><?php
									?><strong><?php _e('– none –'); ?></strong><?php
								?></span><?php
							?></label><?php
						
							foreach ($font_items as $i=>$font) {
								$item_classes = array();
								$item_classes[] = $odd ? 'odd' : 'even';
								
								if ( $font ) {
									if ( $font->family==$family )
										$item_classes[] = 'selected';
									if ( in_array( $font->family , $favs ) )
										$item_classes[] = 'fav';
								}
								
								$id = sanitize_title( $font->family );
						
								?><label class="googlefont-item <?php echo implode(' ',$item_classes) ?>"><?php
									?><span class="favorite" title="<?php _e( 'Add to favorites' , 'twentythirteen' ) ?>"><?php  ?></span><?php
									?><span class="googlefont-item-head"><?php
										?><input class="googlefont-pick-family" <?php checked( $font->family , $family , true); ?> type="radio" name="googlefont-family" value="<?php echo $font->family ?>" /><?php
										?><a class="sample" href="http://www.google.com/webfonts/specimen/<?php echo $font->family ?>" target="_blank"><?php _e('Sample','twentythirteen') ?></a><?php
										?><strong class="title"><?php echo $font->family; ?></strong><br /><?php
										$count_styles = count( $font->variants );
										?><span class="info"><?php printf( _n('(%s style)','(%s styles)',$count_styles,'twentythirteen') , $count_styles ); ?></span><br /><?php
								
									?></span><?php
							
									if ( $this->options->show_styles ) {
							
										?><span class="googlefont-item-styles"><?php
											$this->stylepicker( $font->variants , $id , $style );
										?></span><?php
							
									} else {
										if ( count( $this->options->auto_embed_styles ) ) {
											$styles = array_intersect( $this->options->auto_embed_styles , $font->variants );
											?><input class="autoembed-style" type="hidden" name="<?php echo $id ?>-variant" value="<?php echo implode( ',' , $styles ) ?>" /><?php
										}
									}
							
								?></label><?php
						
								$odd = !$odd;
							}
					
							?>
						</div>
					</div>
				</div>
				<?php
			}
		}
		private function stylepicker($styles , $id , $selected = null ) {
			if ( count($styles) > 1 ) {
				?><div class="googlefont-stylepicker-content"><?php 
				?><strong><?php _e('Styles') ?></strong><br /><?php
				foreach ($styles as $variant) {
					?><label class="googlefont-style-select"><input class="googlefont-pick-style" <?php checked($selected,$variant,true); ?> type="radio" name="<?php echo $id?>-variant" value="<?php echo $variant ?>" /><?php echo $variant ?></label><?php
				}
				?></div><?php
			} else {
				?><input type="hidden" name="<?php echo $id ?>-variant" value="<?php echo $styles[0] ?>" /><?php
			}
		}
	}
}




?>