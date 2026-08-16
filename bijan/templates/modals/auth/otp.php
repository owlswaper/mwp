<?php

use Bijan\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$timer = empty( $args['sms']['settings']['auth']['login']['otp_timer'] ) ? 30 : $args['sms']['settings']['auth']['login']['otp_timer'];
?>
<style>
	#auth-modal { max-height: calc(100dvh - 32px); }
	#auth-modal-inner { max-height: calc(100dvh - 32px); overflow-y: auto; overscroll-behavior: contain; }
	#auth-register-name-wrap[hidden] { display: none !important; }
	#auth-register-name-wrap {
		display: grid !important;
		gap: 14px;
		padding: 18px;
		border: 1px solid #e5e9ef;
		border-radius: 20px;
		background: #fff;
		box-sizing: border-box;
		box-shadow: 0 12px 34px rgba(33, 43, 54, .08);
	}
	.auth-name-heading { display: flex; align-items: center; gap: 11px; }
	.auth-name-icon {
		display: grid;
		place-items: center;
		width: 42px;
		height: 42px;
		flex: 0 0 42px;
		border-radius: 13px;
		background: #f3f6f9;
		color: var(--primary-100);
		font-size: 1.15rem;
		font-weight: 800;
	}
	.auth-name-heading > div { display: grid; gap: 2px; }
	#auth-register-name-wrap .input-label { color: #242b35; font-size: 1rem; font-weight: 800; line-height: 1.6; }
	.auth-name-help { display: block; color: #747d89; font-size: .7rem; line-height: 1.8; }
	#auth-register-name-wrap .input-wrap {
		border: 1px solid #dce2e9;
		border-radius: 14px;
		background: #f8fafc;
		overflow: hidden;
		transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
	}
	#auth-register-name-wrap .input-wrap:focus-within { border-color: var(--primary-100); background: #fff; box-shadow: 0 0 0 4px var(--primary-100-12); }
	#auth-register-name {
		width: 100%;
		height: 52px;
		padding: 0 15px;
		border: 0 !important;
		border-radius: 0 !important;
		background: transparent !important;
		box-sizing: border-box;
		color: #242b35;
		font-family: inherit;
		font-size: .9rem;
		box-shadow: none !important;
	}
	#auth-register-name::placeholder { color: #9aa2ad; opacity: 1; }
	.auth-name-privacy { color: #7d8692; font-size: .65rem; line-height: 1.7; }
	@media (max-width: 480px) {
		#auth-register-name-wrap { gap: 12px; padding: 15px; border-radius: 17px; box-shadow: 0 8px 24px rgba(33, 43, 54, .07); }
		.auth-name-icon { width: 38px; height: 38px; flex-basis: 38px; }
		#auth-register-name { height: 50px; font-size: .85rem; }
	}
</style>
<h4 class="auth-modal-title"><?php esc_html_e( "Verification code", 'bijan' ) ?></h4>
<form action="" method="post" id="auth-otp-form" class="auth-form" data-nonce="<?php echo wp_create_nonce( "bijan-auth-otp" ) ?>">
	<div class="input-group row-full" id="auth-register-name-wrap" hidden>
		<div class="auth-name-heading">
			<span class="auth-name-icon" aria-hidden="true">✦</span>
			<div><label for="auth-register-name" class="input-label">اسمت چیه؟</label><small id="auth-name-help" class="auth-name-help">نامی را بنویس که دوست داری در حساب و نظرات نمایش داده شود.</small></div>
		</div>
		<div class="input-wrap">
			<input
				type="text"
				id="auth-register-name"
				name="auth-register-name"
				class="input-field"
				minlength="2"
				maxlength="60"
				autocomplete="name"
				placeholder="مثلاً آریا محمدی"
				aria-describedby="auth-name-help auth-name-privacy"
			>
		</div>
		<small id="auth-name-privacy" class="auth-name-privacy">شماره همراه شما به‌جای نام نمایش داده نمی‌شود.</small>
	</div>

	<div class="input-group row-full">
		<label class="input-label"><?php esc_html_e( 'Enter your verification code:', 'bijan' ) ?></label>
		<div id="otp-fields">
			<?php for( $index = 0; $index <= 3; $index++ ) { ?>
				<input
					type="text"
					inputmode="numeric"
					pattern="[0-9۰-۹٠-٩]"
					maxlength="4"
					name="auth-otp[<?php echo $index ?>]"
					id="auth-otp-input-<?php echo $index ?>"
					class="input-field input-ltr auth-otp-input"
					required="required"
					autocomplete="one-time-code"
					aria-label="رقم <?php echo esc_attr( $index + 1 ); ?> کد تأیید"
				>
			<?php } ?>
		</div>
	</div>

	<div id="otp-timer" class="auth-modal-switch-link row-full"><?php echo Utils::second_to_string( $timer ) ?></div>
	<?php
	get_template_part( "templates/components/button", null, [
		'text'			=> esc_html__( "Resend code", 'bijan' ),
		'transparent'	=> true,
		'align'			=> 'center',
		'classes'		=> ['auth-send-otp', 'row-full'],
		'id'			=> 'otp-timer-resend',
		'loading'		=> true,
		'atts'		=> [
			'type'	=> 'button'
		],
	] );
	?>

	<div class="auth-modal-submit-wrap row-full">
		<?php
		get_template_part( "templates/components/button", null, [
			'text'		=> esc_html__( "Verify Code", 'bijan' ),
			'align'		=> 'center',
			'id'		=> 'auth-otp-submit',
			'disabled'	=> true,
			'loading'	=> true,
		] );
		get_template_part( "templates/components/button", null, [
			'text'	=> esc_html__( "Change number", 'bijan' ),
			'type'	=> 'action',
			'align'	=> 'center',
			'id'	=> 'auth-change-number',
			'atts'		=> [
				'type'	=> 'button'
			],
		] );
		?>
	</div>
</form>
