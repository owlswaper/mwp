(function($) {
	$(document).ready(function(){
		// Tabs
		$('.bijan_metabox-tab').on('click', function() {
			let active = 'bijan_metabox-tab-active';
			if($(this).hasClass(active)) return;

			$(this).siblings(`.${active}`).removeClass(active)

			$('.bijan_metabox-tab-content:visible').slideUp();
			$(`.bijan_metabox-tab-content[data-tab="${$(this).attr('data-tab')}"]`).slideDown();

			$(this).addClass(active);
		});

		// Post finder
		if($('.bijan_metabox_post_finder').length) {
			$('.bijan_metabox_post_finder').select2({
				width: '25em',
				minimumInputLength: 2,
				ajax: {
					url: bijanMetabox.ajaxUrl,
					type: 'POST',
					data: function(params) {
						return {
							action: `bijan_find_post`,
							text: params.term,
							nonce: bijanMetabox.nonces.postFinder,
						}
					},
					processResults: function(data) {
						if(typeof data.data != 'undefined') {
							return {
								results: data.data,
							}
						} else {
							return {
								results: [],
							}
						}
					},
					cache: false
				},
			});
		}

		// User finder
		if($('.bijan_metabox_user_finder').length) {
			$('.bijan_metabox_user_finder').select2({
				width: '25em',
				minimumInputLength: 2,
				ajax: {
					url: bijanMetabox.ajaxUrl,
					type: 'POST',
					data: function(params) {
						return {
							action: `bijan_find_user`,
							text: params.term,
							nonce: bijanMetabox.nonces.userFinder,
						}
					},
					processResults: function(data) {
						if(typeof data.data != 'undefined') {
							return {
								results: data.data,
							}
						} else {
							return {
								results: [],
							}
						}
					},
					cache: false
				},
			});
		}

		function toggleRelativeOption(mainOption, relativeOption, reverse) {
			$(mainOption).on('change', function() {
				if(!reverse) {
					if( $(this).prop('checked') ) {
						$(relativeOption).show();
					} else {
						$(relativeOption).hide();
					}
				} else {
					if( $(this).prop('checked') ) {
						$(relativeOption).hide();
					} else {
						$(relativeOption).show();
					}
				}
			});
		}

		toggleRelativeOption('#bijan_disable_header', '#disable_header_user-table'); 
		toggleRelativeOption('#bijan_show_title', '#page-icon-row'); // Hide select sidebar if sidebar is hidden
		toggleRelativeOption('#bijan_show_sidebar', '#select-sidebar-row'); // Hide select sidebar if sidebar is hidden
		toggleRelativeOption('#bijan_disable_footer', '#disable_footer_user-table'); 
	});
})(jQuery);