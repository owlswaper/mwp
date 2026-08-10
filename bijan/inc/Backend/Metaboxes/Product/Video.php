<?php
namespace Bijan\Backend\Metaboxes\Product;

use Bijan\Utils;
use Bijan\Utils\Product;

class Video {
	PRIVATE STATIC $PREFIX = 'bijan_video_';
	PRIVATE STATIC $POST_TYPES = ['product'];

	public static function enqueue( $hook ) {
		if( !in_array( $hook, ['post.php', 'post-new.php'] ) ) return;
		if( get_post_type() != 'product' ) return;

		wp_enqueue_media();

		wp_enqueue_style( 'bijan-product-video', BIJAN_URI . "assets/css/backend/metaboxes/product/video.min.css", [], BIJAN_VERSION );
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-product-video', BIJAN_URI . "assets/js/backend/product/video.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-product-video', BIJAN_URI . "assets/js/backend/product/video.min.js", ['jquery'], BIJAN_VERSION, true );
		}
	}

	public static function add() {
		add_meta_box(
			self::$PREFIX,			// id
			__( 'Video', 'bijan' ),	// title
			[__CLASS__, 'view'],	// callback
			self::$POST_TYPES,		// screens
			'side'					// context
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "save-video", self::$PREFIX . "nonce" );
		$post_options = Product::get_options( $post->ID );
		?>
		<div id="<?php echo self::$PREFIX ?>wrap">
			<input type="hidden" name="<?php echo self::$PREFIX ?>video" value="<?php echo $post_options['video_id'] ?>" id="<?php echo self::$PREFIX ?>video_field">
			<video src="<?php echo $post_options['video_id'] ? wp_get_attachment_url( $post_options['video_id'] ) : '' ?>" id="<?php echo self::$PREFIX ?>video" controls <?php echo $post_options['video_id'] ? '' : ' style="display:none"' ?>></video>
			<button type="button" id="<?php echo self::$PREFIX ?>select" class="button button-primary"<?php echo $post_options['video_id'] ? ' style="display:none"' : '' ?>><?php esc_html_e( 'Select video file', 'bijan' ) ?></button>
			<a href="#" id="<?php echo self::$PREFIX ?>remove"<?php echo $post_options['video_id'] ? '' : ' style="display:none"' ?>><?php esc_html_e( 'Remove video', 'bijan' ) ?></a>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( !isset( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save-video" ) ) return;
		
		$options = [
			'video_id'	=> isset( $_POST[self::$PREFIX . "video"] ) ? Utils::convert_chars( $_POST[self::$PREFIX . "video"], true, 'absint' ) : 0,
		];
		Product::save_options( $options, $post_id );
	}
}
add_action( 'admin_enqueue_scripts', [Video::class, 'enqueue'] );
add_action( 'add_meta_boxes', [Video::class, 'add'] );
add_action( 'save_post', [Video::class, 'save'], 10, 2 );