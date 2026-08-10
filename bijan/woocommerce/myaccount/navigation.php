<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\UI;
use Bijan\Utils\WC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );

$icons = WC::my_account_menu_link_icons();

$default_options = [
	'my-account-show-logo'	=> true,
	'my-account-logo-link'	=> home_url(),
	'my-account-logo-type'	=> 'img',
	'my-account-logo-img'	=> BIJAN_URI . "assets/img/logo.svg",
	'my-account-menu-open'	=> is_rtl() ? 'bijan-icon-double-arrow-left' : 'bijan-icon-double-arrow-right',
];
$options = Options::get_options( $default_options );
?>

<?php
static $opener_added = false;
if( !$opener_added ) {
	$opener_added = true;
	?>
	<div class="my-account-menu-expand-btn" tabindex="0" role="link" aria-label="<?php esc_attr_e( "Open menu", 'bijan' ) ?>" aria-expanded="false">
		<span class="screen-reader-text"><?php esc_html_e( "Open menu", 'bijan' ) ?></span>
		<i class="<?php echo $options['my-account-menu-open'] ?> my-account-menu-open-icon" aria-hidden="true"></i>
	</div>
	<?php
}
?>

<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<?php
	UI::curve( 'product' );
	if( $options['my-account-show-logo'] ) {
		?>
		<div id="myaccount-logo-wrap">
			<?php
			echo Options::get_logo( [
				'type'			=> 'my-account-logo-type',
				'text-type'		=> 'my-account-logo-text-type',
				'text-custom'	=> 'my-account-logo-text-custom',
				'img'			=> 'my-account-logo-img',
				'img-size'		=> 'my-account-logo-img-size',
			], $default_options );
			?>
		</div>
	<?php } ?>

	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<i class="myaccount-nav-icon<?php echo !empty( $icons[$endpoint] ) ? " {$icons[$endpoint]}" : '' ?>"></i>
					<span class="myaccount-nav-text"><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
