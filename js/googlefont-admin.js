jQuery(document).ready(function($){
	$(document).on('click','.googlefont-selector-item .handlediv', function() {
		var $box = $($(this).parent().get(0)).toggleClass('closed'); 
		var $input = $box.find("input.selector-label").attr('type', $box.hasClass('closed') ? 'hidden' : 'text');
		if ( $box.hasClass('closed') )
			$box.find("span.selector-label").text($input.val());
	});
	$('.googlefont-selectors').sortable();
	
	$('#googlefont-add-selector').click(function(){
		var i = $('.googlefont-selector-item').length;
		$($('#googlefont-dummy-container').html().replace(/__DUMMY__/g,i+1)).appendTo('#googlefont-selectors');
		return false;
		
	});
	$(document).on('click','.googlefont-remove-selector',function(){
		$(this).closest( '.googlefont-selector-item' ).remove();
		return false;
	});
});
