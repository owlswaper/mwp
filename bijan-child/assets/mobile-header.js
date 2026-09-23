(function () {
	'use strict';

	if (window.clzMobileHeaderReady) {
		return;
	}
	window.clzMobileHeaderReady = true;

	function initMobileHeader() {
		var body = document.body;
		var header = document.getElementById('header-container');
		var menuToggle = document.getElementById('header-toggle-mobile-menu');
		var mainMenu = document.getElementById('mobile-menu-container');
		var accountMenu = document.getElementById('mobile-account-menu-container');
		var overlay = document.getElementById('mobile-menu-overlay');
		var bottomNav = document.getElementById('bottom-nav');

		if (!body || !header || !mainMenu || !overlay) {
			return;
		}
		var menuWasOpen = body.classList.contains('mobile-menu-opened');

		function syncHeaderState() {
			var menuIsOpen = mainMenu.classList.contains('open');
			var panelIsOpen = menuIsOpen || (accountMenu && accountMenu.classList.contains('open'));
			if (menuToggle) {
				menuToggle.setAttribute('aria-expanded', menuIsOpen ? 'true' : 'false');
				menuToggle.setAttribute('aria-label', menuIsOpen ? 'بستن منوی اصلی' : 'باز کردن منوی اصلی');
			}
			mainMenu.setAttribute('aria-hidden', menuIsOpen ? 'false' : 'true');
			if (accountMenu) {
				accountMenu.setAttribute('aria-hidden', accountMenu.classList.contains('open') ? 'false' : 'true');
			}
			overlay.setAttribute('aria-hidden', panelIsOpen ? 'false' : 'true');

			if (!panelIsOpen) {
				// The auto-hide-on-scroll state may predate opening the menu. When the
				// panel closes, explicitly reveal both mobile navigation surfaces.
				if (menuWasOpen) {
					header.classList.remove('hide-header');
					body.classList.remove('header-hidden');
				}
			}

			menuWasOpen = panelIsOpen;
		}

		function setAuxiliaryControlsHidden(hidden) {
			var controls = document.querySelectorAll('.sidebar-mobile-expand-btn, .my-account-menu-expand-btn');
			for (var index = 0; index < controls.length; index++) {
				controls[index].classList.toggle('hidden', hidden);
			}
		}

		function closeMenus() {
			var panels = document.querySelectorAll('.mobile-menu-container.open');
			for (var index = 0; index < panels.length; index++) {
				panels[index].classList.remove('open');
			}
			body.classList.remove('mobile-menu-opened', 'disable-scrolling');
			overlay.style.display = 'none';
			if (bottomNav) {
				bottomNav.classList.remove('hide');
			}
			setAuxiliaryControlsHidden(false);
			syncHeaderState();
		}

		function openMenu(panel) {
			if (!panel) {
				return;
			}
			var panels = document.querySelectorAll('.mobile-menu-container.open');
			for (var index = 0; index < panels.length; index++) {
				if (panels[index] !== panel) {
					panels[index].classList.remove('open');
				}
			}
			panel.classList.add('open');
			body.classList.add('mobile-menu-opened', 'disable-scrolling');
			overlay.style.display = 'block';
			if (bottomNav) {
				bottomNav.classList.add('hide');
			}
			setAuxiliaryControlsHidden(true);
			syncHeaderState();
		}

		document.addEventListener('click', function (event) {
			var mainTrigger = event.target.closest('.toggle-mobile-menu');
			var accountTrigger = event.target.closest('.toggle-account-menu');
			var submenuTrigger = event.target.closest('.mobile-menu-wrap li.menu-item-has-children > a');

			if (mainTrigger) {
				event.preventDefault();
				event.stopImmediatePropagation();
				if (mainMenu.classList.contains('open')) {
					closeMenus();
				} else {
					openMenu(mainMenu);
				}
				return;
			}

			if (accountTrigger) {
				event.preventDefault();
				event.stopImmediatePropagation();
				if (accountMenu && accountMenu.classList.contains('open')) {
					closeMenus();
				} else {
					openMenu(accountMenu);
				}
				return;
			}

			if (event.target.closest('#mobile-menu-overlay')) {
				event.preventDefault();
				event.stopImmediatePropagation();
				closeMenus();
				return;
			}

			if (submenuTrigger) {
				event.preventDefault();
				event.stopImmediatePropagation();
				var item = submenuTrigger.parentElement;
				var submenu = submenuTrigger.nextElementSibling;
				var isOpen = item.classList.toggle('open');
				if (submenu) {
					submenu.style.display = isOpen ? 'flex' : 'none';
				}
				submenuTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			}
		}, true);

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && body.classList.contains('mobile-menu-opened')) {
				event.preventDefault();
				closeMenus();
				if (menuToggle) {
					menuToggle.focus();
				}
			}
		});

		syncHeaderState();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMobileHeader);
	} else {
		initMobileHeader();
	}
}());
