(function($) {
	$(document).ready(function(){
		if($('.bijan-attachment-wrap').length) {
			$('.bijan-attachment-wrap').on('click', function() {
				let wrap = $(this),
					selectedFileInput = wrap.find('.bijan-attachment-input');
				var fileFrame = wp.media({
					frame: 'select',
					editing : false,
					multiple : false,
					library: {
						type: wrap.attr('data-type')
					},
					selection : ""
				});

				fileFrame.on('open', function() {
					var selection = fileFrame.state().get('selection'),
						ids = [selectedFileInput.val()];
					ids.forEach( function( id ) {
						let attachment = wp.media.attachment( id );
						selection.add( attachment ? [ attachment ] : []);
					});
				});

				fileFrame.on('select', function() {
					var selection = fileFrame.state().get('selection').first();
					
					// Show selected attachment
					let iconWrap = wrap.find('.bijan-attachment-icon');
					if(selection.attributes.type == 'image') {
						iconWrap.html(`<img src="${selection.attributes.url}" alt="">`);
					}
					wrap.find('.bijan-attachment-name').text(selection.attributes.filename);
					wrap.find('.bijan-attachment-size').text(selection.attributes.filesizeHumanReadable).show();

					selectedFileInput.val(selection.attributes.id).trigger('change');
				});

				fileFrame.open();
			});
		}

		if($('.bijan-color-picker').length) {
			$('.bijan-color-picker').each(function() {
				new ColorPicker(this, {
					submitMode: 'instant'
				});
			})
		}
	});
})(jQuery);