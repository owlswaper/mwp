<?php

use Bijan\Model\Wishlist;
use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\User;
use Bijan\Utils\Wishlist as UtilsWishlist;

if( !defined( 'ABSPATH' ) ) exit;

$user_id = get_current_user_id();
if( !empty( $_GET['remove'] ) && !empty( $_GET['nonce'] ) ) {
	$nonce = Utils::convert_chars( $_GET['nonce'] );
	$remove_product_id = Utils::convert_chars( $_GET['remove'], true, 'absint' );
	if( wp_verify_nonce( $nonce, "bijan-remove-wishlist-{$remove_product_id}" ) ) {
		UtilsWishlist::remove_from_wishlist( $remove_product_id, $user_id );
	}
}

$search_text = sanitize_text_field( get_query_var( 'search' ) );
$products = [];

$options = Options::get_options( [
	'wishlist_ppp'	=> 3,
] );
$ppp = Utils::absint_pro( $options['wishlist_ppp'], 1 );
$current_page = !empty( $_GET['wishlist-page'] ) ? Utils::convert_chars( $_GET['wishlist-page'], true, 'absint' ) : 1;
$current_page = $current_page < 1 ? 1 : $current_page;
$offset = ( $current_page - 1 ) * $ppp;

if( !$search_text ) {
	$products = User::get_wishlist_products(
		$user_id,
		'products',
		[
			'columns'	=> 'product_id',
		],
		[
			'limit'		=> $ppp,
			'offset'	=> $offset,
			'paginate'	=> true,
		]
	);
} else {
	$wishlist_table = Wishlist::tableName();
	global $wpdb;
	$query = "SELECT DISTINCT `{$wpdb->posts}`.`ID` FROM `{$wpdb->posts}` LEFT JOIN `{$wishlist_table}` ON `{$wpdb->posts}`.`ID`=`{$wishlist_table}`.`product_id` WHERE `{$wishlist_table}`.`user_id`=%d AND `{$wpdb->posts}`.`post_title` LIKE %s ORDER BY `{$wishlist_table}`.`created_at` DESC";
	$query = $wpdb->prepare( $query, [
		get_current_user_id(),
		'%' . $wpdb->esc_like( $search_text ) . '%',
	] );
	$product_ids = $wpdb->get_col( $query );
	if( !empty( $product_ids ) ) {
		$products = wc_get_products( [
			'include'	=> $product_ids,
			'limit'		=> $ppp,
			'offset'	=> $offset,
			'paginate'	=> true,
		] );
	}
}
?>

<?php if( $products ) { ?>
	<div id="wishlist-content">
		<form action="" method="get" id="wishlist-search">
			<label class="screen-reader-text"><?php esc_html_e( 'Search for:', 'bijan' ) ?></label>
			<button type="submit" class="button-transparent circle" title="<?php echo esc_attr_e( "Search", 'bijan' ) ?>"><i class="bijan-icon-search-normal"></i></button>
			<input type="search" name="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search in wishlist', 'placeholder', 'bijan' ) ?>" value="<?php echo esc_attr( $search_text ) ?>" title="<?php echo esc_attr_x( 'Search for:', 'label', 'bijan' ) ?>" />
		</form>

		<div id="wishlist-items">
			<?php
			foreach( $products->products as $product ) {
				$product_link = get_permalink( $product->get_id() );
				?>
				<div <?php wc_product_class( 'wishlist-item', $product ) ?>>
					<div class="wishlist-item-image">
						<?php echo $product->get_image( [86, 86] ) ?>
					</div>

					<div class="wishlist-item-texts">
						<h3 class="wishlist-item-title">
							<a href="<?php echo esc_url( $product_link ) ?>" class="wishlist-item-link"><?php echo esc_html( $product->get_name() ) ?></a>
						</h3>
						<div class="wishlist-item-price">
							<?php echo $product->get_price_html() ?>
						</div>
					</div>

					<div class="wishlist-item-buttons">
						<?php
						get_template_part( "templates/components/button", null, [
							'text'	=> __( 'View', 'bijan' ),
							'link'	=> $product_link,
						] );

						$remove_url = add_query_arg( [
							'remove'	=> $product->get_id(),
							'nonce'		=> wp_create_nonce( "bijan-remove-wishlist-" . $product->get_id() ),
						] );
						get_template_part( "templates/components/button", null, [
							'text'	=> __( 'Remove', 'bijan' ),
							'type'	=> 'gray',
							'link'	=> $remove_url,
						] );
						?>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
		if( $products->max_num_pages > 1 ) {
			get_template_part( 'templates/archives/pagination', 'custom', [
				'query'				=> $products,
				'paged'				=> $current_page,
				'query_arg_name'	=> 'wishlist-page',
			] );
		}
		?>
	</div>
<?php } else { ?>
	<div class="empty-page">
		<i class="empty-page-icon empty-cart-icon bijan-icon-heart"></i>
		<div class='empty-page-text'>
			<?php esc_html_e( Options::get_options( [
				'wc_empty_wishlist_text'	=> esc_html__( 'There are no products in wishlist.', 'bijan' )
			] )['wc_empty_wishlist_text'], 'woocommerce' ) ?>
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