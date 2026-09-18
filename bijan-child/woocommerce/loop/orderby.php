<?php
/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/orderby.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.7.0
 */

use Bijan\Utils;
use Bijan\Utils\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = Options::get_options( [
	'wc-show-archive-order'	=> true,
] );

$primary_classes = ['content-area', 'site-content', 'row'];
$show_sidebar = is_active_sidebar( 'sidebar-shop' );
if( $show_sidebar ) {
	$primary_classes[] = 'content-area-with-sidebar';
}

$id_suffix = wp_unique_id();
?>
</header>
<div id="primary" <?php echo Utils::prepare_html_classes( $primary_classes, true ) ?>>
	<?php do_action( 'bijan/wc/archive/start_primary' ); ?>
	<div class="cloz-catalog-toolbar">
		<?php if ( $show_sidebar ) { get_sidebar( 'shop' ); } ?>
		<?php if ( $options['wc-show-archive-order'] ) : ?>
		<form id="sort-wrap" class="woocommerce-ordering cloz-catalog-sort" method="get">
			<span class="cloz-sort-label" id="cloz-sort-label-<?php echo esc_attr( $id_suffix ); ?>">مرتب‌سازی:</span>
			<div class="cloz-sort-options" role="radiogroup" aria-labelledby="cloz-sort-label-<?php echo esc_attr( $id_suffix ); ?>">
				<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
				<button
					type="button"
					class="sort-item<?php echo $id === $orderby ? ' sort-item-active' : ''; ?>"
					data-sort="<?php echo esc_attr( $id ); ?>"
					role="radio"
					aria-checked="<?php echo $id === $orderby ? 'true' : 'false'; ?>"
				><?php echo esc_html( $name ); ?></button>
				<?php endforeach; ?>
			</div>
			<select name="orderby" class="orderby cloz-orderby-fallback" id="woocommerce-orderby-<?php echo esc_attr( $id_suffix ); ?>" tabindex="-1" aria-hidden="true">
				<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="hidden" name="paged" value="1" />
			<?php wc_query_string_form_fields( null, [ 'orderby', 'submit', 'paged', 'product-page' ] ); ?>
		</form>
		<?php endif; ?>
	</div>

	<div class="entry-container<?php echo $show_sidebar ? ' col-md-9 col-sm-12' : ' col-12' ?>">
