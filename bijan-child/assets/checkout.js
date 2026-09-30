(function ($) {
	'use strict';

	var form = document.querySelector('form.checkout');
	if (!form) {
		return;
	}

	var fieldNames = ['billing_state', 'billing_city', 'shipping_state', 'shipping_city'];
	var memory = Object.create(null);
	var labels = Object.create(null);
	var lastObserved = Object.create(null);
	var updateTimer = null;
	var updateSafetyTimer = null;
	var observerTimer = null;
	var syncFlag = form.elements.clz_location_sync || document.getElementById('clz_location_sync');
	if (syncFlag) {
		syncFlag.value = '1';
	}

	function normalize(value) {
		return String(value || '')
			.replace(/[\u064a\u0649]/g, '\u06cc')
			.replace(/\u0643/g, '\u06a9')
			.replace(/[\u200c\u200f\u202a-\u202e]/g, ' ')
			.replace(/\s+/g, ' ')
			.trim()
			.toLowerCase();
	}

	function field(name) {
		return form.elements[name] || document.getElementById(name);
	}

	function hidden(name) {
		return form.elements['clz_' + name] || document.getElementById('clz_' + name);
	}

	function validValue(value) {
		var normalized = normalize(value);
		return normalized && normalized !== '0' && normalized !== '-1';
	}

	function selectedLabel(element) {
		if (!element) {
			return '';
		}
		if (element.tagName === 'SELECT' && element.selectedIndex >= 0) {
			return element.options[element.selectedIndex].text || '';
		}
		return element.value || '';
	}

	function writeHidden(name, value) {
		var target = hidden(name);
		if (target) {
			target.value = value || '';
		}
	}

	function remember(name, element) {
		element = element || field(name);
		if (!element || !validValue(element.value)) {
			return false;
		}

		var changed = memory[name] !== String(element.value);
		memory[name] = String(element.value);
		labels[name] = selectedLabel(element);
		writeHidden(name, memory[name]);

		if (name.slice(-5) === '_city') {
			var prefix = name.indexOf('shipping_') === 0 ? 'shipping' : 'billing';
			var stateName = prefix + '_state';
			var cityState = memory[stateName] || (field(stateName) ? field(stateName).value : '');
			writeHidden(prefix + '_city_state', cityState);
		}

		return changed;
	}

	function rememberAll() {
		fieldNames.forEach(function (name) {
			remember(name);
		});
	}

	function findOption(select, savedValue, savedLabel) {
		var wantedValue = normalize(savedValue);
		var wantedLabel = normalize(savedLabel || savedValue);
		var options = Array.prototype.slice.call(select.options || []);

		return options.find(function (option) {
			return validValue(option.value) && normalize(option.value) === wantedValue;
		}) || options.find(function (option) {
			return validValue(option.value) && wantedLabel && normalize(option.text) === wantedLabel;
		}) || null;
	}

	function syncSelect2Display(select) {
		if (!select || select.tagName !== 'SELECT' || validValue(select.value)) {
			return false;
		}

		var container = select.nextElementSibling;
		var rendered = container && container.querySelector('.select2-selection__rendered');
		var visibleLabel = rendered ? (rendered.getAttribute('title') || rendered.textContent) : '';
		var option = visibleLabel ? findOption(select, visibleLabel, visibleLabel) : null;
		if (!option) {
			return false;
		}

		select.value = option.value;
		$(select).trigger('change.select2');
		return true;
	}

	function restore(name) {
		var element = field(name);
		var savedValue = memory[name] || (hidden(name) ? hidden(name).value : '');
		if (!element || !validValue(savedValue)) {
			return false;
		}

		if (name.slice(-5) === '_city') {
			var prefix = name.indexOf('shipping_') === 0 ? 'shipping' : 'billing';
			var stateElement = field(prefix + '_state');
			var savedCityState = hidden(prefix + '_city_state');
			if (stateElement && savedCityState && validValue(savedCityState.value) && stateElement.value !== savedCityState.value) {
				return false;
			}
		}

		if (validValue(element.value)) {
			remember(name, element);
			return false;
		}

		if (element.tagName === 'SELECT') {
			var option = findOption(element, savedValue, labels[name]);
			if (!option) {
				return false;
			}
			element.value = option.value;
			$(element).trigger('change.select2');
		} else {
			element.value = savedValue;
		}

		remember(name, element);
		return true;
	}

	function restoreAll() {
		fieldNames.forEach(function (name) {
			var element = field(name);
			if (element) {
				syncSelect2Display(element);
			}
			restore(name);
		});
	}

	function requestSingleUpdate() {
		window.clearTimeout(updateTimer);
		updateTimer = window.setTimeout(function () {
			if (!document.body.classList.contains('clz-checkout-updating') && !document.body.classList.contains('clz-checkout-submitting')) {
				$(document.body).trigger('update_checkout');
			}
		}, 180);
	}

	function observeAutofill() {
		var changed = false;
		fieldNames.forEach(function (name) {
			var element = field(name);
			if (!element) {
				return;
			}
			var value = String(element.value || '');
			if (lastObserved[name] !== value) {
				if (typeof lastObserved[name] !== 'undefined' && validValue(value)) {
					var remembered = remember(name, element);
					changed = remembered || changed;
					if (remembered) {
						// Notify WooCommerce and province/city plugins when the browser
						// changed a property without emitting its own change event.
						$(element).trigger('change');
					}
				}
				lastObserved[name] = value;
			}
		});
		if (changed) {
			requestSingleUpdate();
		}
	}

	document.addEventListener('input', function (event) {
		if (event.target && fieldNames.indexOf(event.target.name) !== -1) {
			remember(event.target.name, event.target);
			lastObserved[event.target.name] = String(event.target.value || '');
		}
	}, true);

	document.addEventListener('change', function (event) {
		var name = event.target && event.target.name;
		if (fieldNames.indexOf(name) === -1) {
			return;
		}

		if (name.slice(-6) === '_state') {
			var prefix = name.indexOf('shipping_') === 0 ? 'shipping' : 'billing';
			var previousState = memory[name] || '';
			if (previousState && validValue(event.target.value) && previousState !== event.target.value && event.isTrusted) {
				delete memory[prefix + '_city'];
				delete labels[prefix + '_city'];
				writeHidden(prefix + '_city', '');
				writeHidden(prefix + '_city_state', event.target.value);
			}
		}

		if (!validValue(event.target.value) && event.isTrusted) {
			delete memory[name];
			delete labels[name];
			writeHidden(name, '');
			lastObserved[name] = String(event.target.value || '');
			return;
		}

		remember(name, event.target);
		lastObserved[name] = String(event.target.value || '');
	}, true);

	form.addEventListener('submit', function () {
		restoreAll();
		rememberAll();
	}, true);

	$(form).on('checkout_place_order', function () {
		restoreAll();
		rememberAll();
		document.body.classList.add('clz-checkout-submitting');
		return true;
	});

	$(document.body)
		.on('update_checkout', function () {
			rememberAll();
			document.body.classList.add('clz-checkout-updating');
			window.clearTimeout(updateSafetyTimer);
			updateSafetyTimer = window.setTimeout(function () {
				document.body.classList.remove('clz-checkout-updating');
			}, 20000);
		})
		.on('updated_checkout', function () {
			window.clearTimeout(updateSafetyTimer);
			document.body.classList.remove('clz-checkout-updating');
			window.setTimeout(function () {
				restoreAll();
				rememberAll();
			}, 0);
		})
		.on('checkout_error', function () {
			window.clearTimeout(updateSafetyTimer);
			document.body.classList.remove('clz-checkout-updating', 'clz-checkout-submitting');
			window.setTimeout(function () {
				var invalid = form.querySelector('.woocommerce-invalid-required-field select, .woocommerce-invalid-required-field input, .woocommerce-invalid-required-field textarea');
				if (!invalid) {
					return;
				}
				invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
				if (invalid.classList.contains('select2-hidden-accessible')) {
					$(invalid).select2('open');
				} else {
					invalid.focus({ preventScroll: true });
				}
			}, 80);
		});

	window.addEventListener('pageshow', function () {
		window.setTimeout(function () {
			restoreAll();
			rememberAll();
		}, 0);
	});

	// Browser autofill can update DOM properties without firing input/change.
	// A short bounded observer catches that case without a permanent polling cost.
	rememberAll();
	fieldNames.forEach(function (name) {
		var element = field(name);
		lastObserved[name] = element ? String(element.value || '') : '';
	});
	var checks = 0;
	observerTimer = window.setInterval(function () {
		observeAutofill();
		checks += 1;
		if (checks >= 15) {
			window.clearInterval(observerTimer);
		}
	}, 200);
})(jQuery);
