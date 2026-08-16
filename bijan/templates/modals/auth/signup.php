<?php
if( !defined( 'ABSPATH' ) ) exit;
?>

<h4 class="auth-modal-title"><?php esc_html_e( "Signup", 'bijan' ) ?></h4>
<form action="" method="post" id="signup-form" class="auth-form" data-nonce="<?php echo wp_create_nonce( "bijan-auth-signup" ) ?>">
	<div class="input-group row-full">
		<label for="signup-display-name" class="input-label"><?php esc_html_e( 'Name', 'bijan' ) ?></label>
		<div class="input-wrap">
			<input
				type="text"
				id="signup-display-name"
				name="signup-display-name"
				class="input-field"
				required="required"
				autocomplete="name"
				minlength="2"
				maxlength="60"
				placeholder="نام و نام خانوادگی"
			>
		</div>
	</div>

	<div class="input-group row-full">
		<label for="signup-username" class="input-label"><?php esc_html_e( 'Username', 'bijan' ) ?></label>
		<div class="input-wrap">
			<input
				type="text"
				id="signup-username"
				name="signup-username"
				class="input-field input-ltr"
				required="required"
				autocomplete="username"
				autocapitalize="off"
				minlength="1"
				spellcheck="false"
			>
		</div>
	</div>

	<div class="input-group row-full">
		<label for="signup-email" class="input-label"><?php esc_html_e( 'Email', 'bijan' ) ?></label>
		<div class="input-wrap">
			<input
				type="email"
				id="signup-email"
				name="signup-email"
				class="input-field input-ltr"
				required="required"
				autocapitalize="off"
				autocomplete="email"
				minlength="1"
				spellcheck="false"
			>
		</div>
	</div>

	<?php if( !empty( $args['sms']['settings']['auth']['register']['enabled'] ) ) { ?>
		<div class="input-group row-full">
			<label for="signup-mobile" class="input-label"><?php esc_html_e( 'Mobile', 'bijan' ) ?></label>
			<div class="input-wrap">
				<input
					type="text"
					minlength="13"
					maxlength="13"
					placeholder="09..."
					id="signup-mobile"
					name="signup-mobile"
					class="input-field input-ltr auth-mobile-input"
					inputmode="numeric"
					autocapitalize="off"
					autocomplete="tel"
					spellcheck="false"
				>
			</div>
		</div>
	<?php } ?>

	<div class="input-group row-full">
		<label for="signup-password" class="input-label"><?php esc_html_e( 'Password', 'bijan' ) ?></label>
		<div class="input-wrap password-wrap">
			<input
				type="password"
				id="signup-password"
				name="signup-password"
				class="input-field input-ltr"
				required="required"
				autocomplete="current-password"
				spellcheck="false"
				autocapitalize="off"
				minlength="1"
			>
			<i class="show-password bijan-icon-eye"></i>
			<i class="hide-password bijan-icon-eye-slash"></i>
		</div>
	</div>

	<div id="login-btn" class="auth-modal-switch-link row-full"><?php _e( "Have an account? <span>Login now</span>", 'bijan' ) ?></div>

	<div class="auth-modal-submit-wrap row-full">
		<?php
		get_template_part( "templates/components/button", null, [
			'text'		=> esc_html__( "Signup", 'bijan' ),
			'align'		=> 'center',
			'loading'	=> true,
			'disabled'	=> true,
			'id'		=> 'auth-signup-submit',
		] );
		if( !empty( $args['sms']['settings']['auth']['one_form'] ) ) {
			get_template_part( "templates/components/button", null, [
				'text'		=> esc_html__( "Signup with OTP", 'bijan' ),
				'type'		=> 'action',
				'align'		=> 'center',
				'classes'	=> ['go-to-mobile-btn'],
			] );
		}
		?>
	</div>
</form>
