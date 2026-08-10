<?php

use Bijan\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$timer = empty( $args['sms']['settings']['auth']['login']['otp_timer'] ) ? 30 : $args['sms']['settings']['auth']['login']['otp_timer'];
?>
<h4 class="auth-modal-title"><?php esc_html_e( "Verification code", 'bijan' ) ?></h4>
<form action="" method="post" id="auth-otp-form" class="auth-form" data-nonce="<?php echo wp_create_nonce( "bijan-auth-otp" ) ?>">
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