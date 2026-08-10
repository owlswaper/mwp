(function($) {
	let localize = bijanWCSingle;
	$(document).ready(function(){
		$('.product-attribute-dropdown').on('mouseenter touchenter click', function() {
			$(this).addClass('hover');
			let dropdown = $(this).find('.dropdown-items');
            dropdown.css('display', 'flex');
		});
		$('.product-attribute-dropdown').on('mouseleave touchleave', function() {
			$(this).removeClass('hover');
		});
		// Select dropdown attr
		$('.product-attribute-dropdown .dropdown-item').on('click', function(e) {
			let dropdown = $(this).closest('.dropdown');
			dropdown.find('.dropdown-current').text($(this).text())
			selectAttr(dropdown.attr('data-attr'), $(this).attr('data-value'));
			dropdown.find('.dropdown-items').hide();
			dropdown.removeClass('hover');
			e.stopPropagation();
		})
		// Select color attr
		$('.product-head-variation-item').on('click', function() {
			$(this).siblings('.selected').removeClass('selected');
			$(this).addClass('selected');
			selectAttr($(this).closest('.product-head-variation').attr('data-attr'), $(this).attr('data-value'));
		})

		// Remove unselectable items from selects
		function checkVariationItems() {
			$('.product-attribute-dropdown .dropdown-item').each(function() {
				let attr = $(this).closest('.dropdown').attr('data-attr');
				if( !$(`select[id="${attr}"] option[value="${$(this).attr('data-value')}"]`).length ) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});
			$('.product-head-variation-item').each(function() {
				let attr = $(this).closest('.product-head-variation').attr('data-attr');
				if( !$(`select[id="${attr}"] option[value="${$(this).attr('data-value')}"]`).length ) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});
		}
		$(document).on('woocommerce_update_variation_values', checkVariationItems);

		function selectAttr( attrName, value ) {
			$(`select[id="${attrName}"]`).val(value).trigger('change');
		}

		$('form.variations_form.cart').on('found_variation', function(e, variation) {
			// Change image in slider after selected the attribute
			let slideIndex = $(`.product-thumb-slider .swiper-slide[data-id="${variation.image_id}"]`).index(),
				mainSlider = $('.product-main-slider')[0].swiper;
			if( mainSlider ) {
				mainSlider.slideTo(slideIndex);
			}

			// Set availability html
			let availabilityHtml = variation.availability_html;
			if(!availabilityHtml) {
				availabilityHtml = '<div class="stock instock">' + localize.i18n.instock + '</div>';
			}
			$('#product-head-stock-status').html(availabilityHtml);

			checkVariationItems();
		})

		$('.product-featured-attributes-link').on('click', function(e) {
			e.preventDefault();
			$('#tab-title-additional_information a').click();
			document.getElementById("tab-title-additional_information").scrollIntoView();
		})

		$(document).on('click', '.product-thumb-slider a', function(e) {
			e.preventDefault();
		})
	});
})(jQuery);

function checkSliders() {
	const localize = bijanWCSingle;
	let thumbsOptions, mainOptions;
	let thumbSliders = jQuery('.product-thumb-slider:visible'),
		mainSliders = jQuery('.product-main-slider:visible'),
		sliderDirection = thumbSliders.closest( '.bijan-product-gallery-thumb-top, .bijan-product-gallery-thumb-bottom' ).length ? 'horizontal' : 'vertical';
	if( window.innerWidth > 768 ) {
		thumbsOptions = {
			loop: false,
			direction: sliderDirection,
			spaceBetween: 12,
			slidesPerView: parseInt(thumbSliders[0].closest('.images').getAttribute('data-columns')),
			watchSlidesProgress: true,
			mousewheel: true,
			freeMode: true,
			scrollbar: {
				el: ".swiper-scrollbar",
			}
		},
		mainOptions = {
			loop: true,
			direction: sliderDirection,
			spaceBetween: 12,
			slidesPerView: 1,
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
			thumbs: {},
		};
	} else {
		thumbsOptions = {
			loop: true,
			direction: 'horizontal',
			spaceBetween: 12,
			slidesPerView: 'auto',
			watchSlidesProgress: true,
			freeMode: true,
			scrollbar: {
				el: ".swiper-scrollbar",
			}
		},
		mainOptions = {
			loop: true,
			direction: 'horizontal',
			slidesPerView: 1,
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
			thumbs: {},
		};
	}

	for( let sliderIndex = 0; sliderIndex <= thumbSliders.length; sliderIndex++ ) {
		let thumbSlider = thumbSliders[sliderIndex],
			mainSlider = mainSliders[sliderIndex];
		if( typeof thumbSlider != 'undefined' && typeof thumbSlider.swiper != 'undefined' ) {
			thumbSlider.swiper.destroy( true, true );
		}
		if( typeof mainSlider != 'undefined' && typeof mainSlider.swiper != 'undefined' ) {
			mainSlider.swiper.destroy( true, true );
		}	
		if( !jQuery(thumbSlider).is(":visible") ) continue;
		thumbSlider = new Swiper(thumbSlider, thumbsOptions);
		mainOptions = {...mainOptions};
		mainOptions.thumbs = {
			swiper: thumbSlider
		};

		if(localize.lightbox === '1') {
			let lightGalleryConfig = {
				zoomFromOrigin: true,
				selector: 'a',
				plugins: [lgZoom, lgVideo],
				videojs: true,
				download: localize.lightboxDownload === '1',
				thumbnail: localize.lightboxThumb === '1',
				fullScreen: localize.lightboxFullscreen === '1',
				rotateLeft: localize.lightboxRotate === '1',
				rotateRight: localize.lightboxRotate === '1',
			};
			if(lightGalleryConfig.thumbnail) {
				lightGalleryConfig.plugins.push(lgThumbnail);
			}
			if(lightGalleryConfig.fullScreen) {
				lightGalleryConfig.plugins.push(lgFullscreen);
			}
			if(lightGalleryConfig.rotateLeft) {
				lightGalleryConfig.plugins.push(lgRotate);
			}
			mainOptions.on = {
				init: function() {
					lightGallery(mainSlider, lightGalleryConfig)
				}
			};
		}

		new Swiper(mainSlider, mainOptions);
	}
}
document.addEventListener("DOMContentLoaded", checkSliders);
window.matchMedia(`(min-width: 768px)`).addEventListener('change', checkSliders)