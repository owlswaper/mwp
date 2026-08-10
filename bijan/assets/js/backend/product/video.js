(function($) {
	let prefix = 'bijan_video_';
	$(document).ready(function(){
		$(`#${prefix}wrap`).on('click', function(e) {
			e.preventDefault();
			let wrap = $(this),
				selectedFileInput = wrap.find(`#${prefix}video_field`);
			var fileFrame = wp.media({
				frame: 'select',
				editing : false,
				multiple : false,
				library: {
					type: 'video'
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
				wrap.find('video').attr('src', selection.attributes.url).show();
				wrap.find(`#${prefix}remove`).show();
				wrap.find(`#${prefix}select`).hide();

				selectedFileInput.val(selection.attributes.id).trigger('change');
			});

			fileFrame.open();
		})

		$(`#${prefix}remove`).on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			let wrap = $(this).parent();
			wrap.find('input').val('');
			wrap.find(`#${prefix}remove,video`).hide();
			wrap.find(`#${prefix}select`).show();
		})
	});
})(jQuery);