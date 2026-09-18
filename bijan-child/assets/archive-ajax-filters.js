(function ($) {
	'use strict';

	const config = window.clozArchiveFilters;
	if (!config || !config.url || !config.key) {
		return;
	}

	let state = { ...(config.state || {}) };
	let activeRequest = null;
	let requestNumber = 0;
	let priceTimer = null;

	const controlledKey = (key) => (
		key === 's'
		|| key === 'orderby'
		|| key === 'min_price'
		|| key === 'max_price'
		|| key === 'rating_filter'
		|| key === 'instock'
		|| key === 'onsale'
		|| key === 'special-products'
		|| key === 'paged'
		|| key === 'product-page'
		|| key.startsWith('filter_')
		|| key.startsWith('query_type_')
	);

	const normalizeState = (candidate) => {
		const clean = {};
		Object.entries(candidate || {}).forEach(([key, value]) => {
			if (controlledKey(key) && value !== '' && value !== null && typeof value !== 'undefined') {
				clean[key] = String(value);
			}
		});
		return clean;
	};

	const pageFromLink = (href) => {
		try {
			const url = new URL(href, window.location.origin);
			const queryPage = url.searchParams.get('paged') || url.searchParams.get('product-page');
			if (queryPage) {
				return Math.max(1, parseInt(queryPage, 10) || 1);
			}

			const match = url.pathname.match(/\/page\/(\d+)\/?$/);
			return match ? Math.max(1, parseInt(match[1], 10) || 1) : 1;
		} catch (error) {
			return 1;
		}
	};

	const prepareAutoplay = (options) => {
		if (options && options.autoplay && !isNaN(options.autoplay.delay)) {
			options.autoplay.delay *= 1000;
		} else if (options) {
			delete options.autoplay;
		}
		return options;
	};

	const initializeProductSliders = () => {
		if (typeof window.Swiper !== 'function') {
			return;
		}

		const breakpoints = { desktop: 1201, tablet: 769, mobile: 0 };
		document.querySelectorAll('.entry-container .bijan-slider-wrap').forEach((element) => {
			if (element.swiper) {
				return;
			}

			let settings;
			try {
				settings = JSON.parse(element.getAttribute('data-settings')) || {};
			} catch (error) {
				return;
			}

			const width = window.innerWidth;
			const device = width >= breakpoints.desktop ? 'desktop' : (width >= breakpoints.tablet ? 'tablet' : 'mobile');
			const deviceSettings = settings[device] && settings[device].slider;
			if (!deviceSettings || !deviceSettings.enabled) {
				return;
			}

			const options = {
				direction: 'horizontal',
				navigation: {
					nextEl: element.querySelector('.swiper-button-next'),
					prevEl: element.querySelector('.swiper-button-prev')
				},
				...(settings.slider || {}),
				breakpoints: {}
			};

			Object.entries(breakpoints).forEach(([name, point]) => {
				if (settings[name] && settings[name].slider && settings[name].slider.enabled) {
					options.breakpoints[point] = prepareAutoplay({ ...settings[name].slider });
				}
			});
			prepareAutoplay(options);

			element.classList.add('swiper');
			const wrapper = element.querySelector('.wrapper');
			if (wrapper) {
				wrapper.classList.add('swiper-wrapper');
				wrapper.querySelectorAll('.slider-slide').forEach((slide) => slide.classList.add('swiper-slide'));
			}
			if (!options.navigation.nextEl || !options.navigation.prevEl) {
				delete options.navigation;
			}

			new window.Swiper(element, options);
		});
	};

	const initializeLayeredDropdowns = () => {
		if (!$.fn.selectWoo) {
			return;
		}

		$('.woocommerce-widget-layered-nav-dropdown').each(function () {
			const select = $(this);
			if (select.hasClass('select2-hidden-accessible')) {
				return;
			}

			select.attr('data-cloz-archive-managed', '1').selectWoo({
				placeholder: select.find('option').first().text(),
				minimumResultsForSearch: 5,
				width: '100%',
				allowClear: !select.prop('multiple')
			});
		});
	};

	const replaceFragment = (incomingDocument, selector) => {
		const current = document.querySelector(selector);
		const incoming = incomingDocument.querySelector(selector);
		if (!current || !incoming) {
			return false;
		}
		current.replaceWith(incoming);
		return true;
	};

	let filterTrigger = null;
	const isMobileDrawer = () => window.matchMedia('(max-width: 768px)').matches;
	const filterIsOpen = () => document.querySelector('.cloz-filter-trigger')?.getAttribute('aria-expanded') === 'true';

	const setFilterDrawer = (open, manageFocus = true) => {
		const trigger = document.querySelector('.cloz-filter-trigger');
		const panel = document.querySelector('.cloz-filter-panel');
		const backdrop = document.querySelector('.cloz-filter-backdrop');
		if (!trigger || !panel) {
			return;
		}

		if (open) {
			filterTrigger = trigger;
			trigger.setAttribute('aria-expanded', 'true');
			panel.setAttribute('role', isMobileDrawer() ? 'dialog' : 'region');
			if (isMobileDrawer()) panel.setAttribute('aria-modal', 'true');
			else panel.removeAttribute('aria-modal');
			panel.hidden = false;
			if (backdrop) backdrop.hidden = !isMobileDrawer();
			document.body.classList.toggle('cloz-filter-drawer-open', isMobileDrawer());
			window.requestAnimationFrame(() => {
				panel.classList.add('is-open');
				if (manageFocus) {
					const initialFocus = isMobileDrawer()
						? panel.querySelector('.cloz-filter-close')
						: (panel.querySelector('input[type="search"]') || panel.querySelector('input, button, select'));
					initialFocus?.focus({ preventScroll: true });
				}
			});
			return;
		}

		trigger.setAttribute('aria-expanded', 'false');
		panel.setAttribute('role', 'region');
		panel.removeAttribute('aria-modal');
		panel.classList.remove('is-open');
		document.body.classList.remove('cloz-filter-drawer-open');
		if (backdrop) backdrop.hidden = true;
		panel.hidden = true;
		if (manageFocus) (filterTrigger || trigger).focus({ preventScroll: true });
	};

	const refresh = async (nextState, shouldScroll) => {
		state = normalizeState(nextState);
		const currentRequest = ++requestNumber;
		const reopenFilters = filterIsOpen();
		const filterScrollTop = document.querySelector('.cloz-filter-widgets')?.scrollTop || 0;

		if (activeRequest) {
			activeRequest.abort();
		}
		activeRequest = new AbortController();

		const body = new URLSearchParams(state);
		body.set(config.key, '1');
		document.body.classList.add('cloz-archive-is-loading');
		document.querySelector('.entry-container')?.setAttribute('aria-busy', 'true');

		try {
			const response = await fetch(config.url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
					'X-Cloz-Archive-Ajax': '1'
				},
				body: body.toString(),
				signal: activeRequest.signal
			});

			if (!response.ok) {
				throw new Error(`Archive request failed: ${response.status}`);
			}

			const incoming = new DOMParser().parseFromString(await response.text(), 'text/html');
			if (currentRequest !== requestNumber || !incoming.querySelector('.entry-container')) {
				return;
			}

			replaceFragment(incoming, '#sort-wrap');
			replaceFragment(incoming, '#sidebar.sidebar-shop');
			replaceFragment(incoming, '.entry-container');
			if (reopenFilters) {
				setFilterDrawer(true, false);
				const filterWidgets = document.querySelector('.cloz-filter-widgets');
				if (filterWidgets) filterWidgets.scrollTop = filterScrollTop;
			}

			$(document.body).trigger('init_price_filter');
			initializeLayeredDropdowns();
			initializeProductSliders();
			document.body.dispatchEvent(new CustomEvent('cloz_archive_updated', { detail: { state: { ...state } } }));

			if (shouldScroll) {
				document.querySelector('.entry-container')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		} catch (error) {
			if (error.name !== 'AbortError') {
				console.error(error);
			}
		} finally {
			if (currentRequest === requestNumber) {
				document.body.classList.remove('cloz-archive-is-loading');
				document.querySelector('.entry-container')?.removeAttribute('aria-busy');
				activeRequest = null;
			}
		}
	};

	document.addEventListener('click', (event) => {
		const drawerToggle = event.target.closest('[data-cloz-filter-toggle]');
		if (drawerToggle) {
			event.preventDefault();
			setFilterDrawer(!filterIsOpen());
			return;
		}

		if (event.target.closest('[data-cloz-filter-close]')) {
			event.preventDefault();
			setFilterDrawer(false);
			return;
		}

		const filter = event.target.closest('[data-cloz-archive-state]');
		if (filter) {
			event.preventDefault();
			try {
				const targetState = JSON.parse(filter.getAttribute('data-cloz-archive-state')) || {};
				targetState.paged = '1';
				refresh(targetState, false);
			} catch (error) {
				console.error(error);
			}
			return;
		}

		const sort = event.target.closest('.woocommerce-ordering .sort-item[data-sort]');
		if (sort) {
			event.preventDefault();
			refresh({ ...state, orderby: sort.getAttribute('data-sort'), paged: '1' }, false);
			return;
		}

		const pagination = event.target.closest('.entry-container .woocommerce-pagination a, .entry-container nav.navigation.pagination a, .entry-container .pagination a.page-numbers');
		if (pagination) {
			event.preventDefault();
			refresh({ ...state, paged: String(pageFromLink(pagination.href)) }, true);
		}
	});

	const submitArchiveForm = (event, form) => {
		event.preventDefault();
		const next = { ...state, paged: '1' };
		new FormData(form).forEach((value, key) => {
			if (controlledKey(key)) {
				if (String(value) === '') {
					delete next[key];
				} else {
					next[key] = String(value);
				}
			}
		});
		refresh(next, false);
	};

	$(function () {
		// The parent theme submits these controls with GET. Remove only those two
		// direct handlers; all visual and WooCommerce slider behaviour stays intact.
		$('.woocommerce-ordering .sort-item').off('click');
		$('.price_slider').off('slidechange');

		$(document).on(
			'submit.clozArchive',
			'form.woocommerce-ordering, .widget_price_filter form, form.woocommerce-widget-layered-nav-dropdown, form.cloz-archive-search',
			function (event) {
				submitArchiveForm(event, this);
			}
		);

		$(document).on('change.clozArchive', 'select.woocommerce-widget-layered-nav-dropdown', function () {
			const select = $(this);
			if (!select.is('[data-cloz-archive-managed]')) {
				return;
			}
			const value = select.val();
			select.closest('form').find('input[name^="filter_"]').val(Array.isArray(value) ? value.join(',') : (value || ''));
			if (!select.prop('multiple')) {
				select.closest('form').trigger('submit');
			}
		});

		$(document).on('slidechange.clozArchive', '.price_slider', function () {
			window.clearTimeout(priceTimer);
			const form = this.closest('form');
			priceTimer = window.setTimeout(() => {
				if (form) {
					form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
				}
			}, 300);
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && filterIsOpen()) {
				setFilterDrawer(false);
				return;
			}

			if (event.key === 'Tab' && filterIsOpen() && isMobileDrawer()) {
				const panel = document.querySelector('.cloz-filter-panel');
				const focusable = panel ? Array.from(panel.querySelectorAll('button:not(:disabled), input:not(:disabled), select:not(:disabled), [href], [tabindex]:not([tabindex="-1"])')) : [];
				if (!focusable.length) return;
				const first = focusable[0];
				const last = focusable[focusable.length - 1];
				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			}
		});

		window.addEventListener('resize', () => {
			if (!filterIsOpen()) return;
			const backdrop = document.querySelector('.cloz-filter-backdrop');
			const panel = document.querySelector('.cloz-filter-panel');
			if (backdrop) backdrop.hidden = !isMobileDrawer();
			if (panel) {
				panel.setAttribute('role', isMobileDrawer() ? 'dialog' : 'region');
				if (isMobileDrawer()) panel.setAttribute('aria-modal', 'true');
				else panel.removeAttribute('aria-modal');
			}
			document.body.classList.toggle('cloz-filter-drawer-open', isMobileDrawer());
		});
	});
})(jQuery);
