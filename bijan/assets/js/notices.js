(function($) {
	$(document).ready(function(){
		$.ajax({
			url: bijan_notices.ajaxUrl,
			type: 'POST',
			data: {
				action: 'bijan_get_notices',
			},
			success: function(res) {
				if(res) {
					$('#bijan-notices').html(res).show();
				}
			}
		});
		$(document).on('click', '.bijan_notice .notice-dismiss', function(e) {
			e.preventDefault();
			$(this).closest('.bijan_notice').fadeOut();
			$.ajax({
				url: bijan_notices.ajaxUrl,
				type: 'POST',
				data: {
					action: 'bijan_dismiss_notice',
					id: $(this).closest( '.bijan_notice' ).attr('data-id'),
					nonce: bijan_notices.nonce
				}
			});
		})
	});
})(jQuery);