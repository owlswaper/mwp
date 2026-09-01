(function ($) {
	'use strict';

	$(function () {
		const config = window.clzTracking || {};
		const $tracker = $('.clz-tracker');
		if (!$tracker.length) return;

		const $message = $tracker.find('.clz-track-message');
		const $results = $tracker.find('#clz-track-results');
		let challenge = '';
		let lastListHtml = '';

		function digits(value) {
			const map = {'۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9','٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
			return String(value || '').replace(/[۰-۹٠-٩]/g, function (digit) { return map[digit]; });
		}

		function normalizePhone(value) {
			let phone = digits(value).replace(/[^0-9]/g, '');
			if (phone.indexOf('0098') === 0) phone = phone.slice(4);
			else if (phone.indexOf('98') === 0) phone = phone.slice(2);
			if (/^9\d{9}$/.test(phone)) phone = '0' + phone;
			return /^09\d{9}$/.test(phone) ? phone : '';
		}

		function setMessage(text, type) {
			$message.removeClass('is-error is-success is-info').addClass(type ? 'is-' + type : '').text(text || '');
		}

		function responseMessage(xhr, fallback) {
			return xhr?.responseJSON?.data?.message || fallback || config.i18n?.error || 'خطایی رخ داد.';
		}

		function setLoading($form, loading) {
			const $button = $form.find('button[type="submit"]');
			if (!$button.data('label')) $button.data('label', $button.text());
			$button.prop('disabled', loading).toggleClass('is-loading', loading).text(loading ? (config.i18n?.wait || 'کمی صبر کنید…') : $button.data('label'));
		}

		function scrollResults() {
			if ($results.children().length) {
				window.setTimeout(function () { $results[0].scrollIntoView({behavior: 'smooth', block: 'start'}); }, 80);
			}
		}

		$tracker.find('[data-track-tab]').on('click', function () {
			const tab = $(this).data('track-tab');
			$tracker.find('[data-track-tab]').removeClass('is-active').attr('aria-selected', 'false');
			$(this).addClass('is-active').attr('aria-selected', 'true');
			$tracker.find('[data-track-panel]').removeClass('is-active').attr('hidden', true);
			$tracker.find('[data-track-panel="' + tab + '"]').addClass('is-active').removeAttr('hidden');
			setMessage('');
		});

		$('#clz-track-phone').on('input', function () {
			let value = digits(this.value).replace(/[^0-9+]/g, '').slice(0, 14);
			this.value = value;
		});

		$('#clz-track-otp').on('input', function () {
			this.value = digits(this.value).replace(/[^0-9]/g, '').slice(0, 6);
		});

		$('#clz-track-phone-form').on('submit', function (event) {
			event.preventDefault();
			const $form = $(this);
			const phone = normalizePhone($('#clz-track-phone').val());
			if (!phone) {
				setMessage('شماره همراه را درست وارد کنید؛ مانند ۰۹۱۲۱۲۳۴۵۶۷.', 'error');
				return;
			}
			setLoading($form, true);
			setMessage('در حال ارسال کد تأیید…', 'info');
			$.post(config.ajaxUrl, {action: 'clz_tracking_send_otp', nonce: config.nonce, phone: phone})
				.done(function (response) {
					if (!response.success) return;
					challenge = response.data.challenge;
					$form.attr('hidden', true);
					$('#clz-track-otp-form').removeAttr('hidden').find('small').text('کد برای ' + response.data.masked + ' ارسال شد.');
					$('#clz-track-otp').val('').trigger('focus');
					setMessage(response.data.message, 'success');
				})
				.fail(function (xhr) { setMessage(responseMessage(xhr), 'error'); })
				.always(function () { setLoading($form, false); });
		});

		$('#clz-track-otp-form').on('submit', function (event) {
			event.preventDefault();
			const $form = $(this);
			const otp = digits($('#clz-track-otp').val()).replace(/\D/g, '');
			if (!challenge || otp.length < 4) {
				setMessage('کد تأیید را کامل وارد کنید.', 'error');
				return;
			}
			setLoading($form, true);
			$.post(config.ajaxUrl, {action: 'clz_tracking_verify_otp', nonce: config.nonce, challenge: challenge, otp: otp})
				.done(function (response) {
					if (!response.success) return;
					lastListHtml = response.data.html;
					$results.html(response.data.html).data('access', response.data.access);
					setMessage(response.data.message, response.data.html.indexOf('clz-track-empty') === -1 ? 'success' : 'info');
					scrollResults();
				})
				.fail(function (xhr) { setMessage(responseMessage(xhr), 'error'); })
				.always(function () { setLoading($form, false); });
		});

		$tracker.on('click', '[data-track-change-phone]', function () {
			challenge = '';
			$('#clz-track-otp-form').attr('hidden', true);
			$('#clz-track-phone-form').removeAttr('hidden');
			$('#clz-track-phone').trigger('focus');
			setMessage('');
		});

		$('#clz-track-code-form').on('submit', function (event) {
			event.preventDefault();
			const $form = $(this);
			const code = digits($('#clz-track-code').val()).trim().toUpperCase();
			if (code.length < 10) {
				setMessage('کد پیگیری را کامل وارد کنید.', 'error');
				return;
			}
			setLoading($form, true);
			setMessage('در حال بررسی کد پیگیری…', 'info');
			$.post(config.ajaxUrl, {action: 'clz_tracking_lookup', nonce: config.nonce, tracking_code: code})
				.done(function (response) {
					if (!response.success) return;
					lastListHtml = '';
					$results.html(response.data.html).data('access', response.data.access);
					setMessage('سفارش شما پیدا شد.', 'success');
					scrollResults();
				})
				.fail(function (xhr) { setMessage(responseMessage(xhr), 'error'); })
				.always(function () { setLoading($form, false); });
		});

		$results.on('click', '[data-track-order]', function () {
			const $button = $(this);
			if ($button.prop('disabled')) return;
			$button.prop('disabled', true).addClass('is-loading');
			$.post(config.ajaxUrl, {action: 'clz_tracking_detail', nonce: config.nonce, order_id: $button.data('track-order'), access: $button.data('track-access')})
				.done(function (response) {
					if (response.success) {
						$results.html(response.data.html);
						$results[0].scrollIntoView({behavior: 'smooth', block: 'start'});
					}
				})
				.fail(function (xhr) { setMessage(responseMessage(xhr), 'error'); })
				.always(function () { $button.prop('disabled', false).removeClass('is-loading'); });
		});

		$results.on('click', '[data-track-back]', function () {
			if (lastListHtml) {
				$results.html(lastListHtml);
			} else {
				$results.empty();
				$('.clz-track-shell')[0].scrollIntoView({behavior: 'smooth', block: 'center'});
			}
		});

		$results.on('click', '[data-track-reset]', function () {
			challenge = '';
			lastListHtml = '';
			$results.empty();
			$('#clz-track-otp-form').attr('hidden', true);
			$('#clz-track-phone-form').removeAttr('hidden');
			$('#clz-track-phone, #clz-track-otp, #clz-track-code').val('');
			setMessage('');
			$('.clz-track-shell')[0].scrollIntoView({behavior: 'smooth', block: 'center'});
		});
	});
})(jQuery);
