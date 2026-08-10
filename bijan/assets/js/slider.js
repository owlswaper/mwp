document.addEventListener("DOMContentLoaded", () => {
	const slidersElement = document.getElementsByClassName('bijan-slider-wrap');
	if (slidersElement.length) {
		const breakpoints = {
			desktop: 1201,
			tablet: 769,
			mobile: 0,
		};

		const defaultOptions = {
			direction: 'horizontal',
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
		};

		const replaceableDeviceOptions = ['direction']; // Replace some options that need to used in reInit mode

		const prepareAutoplay = (options) => {
			if (options?.autoplay?.delay && !isNaN(options.autoplay.delay)) {
				options.autoplay.delay *= 1000;
			} else {
				delete options.autoplay;
			}
			return options;
		};

		const disableSlider = (element) => {
			element.swiper?.destroy(true, true);

			element.classList.remove('swiper');
			element.querySelectorAll('.swiper-wrapper, .swiper-slide').forEach((el) => {
				el.classList.remove('swiper-wrapper', 'swiper-slide');
			});

			const beforeStyles = element.getAttribute('data-before-styles');
			if (beforeStyles) {
				element.setAttribute('style', beforeStyles);
			}
		};

		const checkSliderSettings = () => {			
			Array.from(slidersElement).forEach((element) => {		
				const elementSettings = (() => {
					try {
						return JSON.parse(element.getAttribute('data-settings')) || {};
					} catch {
						return null;
					}
				})();

				if (!elementSettings) return;

				const currentDevice = (() => {
					const width = window.innerWidth;
					if (width >= breakpoints.tablet && width < breakpoints.desktop) return 'tablet';
					if (width >= breakpoints.mobile && width < breakpoints.tablet) return 'mobile';
					return 'desktop';
				})();

				let options = { ...defaultOptions, ...(elementSettings.slider || {}) };
				prepareAutoplay(options);

				// Check if has 'on' events, convert to functions
				if( typeof options.on != 'undefined' ) {
					for( const [event, func] of Object.entries(options.on) ) {
						if( typeof func == 'string' && func.trim().length > 0 ) {
						const functionParts = func.split('.');
						let functionRef = window;
						functionParts.forEach(part => {
							functionRef = functionRef?.[part];
						});
						if( typeof functionRef == 'function' ) {
							options.on[event] = functionRef;
						} else {
							delete options.on[event];
						}
						} else if( typeof func != 'function' ) {
						delete options.on[event];
						}
					}
				}

				for (const [device, size] of Object.entries(breakpoints)) {
					const deviceSettings = elementSettings[device]?.slider;
					if (deviceSettings?.enabled) {
						options.breakpoints ??= {};
						options.breakpoints[size] = { ...deviceSettings };
						prepareAutoplay(options.breakpoints[size]);

						// Replace some options that need to used in reInit mode
						if( currentDevice == device ) {
							let needReInit = false;
							replaceableDeviceOptions.forEach(function(option) {
								if (typeof deviceSettings[option] != 'undefined') {
									needReInit = true;
									options[option] = deviceSettings[option];
								}
							})
							if( needReInit ) {
								disableSlider(element);
							}
						}
					}
				}

				const isSwiper = element.classList.contains('swiper');
				const currentSettings = options.breakpoints?.[breakpoints[currentDevice]];
				
				if (currentSettings?.enabled) {
					if (!isSwiper) {
						jQuery(element).addClass('swiper');
						jQuery(element).find('.wrapper').addClass('swiper-wrapper')
						jQuery(element).find('.slider-slide').addClass('swiper-slide')
					}

					const beforeStyles = element.getAttribute('data-before-styles');
					if (!beforeStyles && element.getAttribute('style')) {
						element.setAttribute('data-before-styles', element.getAttribute('style'));
					}

					if (!element.querySelector('.swiper-button-next')) {
						delete options.navigation;
					}

					if( typeof options.thumbs != 'undefined' ) {
						options.thumbs = {
							swiper: jQuery(element).prev()[0]
						};
					}
					
					if(typeof options.reInit != 'undefined' && options.reInit === true) {
						if( typeof element.swiper != 'undefined' ) {
							element.swiper.destroy(true, true);
						}
					}
				
					new Swiper(element, options);
				} else if (isSwiper) {
					disableSlider(element);
				}
			});
		};

		// Debounced event listeners
		window.addEventListener('load', checkSliderSettings);
		
		for(const [key, breakpoint] of Object.entries(breakpoints)) {
			window.matchMedia(`(min-width: ${breakpoint}px)`).addEventListener('change', checkSliderSettings);
		}
	}
});