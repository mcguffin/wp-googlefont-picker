jQuery(document).ready(function($){
	$(document).on('click','.googlefont-selector-item .handlediv', function() {
		var $box = $($(this).parent().get(0)).toggleClass('closed'); 
		var $input = $box.find("input.selector-label").attr('type', $box.hasClass('closed') ? 'hidden' : 'text');
		if ( $box.hasClass('closed') )
			$box.find("span.selector-label").text($input.val());
	});
	$('.googlefont-selectors').sortable();
	
	$(document).on('click','#googlefont-add-selector',function(){
		var i = $('.googlefont-selector-item').length;
		var $new_selector = $($('#googlefont-dummy-container').html().replace(/__DUMMY__/g,i+1)).appendTo('#googlefont-selectors');
		$new_selector.find('.handlediv').trigger('click');
		return false;
	});
	$(document).on('click','.googlefont-remove-selector',function(){
		$(this).closest( '.googlefont-selector-item' ).remove();
		return false;
	});
	
	$(document).on('click','#googlefont-refresh-now',function(){
		var $button = $(this).addClass('working').attr('disabled','disabled');
		// send ajax request
		var data = { 
			'action'			: 'googlefont_refresh_fontlist' ,
			'_wp_ajax_nonce'	: $('input[name="_wp_ajax_nonce"]').val(),
			'_wp_http_referer'	: $('input[name="_wp_http_referer"]').val()
		}; // ... 
		$.post(
			ajaxurl,data,
			function( data, textStatus, jqXHR ) {
				$button.removeClass('working').removeAttr('disabled');
				if ( data.success )
					$('<span style="padding:0 0.5em;color:#006600;">'+data.message+'</span>').insertAfter($button).fadeOut(2000);
			}
		);
		
		return false;
	});
	
	
});
