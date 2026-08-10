(function($) {
	$(document).ready(function(){
		// Upload new avatar
		$('#bijan-edit-avatar').on('click', function(e) {
			e.preventDefault();
			let wrap = $(this).closest('.bijan-edit-avatar-wrap-row'),
				selectedFileInput = wrap.find('#account_avatar_id');
			var fileFrame = wp.media({
				frame: 'select',
				editing : false,
				multiple : false,
				library: {
					type: 'image'
				},
				selection : ""
			});

			fileFrame.on('open', function() {
				var selection = fileFrame.state().get('selection');
				let attachment = wp.media.attachment( selectedFileInput.val() );
				selection.add( attachment ? [ attachment ] : []);
			});

			fileFrame.on('select', function() {
				var selection = fileFrame.state().get('selection').first();

				selectedFileInput.val(selection.attributes.id);
				// Show avatar
				wrap.find('.bijan-edit-avatar-wrap img').attr('src', selection.attributes.url).removeAttr('srcset');
			});

			fileFrame.open();
		});

		$('.bijan-delete-avatar-icon').on('click', function(e) {
			e.stopPropagation();
			$('#account_avatar_id').val('');
			$(this).siblings('img').attr('src', bijanVars.defaults.avatar).removeAttr('srcset');
		});
	});
})(jQuery);