<?php
namespace Bijan\Backend\Metaboxes\Product;

use Automattic\WooCommerce\Enums\ProductType;

use Bijan\Model\PriceHistory as ModelPriceHistory;
use Bijan\PublicScripts;
use Bijan\Utils;
use Bijan\Utils\WC\PriceHistory as UtilsPriceHistory;

use MJ\Whitebox\Utils\Date as WhiteboxDate;

class PriceHistory {
	PRIVATE STATIC $PREFIX = 'bijan_price_history_';
	PRIVATE STATIC $POST_TYPES = ['product'];

	public static function enqueue( $hook ) {
		if( !in_array( $hook, ['post.php', 'post-new.php'] ) ) return;
		if( get_post_type() != 'product' ) return;

		PublicScripts::pdp();
		wp_enqueue_script( 'wp-util' );

		wp_enqueue_style( 'bijan-wc-metabox-price_history', BIJAN_URI . "assets/css/backend/metaboxes/product/price_history.min.css", [], BIJAN_VERSION );
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-wc-metabox-price_history', BIJAN_URI . "assets/js/backend/product/price_history.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-wc-metabox-price_history', BIJAN_URI . "assets/js/backend/product/price_history.min.js", ['jquery'], BIJAN_VERSION, true );
		}
		wp_localize_script( 'bijan-wc-metabox-price_history', 'bijanPriceHistory', Utils::general_js_localizations() );
	}

	public static function add() {
		add_meta_box(
			self::$PREFIX,					// id
			__( 'Price history', 'bijan' ),	// title
			[__CLASS__, 'view'],			// callback
			self::$POST_TYPES				// screens
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "save-price_history", self::$PREFIX . "nonce" );
		$list = UtilsPriceHistory::list( $post->ID );
		$product = wc_get_product( $post->ID );
		?>
		<div id="<?php echo self::$PREFIX ?>wrap" <?php echo $product->is_type( 'variable' ) ? ' style="display:none"' : '' ?>>
			<p class="description"><?php esc_html_e( 'The list will be automatically sorted by time.', 'bijan' ) ?></p>
			<div id="<?php echo self::$PREFIX ?>list">
				<?php
				$index = 0;
				foreach( $list as $time => $value ) {
					self::item_template( $index, $time, $value );
					$index++;
				}
				self::item_template( $index );
				?>
			</div>
			<script type="text/html" id="tmpl-bijan-price_history-item">
				<?php self::item_template( "{{{data.index}}}" ) ?>
			</script>
		</div>
		<?php
	}

	public static function item_template( $index, $time = '', $value = '' ) {
		?>
		<div class="<?php echo self::$PREFIX ?>item" data-index="<?php echo $index ?>">
			<button type="button" class="<?php echo self::$PREFIX ?>item-action <?php echo self::$PREFIX ?>item-remove"><i class="dashicons dashicons-trash"></i></button>
			<input
				type="text"
				name="<?php echo self::$PREFIX ?>item[<?php echo $index ?>][time]"
				class="regular-text ltr <?php echo self::$PREFIX ?>time bijan-datepicker-input"
				data-time="<?php echo esc_attr( $time ) ?>"
				placeholder="<?php esc_attr_e( "Time", 'bijan' ) ?>"
				readonly
			>
			<input
				type="number"
				name="<?php echo self::$PREFIX ?>item[<?php echo $index ?>][value]"
				class="regular-text ltr <?php echo self::$PREFIX ?>value"
				inputmode="numeric"
				value="<?php echo esc_attr( $value ) ?>"
				placeholder="<?php esc_attr_e( "Value", 'bijan' ) ?>"
			>
			<button type="button" class="<?php echo self::$PREFIX ?>item-action <?php echo self::$PREFIX ?>item-add"><i class="dashicons dashicons-plus"></i></button>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( !isset( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save-price_history" ) ) return;

		$product = wc_get_product( $post_id );
		if( $product->is_type( ProductType::SIMPLE ) ) {
			$price_list = [];
			if( !empty( $_POST['bijan_price_history_item'] ) ) {
				$posted_price_list = wp_list_pluck( $_POST['bijan_price_history_item'], 'value', 'time' );
				$posted_price_list = array_filter( $posted_price_list );
				if( !empty( $posted_price_list ) ) {
					// print_r( $posted_price_list ); echo PHP_EOL;
					foreach( $posted_price_list as $time => $value ) {
						$time = strtotime( WhiteboxDate::maybe_j2g( $time . ":00" ) );
						$value = Utils::convert_chars( $value, true, 'floatval' );
						$price_list = UtilsPriceHistory::add_price_to_list( $price_list, $value, $time );
					}
				}
			}
			$price_list = UtilsPriceHistory::add_price_to_list( $price_list, $product->get_price() );

			$find_product = ModelPriceHistory::query()->select( 'id' )->where( 'product_id', $post_id )->first();
			if( !$find_product ) {
				$find_product = new ModelPriceHistory( [
					'product_id'	=> $post_id,
				] );
			}
			// print_r( $price_list ); die;
			$find_product->history = $price_list;
			$find_product->save();
		}
	}
}
add_action( 'admin_enqueue_scripts', [PriceHistory::class, 'enqueue'] );
add_action( 'add_meta_boxes', [PriceHistory::class, 'add'] );
add_action( 'save_post', [PriceHistory::class, 'save'], 10, 2 );