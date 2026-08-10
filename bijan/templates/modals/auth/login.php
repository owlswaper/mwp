<?php
if( !defined( 'ABSPATH' ) ) exit;

$username_label = '';
if( !empty( $args['sms']['settings']['auth']['login']['enabled'] ) ) {
	$username_label = __( 'Username or email or mobile number', 'bijan' );
} else {
	$username_label = __( 'Username or email', 'bijan' );
}
?>

<h4 class="auth-modal-title"><?php esc_html_e( "Login", 'bijan' ) ?></h4>
<form action="" method="post" id="login-form" class="auth-form" data-nonce="<?php echo wp_create_nonce( "bijan-auth-login" ) ?>">
	<div class="input-group row-full">
		<label for="login-username" class="input-label"><?php echo esc_html( $username_label ) ?></label>
		<div class="input-wrap">
			<input
				type="text"
				id="login-username"
				name="login-username"
				class="input-field input-ltr"
				required="required"
				autocomplete="username"
				autocapitalize="off"
				spellcheck="false"
				minlength="1"
			>
		</div>
	</div>

	<div class="input-group row-full">
		<label for="login-password" class="input-label"><?php esc_html_e( 'Password', 'bijan' ) ?></label>
		<div class="input-wrap password-wrap">
			<input
				type="password"
				id="login-password"
				name="login-password"
				class="input-field input-ltr"
				required="required"
				autocomplete="current-password"
				spellcheck="false"
				autocapitalize="off"
				minlength="1"
			>
			<i class="input-icon show-password bijan-icon-eye"></i>
			<i class="input-icon hide-password bijan-icon-eye-slash"></i>
		</div>
	</div>

	<label id="rememberme-label" class="checkbox-wrap">
		<input type="checkbox" name="login-rememberme" id="login-rememberme" value="forever" checked>
		<?php esc_html_e( 'Remember me', 'bijan' ) ?>
	</label>

	<span id="lost-password-link"><?php esc_html_e( "Lost your password?", 'bijan' ) ?></span>

	<div id="signup-btn" class="auth-modal-switch-link row-full"><?php _e( "Don't have an account? <span>Signup now</span>", 'bijan' ) ?></div>

	<div class="auth-modal-submit-wrap row-full">
		<?php
		get_template_part( "templates/components/button", null, [
			'text'		=> esc_html__( "Login", 'bijan' ),
			'align'		=> 'center',
			'loading'	=> true,
			'disabled'	=> true,
			'id'		=> 'auth-login-submit',
		] );
		if( !empty( $args['sms']['settings']['auth']['one_form'] ) ) {
			get_template_part( "templates/components/button", null, [
				'text'		=> esc_html__( "Login with OTP", 'bijan' ),
				'type'		=> 'action',
				'align'		=> 'center',
				'classes'	=> ['go-to-mobile-btn'],
				'atts'		=> [
					'type'	=> 'button'
				],
			] );
		}
		?>
	</div>
</form>