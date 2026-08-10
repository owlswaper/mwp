<?php
namespace Bijan\Backend\Metaboxes\Product;

use Bijan\Utils;
use Bijan\Utils\Product;

class Notes {
	PRIVATE STATIC $PREFIX = 'bijan_notes_';
	PRIVATE STATIC $POST_TYPES = ['product'];

	public static function add() {
		add_meta_box(
			self::$PREFIX,			// id
			__( 'Notes', 'bijan' ),	// title
			[__CLASS__, 'view'],	// callback
			self::$POST_TYPES		// screens
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "save-notes", self::$PREFIX . "nonce" );
		$product_options = Product::get_options( $post->ID );
		?>
		<p class="description"><?php esc_html_e( 'This text will show on top of page and bottom of the add to cart section', 'bijan' ) ?></p>
		<?php wp_editor( $product_options['notes'], self::$PREFIX . "notes" ) ?>
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( !isset( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save-notes" ) ) return;
		
		Product::save_options( [
			'bijan_notes'	=> wp_kses_post( $_POST[self::$PREFIX . "notes"] )
		], $post_id );
	}
}
add_action( 'add_meta_boxes', [Notes::class, 'add'] );
add_action( 'save_post', [Notes::class, 'save'], 10, 2 );