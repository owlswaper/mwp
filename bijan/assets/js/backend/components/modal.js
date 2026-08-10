function bijanModalOpen(id) {
	if(typeof id == 'string' && id.substring(0,1) != '#') {
		id = `#${id}`;
	}
	jQuery(id).fadeIn();
	jQuery(id).next().fadeIn();
	jQuery('body').addClass('disable-scrolling');
	jQuery('#header-toggle-mobile-menu').hide();
	jQuery('.sidebar-mobile-expand-btn, .my-account-menu-expand-btn').addClass('hidden')
}
function bijanModalClose(id) {
	if(typeof id == 'string' && id.substring(0,1) != '#') {
		id = `#${id}`;
	}
	jQuery(id).fadeOut();
	jQuery(id).next().fadeOut();
	jQuery('body').removeClass('disable-scrolling');
	jQuery('#header-toggle-mobile-menu').show();
	jQuery('.sidebar-mobile-expand-btn, .my-account-menu-expand-btn').removeClass('hidden')
}
(function($) {
	$(document).ready(function(){
		$('.bijan-modal-overlay').on('click', function() {
			bijanModalClose($(this).prev());
		})
		$('.bijan-modal-close').on('click', function(e) {
			e.preventDefault();
			bijanModalClose($(this).closest('.bijan-modal'))
		})
	});
})(jQuery);