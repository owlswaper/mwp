(function () {
	'use strict';

	function initMobileHeaderState() {
		var body = document.body;
		var header = document.getElementById('header-container');

		if (!body || !header) {
			return;
		}

		var menuWasOpen = body.classList.contains('mobile-menu-opened');

		function syncHeaderState() {
			var menuIsOpen = body.classList.contains('mobile-menu-opened');

			if (!menuIsOpen) {
				// The auto-hide-on-scroll state may predate opening the menu. When the
				// panel closes, explicitly reveal both mobile navigation surfaces.
				if (menuWasOpen) {
					header.classList.remove('hide-header');
					body.classList.remove('header-hidden');
				}
			}

			menuWasOpen = menuIsOpen;
		}

		var observer = new MutationObserver(syncHeaderState);
		observer.observe(body, { attributes: true, attributeFilter: ['class'] });
		syncHeaderState();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMobileHeaderState);
	} else {
		initMobileHeaderState();
	}
}());
