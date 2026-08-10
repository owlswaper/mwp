<?php
namespace Bijan;


use Bijan\Utils\Options;
use MJ\Whitebox\Utils as WhiteboxUtils;

class Utils extends WhiteboxUtils {
	public static function app_markets() {
		return [
			'cafebazar'		=> __( "Cafebazar", 'bijan' ),
			'myket'			=> __( "Myket", 'bijan' ),
			'google_play'	=> __( "Google Play", 'bijan' ),
			'galaxy_store'	=> __( "Galaxy Store", 'bijan' ),
			'app_store'		=> __( "Apple App Store", 'bijan' ),
			'sibapp'		=> __( "Sibapp", 'bijan' ),
			'anardoni'		=> __( "Anardoni", 'bijan' ),
			'iapps'			=> __( "iApps", 'bijan' ),
			'custom'		=> __( "Custom market", 'bijan' ),
		];
	}

	public static function megamenu_columns() {
		$columns = [
			'auto'	=> _x( "Auto", 'MegaMenu item column type', 'bijan' ),
		];
		for( $index = 1; $index <= 6; $index++ ) {
			$columns[$index] = sprintf( _x( "%d column", 'MegaMenu item column type', 'bijan' ), $index );
		}
		return $columns;
	}

	public static function fonts() {
		return apply_filters( 'bijan/fonts', [
			'IRANYekanX'		=> [
				'fa'	=> __( 'IRANYekanX', 'bijan' ),
				'en'	=> 'IRANYekanX'
			],
			'IRANYekanXFANum'	=> [
				'fa'	=> __( 'IRANYekanXFANum', 'bijan' ),
				'en'	=> 'IRANYekanXFANum'
			],
			'AbiFANum'			=> [
				'fa'	=> __( 'AbiFANum', 'bijan' ),
				'en'	=> 'AbiFANum'
			],
			'Abi'				=> [
				'fa'	=> __( 'Abi', 'bijan' ),
				'en'	=> 'Abi'
			],
			'Rokh'				=> [
				'fa'	=> __( 'Rokh', 'bijan' ),
				'en'	=> 'Rokh'
			],
			'RokhFANum'			=> [
				'fa'	=> __( 'RokhFANum', 'bijan' ),
				'en'	=> 'RokhFANum'
			],
			'Vazirmatn'			=> [
				'fa'	=> __( 'Vazirmatn', 'bijan' ),
				'en'	=> 'Vazirmatn'
			],
			'VazirmatnFANum'	=> [
				'fa'	=> __( 'VazirmatnFANum', 'bijan' ),
				'en'	=> 'VazirmatnFANum'
			],
			'Estedad'			=> [
				'fa'	=> __( 'Estedad', 'bijan' ),
				'en'	=> 'Estedad'
			],
			'EstedadFANum'	=> [
				'fa'	=> __( 'EstedadFANum', 'bijan' ),
				'en'	=> 'EstedadFANum'
			],
		] );
	}

	public static function default_active_fonts() {
		return apply_filters( 'bijan/fonts/default_actives', ['IRANYekanXFANum'] );
	}

	public static function get_font_stylesheet( string $font_name ) {
		return apply_filters( "bijan/fonts/{$font_name}/stylesheet", BIJAN_URI . "assets/css/fonts/{$font_name}.min.css" );
	}

	public static function get_active_fonts() {
		static $cached_active_fonts = null;
		if( $cached_active_fonts !== null ) {
			return $cached_active_fonts;
		}

		$fonts = array_keys( self::fonts() );
		$default_options = [];
		foreach( $fonts as $font ) {
			$default_options["font_{$font}"] = in_array( $font, self::default_active_fonts() );
		}
		include_once( BIJAN_DIR . 'inc/Utils/Options.php' );
		$options = array_filter( Options::get_options( $default_options ) );
		
		$active_fonts = [];
		foreach( $fonts as $font ) {
			if( !empty( $options["font_{$font}"] ) ) {
				$active_fonts[] = $font;
			}
		}

		$cached_active_fonts = apply_filters( 'bijan/fonts/active', $active_fonts );
		return $cached_active_fonts;
	}

	public static function get_icon_packs() {
		static $packs = null;
		if( $packs === null ) {
			$theme_packs = wp_json_file_decode( BIJAN_DIR . "assets/icons.json", ['associative' => true] );
			$packs = apply_filters( 'bijan/icon-picker/packs', $theme_packs );

			// Set svg icons url
			foreach( $packs as $pack_name => $pack ) {
				if( $pack['mode'] == 'svg' ) {
					$pack['dir'] = trailingslashit( $pack['dir'] );
					foreach( $pack['icons'] as $icon_index => $icon ) {
						$svg_url = BIJAN_URI . "assets/{$pack['dir']}{$icon}";
						if( !isset( $theme_packs[$pack_name] ) ) {
							$svg_url = "{$pack['dir']}{$icon}";
						}
						if( substr( $svg_url, -4 ) != '.svg' ) {
							$svg_url .= '.svg';
						}

						if( $pack['label_icon'] == str_replace( ".svg", "", $icon ) ) {
							$packs[$pack_name]['label_icon'] = $svg_url;
						}

						$packs[$pack_name]['icons'][$icon_index] = $svg_url;
					}
				}
			}
		}

		return $packs;
	}

	/**
	 * Get HTML of the variables can be used in the string options
	 *
	 * @param array $variables. Key for variable and value for description
	 * @return void HTML
	 */
	public static function variables_html( array $variables ) {
		?>
		<div class="bijan_variables">
			<?php foreach( $variables as $variable => $description ) { ?>
				<div class="bijan_variable" data-variable="<?php echo esc_attr( $variable ) ?>">
					<code class="bijan_variable-value">{<?php echo esc_html( $variable ) ?>}</code><span class="bijan_variable-description"><?php echo esc_html( $description ) ?></span>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	public static function general_js_localizations() {
		return [
			'rtl'	=> is_rtl(),
			'i18n'	=> [
				'today'		=> __( "Today", 'bijan' ),
				'submit'	=> _x( "Submit", 'Date picker', 'bijan' ),
			],
		];
	}
}
