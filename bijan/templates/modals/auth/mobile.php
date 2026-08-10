<?php
if( !defined( 'ABSPATH' ) ) exit;

$title = esc_html__( "Login / Signup", 'bijan' );
if( empty( $args['sms']['settings']['auth']['one_form'] ) ) {
	$title = esc_html__( "Login", 'bijan' );
}
?>
<h4 class="auth-modal-title"><?php echo $title ?></h4>
<form action="" method="post" id="auth-mobile-form" class="auth-form" data-nonce="<?php echo wp_create_nonce( "bijan-auth-mobile" ) ?>">
	<div class="input-group row-full">
		<label for="auth-mobile-input" class="input-label"><?php esc_html_e( 'Enter your mobile number:', 'bijan' ) ?></label>
		<div class="input-wrap">
			<input
				type="text"
				placeholder="09..."
				minlength="13"
				maxlength="13"
				id="auth-mobile-input"
				name="auth-mobile"
				class="input-field input-ltr auth-mobile-input"
				inputmode="numeric"
				required="required"
				autocomplete="tel"
				autocapitalize="off"
				spellcheck="false"
			>
		</div>
	</div>

	<?php if( !empty( $args['auth_email'] ) ) { ?>
		<div class="back-to-login-btn auth-modal-switch-link row-full"><span><?php esc_html_e( "Login with username or email", 'bijan' ) ?></span></div>
	<?php } ?>

	<div class="auth-modal-submit-wrap row-full">
		<?php
		get_template_part( "templates/components/button", null, [
			'text'		=> esc_html__( "Send verification code", 'bijan' ),
			'align'		=> 'center',
			'disabled'	=> true,
			'classes'	=> ['auth-send-otp'],
			'id'		=> 'auth-mobile-submit',
			'loading'	=> true,
		] );
		if( empty( $args['sms']['settings']['auth']['one_form'] ) ) {
			get_template_part( "templates/components/button", null, [
				'text'		=> esc_html__( "Signup", 'bijan' ),
				'type'		=> 'action',
				'align'		=> 'center',
				'classes'	=> ['go-to-signup-btn'],
				'atts'		=> [
					'type'	=> 'button'
				],
			] );
		}
		?>
	</div>
</form>