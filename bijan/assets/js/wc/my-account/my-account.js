(function($) {
	$(document).ready(function(){
		function closeMenu() {
			$('.woocommerce-MyAccount-navigation').removeClass('mobile-expanded');
			$('#bijan-overlay').fadeOut();
			$('.my-account-menu-expand-btn').removeClass('active');
		}
		$('.my-account-menu-expand-btn').on('click', function() {
			$sidebar = $('.woocommerce-MyAccount-navigation');
			if($sidebar.hasClass('mobile-expanded')) {
				closeMenu();
			} else {
				$sidebar.addClass('mobile-expanded');
				$('#bijan-overlay').fadeIn();
				$(this).addClass('active');
			}
		})
		$('#bijan-overlay').on('click', closeMenu)
	});
})(jQuery);