<?php
namespace Bijan\Backend\Metaboxes\Product;

use Bijan\Utils\Product;

class InstantDiscount {
	public static function fields() {
		$product_id = get_the_ID();

		$instant_discount = Product::get_instant_discount( $product_id );
		?>
		<div class="options_group">
			<?php
			woocommerce_wp_text_input(
				array(
					'id'                => 'bijan_instant_discount_total',
					'value'             => $instant_discount['total'],
					'label'             => __( '<strong>Total</strong> instant discount quantity', 'bijan' ),
					'description'       => __( 'Set to 0 to disable', 'bijan' ),
					'type'              => 'number',
					'custom_attributes'	=> [
						'min'	=> 0,
					]
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'bijan_instant_discount_remaining',
					'value'             => $instant_discount['remaining'],
					'label'             => __( '<strong>Remaining</strong> instant discount quantity', 'bijan' ),
					'description'       => __( 'Set to 0 to disable', 'bijan' ),
					'type'              => 'number',
					'custom_attributes'	=> [
						'min'	=> 0,
					]
				)
			);
			?>
		</div>
		<?php
	}

	public static function save( $product_id, $post ) {
		global $typenow;
		if ( 'product' === $typenow ) {
			if( $post->post_status == 'auto-draft' ) return;
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

			Product::update_instant_discount( $product_id, absint( $_POST["bijan_instant_discount_total"] ), absint( $_POST["bijan_instant_discount_remaining"] ) );
		}
	}
}
add_action( 'woocommerce_product_options_inventory_product_data', [InstantDiscount::class, 'fields'] );
add_action( 'save_post_product', [InstantDiscount::class, 'save'], 10, 2 );