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
		var returnFocus = null;
		var backgroundNodes = [];
		var activePanel = null;
		var menuWasOpen = body.classList.contains('mobile-menu-opened');

		var searchLabel = mainMenu.querySelector('.bijan-search label');
		var searchField = mainMenu.querySelector('#mobile-search-field');
		if (searchLabel && searchField) { searchLabel.htmlFor = searchField.id; }

		// Separate navigation links from disclosure controls, including nested levels.
		mainMenu.querySelectorAll('.mobile-menu-wrap li.menu-item-has-children').forEach(function (item, index) {
			var link = item.querySelector(':scope > a');
			var list = item.querySelector(':scope > ul');
			if (!link || !list) { return; }
			var expanded = item.classList.contains('current-menu-ancestor') || item.classList.contains('current-menu-parent');
			var button = document.createElement('button');
			button.type = 'button';
			button.className = 'clz-submenu-toggle';
			list.id = list.id || 'clz-drawer-submenu-' + index;
			button.setAttribute('aria-controls', list.id);
			button.setAttribute('aria-label', 'زیرمنوی ' + link.textContent.trim());
			button.setAttribute('aria-expanded', String(expanded));
			list.hidden = !expanded;
			item.classList.toggle('open', expanded);
			item.insertBefore(button, list);
		});

		function setBackgroundInert(hidden) {
			if (!hidden) {
				backgroundNodes.forEach(function (entry) { entry.node.inert = entry.inert; });
				backgroundNodes = [];
				return;
			}
			// Walk ancestors too: the theme may place its drawer inside a wrapper.
			var path = activePanel;
			while (path && path.parentElement) {
				Array.from(path.parentElement.children).forEach(function (node) {
					if (node === path || node === overlay || node.contains(overlay) || node.tagName === 'SCRIPT' || node.tagName === 'STYLE') { return; }
					backgroundNodes.push({node: node, inert: node.inert});
					node.inert = true;
				});
				path = path.parentElement;
				if (path === body) { break; }
			}
		}

		function focusable(panel) {
			return Array.from(panel.querySelectorAll('a[href], button, input, select, textarea, [tabindex]')).filter(function (node) {
				return !node.disabled && node.tabIndex >= 0 && !node.closest('[hidden], [inert]') && node.getClientRects().length;
			});
		}

		function syncHeaderState() {
			var menuIsOpen = mainMenu.classList.contains('open');
			body.classList.toggle('clz-main-drawer-open', menuIsOpen);
			var panelIsOpen = menuIsOpen || (accountMenu && accountMenu.classList.contains('open'));
			if (menuToggle) {
				menuToggle.setAttribute('aria-expanded', menuIsOpen ? 'true' : 'false');
				menuToggle.setAttribute('aria-label', menuIsOpen ? 'بستن منوی اصلی' : 'باز کردن منوی اصلی');
			}
			mainMenu.setAttribute('aria-hidden', menuIsOpen ? 'false' : 'true');
			mainMenu.inert = !menuIsOpen;
			if (accountMenu) {
				accountMenu.setAttribute('aria-hidden', accountMenu.classList.contains('open') ? 'false' : 'true');
				accountMenu.inert = !accountMenu.classList.contains('open');
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

		function closeMenus(restoreFocus) {
			setBackgroundInert(false);
			activePanel = null;
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
			if (restoreFocus !== false && returnFocus && returnFocus.isConnected) {
				var target = returnFocus;
				target.focus({preventScroll: true});
				// The existing header has a visibility transition; retry after it settles.
				window.setTimeout(function () {
					if (!activePanel && target.isConnected && (document.activeElement === body || mainMenu.contains(document.activeElement))) { target.focus({preventScroll: true}); }
				}, 260);
			}
		}

		function openMenu(panel, trigger) {
			if (!panel) {
				return;
			}
			var panels = document.querySelectorAll('.mobile-menu-container.open');
			for (var index = 0; index < panels.length; index++) {
				if (panels[index] !== panel) {
					panels[index].classList.remove('open');
				}
			}
			setBackgroundInert(false);
			returnFocus = trigger || document.activeElement;
			activePanel = panel;
			panel.classList.add('open');
			body.classList.add('mobile-menu-opened', 'disable-scrolling');
			overlay.style.display = 'block';
			if (bottomNav) {
				bottomNav.classList.add('hide');
			}
			setAuxiliaryControlsHidden(true);
			syncHeaderState();
			setBackgroundInert(true);
			var first = panel.querySelector('.clz-drawer-close') || focusable(panel)[0];
			if (first) { first.focus({preventScroll: true}); }
		}

		document.addEventListener('click', function (event) {
			var mainTrigger = event.target.closest('.toggle-mobile-menu');
			var accountTrigger = event.target.closest('.toggle-account-menu');
			var submenuTrigger = event.target.closest('#mobile-menu-container .clz-submenu-toggle');

			if (mainTrigger) {
				event.preventDefault();
				event.stopImmediatePropagation();
				if (mainMenu.classList.contains('open')) {
					closeMenus();
				} else {
					openMenu(mainMenu, mainTrigger);
				}
				return;
			}

			if (accountTrigger) {
				event.preventDefault();
				event.stopImmediatePropagation();
				if (accountMenu && accountMenu.classList.contains('open')) {
					closeMenus();
				} else {
					openMenu(accountMenu, accountTrigger);
				}
				return;
			}

			if (event.target.closest('#mobile-menu-overlay, #mobile-menu-container .clz-drawer-close')) {
				event.preventDefault();
				event.stopImmediatePropagation();
				closeMenus();
				return;
			}

			if (submenuTrigger) {
				event.preventDefault();
				event.stopImmediatePropagation();
				var item = submenuTrigger.parentElement;
				var submenu = document.getElementById(submenuTrigger.getAttribute('aria-controls'));
				var isOpen = submenuTrigger.getAttribute('aria-expanded') !== 'true';
				item.classList.toggle('open', isOpen);
				if (submenu) { submenu.hidden = !isOpen; }
				submenuTrigger.setAttribute('aria-expanded', String(isOpen));
				return;
			}
			var parentLink = event.target.closest('#mobile-menu-container .menu-item-has-children > a');
			if (parentLink) {
				// Keep real category links navigable; skip the parent's legacy accordion.
				event.stopImmediatePropagation();
				var href = parentLink.getAttribute('href');
				if (!href || href === '#') {
					event.preventDefault();
					var toggle = parentLink.parentElement.querySelector(':scope > .clz-submenu-toggle');
					if (toggle) { toggle.click(); }
				}
			}

		}, true);

		// Prevent the parent mega-menu's hover/masonry initialization in this drawer.
		mainMenu.addEventListener('mouseover', function (event) { event.stopPropagation(); });
		document.addEventListener('keydown', function (event) {
			if (!activePanel) { return; }
			if (event.key === 'Escape') {
				event.preventDefault();
				event.stopImmediatePropagation();
				closeMenus();
			} else if (event.key === 'Tab') {
				var nodes = focusable(activePanel);
				var first = nodes[0], last = nodes[nodes.length - 1];
				if (!first) { event.preventDefault(); return; }
				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault(); last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault(); first.focus();
				}
			}
		}, true);

		syncHeaderState();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMobileHeader);
	} else {
		initMobileHeader();
	}
}());
