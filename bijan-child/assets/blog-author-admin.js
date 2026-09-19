(function ($) {
	'use strict';
	var frame;
	$('#cloz-author-image-select').on('click', function (event) {
		event.preventDefault();
		if (!frame) {
			frame = wp.media({ title: 'انتخاب تصویر نویسنده', button: { text: 'استفاده از تصویر' }, multiple: false, library: { type: 'image' } });
			frame.on('select', function () {
				var image = frame.state().get('selection').first().toJSON();
				var url = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
				$('#cloz-author-image-id').val(image.id);
				$('#cloz-author-preview').empty().append($('<img>', { src: url, alt: '', width: 96, height: 96 }));
			});
		}
		frame.open();
	});
	$('#cloz-author-image-remove').on('click', function (event) {
		event.preventDefault();
		$('#cloz-author-image-id').val('');
		$('#cloz-author-preview').empty();
	});
})(jQuery);
