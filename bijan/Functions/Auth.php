<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\SMS;

if( !function_exists( "bijan_auth_modals" ) ) {
	function bijan_auth_modals() {
		$default_options = [
			'auth-modal'			=> true,
			'show-auth-modal-logo'	=> true,
			'auth-modal-logo-type'	=> 'img',
			'auth-modal-logo-img'	=> BIJAN_URI . "assets/img/logo.svg",
			'auth-email'			=> true,
			'auth_sms'				=> true,
			'sms'					=> true,
		];
		$options = Options::get_options( $default_options );

		if( !Utils::to_bool( $options['auth-modal'] ) ) return;
		
		$auth_email = Utils::to_bool( $options['auth-email'] );
		$auth_sms = Utils::to_bool( $options['auth_sms'] );

		$sms_settings = [];
		if( Utils::to_bool( $options['sms'] ) ) {
			$sms_settings = SMS::get_settings();
		}

		if( !Utils::to_bool( $options['auth-email'] ) && !Utils::to_bool( $options['auth_sms'] ) ) return;
		?>
		<div id="auth-modal">
			<div id="auth-modal-msg"></div>
			<div id="auth-modal-inner">
				<?php if( Utils::to_bool( $options['show-auth-modal-logo'] ) ) { ?>
					<div id="auth-modal-logo">
						<?php echo Options::get_logo( [
							'type'			=> 'auth-modal-logo-type',
							'text-type'		=> 'auth-modal-logo-text-type',
							'text-custom'	=> 'auth-modal-logo-text-custom',
							'img'			=> 'auth-modal-logo-img',
							'img-size'		=> 'auth-modal-logo-img-size',
						], $default_options ) ?>
					</div>
				<?php } ?>

				<?php if( !empty( $sms_settings['settings']['auth']['one_form'] ) || $auth_sms ) { ?>
					<div class="auth-modal-content auth-modal-mobile" data-type="mobile">
						<?php get_template_part( "templates/modals/auth/mobile", null, [
							'auth_email'	=> $auth_email,
							'sms'			=> $sms_settings,
						] ) ?>
					</div>

					<div class="auth-modal-content auth-modal-otp" data-type="otp">
						<?php get_template_part( "templates/modals/auth/otp", null, [
							'sms'	=> $sms_settings,
						] ) ?>
					</div>
				<?php } ?>
				
				<?php if( $auth_email ) { ?>
					<div class="auth-modal-content auth-modal-login" data-type="login">
						<?php get_template_part( "templates/modals/auth/login", null, [
							'sms'	=> $sms_settings,
						] ) ?>
					</div>
				<?php } ?>

				<?php if( $auth_email || empty( $sms_settings['settings']['auth']['one_form'] ) ) { ?>
					<div class="auth-modal-content auth-modal-signup" data-type="signup">
						<?php get_template_part( "templates/modals/auth/signup", null, [
							'sms'	=> $sms_settings,
						] ) ?>
					</div>

					<div class="auth-modal-content auth-modal-lost_password" data-type="lost_password">
						<?php get_template_part( "templates/modals/auth/lost_password", null, [
							'sms'	=> $sms_settings,
						] ) ?>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}
}
if( !is_user_logged_in() ) {
	add_action( 'wp_footer', 'bijan_auth_modals' );
}