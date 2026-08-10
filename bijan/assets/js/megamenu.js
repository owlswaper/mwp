(function($) {
	$(document).ready(function(){
		$(document).on('mouseenter', '.megamenu-wrap', function() {
			if(!$(this).hasClass('megamenu-selected')) { // Active hover mode for each item menu
				$(this).siblings('.megamenu-selected').removeClass('megamenu-selected')
				$(this).addClass('megamenu-selected');
			}

			// Set masonry
			let submenu = $(this).children('.sub-menu');
			if(submenu.length && !submenu.hasClass('is-masonry')) {
				submenu.masonry({
					columnWidth: 255,
					originLeft: !bijanVars.rtl,
					horizontalOrder: true,
					gutter: 80,
					resize: false,
				});
				submenu.addClass('is-masonry');
			}
		});
		let activeMegaMenuItem = $('.bijan-megamenu-container.current-menu-item');
		if(activeMegaMenuItem.length) {
			activeMegaMenuItem.css('--width', activeMegaMenuItem.outerWidth()+"px").css('--start', -activeMegaMenuItem.position().left+$('#header').outerWidth()+'px');
		}
	});
})(jQuery);