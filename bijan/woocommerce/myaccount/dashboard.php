<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

use Bijan\Model\Wishlist;
use Bijan\Utils\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$summery_items = [
	'completed-orders'	=> [
		'icon'	=> 'bijan-icon-cart-tick',
		'title'	=> esc_html__( 'Completed orders', 'bijan' ),
		'count'	=> 0,
		'link'	=> wc_get_account_endpoint_url( 'orders' ),
	],
	'wishlist-count'	=> [
		'icon'	=> 'bijan-icon-heart',
		'title'	=> esc_html__( 'Favorite products', 'bijan' ),
		'count'	=> Wishlist::query()->where( 'user_id', get_current_user_id() )->count(),
		'link'	=> wc_get_account_endpoint_url( 'wishlist' ),
	],
	'in-progress-orders'	=> [
		'icon'	=> 'bijan-icon-cart-happy',
		'title'	=> esc_html__( 'Orders in progress', 'bijan' ),
		'count'	=> 0,
		'link'	=> wc_get_account_endpoint_url( 'orders' ),
	],
	'canceled-orders'	=> [
		'icon'	=> 'bijan-icon-clipboard-close',
		'title'	=> esc_html__( 'Canceled orders', 'bijan' ),
		'count'	=> 0,
		'link'	=> wc_get_account_endpoint_url( 'orders' ),
	],
];

$orders = wc_get_orders( [
	'customer_id'	=> get_current_user_id(),
	'status'		=> ['wc-completed', 'wc-processing', 'wc-cancelled', 'wc-failed'],
	'limit'			=> -1,
] );

foreach( $orders as $order ) {
	$status = $order->get_status();
	if( $status == 'completed' ) {
		$summery_items['completed-orders']['count']++;
	} else if( $status == 'processing' ) {
		$summery_items['in-progress-orders']['count']++;
	} else if( $status == 'cancelled' || $status == 'failed' ) {
		$summery_items['canceled-orders']['count']++;
	}
}

$user = wp_get_current_user();
?>

<div id="myaccount-dashboard-summery">
	<?php foreach( $summery_items as $id => $item ) { ?>
		<a href="<?php echo esc_url( $item['link'] ) ?>" class="myaccount-dashboard-summery" id="myaccount-dashboard-summery-<?php echo esc_attr( $id ) ?>">
			<?php UI::curve( 'product' ) ?>
			<div class="myaccount-dashboard-summery-icon-wrap"><i class="myaccount-dashboard-summery-icon <?php echo esc_attr( $item['icon'] ) ?>"></i></div>
			<div class="myaccount-dashboard-summery-texts">
				<div class="myaccount-dashboard-summery-title"><?php echo esc_html( $item['title'] ) ?></div>
				<div class="myaccount-dashboard-summery-count"><?php echo esc_html( $item['count'] ) ?></div>
			</div>
		</a>
	<?php } ?>
</div>

<form method="post" action="" id="myaccount-dashboard-edit" class="bijan-section">
	<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
	<input type="hidden" name="action" value="save_account_details" />
	<div id="myaccount-dashboard-edit-head">
		<h4 id="myaccount-dashboard-edit-title"><?php esc_html_e( 'Personal info', 'bijan' ) ?></h4>
		<?php
		get_template_part( "templates/components/button", null, [
			'text'		=> __( 'Update info', 'bijan' ),
			'rounded'	=> true,
		] );
		?>
	</div>

	<div id="myaccount-dashboard-edit-inputs">
		<div class="myaccount-dashboard-edit-field">
			<label for="account_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
		</div>

		<div class="myaccount-dashboard-edit-field">
			<label for="account_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
		</div>

		<div class="myaccount-dashboard-edit-field">
			<label for="account_display_name"><?php esc_html_e( 'Display name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
		</div>

		<div class="myaccount-dashboard-edit-field">
			<label for="account_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
		</div>
	</div>
</form>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
