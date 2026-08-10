(function($) {
	$(document).ready(function(){
		$(document).on('click', '.quantity button', function(e) {
			e.preventDefault();
			let $this = $(this),
				qtyInput = $this.siblings('input.qty'),
				qty = parseInt(qtyInput.val()),
				min = parseInt(qtyInput.attr('min')),
				max = parseInt(qtyInput.attr('max'));
			if($this.hasClass('plus-quantity')) {
				qty++;
			} else {
				qty--;
			}
			if(typeof min != 'undefined' && min > qty) {
				qty = min;
			}
			if(typeof max != 'undefined' && max < qty) {
				qty = max;
			}
			qtyInput.val(qty).trigger('change');
		});

		// Select sort item
		$('.woocommerce-ordering .sort-item').on('click', function(e) {
			e.preventDefault();			
			$(this).siblings('.orderby').val($(this).attr('data-sort'));
			$(this).closest('form').submit();
		})

		// Set qty in mini cart
		var miniCartQTYTimer;
		$(document).on('click', '.mini-cart-product-bottom .quantity button', function() {
			clearTimeout(miniCartQTYTimer);
			
			miniCartQTYTimer = setTimeout(() => {
				let loading = $('.mini-cart-loading');
				$('.mini-cart-loading').fadeIn({
					start: () => {
						$(loading).css('display', 'flex');
					}
				});
				
				let wrap = $(this).closest('.mini-cart-product-bottom');

				$.ajax({
					url: bijanVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'bijan_update_mini_cart',
						nonce: wrap.attr('data-nonce'),
						item_key: wrap.attr('data-key'),
						item_qty: $(this).siblings('.qty').val()
					},
					success: function(res) {
						if(res) {
							if( $('body').hasClass('woocommerce-cart') ) {
								location.reload();
							}
							$.each( res.fragments, function( key, value ) {
								jQuery(key).replaceWith(value);
							});
							sessionStorage.setItem( "wc_fragments", JSON.stringify( res.fragments ) );
            				sessionStorage.setItem( "wc_cart_hash", res.cart_hash );
							$('body').trigger( 'wc_fragment_refresh' );
							loading.fadeOut();
						}
					}
				});
			}, 500);
		});

		// Price filter submit
		var priceFilterTimer;
		$('.price_slider').on('slidechange', function() {
			clearTimeout(priceFilterTimer);
			priceFilterTimer = setTimeout(() => {
				$(this).closest('form').submit();
			}, 1000);
		});

		// Change color filter display
		if($('.bijan-filter-color-wrap').length) {
			$('.bijan-filter-color-wrap').closest('ul').addClass('bijan-filter-color-list');
		}

		//***** Timer *****//
		$('.product-timer').each(function() {
			let $this = $(element),
				endTime = new Date(parseInt($this.attr('data-time'))*1000).getTime(),
				$days = $this.find('.product-timer-days .product-timer-item-value'),
				$hours = $this.find('.product-timer-hours .product-timer-item-value'),
				$minutes = $this.find('.product-timer-minutes .product-timer-item-value'),
				$seconds = $this.find('.product-timer-seconds .product-timer-item-value');

			let timerInterval;
			function updateTimer() {
				let now = new Date().getTime();
				let distance = endTime - now;

				if (distance <= 0) {
					clearInterval(timerInterval);
					$days.text('0');
					$hours.text('00');
					$minutes.text('00');
					$seconds.text('00');
					return;
				}

				let days = Math.floor(distance / (1000 * 60 * 60 * 24));
				let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				let seconds = Math.floor((distance % (1000 * 60)) / 1000);

				$days.text(days);
				$hours.text(hours.toString().padStart(2,'0'));
				$minutes.text(minutes.toString().padStart(2,'0'));
				$seconds.text(seconds.toString().padStart(2,'0'));
			}
			if( timerInterval ) {
				clearInterval(timerInterval);
			}
			updateTimer();
			timerInterval = setInterval(updateTimer, 1000);
		})
	});
})(jQuery);