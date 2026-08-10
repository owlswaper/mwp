<?php



if( !defined( 'ABSPATH' ) ) exit;

// Define constants
if( !defined( 'BIJAN_DIR' ) ) {
	define( 'BIJAN_DIR', trailingslashit( get_template_directory() ) );
}

if( !defined( 'BIJAN_URI' ) ) {
	define( 'BIJAN_URI', trailingslashit( get_template_directory_uri() ) );
}

if( !defined( 'BIJAN_VERSION' ) ) {
	define( 'BIJAN_VERSION', "2.2.1.0" );
}

if( !defined( 'BIJAN_DEV' ) ) {
	define( 'BIJAN_DEV', false );
}

if( !defined( 'BIJAN_IS_LOCAL' ) ) {
	define( 'BIJAN_IS_LOCAL', !empty( $_SERVER['SERVER_NAME'] ) && $_SERVER['SERVER_NAME'] == 'localhost' );
}

if( !function_exists( "bijan_custom_endpoints" ) ) {
	function bijan_custom_endpoints() {
		$links = ['wishlist', 'coupons', 'notifications'];
		foreach( $links as $link ) {
			add_rewrite_endpoint( $link, EP_PAGES );
		}
		flush_rewrite_rules();
	}
}
add_action( 'after_switch_theme', 'bijan_custom_endpoints' );

include( BIJAN_DIR . "Functions/Init.php" );
include( BIJAN_DIR . "Functions/Elementor.php" );
include( BIJAN_DIR . "Functions/Auth.php" );
include( BIJAN_DIR . "Functions/Archive.php" );
include( BIJAN_DIR . "Functions/Comments.php" );
include( BIJAN_DIR . "Functions/User.php" );
