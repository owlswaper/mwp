(function($) {
	$(document).ready(function(){
		let otpTimer,
			otpMode = 'login',
			otpNeedsName = false;

		function validDisplayName(value) {
			value = $.trim(value || '').replace(/\s+/g, ' ');
			return value.length >= 2 && value.length <= 60 && /^[\p{L}\p{M}]+(?:[\p{L}\p{M}\s‌'’\-]*[\p{L}\p{M}])?$/u.test(value);
		}

		function setOtpMode(mode, requiresName) {
			otpMode = mode === 'register' ? 'register' : 'login';
			otpNeedsName = Boolean(requiresName) && (otpMode === 'login' || Boolean(bijanLogin.smsOneForm));
			const nameWrap = $('#auth-register-name-wrap');
			const nameInput = $('#auth-register-name');
			nameWrap.prop('hidden', !otpNeedsName).toggle(otpNeedsName);
			nameInput.prop('required', otpNeedsName);
			if(!otpNeedsName) {
				nameInput.val('');
			}
		}
		function showLoginModal(form = "default") {
			if(form == 'default') {
				if( ( typeof bijanLogin.smsOneForm != 'undefined' && bijanLogin.smsOneForm ) || ( typeof bijanLogin.authSms != 'undefined' && bijanLogin.authSms ) ) {
					form = 'mobile';
				} else {
					form = 'login';
				}
			}
			if( !$(`.auth-modal-${form}`).length ) {
				form = $(`.auth-modal-content`).eq(0).attr('data-type');
			}

			$('body').addClass('disable-scrolling');
			$('#header-toggle-mobile-menu').hide();
			$('.sidebar-mobile-expand-btn, .my-account-menu-expand-btn').addClass('hidden');

			$('#auth-modal-msg').hide();
			$(`.auth-modal-content:not(.auth-modal-${form})`).hide();
			$(`.auth-modal-${form}`).css('display', 'flex');
			$('#bijan-overlay').fadeIn();
			$('#auth-modal').fadeIn({
				start: function() {
					$(this).css('display', 'flex');
				}
			});
			$(`.auth-modal-${form} input:not([type="hidden"]):not([type="button"])`).eq(0).focus();
		}
		if(bijanLogin.showLogin) {
			showLoginModal();
		}

		// Show/Hide modal
		$('#header-account, [data-index="login"]').on('click', function(e) {
			e.preventDefault();
			showLoginModal();
		})
		$('[data-index="signup"]').on('click', function(e) {
			e.preventDefault();
			showLoginModal("signup");
		})
		// Hide - Close modal
		$('#bijan-overlay').on('click', function() {
			$(this).fadeOut();
			$('#auth-modal').fadeOut();
			// WC - Checkout
			$('#createaccount').prop('checked', false).trigger('change');

			$('body').removeClass('disable-scrolling');
			$('#header-toggle-mobile-menu').show();
			$('.sidebar-mobile-expand-btn, .my-account-menu-expand-btn').removeClass('hidden')
		});
		// Show login modal when click on links
		$('a[href*="?login"], a[href*="/my-account"], .showlogin').on('click', function(e) {
			e.preventDefault();
			showLoginModal();
		});
		
		$('.auth-form').on('submit', function(e) {
			e.preventDefault();
		});

		function sendAjax(data) {
			return $.ajax({
				url: bijanVars.ajaxUrl,
				type: 'post',
				data: data,
				success: function(res) {
					if( res.success ) {
						if(data.action != 'bijan_lost_password') {
							window.location.reload();
						} else {
							$('.auth-modal-lost_password').slideUp();
							$('.auth-modal-login').slideDown();
						}
					}
					if(!res.success && data.action === 'bijan_check_otp' && res.data && res.data.code === 'invalid_display_name') {
						setOtpMode(otpMode, true);
						$('#auth-register-name').focus();
					}
					if(typeof res.data !== 'undefined' && typeof res.data.msg !== 'undefined') {
						$('#auth-modal-msg').html(res.data.msg).fadeIn();
						setTimeout(function() {
							$('#auth-modal-msg').fadeOut();
						}, 5000);
					}
				}
			});
		}

		// Login submit
		$('#login-form').on('submit', function() {
			let form = $(this);
			$('#auth-login-submit').addClass('loading');
			sendAjax({
				action: 'bijan_login',
				nonce: form.attr('data-nonce'),
				username: $('#login-username').val(),
				password: $('#login-password').val(),
				remember: $('#login-rememberme').prop('checked')
			}).done(function() {
				$('#auth-login-submit').removeClass('loading');
			});
		})

		// Signup submit
		$('#signup-form').on('submit', function() {
			let form = $(this);
			$('#auth-signup-submit').addClass('loading');
			sendAjax({
				action: 'bijan_signup',
				nonce: form.attr('data-nonce'),
				display_name: $('#signup-display-name').val(),
				username: $('#signup-username').val(),
				email: $('#signup-email').val(),
				mobile: $('#signup-mobile').val(),
				password: $('#signup-password').val(),
			}).done(function() {
				$('#auth-signup-submit').removeClass('loading');
			});
		})

		// Lostpassword submit
		$('#lost_password-form').on('submit', function() {
			let form = $(this);
			$('#auth-lost_password-submit').addClass('loading');
			sendAjax({
				action: 'bijan_lost_password',
				nonce: form.attr('data-nonce'),
				entry: $('#lost_password-email').val(),
			}).done(function() {
				$('#auth-lost_password-submit').removeClass('loading');
			});
		})

		// Switch forms
		function switchForm(from, to) {
			$('#auth-modal-msg').slideUp();
			$(`.auth-modal-${from}`).slideUp();
			$(`.auth-modal-${to}`).slideDown();
			$(`.auth-modal-${to} input:not([type="hidden"]):not([type="button"])`).eq(0).focus();
		}
		$('#login-btn span').on('click', function() {
			if( ( typeof bijanLogin.smsOneForm == 'undefined' || !bijanLogin.smsOneForm ) && ( typeof bijanLogin.authSms != 'undefined' && bijanLogin.authSms ) ) {
				switchForm('signup', 'mobile');
			} else {
				switchForm('signup', 'login');
			}
		})
		$('#signup-btn span').on('click', function() {
			switchForm('login', 'signup');
		})
		$('.go-to-signup-btn').on('click', function() {
			switchForm('mobile', 'signup');
		})
		$('#lost-password-link').on('click', function() {
			switchForm('login', 'lost_password');
		})
		$('.back-to-login-btn span').on('click', function() {
			switchForm($(this).closest('.auth-modal-content').attr('data-type'), 'login');
		})
		$('#auth-change-number').on('click', function(e) {
			e.preventDefault();
			setOtpMode('login', false);
			switchForm('otp', 'mobile');
			$('#auth-mobile-submit').removeClass('loading');
		})
		$('.go-to-mobile-btn').on('click', function(e) {
			e.preventDefault();
			$('#auth-mobile-input').val('').trigger('change').focus();
			$('#auth-mobile-submit').removeClass('loading');
			switchForm($(this).closest('.auth-modal-content').attr('data-type'), 'mobile');
		})

		// OTP
		function startTimer(durationInSeconds, displayElementId) {
			const display = document.getElementById(displayElementId);
			let remainingTime = durationInSeconds;
		
			function updateTimer() {
				const minutes = String(Math.floor(remainingTime / 60)).padStart(2, '0');
				const seconds = String(remainingTime % 60).padStart(2, '0');
				display.textContent = `${minutes}:${seconds}`;
			
				if (remainingTime > 0) {
					remainingTime--;
					$('#otp-timer').show();
					$('#otp-timer-resend').hide();
				} else {
					clearInterval(timerInterval);
					display.textContent = "00:00";
					$('#otp-timer').hide();
					$('#otp-timer-resend').show();
				}
			}
		
			const timerInterval = setInterval(updateTimer, 1000);
			updateTimer();
		}
		// Mobile format
		$(".auth-mobile-input").on('input', function() {
			// Cache the jQuery object for the input to avoid repeated DOM lookups
			const $input = $(this);

			// Remove all non-digit characters and limit the input to 11 digits
			let value = bijan.convertChars($input.val()).replace(/\D/g, '').slice(0, 11);

			// Format the value into the #### ### #### pattern dynamically
			value = value.replace(/^(\d{4})(\d{0,3})(\d{0,4})$/, (_, a, b, c) => {
				// Join the captured groups (a, b, c) with spaces, filtering out empty groups
				return [a, b, c].filter(Boolean).join(' ');
			});

			// Update the input field with the formatted value
			$input.val(value);
		});
		$(".auth-otp-input").on("input", function () { // Move between OTP fields
			// Allow only one character in the input
			let value = bijan.convertChars($(this).val());
			if (value.length > 1) {
				$(this).val(value.charAt(0));
			}
	
			// Move to the next input when a character is typed
			if (bijan.convertChars($(this).val()) !== "") {
				const nextInput = $(this).next(".auth-otp-input");
				if (nextInput.length > 0) {
					nextInput.focus();
				}
			}
		});
		function updateOtpSubmitState(submitWhenReady) {
			// Check if all inputs are filled
			let allFilled = true;
			$(".auth-otp-input").each(function () {
				if (bijan.convertChars($(this).val()) === "") {
					allFilled = false;
					return false; // Break the loop
				}
			});
	
			// Click the submit button if all inputs are filled
			const nameIsValid = !otpNeedsName || validDisplayName($('#auth-register-name').val());
			if (allFilled && nameIsValid) {
				$("#auth-otp-submit").removeClass('disabled').prop('disabled', false);
				if(submitWhenReady) {
					$("#auth-otp-submit").click();
				}
			} else {
				$("#auth-otp-submit").addClass('disabled').prop('disabled', true)
			}
		}
		$('.auth-otp-input').on('change keyup', function() { // Check enable/disable verify btn
			updateOtpSubmitState(true);
		})
		$('#auth-register-name').on('input', function() {
			updateOtpSubmitState(false);
		});
		$(".auth-otp-input").on("keydown", function (e) { // Move with arrow keys or backspace
			let key = e.key;
			let currentInput = $(this);
	
			// Move to the previous input when the left arrow key is pressed
			if (key === "ArrowLeft") {
				currentInput.prev(".auth-otp-input").focus();
			}
			// Move to the next input when the right arrow key is pressed
			else if (key === "ArrowRight") {
				currentInput.next(".auth-otp-input").focus();
			}
	
			// Clear the current input and move to the previous one on backspace
			if (key === "Backspace") {
				if (bijan.convertChars(currentInput.val()) === "") {
					currentInput.prev(".auth-otp-input").focus().val("");
				} else {
					currentInput.val("");
				}
			}
		});
		$("#auth-mobile-input").on('change keyup', function() { // Check enable/disable send OTP button
			let value = bijan.convertChars($(this).val());
			if(value.length === 13 && value.substr(0, 2) == '09') {
				$("#auth-mobile-submit").removeClass('disabled').prop('disabled', false)
			} else {
				$("#auth-mobile-submit").addClass('disabled').prop('disabled', true)
			}
		})
		$(".auth-send-otp").on('click', function(e) { // Send OTP - AJAX
			e.preventDefault();
			$this = $(this);

			if( otpTimer != null && $this.attr('data-id') == 'otp-timer-resend' ) return;

			$this.addClass('loading')
			$.ajax({
				url: bijanVars.ajaxUrl,
				type: 'post',
				data: {
					action: 'bijan_send_otp',
					mobile: $('#auth-mobile-input').val().replaceAll(' ', ''),
					nonce: $('#auth-mobile-form').attr('data-nonce')
				},
				success: function(res) {
					if( res.success ) {
						setOtpMode(res.data.mode, res.data.requires_name);
						switchForm('mobile', 'otp');
						startTimer(bijanLogin.smsOneForm || res.data.mode == 'login' ? bijanLogin.otpLoginTime : bijanLogin.otpRegisterTime, 'otp-timer');
						$('#otp-timer').show();
						$('#otp-timer-resend').hide();
						if(otpNeedsName) {
							$('#auth-register-name').focus();
						} else {
							$('#auth-otp-input-0').focus();
						}
						$('#auth-otp-submit').removeClass('loading')
					}
					if(typeof res.data !== 'undefined' && typeof res.data.msg !== 'undefined') {
						$('#auth-modal-msg').html(res.data.msg).fadeIn();
						setTimeout(function() {
							$('#auth-modal-msg').fadeOut();
						}, 5000);
					}
				},
				complete: function() {
					$this.removeClass('loading');
				}
			});
		})
		$('#auth-otp-submit').on('click', function(e) { // Verify OTP
			e.preventDefault();
			$this = $(this);
			$this.addClass('loading')
			sendAjax( {
				action: 'bijan_check_otp',
				nonce: $('#auth-otp-form').attr('data-nonce'),
				mobile: $('#auth-mobile-input').val(),
				display_name: otpNeedsName ? $('#auth-register-name').val() : '',
				otp: $('#auth-otp-input-0').val() + $('#auth-otp-input-1').val() + $('#auth-otp-input-2').val() + $('#auth-otp-input-3').val()
			}).done(function() {
				$this.removeClass('loading')
			});
		})

		// Email login
		// Check active login button or not
		$('#login-username, #login-password').on('input', function() {
			let username = $('#login-username').val(),
				password = $('#login-password').val(),
				enabled = false,
				btn = $('#auth-login-submit');
			if( username && password ) {
				// Check username
				enabled = bijan.validateUsername(username);
			}
			if( enabled ) {
				btn.removeClass('disabled');
			} else {
				btn.addClass('disabled');
			}
			btn.prop('disabled', !enabled);
		})
		// Check active signup button or not
		$('#signup-display-name, #signup-username, #signup-email, #signup-mobile, #signup-password').on('input', function() {
			let displayName = $('#signup-display-name').val(),
				username = $('#signup-username').val(),
				email = $('#signup-email').val(),
				mobile = $('#signup-mobile').val(),
				password = $('#signup-password').val(),
				enabled = false,
				btn = $('#auth-signup-submit');
			if( validDisplayName(displayName) && username && email && password ) {
				// Check username and email
				enabled = bijan.validateUsername(username) && bijan.validateEmail(email);
				if( mobile && !bijan.validateMobile(mobile) ) {
					enabled = false;
				}
			}
			if( enabled ) {
				btn.removeClass('disabled');
			} else {
				btn.addClass('disabled');
			}
			btn.prop('disabled', !enabled);
		})
		// Check active forgot password button or not
		$('#lost_password-email').on('input', function() {
			let username = $(this).val(),
				enabled = false,
				btn = $('#auth-lost_password-submit');
			if( username ) {
				// Check username
				enabled = bijan.validateUsername(username);
			}
			if( enabled ) {
				btn.removeClass('disabled');
			} else {
				btn.addClass('disabled');
			}
			btn.prop('disabled', !enabled);
		})

		// WC - checkout
		$(document).on('change', '#createaccount', function() {
			if($(this).prop('checked')) {
				showLoginModal();
			} else {
				$('#bijan-overlay, #auth-modal').fadeOut();
			}
		});

		// WC - wishlist button
		$(document).on('click', '.wishlist-button', function() {
			showLoginModal();
		});
	});
})(jQuery);
