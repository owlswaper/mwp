<?php
use Bijan\Utils;
use Bijan\Utils\AdminUI;
use Bijan\Utils\SMS;
?>

<div id="<?php echo self::$PREFIX ?>settings-login-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title" id="<?php echo self::$PREFIX ?>settings-login-title"><?php esc_html_e( 'Login settings', 'bijan' ) ?></h3>
		<i class="bijan-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-login-status"><?php esc_html_e( 'Status', 'bijan' ) ?></label>
				</th>
	
				<td>
					<?php
					$login_status = Utils::to_bool( $settings['settings']['auth']['login']['enabled'] ?? true );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[auth][login][enabled]',
						'id'			=> self::$PREFIX . 'settings-auth-login-status',
						'value'			=> '1',
						'active'		=> $login_status,
						'label'			=> esc_html__( "Active login with mobile", 'bijan' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>
	
			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-login-pattern"><?php esc_html_e( 'Pattern code', 'bijan' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[auth][login][pattern]"
						id="<?php echo self::$PREFIX ?>settings-auth-login-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['auth']['login']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-login"><?php esc_html_e( 'Login OTP message', 'bijan' ) ?></label>
				</th>
	
				<td>
					<textarea
						name="<?php echo self::$PREFIX ?>settings[auth][login][message]"
						id="<?php echo self::$PREFIX ?>settings-auth-login"
						class="large-text <?php echo self::$PREFIX ?>settings-message"
						rows="5"
					><?php echo esc_textarea( $settings['messages']['auth']['login'] ?? '' ) ?></textarea>
					<p class="description"><?php echo esc_html__( 'You can use these variables in the message', 'bijan' ) ?>:</p>
					<?php Utils::variables_html( SMS::auth_variables(), true ) ?>
					<p
						class="description <?php echo self::$PREFIX ?>pattern-description"
						
					>
						<?php esc_html_e( 'Enter your variables in your provider settings in the order you used them in the pattern text. Separate each variable with ; example: {otp};{name}', 'bijan' ) ?>
					</p>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-login-otp_timer"><?php esc_html_e( 'OTP time', 'bijan' ) ?></label>
				</th>
	
				<td>
					<input
						type="number"
						min="30"
						name="<?php echo self::$PREFIX ?>settings[auth][login][otp_timer]"
						id="<?php echo self::$PREFIX ?>settings-auth-login-otp_timer"
						class="small-text ltr <?php echo self::$PREFIX ?>settings-otp_timer"
						value="<?php echo esc_attr( $settings['settings']['auth']['login']['otp_timer'] ?? 60 ) ?>"
					>
					<p class="description"><?php esc_html_e( 'Time in seconds that the OTP is valid.', 'bijan' ) ?></p>
				</td>
			</tr>
		</table>
	</div>
</div>	

<hr>
<div id="<?php echo self::$PREFIX ?>settings-register-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title" id="<?php echo self::$PREFIX ?>settings-register-title"><?php esc_html_e( 'Register settings', 'bijan' ) ?></h3>
		<i class="bijan-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-register-status"><?php esc_html_e( 'Status', 'bijan' ) ?></label>
				</th>
	
				<td>
					<?php
					$register_status = Utils::to_bool( $settings['settings']['auth']['register']['enabled'] ?? true );
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[auth][register][enabled]',
						'id'			=> self::$PREFIX . 'settings-auth-register-status',
						'value'			=> '1',
						'active'		=> $register_status,
						'label'			=> esc_html__( "Active register with mobile", 'bijan' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>
	
			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-register-pattern"><?php esc_html_e( 'Pattern code', 'bijan' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[auth][register][pattern]"
						id="<?php echo self::$PREFIX ?>settings-auth-register-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['auth']['register']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-register"><?php esc_html_e( 'Register OTP message', 'bijan' ) ?></label>
				</th>
	
				<td>
					<textarea
						name="<?php echo self::$PREFIX ?>settings[auth][register][message]"
						id="<?php echo self::$PREFIX ?>settings-auth-register"
						class="large-text <?php echo self::$PREFIX ?>settings-message"
						rows="5"
					><?php echo esc_textarea( $settings['messages']['auth']['register'] ?? '' ) ?></textarea>
					<p class="description"><?php echo esc_html__( 'You can use these variables in the message', 'bijan' ) ?>:</p>
					<?php Utils::variables_html( SMS::auth_variables(), true ) ?>
					<p
						class="description <?php echo self::$PREFIX ?>pattern-description"
						
					>
						<?php esc_html_e( 'Enter your variables in your provider settings in the order you used them in the pattern text. Separate each variable with ; example: {otp};{name}', 'bijan' ) ?>
					</p>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-register-otp_timer"><?php esc_html_e( 'OTP time', 'bijan' ) ?></label>
				</th>
	
				<td>
					<input
						type="number"
						min="30"
						name="<?php echo self::$PREFIX ?>settings[auth][register][otp_timer]"
						id="<?php echo self::$PREFIX ?>settings-auth-register-otp_timer"
						class="small-text ltr <?php echo self::$PREFIX ?>settings-otp_timer"
						value="<?php echo esc_attr( $settings['settings']['auth']['register']['otp_timer'] ?? 60 ) ?>"
					>
					<p class="description"><?php esc_html_e( 'Time in seconds that the OTP is valid.', 'bijan' ) ?></p>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-one_form"><?php esc_html_e( 'One form mode', 'bijan' ) ?></label>
				</th>
	
				<td id="<?php echo self::$PREFIX ?>settings-auth-one_form-wrap">
					<?php
					AdminUI::switch( [
						'name'		=> self::$PREFIX . 'settings[auth][one_form]',
						'id'		=> self::$PREFIX . 'settings-auth-one_form',
						'value'		=> '1',
						'active'	=> Utils::to_bool( $settings['settings']['auth']['one_form'] ?? true ),
						'label'		=> esc_html__( "One form for login and register.", 'bijan' ),
						'disabled'	=> !$login_status || !$register_status
					] );
					?>
					<p class="description"><?php esc_html_e( 'If you check one form mode, Login and register will used by a single form but you can change the settings for login or register.', 'bijan' ) ?></p>
				</td>
			</tr>
		</table>
	</div>
</div>

<hr>
<div id="<?php echo self::$PREFIX ?>settings-lost_password-container" class="<?php echo self::$PREFIX ?>settings-section">
	<div class="<?php echo self::$PREFIX ?>settings-section-head">
		<h3 class="<?php echo self::$PREFIX ?>section-title" id="<?php echo self::$PREFIX ?>settings-lost_password-title"><?php esc_html_e( 'Lost password settings', 'bijan' ) ?></h3>
		<i class="bijan-icon-bottom"></i>
	</div>
	<div class="<?php echo self::$PREFIX ?>settings-section-body">
		<table class="form-table">
			<tr class="<?php echo self::$PREFIX ?>settings-status-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-lost_password-status"><?php esc_html_e( 'Status', 'bijan' ) ?></label>
				</th>
	
				<td>
					<?php
					AdminUI::switch( [
						'name'			=> self::$PREFIX . 'settings[auth][lost_password][enabled]',
						'id'			=> self::$PREFIX . 'settings-auth-lost_password-status',
						'value'			=> '1',
						'active'		=> Utils::to_bool( $settings['settings']['auth']['lost_password']['enabled'] ?? true ),
						'label'			=> esc_html__( "Active lost password with mobile", 'bijan' ),
						'input_classes'	=> [self::$PREFIX . "status-switch"],
					] );
					?>
				</td>
			</tr>
	
			<tr class="<?php echo self::$PREFIX ?>settings-gateway-pattern-row">
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-lost_password-pattern"><?php esc_html_e( 'Pattern code', 'bijan' ) ?></label>
				</th>
	
				<td>
					<input
						type="text"
						name="<?php echo self::$PREFIX ?>settings[auth][lost_password][pattern]"
						id="<?php echo self::$PREFIX ?>settings-auth-lost_password-pattern"
						class="regular-text ltr <?php echo self::$PREFIX ?>settings-pattern"
						value="<?php echo esc_attr( $settings['settings']['auth']['lost_password']['pattern'] ?? '' ) ?>"
					>
				</td>
			</tr>
	
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>settings-auth-lost_password"><?php esc_html_e( 'New password message', 'bijan' ) ?></label>
				</th>
	
				<td>
					<textarea
						name="<?php echo self::$PREFIX ?>settings[auth][lost_password][message]"
						id="<?php echo self::$PREFIX ?>settings-auth-lost_password"
						class="large-text <?php echo self::$PREFIX ?>settings-message"
						rows="5"
					><?php echo esc_textarea( $settings['messages']['auth']['lost_password'] ?? '' ) ?></textarea>
					<p class="description"><?php echo esc_html__( 'You can use these variables in the message', 'bijan' ) ?>:</p>
					<?php Utils::variables_html( SMS::auth_variables( [
						'password'	=> esc_html__( 'Generated password', 'bijan' )
					], ['otp', 'end_time'] ), true ) ?>
					<p class="description"><?php esc_html_e( 'A new password will automatically be generated and set for the user.', 'bijan' ) ?></p>
					<p
						class="description <?php echo self::$PREFIX ?>pattern-description"
						
					>
						<?php esc_html_e( 'Enter your variables in your provider settings in the order you used them in the pattern text. Separate each variable with ; example: {otp};{name}', 'bijan' ) ?>
					</p>
				</td>
			</tr>
		</table>
	</div>
</div>