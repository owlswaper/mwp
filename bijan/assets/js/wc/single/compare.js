(function($) {
	$(document).ready(function(){
		let bijanCookie = Cookies.noConflict()
		// Close
		function closePopup() {
			$('#compare-popup-overlay, #compare-popup').fadeOut();
		}
		$('.compare-popup-close, #compare-popup-overlay').on('click', closePopup);

		$(document).on('keydown', function(e) {
			if (e.key === "Escape" || e.keyCode === 27) {
				closePopup();
			}
		});

		let swiperConfigs = {
			direction: 'horizontal',
			autoplay: {
				delay: 3000,
			},
			loop: true,
			autoHeight: true,
			grabCursor: true,
		};
		function initPopupSwiper() {
			$('.compare-popup-product-images:not(.swiper-initialized)').each(function() {
				new Swiper(this, swiperConfigs);
			});
		}

		let processedProducts = [];
		function addProduct(productID) {
			$('#compare-popup-loading').show();
			$('#compare-popup-table-wrap').remove();
			$.ajax({
				url: bijanVars.ajaxUrl,
				type: "POST",
				data: {
					action: 'bijan_compare_popup',
					product_id: productID,
					nonce: bijanCompare.nonce
				},
				success: function(res) {
					if(res) {
						$('#compare-popup-loading').hide();						
						$('#compare-popup-result').prepend(res);
						initPopupSwiper();
					}
				}
			});
			if( !processedProducts.includes(productID) ) {
				processedProducts.push(productID);
			}
		}

		// Show
		$(document).on('click', '.show-compare-popup', function(e) {
			e.preventDefault();
			$('#compare-popup-overlay').fadeIn();

			let productID = $(this).attr('data-product-id');

			let products = (bijanCookie.get( 'bijan_compare_products' ) || "").split(',');
			if( !products.includes(productID) || !processedProducts.includes(productID) ) {
				addProduct(productID);
			}

			$('#compare-popup').fadeIn({
				start: function() {
					$(this).css('display', 'flex')
					initPopupSwiper();
				}
			});
		});

		// Remove from list
		$(document).on('click', '.compare-popup-product-remove', function() {
			let productID = $(this).attr('data-product-id'),
				$table = $(this).closest('#compare-popup-table-wrap'),
				columns = $table.attr('data-columns');
			$(`.compare-popup-attr-value[attr-product-id="${productID}"]`).remove();
			$table.attr('data-columns', columns-1).css('--columns', columns-1).css('--products', columns-2);
			
			// Remove product id from cookie
			let cookieProducts = (bijanCookie.get( 'bijan_compare_products' ) || "").split(',');
			cookieProducts = bijan.removeArrayItem( cookieProducts, productID );
			bijanCookie.set( 'bijan_compare_products', cookieProducts.join(','), {
				path: bijanCookieVars.path,
				domain: bijanCookieVars.domain,
			} );

			processedProducts = bijan.removeArrayItem(processedProducts, productID);
		})

		// Add product
		$(document).on('click', '#compare-popup-search .bijan-search-post', function(e) {
			e.preventDefault();
			addProduct($(this).attr('data-id'), );
		})
	});
})(jQuery);