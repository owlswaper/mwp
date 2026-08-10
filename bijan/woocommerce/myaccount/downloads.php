<?php
/**
 * Downloads
 *
 * Shows downloads on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/downloads.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

use Bijan\Utils\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$downloads     = WC()->customer->get_downloadable_products();
$has_downloads = (bool) $downloads;

do_action( 'woocommerce_before_account_downloads', $has_downloads ); ?>

<?php if ( $has_downloads ) : ?>

	<?php do_action( 'woocommerce_before_available_downloads' ); ?>

	<?php do_action( 'woocommerce_available_downloads', $downloads ); ?>

	<?php do_action( 'woocommerce_after_available_downloads' ); ?>

<?php else : ?>

	<div class="empty-page">
		<i class="empty-page-icon empty-cart-icon bijan-icon-download-2"></i>
		<div class='empty-page-text'>
			<?php esc_html_e( Options::get_options( [
				'wc_empty_downloads_text'	=> esc_html__( 'No downloads available yet.', 'woocommerce' )
			] )['wc_empty_downloads_text'], 'woocommerce' ) ?>
		</div>
		<?php
		get_template_part( 'templates/components/button', null, [
			'text'	=> apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ),
			'link'	=> apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ),
			'align'	=> 'center'
		] );
		?>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_downloads', $has_downloads ); ?>
