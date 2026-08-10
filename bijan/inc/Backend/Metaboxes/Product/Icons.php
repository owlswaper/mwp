<?php
namespace Bijan\Backend\Metaboxes\Product;

use Bijan\AdminScripts;
use Bijan\Utils;
use Bijan\Utils\AdminUI;
use Bijan\Utils\Product;

class Icons {
	PRIVATE STATIC $PREFIX = 'bijan_icons_';
	PRIVATE STATIC $POST_TYPES = ['product'];

	public static function enqueue( $hook ) {
		if( !in_array( $hook, ['post.php', 'post-new.php'] ) ) return;
		if( get_post_type() != 'product' ) return;

		AdminScripts::metabox();
		AdminScripts::switch_select();
		AdminScripts::attachment();

		wp_enqueue_style( 'bijan-wc-metabox-icons', BIJAN_URI . "assets/css/backend/metaboxes/product/icons.min.css", [], BIJAN_VERSION );
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-wc-metabox-icons', BIJAN_URI . "assets/js/backend/product/icons.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-wc-metabox-icons', BIJAN_URI . "assets/js/backend/product/icons.min.js", ['jquery'], BIJAN_VERSION, true );
		}
		wp_localize_script( 'bijan-wc-metabox-icons', 'bijanProductIcons', [
			'settings'	=> [
				Product::get_icons_from_settings( 1 ),
				Product::get_icons_from_settings( 2 ),
				Product::get_icons_from_settings( 3 ),
				Product::get_icons_from_settings( 4 ),
			],
		] );
	}

	public static function add() {
		add_meta_box(
			self::$PREFIX,			// id
			__( 'Icons', 'bijan' ),	// title
			[__CLASS__, 'view'],	// callback
			self::$POST_TYPES		// screens
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "save-icons", self::$PREFIX . "nonce" );
		$icons = Product::get_icons( $post->ID );
		$default_icons = Product::default_icons();
		?>
		<div id="<?php echo self::$PREFIX ?>icons">
			<?php foreach( $icons as $index => $icon_data ) { ?>
				<div class="<?php echo self::$PREFIX ?>icon-wrap<?php echo $index === 0 ? ' opened' : '' ?>" data-index="<?php echo $index ?>">
					<div class="<?php echo self::$PREFIX ?>icon-head">
						<?php
						$icon_url = is_numeric( $icon_data['icon'] ) ? wp_get_attachment_image_url( $icon_data['icon'], [52, 52] ) : $icon_data['icon'];
						echo '<img src="' . $icon_url . '" alt="">';
						?>
						<div class="<?php echo self::$PREFIX ?>icon-head-title"><?php echo esc_html( $icon_data['title'] ) ?></div>
						<i class="dashicons dashicons-arrow-down-alt2"></i>
					</div>

					<div class="<?php echo self::$PREFIX ?>icon-body">
						<div class="<?php echo self::$PREFIX ?>icon-input-wrap">
							<label><?php esc_html_e( 'Icon type', 'bijan' ) ?></label>
							<?php
							AdminUI::switch_select( [
								'name'		=> self::$PREFIX . "icons[{$index}][type]",
								'id'		=> self::$PREFIX . "icon_type_{$index}-default",
								'active'	=> $icon_data['type'],
								'options'	=> [
									''			=> esc_html__( "From settings", 'bijan' ),
									'default'	=> esc_html__( "Default", 'bijan' ),
									'custom'	=> esc_html__( "Custom", 'bijan' ),
								],
								'classes'	=> [self::$PREFIX . 'icon_type']
							] );
							?>
						</div>	
						
						<div class="<?php echo self::$PREFIX ?>fields"<?php echo $icon_data['type'] === '' ? ' style="display:none"' : '' ?>>
							<div class="<?php echo self::$PREFIX ?>default-icons"<?php echo $icon_data['type'] == 'default' ? '' : ' style="display:none"' ?>>
								<?php
								foreach( $default_icons as $default_icon_index => $default_icon_url ) {
									$selected = $icon_data['icon'] === $default_icon_url;
									if( !$selected ) {
										if( !$icon_data['type'] && $default_icon_index === $index ) {
											$selected = true;
										}
									}
									?>
									<img src="<?php echo $default_icon_url ?>" alt="" class="<?php echo self::$PREFIX ?>default-icon<?php echo $selected ? ' selected' : '' ?>">
								<?php } ?>
							</div>

							<div class="<?php echo self::$PREFIX ?>custom-icon"<?php echo $icon_data['type'] == 'custom' ? '' : ' style="display:none"' ?>>
								<?php
								AdminUI::attachment( [
									'name'	=> self::$PREFIX . "icons[{$index}][icon]",
									'file'	=> is_numeric( $icon_data['icon'] ) ? $icon_data['icon'] : 0,
									'icon'	=> 'dashicons dashicons-media-default',
									'type'	=> 'image',
								] );
								?>
							</div>

							<div class="<?php echo self::$PREFIX ?>icon-input-wrap">
								<label for="<?php echo self::$PREFIX ?>icon-<?php echo $index ?>-title"><?php esc_html_e( 'Title', 'bijan' ) ?></label>
								<input
									type="text"
									name="<?php echo self::$PREFIX ?>icons[<?php echo $index ?>][title]"
									id="<?php echo self::$PREFIX ?>icon-<?php echo $index ?>-title"
									class="regular-text <?php echo self::$PREFIX ?>icon-title-input"
									value="<?php echo esc_attr( $icon_data['title'] ) ?>"
								>
							</div>

							<div class="<?php echo self::$PREFIX ?>icon-input-wrap">
								<label for="<?php echo self::$PREFIX ?>icon-<?php echo $index ?>-subtitle"><?php esc_html_e( 'Subtitle', 'bijan' ) ?></label>
								<input
									type="text"
									name="<?php echo self::$PREFIX ?>icons[<?php echo $index ?>][subtitle]"
									id="<?php echo self::$PREFIX ?>icon-<?php echo $index ?>-subtitle"
									class="regular-text <?php echo self::$PREFIX ?>icon-subtitle-input"
									value="<?php echo esc_attr( $icon_data['subtitle'] ) ?>"
								>
							</div>

							<div class="<?php echo self::$PREFIX ?>icon-input-wrap">
								<label for="<?php echo self::$PREFIX ?>icon-<?php echo $index ?>-link"><?php esc_html_e( 'Link', 'bijan' ) ?></label>
								<input
									type="url"
									name="<?php echo self::$PREFIX ?>icons[<?php echo $index ?>][link]"
									id="<?php echo self::$PREFIX ?>icon-<?php echo $index ?>-link"
									class="ltr regular-text <?php echo self::$PREFIX ?>icon-link-input"
									value="<?php echo esc_url( $icon_data['link'] ) ?>"
								>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( !isset( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save-icons" ) ) return;
		
		Product::save_icons( $post_id, $_POST[self::$PREFIX . "icons"] );
	}
}
add_action( 'admin_enqueue_scripts', [Icons::class, 'enqueue'] );
add_action( 'add_meta_boxes', [Icons::class, 'add'] );
add_action( 'save_post', [Icons::class, 'save'], 10, 2 );