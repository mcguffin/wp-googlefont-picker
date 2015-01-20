(function($){

	$(document).on('click','.googlefont-pick-family',function(){
		// set value
		var $this = $(this);
		var $picker = $this.closest('.googlefont-fontpicker'),
			$control = $this.closest('.googlefont-control').find('.googlefont-value'), 
			$item = $this.closest('.googlefont-item'), 
			font, style;
		
		$picker.find('.googlefont-item').removeClass('selected');
		
		if ($this.is(':checked')) {
			font = $this.val();

			$item.addClass('selected');
			$control
				.val( font.replace(/ /g, '+') )
				.trigger('change');
			$picker
				.next('.googlefont-stylepicker')
				.html( $item.next('.googlefont-item-styles').html() );
			
			if ( style = $item.find('.autoembed-style').val() )
				$control
					.val( $control.val().replace(/^([^:]*)(.*)$/,'$1:'+style )  )
					.trigger('change');
		}
	}).on('click' , '.googlefont-pick-style' , function() {
		var $this = $(this);
		if ($this.is(':checked')) {
			var $control = $this.closest('.googlefont-picker')
				.find('.googlefont-value');
			$control
				.val( $control.val().replace(/^([^:]*)(.*)$/,'$1:'+$this.val() )  )
				.trigger('change');
		}
	}).on('click','.googlefont-picker-label',function() {
		var $self = $(this).next('.googlefont-picker');
		var $list = $self.find('.googlefont-fontpicker');
		var vis = !$self.is(':visible');
		$('.googlefont-picker').not( $self.slideToggle() ).slideUp();
		$selected = $self.find('.googlefont-item.selected');
		if (vis && !$list.scrollTop() && $selected.length )
			$list.scrollTop( $selected.position().top - $list.position().top );
	}).on('keyup click','.googlefont-filter' , function() {
		$this = $(this);
		if ( !! $this.val() )
			$this.closest('.googlefont-picker').find('.googlefont-fontpicker')
				.find('.googlefont-item')
				.css('display','none')
				.has('.title:icontains("'+$this.val()+'")')
				.removeAttr('style');
		else
			$this.closest('.googlefont-picker').find('.googlefont-fontpicker')
				.find('.googlefont-item')
				.removeAttr('style');
		
	} ).on('click','.filter.favorite',function() {
		$(this)
			.closest('.googlefont-picker')
			.toggleClass('nofavs');
		
	}).on('click','.googlefont-item .favorite',function(){
		var font = $(this).closest('.googlefont-item')
			.toggleClass('fav')
			.find('.googlefont-pick-family').val();
		$.post( ajaxurl, {
				'action':'googlefont_add_favorite',
				'font':font
			}, 
			function( response ) { // me no care for response
			});
		
		var $allSuchFonts = $('.googlefont-pick-family[value="'+font+'"]').closest('.googlefont-item');
		
		if ($(this).closest('.googlefont-item').hasClass('fav') )
			$allSuchFonts.addClass('fav');
		else
			$allSuchFonts.removeClass('fav');
		return false;
	});


})(jQuery);



jQuery(document).ready(function($){
});

// 
if ( jQuery.expr.createPseudo )  {
jQuery.expr[":"].icontains = jQuery.expr.createPseudo(function (arg) {                                                                                                                                                                
    return function (elem) {                                                            
        return jQuery(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;        
    };                                                                                  
});
} else {
	jQuery.expr[':'].icontains = function(a,i,m){
		return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase())>=0;
	};
}