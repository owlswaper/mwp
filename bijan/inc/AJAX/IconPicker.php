<?php
namespace Bijan\AJAX;

use Bijan\AJAX;
use Bijan\Utils;
use MJ\Whitebox\Utils\Files;

class IconPicker extends AJAX {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	public function html() {
		// Check for HTML cache
		$html_cache = apply_filters( 'bijan/icon-picker/html_cache', Files::get_file_path( 'bijan-icon-picker-' . BIJAN_VERSION . '.cache' ) );
		if( empty( $html_cache ) || !file_exists( $html_cache ) || BIJAN_DEV ) {
			$packs = Utils::get_icon_packs();

			ob_start();
			?>
			<div class="icon-picker-body">
				<div class="icon-picker-sidebar">
					<div class="icon-picker-packs">
						<div class="icon-picker-pack-selector selected" data-pack=""><?php esc_html_e( "All icons", 'bijan' ) ?></div>
						<?php foreach( $packs as $pack_name => $pack ) { ?>
							<div class="icon-picker-pack-selector" data-pack="<?php echo esc_attr( $pack_name ) ?>">
								<?php if( $pack['mode'] == 'font-icon' ) { ?>
									<i class="icon-picker-pack-selector-icon <?php echo esc_attr( $pack['label_icon'] ) ?>"></i>
								<?php } else { ?>
									<img src="<?php echo esc_url( $pack['label_icon'] ) ?>" alt="" class="icon-picker-pack-selector-icon">
								<?php } ?>
								<span class="icon-picker-pack-selector-label"><?php echo esc_html( is_rtl() ? $pack['label_fa'] : $pack['label'] ) ?></span>
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="icon-picker-icons-content">
					<div class="icon-picker-search-wrap">
						<input type="search" class="icon-picker-search ltr" value="" placeholder="<?php esc_attr_e( "Search...", 'bijan' ) ?>">
					</div>

					<div class="icon-picker-icon-packs">
						<?php foreach( $packs as $pack_name => $pack ) { ?>
							<div class="icon-picker-pack-content" data-pack="<?php echo $pack_name ?>">
								<div class="icon-picker-pack-title"><?php echo esc_html( is_rtl() ? $pack['label_fa'] : $pack['label'] ) ?></div>
								<div class="icon-picker-icons">
									<?php
									foreach( $pack['icons'] as $icon ) {
										$icon_name = ucwords( str_replace( "-", " ", $icon ) );
										?>
										<div class="icon-picker-icon">
											<?php if( $pack['mode'] == 'font-icon' ) { ?>
												<i class="icon-picker-icon-icon <?php echo esc_attr( $pack['prefix'] . $icon ) ?>"></i>
											<?php } else { ?>
												<img src="<?php echo esc_url( $icon ) ?>" class="icon-picker-icon-icon">
											<?php } ?>
											<span class="icon-picker-icon-name"><?php echo esc_html( $icon_name ) ?></span>
										</div>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php
			$html = ob_get_clean();
			echo $html;
			if( $html_cache ) {
				file_put_contents( $html_cache, $html );
			}
		} else {
			echo file_get_contents( $html_cache );
		}
		die;
	}
}