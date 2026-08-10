<?php
namespace Bijan\Metaboxes\Backend\Story;

use Bijan\AdminScripts;
use Bijan\PublicScripts;
use Bijan\Utils;
use Bijan\Utils\AdminUI;
use Bijan\Utils\Story;

if( !defined( 'ABSPATH' ) ) exit;

class Settings {
	PRIVATE STATIC $PREFIX = "bijan_";
	PRIVATE STATIC $POST_TYPES = ['story'];

	public static function enqueue( $hook ) {
		if( !in_array( $hook, ['post-new.php', 'post.php'] ) || !in_array( get_post_type(), self::$POST_TYPES ) ) return;
		
		wp_enqueue_media();
		PublicScripts::select2();
		AdminScripts::metabox();
		AdminScripts::attachment();
	}

	public static function add() {
		add_meta_box(
			self::$PREFIX,				// id
			__( 'Settings', 'bijan' ),	// title
			[__CLASS__, 'view'],		// callback
			self::$POST_TYPES			// screens
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "save_story", self::$PREFIX . "nonce" );

		$settings = Story::get_options( $post->ID );
		?>
		<div class="bijan_metabox">
			<table class="form-table">
				<tr>
					<th>
						<label for="<?php echo self::$PREFIX ?>post"><?php esc_html_e( 'Related post or product', 'bijan' ) ?></label>
					</th>

					<td>
						<select name="<?php echo self::$PREFIX ?>post" id="<?php echo self::$PREFIX ?>post" class="bijan_metabox_post_finder">
							<?php
							if( !empty( $settings['post'] ) ) {
								$post = get_post( $settings['post'] );
								?>
								<option value="<?php echo esc_attr( $settings['post'] ) ?>" selected><?php echo esc_html( $post->post_title ) ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>

				<tr>
					<th>
						<label><?php esc_html_e( 'Small image', 'bijan' ) ?></label>
					</th>

					<td>
						<?php
						AdminUI::attachment( [
							'name'	=> self::$PREFIX . "small_img",
							'file'	=> $settings['small_img'],
							'type'	=> 'image',
						] );
						?>
						<p class="description"><?php esc_html_e( 'Only image is supported', 'bijan' ) ?></p>
					</td>
				</tr>

				<tr>
					<th>
						<label><?php esc_html_e( 'Attachment', 'bijan' ) ?></label>
					</th>

					<td>
						<?php
						AdminUI::attachment( [
							'name'	=> self::$PREFIX . "attachment",
							'file'	=> $settings['attachment'],
							'type'	=> 'image,video'
						] );
						?>
						<p class="description"><?php esc_html_e( 'The attachment aspect ratio should be 16:9 to show correctly', 'bijan' ) ?></p>
						<p class="description"><?php esc_html_e( 'You can select image or video', 'bijan' ) ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( empty( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save_story" ) ) return;

		$settings = [
			'post'			=> Utils::convert_chars( $_POST[self::$PREFIX . "post"], true, 'absint' ),
			'small_img'		=> Utils::convert_chars( $_POST[self::$PREFIX . "small_img"], true, 'absint' ),
			'attachment'	=> Utils::convert_chars( $_POST[self::$PREFIX . "attachment"], true, 'absint' ),
		];
		Story::save_options( $settings, $post_id );
	}
}
add_action( 'admin_enqueue_scripts', [Settings::class, 'enqueue'] );
add_action( 'add_meta_boxes', [Settings::class, 'add'] );
add_action( 'save_post', [Settings::class, 'save'], 10, 2 );