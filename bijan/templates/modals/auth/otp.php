<?php

use Bijan\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$timer = empty( $args['sms']['settings']['auth']['login']['otp_timer'] ) ? 30 : $args['sms']['settings']['auth']['login']['otp_timer'];
?>
<style>
	#auth-modal { max-height: calc(100dvh - 32px); }
	#auth-modal-inner { max-height: calc(100dvh - 32px); overflow-y: auto; overscroll-behavior: contain; }
	#auth-register-name-wrap { padding: 12px; border-radius: 16px; background: var(--primary-500); box-sizing: border-box; }
	.auth-name-help { display: block; margin-bottom: 6px; color: var(--text-300); font-size: .75rem; line-height: 1.8; }
</style>
<h4 class="auth-modal-title"><?php esc_html_e( "Verification code", 'bijan' ) ?></h4>
<form action="" method="post" id="auth-otp-form" class="auth-form" data-nonce="<?php echo wp_create_nonce( "bijan-auth-otp" ) ?>">
	<div class="input-group row-full" id="auth-register-name-wrap" hidden>
		<label for="auth-register-name" class="input-label">نام و نام خانوادگی</label>
		<small class="auth-name-help">برای تکمیل حساب و نمایش صحیح نام شما، این بخش را وارد کنید.</small>
		<div class="input-wrap">
			<input
				type="text"
				id="auth-register-name"
				name="auth-register-name"
				class="input-field"
				minlength="2"
				maxlength="60"
				autocomplete="name"
				placeholder="نام و نام خانوادگی"
			>
		</div>
	</div>

	<div class="input-group row-full">
		<label class="input-label"><?php esc_html_e( 'Enter your verification code:', 'bijan' ) ?></label>
		<div id="otp-fields">
			<?php for( $index = 0; $index <= 3; $index++ ) { ?>
				<input
					type="number"
					min="0"
					max="9"
					minlength="1"
					maxlength="1"
					name="auth-otp[<?php echo $index ?>]"
					id="auth-otp-input-<?php echo $index ?>"
					class="input-field input-ltr auth-otp-input"
					required="required"
					autocomplete="one-time-code"
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
