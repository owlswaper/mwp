<?php

use Bijan\Utils\Options;
use Bijan\Utils\WC;

if( !defined( 'ABSPATH' ) ) exit;

$coupons = WC::get_active_coupons_for_user();
// print_r( $coupons ); die;
?>

<?php if( $coupons ) { ?>
	<div id="coupons-content">
		<table id="coupons-table">
			<thead id="coupons-head">
				<tr>
					<th class="coupons-head-item"><?php esc_html_e( 'Coupon', 'bijan' ) ?></th>
					<th class="coupons-head-item"><?php esc_html_e( 'Discount type', 'bijan' ) ?></th>
					<th class="coupons-head-item"><?php esc_html_e( 'Expiry date', 'bijan' ) ?></th>
					<th class="coupons-head-item"><?php esc_html_e( 'Coupon amount', 'bijan' ) ?></th>
				</tr>
			</thead>

			<tbody id="coupons">
				<?php
				foreach( $coupons as $coupon ) {
					$_type = $coupon->get_discount_type();
					$type = wc_get_coupon_types()[$_type];

					$amount = 0;
					if( $_type == 'percent' ) {
						$amount = sprintf( __( "%s percent", 'bijan' ), $coupon->get_amount() );
					} else if( $_type == 'fixed_cart' || $_type == 'fixed_product' ) {
						$amount = wc_price( $coupon->get_amount() );
					}
					?>
					<tr class="coupon">
						<td class="coupon-code"><code><?php echo esc_html( $coupon->get_code() ) ?></code></td>
						<td class="coupon-discount_type"><?php echo esc_html( $type ) ?></td>
						<td class="coupon-date_expires"><?php echo !empty( $coupon->get_date_expires() ) ? esc_html( date_i18n( "Y-m-d", $coupon->get_date_expires()->format( "U" ) ) ) : '------' ?></td>
						<td class="coupon-amount"><?php echo $amount ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
<?php } else { ?>
	<div class="empty-page">
		<i class="empty-page-icon empty-cart-icon bijan-icon-ticket"></i>
		<div class='empty-page-text'>
			<?php esc_html_e( Options::get_options( [
				'wc_empty_coupons_text'	=> esc_html__( 'There is no coupon code.', 'bijan' )
			] )['wc_empty_coupons_text'], 'woocommerce' ) ?>
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