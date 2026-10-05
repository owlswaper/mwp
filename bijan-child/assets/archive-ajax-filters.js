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

			const base = (config.paginationBase || 'page').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			const match = decodeURIComponent(url.pathname).match(new RegExp(`/${base}/(\\d+)/?$`));
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

	const initializeProductSliders = (root = document) => {
		if (typeof window.Swiper !== 'function') {
			return;
		}

		const breakpoints = { desktop: 1201, tablet: 769, mobile: 0 };
		root.querySelectorAll('.entry-container .bijan-slider-wrap').forEach((element) => {
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

	const paginationSelector = '.entry-container .woocommerce-pagination, .entry-container nav.navigation.pagination, .entry-container .pagination';
	const pageData = (root = document) => root.querySelector('.cloz-archive-page-data');
	const productList = (root = document) => root.querySelector('.entry-container ul.products');
	let infinite = null;
	let suspendedInfinite = null;
	let pageFrame = 0;

	const rememberPage = (url, page, mode = 'replace') => {
		if (!config.infinite || !url) return;
		const target = new URL(url, location.href);
		if (target.origin !== location.origin) return;
		history[mode === 'push' ? 'pushState' : 'replaceState']({
			...(history.state || {}),
			clozArchive: { state: { ...state, paged: String(page) }, url: target.href }
		}, '', target.href);
		document.querySelector('link[rel="canonical"]')?.setAttribute('href', target.href);
	};

	const stopInfinite = () => {
		if (!infinite) return;
		infinite.generation++;
		infinite.prefetchObserver?.disconnect();
		infinite.loadObserver?.disconnect();
		infinite.controller?.abort();
		infinite.list.removeAttribute('aria-busy');
		infinite.controls.remove();
		infinite = null;
	};

	// Only one future page lives in memory. Ordinary category GETs use the
	// existing page/CDN cache; filtered pages retain the native POST query.
	const prefetchPage = (session) => {
		if (session !== infinite || !session.meta.dataset.next || session.failed) return Promise.resolve(null);
		if (session.pending) return session.pending;
		const url = new URL(session.meta.dataset.next, location.href);
		if (url.origin !== location.origin) return Promise.resolve(null);
		const page = Number(session.meta.dataset.page) + 1;
		const filtered = Object.keys(state).some(key => !['paged', 'product-page'].includes(key));
		const controller = new AbortController();
		const generation = ++session.generation;
		session.controller = controller;
		const timeout = setTimeout(() => controller.abort(), 20000);
		const options = { credentials: 'same-origin', signal: controller.signal };
		if (filtered) {
			const body = new URLSearchParams({ ...state, paged: String(page) });
			body.set(config.key, '1');
			options.method = 'POST';
			options.headers = { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Cloz-Archive-Ajax': '1' };
			options.body = body.toString();
		}
		session.pending = fetch(filtered ? config.url : url.href, options).then(async response => {
			if (!response.ok) throw new Error('Archive page unavailable');
			const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
			const meta = pageData(doc);
			const list = productList(doc);
			if (!meta || !list || Number(meta.dataset.page) !== page || !list.querySelector('li.product')) {
				throw new Error('Invalid archive page');
			}
			if (!state.orderby && meta.dataset.orderWindow !== session.meta.dataset.orderWindow) {
				session.rotated = true;
				throw new Error('Archive order changed');
			}
			// Discard the rest of the fetched document, including head and scripts.
			const fragment = document.createDocumentFragment();
			Array.from(list.children).filter(item => item.matches('li.product')).forEach(item => {
				item.querySelectorAll('img').forEach(img => {
					img.loading = 'lazy';
					img.decoding = 'async';
					img.setAttribute('fetchpriority', 'auto');
				});
				fragment.append(item);
			});
			return { fragment, meta, pagination: doc.querySelector(paginationSelector), title: doc.title };
		}).catch(() => {
			if (session === infinite && generation === session.generation) {
				session.failed = true;
				session.prefetchObserver?.disconnect();
				session.loadObserver?.disconnect();
				session.status.textContent = session.rotated
					? 'چیدمان محصولات به‌روز شده است؛ فهرست را به‌روز کنید.'
					: 'بارگذاری انجام نشد. دوباره تلاش کنید یا از شمارهٔ صفحات استفاده کنید.';
				session.more.textContent = session.rotated ? 'به‌روزرسانی فهرست' : 'تلاش دوباره';
			}
			return null;
		}).finally(() => clearTimeout(timeout));
		return session.pending;
	};

	const appendPage = async (session, manual = false) => {
		if (session !== infinite || session.appending || (!manual && session.paused)) return;
		session.appending = true;
		session.more.disabled = true;
		session.list.setAttribute('aria-busy', 'true');
		session.status.textContent = 'در حال بارگذاری محصولات…';
		const pending = prefetchPage(session);
		const generation = session.generation;
		const result = await pending;
		if (session !== infinite || generation !== session.generation) return;
		session.appending = false;
		session.more.disabled = false;
		session.list.removeAttribute('aria-busy');
		if (!result) return;
		// A catalogue can change between requests; never repeat a product card.
		const items = Array.from(result.fragment.children).filter(item => {
			const id = Array.from(item.classList).find(name => /^post-\d+$/.test(name));
			if (id && session.ids.has(id)) { item.remove(); return false; }
			if (id) session.ids.add(id);
			return true;
		});
		if (!items.length) {
			session.failed = true;
			session.rotated = true;
			session.prefetchObserver?.disconnect();
			session.loadObserver?.disconnect();
			session.status.textContent = 'فهرست محصولات تغییر کرده است؛ آن را به‌روز کنید.';
			session.more.textContent = 'به‌روزرسانی فهرست';
			return;
		}
		const first = items[0];
		session.list.append(result.fragment);
		session.pages.push({ element: first, page: Number(result.meta.dataset.page), url: result.meta.dataset.url, title: result.title });
		const pagination = document.querySelector(paginationSelector);
		if (pagination && result.pagination) pagination.replaceWith(result.pagination);
		else if (pagination) pagination.remove();
		session.meta.replaceWith(result.meta);
		session.meta = result.meta;
		session.pending = null;
		session.controller = null;
		session.list.closest('.bijan-slider-wrap')?.swiper?.update();
		session.status.textContent = `${items.length.toLocaleString('fa-IR')} محصول دیگر نمایش داده شد.`;
		session.more.textContent = 'نمایش محصولات بیشتر';
		document.body.dispatchEvent(new CustomEvent('cloz_archive_appended', { detail: { page: Number(result.meta.dataset.page), items } }));
		if (manual) {
			const link = first.querySelector('a');
			link?.focus({ preventScroll: true });
		}
		if (!session.meta.dataset.next) {
			session.prefetchObserver?.disconnect();
			session.loadObserver?.disconnect();
			session.more.hidden = true;
			session.pause.hidden = true;
			session.status.textContent += ' همهٔ محصولات نمایش داده شدند.';
		} else {
			// Reobserve after layout so a short page does not stall the observer.
			requestAnimationFrame(() => {
				if (session !== infinite || session.paused) return;
				session.prefetchObserver?.unobserve(session.controls);
				session.loadObserver?.unobserve(session.controls);
				session.prefetchObserver?.observe(session.controls);
				session.loadObserver?.observe(session.controls);
			});
		}
	};

	const initializeInfinite = () => {
		stopInfinite();
		const meta = pageData();
		const list = productList();
		if (!config.infinite || !meta || !list || !list.closest('.bijan-slider-wrap') || !list.querySelector('li.product')) return;
		const controls = document.createElement('div');
		controls.className = 'cloz-infinite-controls';
		controls.innerHTML = '<p role="status" aria-live="polite" aria-atomic="true"></p><button type="button" class="cloz-load-more">نمایش محصولات بیشتر</button><button type="button" class="cloz-scroll-pause" aria-pressed="false">توقف بارگذاری خودکار</button>';
		list.closest('.bijan-slider-wrap').after(controls);
		const connection = navigator.connection;
		const session = {
			meta, list, controls, status: controls.querySelector('p'), more: controls.querySelector('.cloz-load-more'),
			pause: controls.querySelector('.cloz-scroll-pause'),
			pages: [{ element: list.querySelector('li.product'), page: Number(meta.dataset.page), url: meta.dataset.url, title: document.title }],
			ids: new Set(Array.from(list.children).flatMap(item => Array.from(item.classList).filter(name => /^post-\d+$/.test(name)))),
			paused: !!connection?.saveData, failed: false, pending: null, appending: false, generation: 0
		};
		infinite = session;
		if (!meta.dataset.next) {
			controls.hidden = true;
			return;
		}
		if ('IntersectionObserver' in window) {
			const slow = connection && ['slow-2g', '2g', '3g'].includes(connection.effectiveType);
			session.prefetchObserver = new IntersectionObserver(entries => {
				if (!session.paused && entries.some(entry => entry.isIntersecting)) prefetchPage(session);
			}, { rootMargin: `${slow ? 2800 : 1800}px 0px` });
			session.loadObserver = new IntersectionObserver(entries => {
				if (entries.some(entry => entry.isIntersecting)) appendPage(session);
			}, { rootMargin: '700px 0px' });
			if (!session.paused) {
				session.prefetchObserver.observe(controls);
				session.loadObserver.observe(controls);
			}
		} else {
			session.paused = true;
			session.pause.hidden = true;
		}
		const updatePause = () => {
			session.pause.setAttribute('aria-pressed', String(session.paused));
			session.pause.textContent = session.paused ? 'ادامهٔ بارگذاری خودکار' : 'توقف بارگذاری خودکار';
		};
		updatePause();
		session.pause.addEventListener('click', () => {
			session.paused = !session.paused;
			updatePause();
			session.prefetchObserver?.disconnect();
			session.loadObserver?.disconnect();
			if (!session.paused && !session.failed) {
				session.prefetchObserver?.observe(controls);
				session.loadObserver?.observe(controls);
			}
		});
		session.more.addEventListener('click', () => {
			if (session.rotated) { refresh({ ...state, paged: '1' }, true); return; }
			if (session.failed) { session.failed = false; session.pending = null; }
			appendPage(session, true);
		});
	};

	// Binary search across page boundaries, once per scroll frame, avoids
	// measuring every product. Scrolling never adds history entries.
	window.addEventListener('scroll', () => {
		if (pageFrame || !infinite || activeRequest) return;
		pageFrame = requestAnimationFrame(() => {
			pageFrame = 0;
			if (!infinite || activeRequest) return;
			const pages = infinite.pages;
			let lo = 0, hi = pages.length - 1;
			while (lo < hi) {
				const mid = Math.ceil((lo + hi) / 2);
				if (pages[mid].element.getBoundingClientRect().top <= innerHeight * .35) lo = mid;
				else hi = mid - 1;
			}
			const current = pages[lo];
			if (current?.element && current.url && new URL(current.url, location.href).href !== location.href) {
				rememberPage(current.url, current.page);
				if (current.title) document.title = current.title;
			}
		});
	}, { passive: true });

	let filterTrigger = null;
	const filterIsOpen = () => document.querySelector('.cloz-filter-trigger')?.getAttribute('aria-expanded') === 'true';

	const setFilterDrawer = (open, manageFocus = true) => {
		const trigger = document.querySelector('.cloz-filter-trigger');
		const panel = document.querySelector('.cloz-filter-panel');
		if (!trigger || !panel) {
			return;
		}

		if (open) {
			filterTrigger = trigger;
			trigger.setAttribute('aria-expanded', 'true');
			panel.hidden = false;
			window.requestAnimationFrame(() => {
				panel.classList.add('is-open');
				if (manageFocus) {
					const initialFocus = panel.querySelector('.cloz-filter-widgets input:not([disabled]), .cloz-filter-widgets button:not([disabled]), .cloz-filter-widgets select:not([disabled])') || panel.querySelector('button:not([disabled])');
					initialFocus?.focus({ preventScroll: true });
				}
			});
			return;
		}

		trigger.setAttribute('aria-expanded', 'false');
		panel.classList.remove('is-open');
		panel.hidden = true;
		if (manageFocus) (filterTrigger || trigger).focus({ preventScroll: true });
	};

	const refresh = async (nextState, shouldScroll, navigation = {}) => {
		const requestedState = normalizeState(nextState);
		const previousInfinite = infinite || suspendedInfinite;
		suspendedInfinite = previousInfinite;
		stopInfinite();
		const currentRequest = ++requestNumber;
		const reopenFilters = filterIsOpen();
		const filterScrollTop = document.querySelector('.cloz-filter-widgets')?.scrollTop || 0;

		if (activeRequest) {
			activeRequest.abort();
		}
		activeRequest = new AbortController();
		const refreshController = activeRequest;
		const refreshTimeout = setTimeout(() => refreshController.abort(), 20000);

		const body = new URLSearchParams(requestedState);
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
				signal: refreshController.signal
			});

			if (!response.ok) {
				throw new Error(`Archive request failed: ${response.status}`);
			}

			const incoming = new DOMParser().parseFromString(await response.text(), 'text/html');
			if (currentRequest !== requestNumber) return false;
			if (!incoming.querySelector('.entry-container')) throw new Error('Invalid archive response');
			state = requestedState;

			replaceFragment(incoming, '#sort-wrap');
			replaceFragment(incoming, '#sidebar.sidebar-shop');
			replaceFragment(incoming, '.entry-container');
			// Intro belongs to the first page, outside the replaced product list.
			const intro = document.querySelector('.cloz-category-intro-section');
			const nextIntro = incoming.querySelector('.cloz-category-intro-section');
			if (intro && nextIntro) intro.replaceWith(nextIntro);
			else if (intro) intro.remove();
			else if (nextIntro) document.querySelector('.woocommerce-products-header')?.append(nextIntro);
			const oldDescription = document.querySelector('#primary > .term-description');
			const newDescription = incoming.querySelector('#primary > .term-description');
			if (oldDescription && newDescription) oldDescription.replaceWith(newDescription);
			else if (oldDescription) oldDescription.remove();
			else if (newDescription) {
				const primary = document.querySelector('#primary');
				if (newDescription.classList.contains('term-description-top')) primary?.prepend(newDescription);
				else primary?.append(newDescription);
			}
			if (config.infinite) {
				document.title = incoming.title || document.title;
				const meta = pageData();
				if (navigation.history !== 'none') {
					rememberPage(navigation.url || meta?.dataset.url || config.url, meta?.dataset.page || state.paged || 1, 'push');
				}
			}
			initializeInfinite();
			suspendedInfinite = null;
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
				document.querySelector('.entry-container')?.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start' });
			}
			return true;
		} catch (error) {
			if (currentRequest === requestNumber) {
				if (previousInfinite && previousInfinite.list.isConnected) {
					infinite = previousInfinite;
					infinite.pending = null;
					infinite.appending = false;
					infinite.more.disabled = false;
					infinite.list.closest('.bijan-slider-wrap').after(infinite.controls);
					if (!infinite.paused && !infinite.failed && infinite.meta.dataset.next) {
						infinite.prefetchObserver?.observe(infinite.controls);
						infinite.loadObserver?.observe(infinite.controls);
					}
				}
				let notice = document.querySelector('.cloz-archive-error');
				if (!notice) {
					notice = document.createElement('p');
					notice.className = 'cloz-archive-error';
					notice.setAttribute('role', 'alert');
					document.querySelector('.entry-container')?.prepend(notice);
				}
				notice.textContent = 'بارگذاری انجام نشد؛ لطفاً دوباره تلاش کنید.';
			}
			return false;
		} finally {
			clearTimeout(refreshTimeout);
			if (currentRequest === requestNumber) {
				document.body.classList.remove('cloz-archive-is-loading');
				document.querySelector('.entry-container')?.removeAttribute('aria-busy');
				activeRequest = null;
			}
		}
	};

	document.addEventListener('click', (event) => {
		const faq = event.target.closest('.category-faq-section .faq-question');
		if (faq) {
			const item = faq.closest('.faq-item');
			const open = !item.classList.contains('active');
			faq.closest('.category-faq-section').querySelectorAll('.faq-item').forEach(other => {
				other.classList.remove('active');
				other.querySelector('.faq-question')?.setAttribute('aria-expanded', 'false');
			});
			item.classList.toggle('active', open);
			faq.setAttribute('aria-expanded', String(open));
			return;
		}
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

		if (event.target.closest('[data-cloz-search-clear]')) {
			event.preventDefault();
			const next = { ...state, paged: '1' };
			delete next.s;
			refresh(next, false).then(() => document.querySelector('#cloz-catalog-search-input')?.focus({ preventScroll: true }));
			return;
		}

		if (event.target.closest('[data-cloz-filter-reset]')) {
			event.preventDefault();
			const next = { ...state, paged: '1' };
			Object.keys(next).forEach((key) => {
				if (!['s', 'orderby', 'paged', 'special-products'].includes(key)) delete next[key];
			});
			refresh(next, false);
			return;
		}

		const sort = event.target.closest('.woocommerce-ordering .sort-item[data-sort]');
		if (sort) {
			event.preventDefault();
			const next = { ...state, paged: '1' };
			if (next.orderby === sort.getAttribute('data-sort')) {
				delete next.orderby;
			} else {
				next.orderby = sort.getAttribute('data-sort');
			}
			refresh(next, false);
			return;
		}

		const pagination = event.target.closest('.entry-container .woocommerce-pagination a, .entry-container nav.navigation.pagination a, .entry-container .pagination a.page-numbers');
		if (pagination && event.button === 0 && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey && !pagination.hasAttribute('download') && (!pagination.target || pagination.target === '_self')) {
			event.preventDefault();
			refresh({ ...state, paged: String(pageFromLink(pagination.href)) }, true, { url: pagination.href });
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

	window.addEventListener('popstate', event => {
		if (!config.infinite || !event.state?.clozArchive) return;
		refresh(event.state.clozArchive.state, true, { history: 'none', url: event.state.clozArchive.url });
	});

	$(function () {
		initializeInfinite();
		const meta = pageData();
		if (meta) rememberPage(meta.dataset.url, meta.dataset.page);
		// The parent theme submits these controls with GET. Remove only those two
		// direct handlers; all visual and WooCommerce slider behaviour stays intact.
		$('.woocommerce-ordering .sort-item').off('click');
		$('.woocommerce-ordering').off('change', 'select.orderby');
		$('.price_slider').off('slidechange');

		$(document).on(
			'submit.clozArchive',
			'form.woocommerce-ordering, form.cloz-catalog-search, .widget_price_filter form, form.woocommerce-widget-layered-nav-dropdown, #sidebar.sidebar-shop form.woocommerce-product-search, #sidebar.sidebar-shop form[role="search"]',
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

		$(document).on('change.clozArchive', 'form.woocommerce-ordering select.orderby', function () {
			this.closest('form')?.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
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

		});
	});
})(jQuery);
