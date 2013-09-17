jQuery(document).ready(function($){
	$('.googlefont-selector-item .handlediv').click( function() {
		var $box = $($(this).parent().get(0)).toggleClass('closed'); 
		var $input = $box.find("input.selector-label").attr('type', $box.hasClass('closed') ? 'hidden' : 'text');
		if ( $box.hasClass('closed') )
			$box.find("span.selector-label").text($input.val());
	});
	$('.googlefont-selectors').sortable();
});
