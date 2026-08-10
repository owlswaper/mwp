(function($) {
	let handleResize = function() {
		let width = window.innerWidth,
			body = $('body'),
			classes = {desktop: 'desktop', tablet: 'tablet', mobile: 'mobile'};

		if(width > 1200) {
			body.removeClass([classes.tablet, classes.mobile]).addClass(classes.desktop);
			body.attr('data-device', 'desktop');
		} else if( width <= 1200 && width > 768 ) {
			body.removeClass([classes.desktop, classes.mobile]).addClass(classes.tablet);
			body.attr('data-device', 'tablet');
		} else {
			body.removeClass([classes.desktop, classes.tablet]).addClass(classes.mobile);
			body.attr('data-device', 'mobile');
		}

		if (body.hasClass('sticky-header')) {
			let headerHeight = $('#header-container').outerHeight();
			if(headerHeight) {
				if( $('.header-banner-sticky').length && window.innerWidth > 1200 ) {
					headerHeight += $('.header-banner-sticky').outerHeight();
				}
				// if( $(window).scrollTop() > 10 || $('body').hasClass('scrolled') ) {
				// 	headerHeight += 32;
				// }
				body.css('--header-height', headerHeight + 'px');
				$('html').css('scroll-padding-top', headerHeight + 'px')
			}
		}

		// Set header banner height
		if( $('#header-banner').length) {
			const headerBannerHeight = $('#header-banner').outerHeight();
			if (headerBannerHeight) {
				$(body).css('--header-banner-height', headerBannerHeight + 'px');
			}
		}
	};
	$(window).on('load resize', handleResize);

	$(document).ready(function(){
		let $body = $('body');

		// Set sticky header class
		$(window).on('scroll', function() {
			let classes = ['scrolled'];
			if( $('body').hasClass('sticky-header') ) {
				classes.push('sticky-header-active');
			}
			if( $(window).scrollTop() > 10 ) {
				$('body').addClass(classes);
			} else {
				$('body').removeClass(classes);
			}
		}).trigger('scroll');

		// auto-hide header & bottom nav
		let lastScrollTop = 0;
		let $header = $(".auto-hide-header #header-container");
		$(window).on("scroll", function () {
			var scrollTop = $(this).scrollTop();

			if (scrollTop > lastScrollTop) {
				$header.addClass("hide-header");
				$body.addClass('header-hidden');
			} else {
				$header.removeClass("hide-header");
				$body.removeClass('header-hidden');
			}
			lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
		});

		// Footer newsletter
		if( $("#newsletter-wrap").length ) {
			$(window).on('resize', function() {
				$("#newsletter-wrap").next().css('margin-top', $("#newsletter-wrap").outerHeight()/2-80 + "px")
			}).trigger('resize');
		}

		//***** Show/Hide password *****//
		$(document).on('click', '.show-password', function() {
			$(this).siblings("input[type='password']").attr('type', 'text');
		})
		$(document).on('click', '.hide-password', function() {
			$(this).siblings("input[type='text']").attr('type', 'password');
		})

		//***** Search *****//
		$('.bijan-search.bijan-search-ajax').on('submit', function(e) {
			e.preventDefault();
			$(this).find('.bijan-search-field').trigger('change');
		});
		let timerSearch, ajaxSearch, searchText = '', searchCache = {};
		$('.bijan-search.bijan-search-ajax .bijan-search-field').on('change input', function() {
			let value = $(this).val();
			let popup = $(this).siblings('.bijan-search-results'),
				$form = $(this).closest('.bijan-search');
			if(value.length >= 2) {
				if( value in searchCache ) {
					$form.removeClass('loading');
					popup.html(searchCache[value]).addClass('show');
					return;
				}
				if( value === searchText ) return;
				if (typeof ajaxSearch !== "undefined") ajaxSearch.abort();
				if (typeof timerSearch !== "undefined") clearTimeout(timerSearch);

				searchText = value;
				$form.addClass('loading');
				popup.html('').removeClass('show');
				let postType = '';
				if( $form.attr('data-post_type') ) {
					postType = $form.attr('data-post_type');
				}
				timerSearch = setTimeout(function() {
					let ajaxData = {
						action: 'bijan_search',
						text: value,
						post_type: postType,
						nonce: bijanVars.nonces.ajaxSearch
					};
					if( $form.attr('data-args') ) {
						ajaxData.args = $form.attr('data-args');
					}
					ajaxSearch = $.ajax({
						url: bijanVars.ajaxUrl,
						type: 'POST',
						data: ajaxData,
						success: function(res) {
							$form.removeClass('loading');
							popup.html(res).addClass('show');
							searchCache[value] = res;
						}
					});
				}, 300);
			} else {
				$form.removeClass('loading');
				popup.html('').removeClass('show');
			}
		})
		$(document).on('click', '.bijan-search-post-type', function() {
			let activeClass = 'bijan-search-post-type-active';
			$(this).siblings(`.${activeClass}`).removeClass(activeClass);
			$(this).addClass(activeClass);

			let postType = $(this).attr('data-post_type');
			$('.bijan-search-post').show();			
			if(postType != 'all') {
				$(`.bijan-search-post:not([data-post_type="${postType}"])`).hide();
			}
		})

		//***** Stars *****//
		$('.bijan_stars-has-radio .bijan_star').on('click', function() {
			let star = $(this).index(),
				stars = $(this).closest('.bijan_stars'),
				activeClass = 'bijan_star-active';

			$(this).prev().prop('checked', true); // Checked the radio
			
			stars.find('.bijan_star').removeClass(activeClass);
			for( let index = 0; index <= star+1; index++ ) {
				stars.find(`.bijan_star:nth-child(${index})`).addClass(activeClass);
			}
		});

		//***** Story *****//
		let storyCache = {},
			isDraggingStoryProgress = false;
		$('.story-item').on('click', function() {
			let id = $(this).attr('data-id');
			$('#bijan-overlay, #story-popup').fadeIn();
			isDraggingStoryProgress = false;
			if( typeof storyCache[id] != 'undefined' ) {
				setStoryContent(storyCache[id].html, id, storyCache[id].nonce, storyCache[id].nonce2);

				// Update likes html
				$.ajax({
					url: bijanVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'bijan_story_like_html',
						id: id,
						nonce: $('#story-popup').attr('data-nonce2'),
					},
					success: function(res) {
						if(res) {
							$('#story-popup-like').remove();
							$(res).insertAfter('#story-popup-post');
						}
					}
				});

				return;
			}
			$.ajax({
				url: bijanVars.ajaxUrl,
				type: 'POST',
				data: {
					action: 'bijan_story',
					id: id,
					nonce: $(this).attr('data-nonce'),
				},
				success: function(res) {
					if(res.success) {
						setStoryContent(res.data.html, id, res.data.nonce, res.data.nonce2);
					}
				}
			});
		})
		// Close popup
		$(document).on('click', '#bijan-overlay, #story-popup-back-btn', function() {
			isDraggingStoryProgress = false;
			$('#bijan-overlay').fadeOut();
			$('#story-popup').fadeOut({
				complete: function() {
					$('#story-popup-content').empty();
				}
			});
		});
		$(document).on('click', '#story-popup-like', function() {
			let popup = $(this).closest('#story-popup');
			$.ajax({
				url: bijanVars.ajaxUrl,
				type: 'POST',
				data: {
					action: 'bijan_toggle_like_story',
					id: popup.attr('data-id'),
					nonce: popup.attr('data-nonce')
				},
				success: function(res) {
					if(res) {
						$('.story-popup-like').toggle();
						$('#story-popup-like-count').text(res.data);
					}
				}
			})
		});
		function setStoryContent(content, id, nonce, nonce2) {
			storyCache[id] = {
				html: content,
				nonce: nonce,
				nonce2: nonce2
			};
			$('#story-popup-content').html(content);
			$('#story-popup').attr('data-id', id);
			$('#story-popup').attr('data-nonce', nonce);
			$('#story-popup').attr('data-nonce2', nonce2);
			// Start video progress
			let video = $('video.story-popup-attachment-item').get(0);
			if(video) {
				video.addEventListener('timeupdate', function() {
					// Calculate the percentage of video played
					let progress = (video.currentTime / video.duration) * 100;
					
					// Set the width of the progress bar
					$('#story-popup-video-progress-fill').css('width', progress + '%');
					$('#story-popup-video-progress-dot').css('inset-inline-start', progress + '%');
					$('#story-popup-video-progress-time').text(bijan.formatTime(video.currentTime));
				});
			}
		}

		document.addEventListener('mousedown', function (e) {
			const progressBar = e.target.closest('#story-popup-video-progress');
			if (!progressBar) return;
			isDraggingStoryProgress = true;
			updateVideoProgress(e, progressBar);
		});

		document.addEventListener('mousemove', function (e) {
			if (!isDraggingStoryProgress) return;
			const progressBar = document.querySelector('#story-popup-video-progress');
			if (progressBar) {
				updateVideoProgress(e, progressBar);
			}
		});

		document.addEventListener('mouseup', function () {
			isDraggingStoryProgress = false;
		});

		function updateVideoProgress(e, progressBar) {
			const video = document.querySelector('#story-popup video');
			if (!video || !video.duration) return;

			const rect = progressBar.getBoundingClientRect();
			const clickX = Math.min(Math.max(e.clientX - rect.left, 0), rect.width);
			const ratio = clickX / rect.width;

			video.currentTime = ratio * video.duration;

			const fill = document.getElementById('story-popup-video-progress-fill');
			const dot = document.getElementById('story-popup-video-progress-dot');
			fill.style.width = (ratio * 100) + '%';
			dot.style.insetInlineStart = (ratio * 100) + '%';
			video.play();
		}

		//***** Special offer *****//
		if($('.special-offer').length) {
			$('.special-offer').each(function() {
				let $this = $(this);
				let endTimestamp = $this.attr('data-end');
				if(endTimestamp) {
					setInterval(function() {
						let now = new Date().getTime(),
							timeRemaining = endTimestamp - now;
						if (timeRemaining >= 0) {
							let days = String(bijan.addZero(Math.floor(timeRemaining / (1000 * 60 * 60 * 24)))),
								hours = String(bijan.addZero(Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)))),
								minutes = String(bijan.addZero(Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60)))),
								seconds = String(bijan.addZero(Math.floor((timeRemaining % (1000 * 60)) / 1000)));
							if( $this.find('.special-offer-timer-days').length ) {
								let numbers = $this.find('.special-offer-timer-days').find('.special-offer-timer-number');
								numbers.eq(0).text(days.substr(0,1));
								numbers.eq(1).text(days.substr(1,1));
							}
							if( $this.find('.special-offer-timer-hours').length ) {
								let numbers = $this.find('.special-offer-timer-hours').find('.special-offer-timer-number');
								numbers.eq(0).text(hours.substr(0,1));
								numbers.eq(1).text(hours.substr(1,1));
							}
							if( $this.find('.special-offer-timer-minutes').length ) {
								let numbers = $this.find('.special-offer-timer-minutes').find('.special-offer-timer-number');
								numbers.eq(0).text(minutes.substr(0,1));
								numbers.eq(1).text(minutes.substr(1,1));
							}
							if( $this.find('.special-offer-timer-seconds').length ) {
								let numbers = $this.find('.special-offer-timer-seconds').find('.special-offer-timer-number');
								numbers.eq(0).text(seconds.substr(0,1));
								numbers.eq(1).text(seconds.substr(1,1));
							}
						}
					}, 1000);
				}
			});
		}

		//***** Brands *****//
		$(window).on('load resize', function() {
			$('.bijan-brand').each(function() {
				$(this).height($(this).outerWidth());
			})
		})

		//***** Video *****//
		$('.video-wrap').on('click', function() {
			let video = $(this).find('video');
			if(video.length) {
				video.show();
				video[0].play();
				$(this).addClass('video-playing');
			}
		})

		// Bottom nav
		$('.bottom-nav-item-cart').on('click', function(e) {
			e.preventDefault();
			$('.bottom-nav-cart-wrap').toggleClass('open')
		})

		// Mobile menu
		function openMobileMenu(id) {
			$(id).toggleClass('open');
			$('body').toggleClass('mobile-menu-opened');
			$('#mobile-menu-overlay').fadeToggle();
			$('#bottom-nav').toggleClass('hide');
			$('.sidebar-mobile-expand-btn, .my-account-menu-expand-btn').addClass('hidden');
			$('#header-toggle-mobile-menu').hide();

			$('body').addClass('disable-scrolling');
		}
		$('.toggle-mobile-menu').on('click', function(e) {
			e.preventDefault();
			openMobileMenu('#mobile-menu-container');
		})
		$('.mobile-menu-wrap li.menu-item-has-children > a').on('click', function(e) {
			e.preventDefault();
			$(this).parent().toggleClass('open');
			$(this).siblings('ul').slideToggle({
				start: function() {
					$(this).css('display', 'flex')
				}
			});
		})
		$('.toggle-account-menu').on('click', function(e) {
			e.preventDefault();
			openMobileMenu('#mobile-account-menu-container');
		})
		// Close mobile menu
		$('#mobile-menu-overlay').on('click', function() {
			$('.mobile-menu-container').removeClass('open');
			$('body').removeClass('mobile-menu-opened');
			$('#mobile-menu-overlay').fadeOut();
			$('#bottom-nav').removeClass('hide');
			$('.sidebar-mobile-expand-btn, .my-account-menu-expand-btn').removeClass('hidden');
			$('#header-toggle-mobile-menu').show();

			$('body').removeClass('disable-scrolling');
		})

		// Set footer margin top
		if( $('#newsletter-wrap').length ) {
			$(window).on('resize', function() {
				$('#footer').css('margin-top', $('#newsletter-wrap').outerHeight()/2+76 + "px")
			}).trigger('resize');
		}

		// Expand/Fold sidebar in mobile
		function closeSidebar() {
			$('#sidebar').removeClass('mobile-expanded');
			$('#bijan-overlay').fadeOut();
			$('.sidebar-mobile-expand-btn').removeClass('active');
			$('#header-toggle-mobile-menu').show();

			$('body').removeClass('disable-scrolling');
		}
		$('.sidebar-mobile-expand-btn').on('click', function() {
			$sidebar = $('#sidebar');
			if($sidebar.hasClass('mobile-expanded')) {
				closeSidebar();
			} else { // Show sidebar - Open sidebar
				$sidebar.addClass('mobile-expanded');
				$('#bijan-overlay').fadeIn();
				$(this).addClass('active');
				$('#header-toggle-mobile-menu').hide();
				$('body').addClass('disable-scrolling');
			}
		})
		$('#bijan-overlay').on('click', closeSidebar)

		// Close when esc key is pressed
		$(document).on('keydown', function(e) {
			if(e.key === 'Escape') {
				closeSidebar();
				$('#mobile-menu-overlay').click();
			}
		})

		// Fix elementor lightbox
		$('a').on('click', function() {
			if (this.dataset.elementorOpenLightbox === 'yes' || /^[^?]+\.(png|jpe?g|gif|svg|webp|avif)(\?.*)?$/i.test(this.getAttribute('href') || '')) {
				setTimeout(() => {
					$('.elementor-lightbox-item img[data-src]').each(function() {
						this.src = this.dataset.src;
						this.classList.remove('swiper-lazy');
					});
				}, 10);
			}
		});
	});
})(jQuery);