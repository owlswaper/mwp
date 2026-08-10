(function($) {
	$(document).ready(function(){
		$(document).on('click', '.bijan_icons_icon-head', function() {
			$(this).parent().siblings('.opened').removeClass('opened');
			$(this).parent().toggleClass('opened')
		})

		// Set icon type
		$('.bijan_icons_icon_type input').on('change', function() {
			let value = $(this).val(),
				wrap = $(this).closest('.bijan_icons_icon-wrap');
			if( !value ) { // From settings
				wrap.find('.bijan_icons_fields').hide();

				// Set icon and title in header
				let index = parseInt(wrap.attr('data-index'));
				wrap.find('.bijan_icons_icon-head img').attr('src', bijanProductIcons.settings[index].icon)
				wrap.find('.bijan_icons_icon-head-title').text(bijanProductIcons.settings[index].title);
			} else {
				wrap.find('.bijan_icons_icon-title-input').trigger('input');
				wrap.find( '.bijan_icons_default-icon.selected' ).click();
				if( value == 'default' ) {
					$('.bijan_icons_default-icons').show();
					$('.bijan_icons_custom-icon').hide();
				} else {
					$('.bijan_icons_default-icons').hide();
					$('.bijan_icons_custom-icon').show();
				}

				wrap.find('.bijan_icons_fields').show();
			}
		})

		$('.bijan_icons_default-icon').on('click', function() {
			$(this).closest('.bijan_icons_icon-body').find('.bijan-attachment-input').val($(this).attr('src')).trigger('change');
			$(this).siblings('.selected').removeClass('selected');
			$(this).addClass('selected');
		})

		$('.bijan_icons_icon-title-input').on('input', function() {
			$(this).closest('.bijan_icons_icon-wrap').find('.bijan_icons_icon-head-title').text($(this).val());
		})

		$('.bijan_icons_custom-icon .bijan-attachment-input').on('change', function() {
			let wrap = $(this).closest('.bijan_icons_icon-wrap'),
				headImg = wrap.find('.bijan_icons_icon-head img');
			wrap.find('.bijan_icons_default-icon.selected').removeClass('selected');
			if(isNaN($(this).val())) {
				headImg.attr('src', $(this).val())
			} else {
				headImg.attr('src', $(this).siblings('.bijan-attachment-icon').find('img').attr('src'));
			}
		})
	});
})(jQuery);