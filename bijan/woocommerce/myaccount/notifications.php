<?php

use Bijan\Utils\Notifications;
use Bijan\Utils\Options;
use Bijan\Utils\UI;

if( !defined( 'ABSPATH' ) ) exit;

$notifications = Notifications::get_user_notifications( empty( $_GET['only-unread'] ) );
?>
<div id="notifications-filters" class="bijan_filter_additional_options">
	<?php UI::filter_radio( __( "Show only unread notifications", 'bijan' ), 'only-unread', true, [
		'radio-align'	=> 'start'
	] ) ?>
</div>
<?php if( $notifications ) { ?>
	<div id="notifications-content">
		<div id="notifications">
			<?php foreach( $notifications as $notification ) { ?>
				<div class="notification <?php echo $notification->read ? 'notification-read' : 'notification-unread' ?>" data-id="<?php echo esc_attr( $notification->ID ) ?>">
					<div class="notification-head">
						<div class="notification-details">
							<h4 class="notification-title"><?php echo esc_html( $notification->post_title ) ?></h4>
							<div class="notification-time"><?php echo esc_html( $notification->post_date ) ?></div>
						</div>

						<div class="notification-actions">
							<div class="notification-status"><?php echo $notification->read ? esc_html__( "Read", 'bijan' ) : esc_html__( "Unread", 'bijan' ) ?></div>
							<?php
							get_template_part( "templates/components/button", null, [
								'text'		=> __( "View", 'bijan' ),
								'classes'	=> ['notification-view'],
							] );
							?>
						</div>
					</div>

					<div class="notification-text"><?php echo wpautop( $notification->post_content ) ?></div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } else { ?>
	<div class="empty-page">
		<i class="empty-page-icon empty-cart-icon bijan-icon-notification"></i>
		<div class='empty-page-text'>
			<?php esc_html_e( Options::get_options( [
				'wc_empty_notifications_text'	=> esc_html__( 'The notification list is empty.', 'bijan' )
			] )['wc_empty_notifications_text'], 'woocommerce' ) ?>
		</div>
		<?php
		get_template_part( 'templates/components/button', null, [
			'text'	=> apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ),
			'link'	=> apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ),
			'align'	=> 'center'
		] );
		?>
	</div>
<?php } ?>