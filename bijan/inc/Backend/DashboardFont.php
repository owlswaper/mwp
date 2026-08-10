<?php
namespace Bijan\Backend;

use Bijan\Utils;
use Bijan\Utils\Options;

class DashboardFont {
	public static function enqueue() {
		$options = Options::get_options( [
			'wp-dashboard-font-change'	=> true,
			'wp-dashboard-font'			=> ['font-family' => 'IRANYekanXFANum']
		] );
		if( !Utils::to_bool( $options['wp-dashboard-font-change'] ) ) return;

		wp_enqueue_style( 'bijan-wp-dashboard', BIJAN_URI . "assets/css/backend/dashboard.min.css", [], BIJAN_VERSION );
		$css_code = ":root{--dashboard-font: {$options['wp-dashboard-font']['font-family']}}";
		if( in_array( $options['wp-dashboard-font']['font-family'], array_keys( Utils::fonts() ) ) ) {
			wp_enqueue_style( 'bijan-wp-font', BIJAN_URI . "assets/css/fonts/{$options['wp-dashboard-font']['font-family']}.min.css", [], BIJAN_VERSION );
		}
		wp_add_inline_style( 'bijan-wp-dashboard', $css_code );
	}
}
add_action( 'admin_enqueue_scripts', [DashboardFont::class, 'enqueue'] );
add_action( 'elementor/editor/after_enqueue_styles', [DashboardFont::class, 'enqueue'] );