<?php
if( !defined( 'ABSPATH' ) ) exit;

$label = '';

if( !empty( $args['sms']['settings']['auth']['one_form'] ) || !empty( $args['sms']['settings']['auth']['login']['enabled'] ) ) {
	$label = __( 'Enter your <strong>mobile</strong> or <strong>username</strong> or <strong>email</strong>. A new password will be sent to you.', 'bijan' );
} else {
	$label = __( 'Enter your <strong>username</strong> or <strong>email</strong>. A new password will be sent to you.', 'bijan' );
}
?>

<h4 class="auth-modal-title"><?php esc_html_e( "Lost password", 'bijan' ) ?></h4>
<form action="" method="post" id="lost_password-form" class="auth-form" data-nonce="<?php echo wp_create_nonce( "bijan-auth-lost_password" ) ?>">
	<div class="input-group row-full">
		<label for="lost_password-email" class="input-label"><?php echo $label ?></label>
		<div class="input-wrap">
			<input
				type="text"
				id="lost_password-email"
				name="lost_password-email"
				class="input-field input-ltr"
				required="required"
				autocomplete="username"
				autocapitalize="off"
				minlength="1"
				spellcheck="false"
			>
		</div>
	</div>

	<div class="back-to-login-btn auth-modal-switch-link row-full"><span><?php esc_html_e( "Back to login", 'bijan' ) ?></span></div>

	<div class="auth-modal-submit-wrap row-full">
		<?php get_template_part( "templates/components/button", null, [
			'text'		=> esc_html__( "Restore password", 'bijan' ),
			'align' 	=> 'center',
			'loading'	=> true,
			'disabled'	=> true,
			'id'		=> 'auth-lost_password-submit',
		] ) ?>
	</div>
</form>