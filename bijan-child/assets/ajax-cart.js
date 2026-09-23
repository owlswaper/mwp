(function ($) {
	'use strict';

	if (!window.ClozAjaxCart || !window.fetch || !window.FormData) {
		return;
	}
	if (window.ClozAjaxCartInitialized) {
		return;
	}
	window.ClozAjaxCartInitialized = true;

	var config = window.ClozAjaxCart;
	var toast;
	var closeTimer;
	var submittedActions = Object.create(null);

	function createActionId() {
		if (window.crypto && typeof window.crypto.randomUUID === 'function') {
			return window.crypto.randomUUID();
		}

		return String(Date.now()) + '-' + Math.random().toString(36).slice(2);
	}

	function getActionId(button, event) {
		// A real click/submit always starts a new cart action. A click replayed by
		// a delayed-script loader is untrusted and therefore keeps the old id.
		if (event && event.isTrusted) {
			button.dataset.clozCartActionId = createActionId();
		} else if (!button.dataset.clozCartActionId) {
			button.dataset.clozCartActionId = createActionId();
		}

		return button.dataset.clozCartActionId;
	}

	function getToast() {
		if (!toast) {
			toast = document.getElementById('cloz-cart-toast');
		}
		return toast;
	}

	function cleanText(value) {
		var node = document.createElement('div');
		node.innerHTML = value || '';
		return (node.textContent || node.innerText || '').trim();
	}

	function showToast(type, item, message) {
		var element = getToast();
		if (!element) {
			return;
		}

		var isError = type === 'error';
		var image = element.querySelector('.cloz-cart-toast__image');
		var title = element.querySelector('.cloz-cart-toast__title');
		var product = element.querySelector('.cloz-cart-toast__product');
		var meta = element.querySelector('.cloz-cart-toast__meta');
		var action = element.querySelector('.cloz-cart-toast__action');

		element.classList.remove('is-visible', 'is-error', 'is-restarting');
		void element.offsetWidth;
		element.classList.toggle('is-error', isError);
		title.textContent = isError ? config.errorTitle : config.successTitle;

		if (isError) {
			image.removeAttribute('src');
			image.hidden = true;
			product.textContent = message || config.genericError;
			meta.textContent = '';
			action.hidden = true;
			element.setAttribute('role', 'alert');
		} else {
			var quantity = item && item.quantity ? item.quantity : 1;
			var count = item && item.count ? Number(item.count) : 1;
			image.src = item && item.image ? item.image : '';
			image.hidden = !image.src;
			product.textContent = count > 1 ? count + ' محصول به سبد خرید اضافه شد' : (item.name || 'محصول');
			meta.textContent = (count === 1 && quantity > 1 ? 'تعداد: ' + quantity : '') +
				(item && item.meta ? ((count === 1 && quantity > 1 ? ' — ' : '') + item.meta) : '');
			action.href = config.cartUrl;
			action.textContent = config.viewCart;
			action.hidden = false;
			element.setAttribute('role', 'status');
		}

		element.style.setProperty('--cloz-toast-duration', Number(config.timeout || 5000) + 'ms');
		element.setAttribute('aria-hidden', 'false');
		element.classList.add('is-visible');

		window.clearTimeout(closeTimer);
		closeTimer = window.setTimeout(hideToast, Number(config.timeout || 5000));
	}

	function hideToast() {
		var element = getToast();
		if (!element) {
			return;
		}
		element.classList.remove('is-visible');
		element.setAttribute('aria-hidden', 'true');
		if (document.activeElement && element.contains(document.activeElement)) {
			document.activeElement.blur();
		}
		window.clearTimeout(closeTimer);
	}

	function setLoading(button, loading) {
		if (!button) {
			return;
		}

		if (loading) {
			button.dataset.clozWasDisabled = button.disabled ? '1' : '0';
			button.dataset.clozAriaLabel = button.getAttribute('aria-label') || '';
			button.classList.add('cloz-cart-is-loading');
			button.setAttribute('aria-busy', 'true');
			button.setAttribute('aria-label', config.working);
			button.disabled = true;
		} else {
			button.classList.remove('cloz-cart-is-loading');
			button.removeAttribute('aria-busy');
			if (button.dataset.clozAriaLabel) {
				button.setAttribute('aria-label', button.dataset.clozAriaLabel);
			} else {
				button.removeAttribute('aria-label');
			}
			button.disabled = button.dataset.clozWasDisabled === '1';
			delete button.dataset.clozWasDisabled;
			delete button.dataset.clozAriaLabel;
		}
	}

	function updateFragments(fragments, cartHash, button) {
		if (fragments) {
			Object.keys(fragments).forEach(function (selector) {
				try {
					$(selector).replaceWith(fragments[selector]);
				} catch (ignore) {
					// An extension may return a selector not present on this page.
				}
			});
		}

		try {
			window.sessionStorage.setItem('wc_fragments', JSON.stringify(fragments || {}));
			window.sessionStorage.setItem('wc_cart_hash', cartHash || '');
			window.localStorage.setItem('wc_cart_hash', cartHash || '');
		} catch (ignore) {
			// Storage can be unavailable in private/restricted browsers.
		}

		var hadViewCart = button && button.parentElement && button.parentElement.querySelector('.added_to_cart');
		$(document.body).trigger('added_to_cart', [fragments || {}, cartHash || '', $(button)]);

		// Keep the theme's original control visible; the toast is the single cart CTA.
		if (button) {
			button.classList.remove('added', 'loading');
			if (!hadViewCart && button.parentElement) {
				Array.prototype.forEach.call(button.parentElement.querySelectorAll('.added_to_cart'), function (link) {
					link.remove();
				});
			}
		}
	}

	function submitRequest(formData, button, actionId) {
		if (!button || button.classList.contains('cloz-cart-is-loading')) {
			return;
		}
		if (actionId && submittedActions[actionId]) {
			return;
		}
		if (actionId) {
			submittedActions[actionId] = true;
			window.setTimeout(function () {
				delete submittedActions[actionId];
			}, 60000);
		}

		setLoading(button, true);

		fetch(config.endpoint, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: formData
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error(config.genericError);
				}
				return response.json();
			})
			.then(function (response) {
				if (!response || !response.success) {
					throw new Error(response && response.data && response.data.message ? response.data.message : config.genericError);
				}

				updateFragments(response.data.fragments, response.data.cart_hash, button);
				showToast('success', response.data.item || {}, '');
			})
			.catch(function (error) {
				showToast('error', null, cleanText(error && error.message ? error.message : config.genericError));
			})
			.finally(function () {
				setLoading(button, false);
			});
	}

	function handleLoopClick(event) {
		var button = event.target.closest('.cloz-ajax-add-to-cart');
		if (!button || button.closest('form.cart')) {
			return;
		}

		var productId = button.getAttribute('data-product_id');
		if (!productId) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		event.stopImmediatePropagation();

		var data = new FormData();
		data.append('product_id', productId);
		data.append('quantity', button.getAttribute('data-quantity') || '1');
		submitRequest(data, button, getActionId(button, event));
	}

	function handleProductSubmit(event) {
		var form = event.target;
		if (!form.matches || !form.matches('form.cart')) {
			return;
		}

		var button = event.submitter || form.querySelector('.single_add_to_cart_button, button[name="add-to-cart"]');
		if (!button || button.type === 'button' || (button.matches('.disabled, .wc-variation-selection-needed') && !form.querySelector('[name="variation_id"]'))) {
			return;
		}

		var productField = form.querySelector('[name="product_id"], [name="add-to-cart"]');
		var groupedQuantity = form.querySelector('[name^="quantity["]');
		if (!productField && !groupedQuantity) {
			return;
		}

		var variation = form.querySelector('[name="variation_id"]');
		if (variation && Number(variation.value || 0) < 1) {
			event.preventDefault();
			event.stopPropagation();
			event.stopImmediatePropagation();
			showToast('error', null, config.chooseOptions);
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		event.stopImmediatePropagation();

		var data = new FormData(form);
		if (button.name && !data.has(button.name)) {
			data.append(button.name, button.value || '');
		}
		if (!data.has('product_id') && data.has('add-to-cart')) {
			data.append('product_id', data.get('add-to-cart'));
		}
		// `add-to-cart` is the native form submit marker. Sending it to the
		// custom endpoint makes WooCommerce's form handler add the item before
		// our AJAX handler runs, resulting in a quantity of two.
		data.delete('add-to-cart');

		var actionId = button.dataset.clozCartActionId
			? getActionId(button)
			: getActionId(button, event);
		submitRequest(data, button, actionId);
	}

	document.addEventListener('click', handleLoopClick, true);
	document.addEventListener('click', function (event) {
		// Let the native button submit the form, but keep delayed-script loaders
		// from capturing and replaying the same click after the AJAX request.
		var button = event.target.closest('form.cart .single_add_to_cart_button, form.cart button[name="add-to-cart"]');
		if (!button) {
			return;
		}
		getActionId(button, event);
		event.stopImmediatePropagation();
	});
	document.addEventListener('click', function (event) {
		// Variation controls are already live; keep their clicks from starting and
		// replaying FlyingPress' non-critical script queue.
		if (event.target.closest('.product-attribute-dropdown, .product-head-variation-item')) {
			event.stopImmediatePropagation();
		}
	});
	document.addEventListener('submit', handleProductSubmit, true);
	document.addEventListener('click', function (event) {
		if (event.target.closest('.cloz-cart-toast__close')) {
			hideToast();
		}
	});
	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && getToast() && getToast().classList.contains('is-visible')) {
			hideToast();
		}
	});
})(jQuery);
