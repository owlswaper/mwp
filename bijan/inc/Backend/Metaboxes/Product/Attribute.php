<?php
namespace Bijan\Backend\Metaboxes\Product;

use Bijan\Utils\Product;

class Attribute {
	public static function fields( $attr ) {
		$attr_name = $attr->get_name();

		$product_id = wp_doing_ajax() && !empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : get_the_ID();

		$featured_attrs = [];
		if( $product_id ) {
			$featured_attrs = Product::get_featured_attributes( $product_id );
		}
		?>
		<tr>
			<td>
				<label><?php esc_html_e( 'Featured attribute', 'bijan' ) ?></label>
			</td>

			<td>
				<label>
					<input type="checkbox" name="bijan_attr[featured][<?php echo esc_attr( $attr_name ) ?>]" class="checkbox" value="<?php echo esc_attr( $attr_name ) ?>" <?php checked( true, in_array( $attr_name, $featured_attrs ) ) ?>>
					<?php esc_html_e( 'This attribute will show on top of single page as a featured attribute', 'bijan' ) ?>
				</label>
			</td>
		</tr>
		<?php
	}

	public static function save( $attribute, $data ) {
		$product_id = wp_doing_ajax() && !empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : get_the_ID();
		$featured_attrs = [];
		if( !empty( $data["bijan_attr"] ) && !empty( $data["bijan_attr"]['featured'] ) ) {
			$featured_attrs = array_values( array_map( 'sanitize_text_field', $data["bijan_attr"]['featured'] ) );
		}
		Product::update_featured_attributes( $product_id, $featured_attrs );

		return $attribute;
	}
}
add_action( 'woocommerce_after_product_attribute_settings', [Attribute::class, 'fields'], 10, 2 );
add_filter( 'woocommerce_admin_meta_boxes_prepare_attribute', [Attribute::class, 'save'], 10, 3 );