(function ($) {
	'use strict';

	$(function () {
		const config = window.bijanCommunity || {};
		const $modal = $('#bc-community-modal');
		let selectedFiles = [];
		let $modalTrigger = $();
		let closeTimer = null;

		function activateTab(tab) {
			const safeTab = tab === 'questions' ? 'questions' : 'reviews';
			$('.bc-tab').removeClass('is-active').attr('aria-selected', 'false');
			$('.bc-tab[data-tab="' + safeTab + '"]').addClass('is-active').attr('aria-selected', 'true');
			$('.bc-panel').removeClass('is-active').attr('hidden', true);
			$('.bc-panel[data-panel="' + safeTab + '"]').addClass('is-active').removeAttr('hidden');
		}

		$('.bc-tab').on('click', function () {
			activateTab($(this).data('tab'));
		});

		const initialTab = new URLSearchParams(window.location.search).get('community_tab');
		if (initialTab) {
			activateTab(initialTab);
		}

		function setFormView(type) {
			$('.bc-form-view').attr('hidden', true);
			$('.bc-form-view[data-form-view="' + type + '"]').removeAttr('hidden');
		}

		function openModal(type, trigger) {
			if (!$modal.length) return;
			window.clearTimeout(closeTimer);
			$modalTrigger = $(trigger);
			setFormView(type);
			$modal.removeAttr('hidden').attr('aria-hidden', 'false');
			$('body').addClass('bc-modal-open');
			window.setTimeout(function () {
				$modal.addClass('is-open');
				$modal.find('.bc-modal-close').trigger('focus');
			}, 10);
		}

		function closeModal() {
			if (!$modal.length || $modal.attr('hidden')) return;
			window.clearTimeout(closeTimer);
			$modal.removeClass('is-open').attr('aria-hidden', 'true');
			$('body').removeClass('bc-modal-open');
			closeTimer = window.setTimeout(function () {
				$modal.attr('hidden', true);
				if ($modalTrigger.length) $modalTrigger.trigger('focus');
			}, 220);
		}

		$('[data-community-open]').on('click', function () {
			openModal($(this).data('community-open'), this);
		});
		$('[data-community-close]').on('click', function (event) {
			event.preventDefault();
			closeModal();
		});
		$(document).on('keydown', function (event) {
			if (event.key === 'Escape' && $modal.hasClass('is-open')) {
				closeModal();
				return;
			}
			if (event.key !== 'Tab' || !$modal.hasClass('is-open')) return;
			const $focusable = $modal.find('button:visible, input:visible, textarea:visible, [tabindex]:visible').filter(':not([disabled]):not([tabindex="-1"])');
			if (!$focusable.length) return;
			const first = $focusable[0];
			const last = $focusable[$focusable.length - 1];
			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});

		function updateStars() {
			const value = Number($('.bc-star-picker input:checked').val()) || 5;
			$('.bc-star-picker label').each(function () {
				$(this).toggleClass('is-active', Number($(this).data('score')) <= value);
			});
			$('.bc-rating-field output strong').text(value.toLocaleString('fa-IR'));
		}
		$('.bc-star-picker input').on('change', updateStars);
		updateStars();

		function refreshPointGroup($group) {
			const count = $group.find('.bc-point-input').length;
			$group.toggleClass('has-points', count > 0);
			$group.find('[data-point-add]').prop('disabled', count >= 6);
		}

		function addPoint(type) {
			const $group = $('[data-point-group="' + type + '"]');
			const $list = $group.find('[data-point-list]');
			if (!$group.length || $list.children().length >= 6) return;
			const label = type === 'strengths' ? 'نقطه قوت' : 'نقطه ضعف';
			const placeholder = type === 'strengths' ? 'مثلاً کیفیت ساخت خوب' : 'مثلاً بسته‌بندی معمولی';
			const $row = $('<div class="bc-point-input"><input type="text" maxlength="120"><button type="button"><span aria-hidden="true">×</span></button></div>');
			$row.find('input').attr({ name: type + '[]', placeholder: placeholder, 'aria-label': label });
			$row.find('button').attr('aria-label', 'حذف ' + label);
			$list.append($row);
			refreshPointGroup($group);
			$row.find('input').trigger('focus');
		}

		$('[data-point-add]').on('click', function () { addPoint($(this).data('point-add')); });
		$('.bc-points-editor').on('click', '.bc-point-input button', function () {
			const $group = $(this).closest('[data-point-group]');
			$(this).closest('.bc-point-input').remove();
			refreshPointGroup($group);
		});

		$('.bc-field textarea').on('input', function () {
			const $counter = $(this).siblings('.bc-counter');
			if ($counter.length) $counter.find('b').text(this.value.length.toLocaleString('fa-IR'));
		});

		function showMessage($form, message, success) {
			$form.find('.bc-form-message').toggleClass('is-success', !!success).toggleClass('is-error', !success).text(message).attr('tabindex', '-1').trigger('focus');
		}

		function renderPreviews() {
			const $preview = $('.bc-image-preview').empty();
			selectedFiles.forEach(function (file, index) {
				const url = URL.createObjectURL(file);
				const $item = $('<div class="bc-preview-item"><img alt=""><button type="button" aria-label="' + (config.i18n?.removeImage || 'حذف تصویر') + '">×</button></div>');
				$item.find('img').attr('src', url).on('load', function () { URL.revokeObjectURL(url); });
				$item.find('button').on('click', function () {
					selectedFiles.splice(index, 1);
					renderPreviews();
				});
				$preview.append($item);
			});
		}

		$('#bc-review-images').on('change', function () {
			const incoming = Array.from(this.files || []);
			const allowed = ['image/jpeg', 'image/png', 'image/webp'];
			if (selectedFiles.length + incoming.length > Number(config.maxImages || 4)) {
				showMessage($('#bc-review-form'), config.i18n?.fileCount || 'تعداد تصاویر بیش از حد مجاز است.', false);
				this.value = '';
				return;
			}
			for (const file of incoming) {
				if (!allowed.includes(file.type)) {
					showMessage($('#bc-review-form'), config.i18n?.fileType || 'فرمت تصویر مجاز نیست.', false);
					this.value = '';
					return;
				}
				if (file.size > Number(config.maxSize || 5242880)) {
					showMessage($('#bc-review-form'), config.i18n?.fileSize || 'حجم تصویر بیش از حد مجاز است.', false);
					this.value = '';
					return;
				}
			}
			selectedFiles = selectedFiles.concat(incoming);
			this.value = '';
			renderPreviews();
		});

		function submitForm($form) {
			const button = $form.find('.bc-submit');
			const originalText = button.text();
			const formData = new FormData($form[0]);
			if ($form.attr('id') === 'bc-review-form') {
				formData.delete('images[]');
				selectedFiles.forEach(function (file) { formData.append('images[]', file, file.name); });
			}
			button.prop('disabled', true).addClass('is-loading').text(config.i18n?.sending || 'در حال ارسال…');
			$form.find('.bc-form-message').removeClass('is-error is-success').empty();

			$.ajax({
				url: config.ajaxUrl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json'
			}).done(function (response) {
				const message = response?.data?.message || response?.data?.msg || (response.success ? 'با موفقیت ثبت شد.' : 'ثبت اطلاعات انجام نشد.');
				showMessage($form, message, !!response.success);
				if (response.success) {
					$form[0].reset();
					selectedFiles = [];
					renderPreviews();
					$form.find('.bc-counter b').text('۰');
					$form.find('.bc-point-inputs').empty();
					$form.find('[data-point-group]').each(function () { refreshPointGroup($(this)); });
					updateStars();
				}
			}).fail(function (xhr) {
				const data = xhr.responseJSON?.data;
				if (data?.login_required) {
					closeModal();
					$('a.showlogin').first().trigger('click');
					return;
				}
				showMessage($form, data?.message || config.i18n?.network || 'خطایی رخ داد.', false);
			}).always(function () {
				button.prop('disabled', false).removeClass('is-loading').text(originalText);
			});
		}

		$('#bc-review-form, #bc-question-form').on('submit', function (event) {
			event.preventDefault();
			if (!this.reportValidity()) return;
			submitForm($(this));
		});

		$('.bc-helpful[data-comment-id]').on('click', function () {
			const $button = $(this);
			if ($button.prop('disabled')) return;
			$button.prop('disabled', true);
			$.post(config.ajaxUrl, {
				action: 'bijan_toggle_review_helpful',
				nonce: config.nonce,
				comment_id: $button.data('comment-id')
			}).done(function (response) {
				if (!response.success) return;
				$button.toggleClass('is-active', response.data.active).attr('aria-pressed', response.data.active ? 'true' : 'false');
				$button.find('b').text(Number(response.data.count).toLocaleString('fa-IR'));
			}).always(function () { $button.prop('disabled', false); });
		});

		$('[data-review-image]').on('click', function (event) {
			event.preventDefault();
			const trigger = this;
			let isClosing = false;
			const $viewer = $('<div class="bc-image-viewer" role="dialog" aria-modal="true" aria-label="تصویر ارسالی کاربر"><button type="button" aria-label="بستن">×</button><img alt="تصویر ارسالی کاربر"></div>');
			$viewer.find('img').attr('src', this.href);
			$('body').append($viewer).addClass('bc-modal-open');
			window.setTimeout(function () { $viewer.addClass('is-open').find('button').trigger('focus'); }, 10);
			const closeViewer = function () {
				if (isClosing) return;
				isClosing = true;
				$viewer.removeClass('is-open');
				$('body').removeClass('bc-modal-open');
				$(document).off('keydown.bcImageViewer');
				window.setTimeout(function () { $viewer.remove(); $(trigger).trigger('focus'); }, 180);
			};
			$viewer.on('click', function (clickEvent) {
				if (clickEvent.target === this || $(clickEvent.target).is('button')) {
					closeViewer();
				}
			});
			$(document).on('keydown.bcImageViewer', function (keyEvent) {
				if (keyEvent.key === 'Escape') closeViewer();
			});
		});

		// Lightweight native horizontal scroller for related products.
		$('.bc-smart-related').each(function () {
			const $section = $(this);
			const viewport = $section.find('.bc-related-viewport')[0];
			if (!viewport) return;
			const move = function (direction) {
				viewport.scrollBy({ left: direction * Math.max(260, viewport.clientWidth * 0.78), behavior: 'smooth' });
			};
			$section.find('[data-related-next]').on('click', function () { move(-1); });
			$section.find('[data-related-prev]').on('click', function () { move(1); });
		});

		// Modern variation state layer, synchronized with WooCommerce's native selects.
		$('form.variations_form.cart').each(function () {
			const $form = $(this);
			const $button = $form.find('.single_add_to_cart_button');
			const originalLabel = $.trim($button.text()) || 'افزودن به سبد خرید';
			const $selectors = $('.product-head-variations').first();
			if (!$selectors.length || !$button.length) return;

			$selectors.addClass('bc-variation-picker');
			$selectors.before('<div class="bc-variation-guide" role="status" aria-live="polite"><span class="bc-variation-guide-icon">◇</span><div><strong>گزینه‌های محصول را انتخاب کنید</strong><small>برای مشاهده قیمت و موجودی، همه موارد را مشخص کنید.</small></div><button type="button" class="bc-reset-variations" hidden>انتخاب دوباره</button></div>');
			const $guide = $selectors.prev('.bc-variation-guide');

			function selectFor($group) {
				const attr = String($group.data('attr') || '');
				return $form.find('select').filter(function () { return this.id === attr || this.name === 'attribute_' + attr; }).first();
			}

			function syncItems() {
				$selectors.find('.product-head-variation').each(function () {
					const $group = $(this);
					const $select = selectFor($group);
					if (!$select.length) return;
					const value = String($select.val() || '');
					const label = $.trim($group.find('.product-head-variation-label-text').text()).replace(/[:：]\s*$/, '');

					$group.toggleClass('has-value', !!value);
					$group.find('.product-head-variation-item, .dropdown-item').each(function () {
						const $item = $(this);
						const itemValue = String($item.data('value'));
						const $option = $select.find('option').filter(function () { return this.value === itemValue; });
						const unavailable = !$option.length || $option.prop('disabled');
						$item.toggleClass('is-unavailable', unavailable).attr('aria-disabled', unavailable ? 'true' : 'false');
						if ($item.hasClass('product-head-variation-item')) {
							$item.toggleClass('selected', itemValue === value).attr({ role: 'radio', 'aria-checked': itemValue === value ? 'true' : 'false', tabindex: unavailable ? '-1' : '0' });
						} else {
							$item.toggleClass('is-selected', itemValue === value).attr({ role: 'option', 'aria-selected': itemValue === value ? 'true' : 'false', tabindex: unavailable ? '-1' : '0' });
						}
					});

					const selectedText = value ? $select.find('option:selected').text() : label;
					$group.find('.dropdown-current').text(selectedText || label);
				});
			}

			function selectionState() {
				const missing = [];
				$form.find('select[name^="attribute_"]').each(function () {
					if (!this.value) missing.push($.trim($(this).closest('tr').find('label').text()) || 'گزینه');
				});
				return missing;
			}

			function showSelectionNeeded() {
				const missing = selectionState();
				const message = missing.length ? 'لطفاً ' + missing.join(' و ') + ' را انتخاب کنید' : 'این ترکیب موجود نیست؛ انتخاب دیگری امتحان کنید';
				$button.text(message).addClass('bc-needs-selection');
				$guide.removeClass('is-ready is-unavailable').addClass('is-waiting');
				$guide.find('strong').text(missing.length ? 'انتخاب محصول کامل نشده' : 'این ترکیب ناموجود است');
				$guide.find('small').text(message);
				const hasSelection = $form.find('select[name^="attribute_"]').filter(function () { return !!this.value; }).length > 0;
				$guide.find('.bc-reset-variations').prop('hidden', !hasSelection);
			}

			function setReady(variation) {
				const purchasable = variation && variation.is_purchasable && variation.is_in_stock && variation.variation_is_active !== false;
				if (!purchasable) {
					$button.text('این ترکیب در حال حاضر ناموجود است').addClass('bc-needs-selection');
					$guide.removeClass('is-ready is-waiting').addClass('is-unavailable');
					$guide.find('strong').text('ناموجود');
					$guide.find('small').text('ترکیب دیگری از گزینه‌ها را انتخاب کنید.');
				} else {
					$button.text(originalLabel).removeClass('bc-needs-selection');
					$guide.removeClass('is-waiting is-unavailable').addClass('is-ready');
					$guide.find('strong').text('انتخاب شما موجود است');
					$guide.find('small').text('قیمت و موجودی بر اساس گزینه‌های انتخاب‌شده به‌روزرسانی شد.');
				}
				$guide.find('.bc-reset-variations').prop('hidden', false);
			}

			$selectors.find('.product-head-variation-item, .dropdown-item').on('keydown', function (event) {
				if ((event.key === 'Enter' || event.key === ' ') && !$(this).hasClass('is-unavailable')) {
					event.preventDefault();
					$(this).trigger('click');
				}
			});

			$selectors.find('.dropdown-current-wrap').attr({ role: 'button', tabindex: '0', 'aria-expanded': 'false' }).on('click keydown', function (event) {
				if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
				event.preventDefault();
				const $dropdown = $(this).closest('.dropdown');
				const opening = !$dropdown.hasClass('bc-dropdown-open');
				$('.product-attribute-dropdown').removeClass('bc-dropdown-open');
				$dropdown.toggleClass('bc-dropdown-open', opening);
				$(this).attr('aria-expanded', opening ? 'true' : 'false');
			});

			document.addEventListener('click', function (event) {
				const unavailable = event.target.closest('.bc-variation-picker .is-unavailable');
				if (unavailable) {
					event.preventDefault();
					event.stopImmediatePropagation();
					return;
				}
				if (!event.target.closest('.product-attribute-dropdown')) {
					$('.product-attribute-dropdown').removeClass('bc-dropdown-open').find('.dropdown-current-wrap').attr('aria-expanded', 'false');
				}
				if (event.target.closest('.product-attribute-dropdown .dropdown-item')) {
					window.setTimeout(function () { $('.product-attribute-dropdown').removeClass('bc-dropdown-open'); }, 0);
				}
			}, true);

			$form.on('change', 'select[name^="attribute_"]', function () {
				window.setTimeout(function () { syncItems(); if (!$form.find('input.variation_id').val()) showSelectionNeeded(); }, 20);
			});
			$form.on('woocommerce_update_variation_values', function () {
				window.setTimeout(syncItems, 20);
			});
			$form.on('found_variation show_variation', function (event, variation) { syncItems(); setReady(variation); });
			$form.on('hide_variation reset_data', function () { syncItems(); showSelectionNeeded(); });
			$guide.find('.bc-reset-variations').on('click', function () {
				$form.find('.reset_variations').trigger('click');
				$form.find('select[name^="attribute_"]').val('').trigger('change');
			});
			$form.on('submit', function (event) {
				if (!Number($form.find('input.variation_id').val())) {
					event.preventDefault();
					showSelectionNeeded();
					$guide.addClass('bc-attention');
					window.setTimeout(function () { $guide.removeClass('bc-attention'); }, 500);
				}
			});

			syncItems();
			if (selectionState().length) {
				showSelectionNeeded();
			} else {
				$guide.find('strong').text('در حال بررسی موجودی…');
				$form.trigger('check_variations');
			}
		});
	});
})(jQuery);
