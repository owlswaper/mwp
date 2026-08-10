<?php
/**************************************************************************
***************************************************************************
***************************************************************************
***************************************************************************
⛔ DON'T EDIT THIS FILE ⛔
***************************************************************************
***************************************************************************
***************************************************************************
***************************************************************************/

if( !defined( 'ABSPATH' ) ) exit;

// Define constants
if( !defined( 'BIJAN_CHILD_DIR' ) ) {
	define( 'BIJAN_CHILD_DIR', trailingslashit( get_stylesheet_directory() ) );
}

if( !defined( 'BIJAN_CHILD_URI' ) ) {
	define( 'BIJAN_CHILD_URI', trailingslashit( get_stylesheet_directory_uri() ) );
}

if( !defined( 'BIJAN_CHILD_VERSION' ) ) {
	define( 'BIJAN_CHILD_VERSION', "1.0.0.5" );
}

if( !defined( 'BIJAN_CHILD_DEV' ) ) {
	define( 'BIJAN_CHILD_DEV', true );
}

if( !function_exists( "bijan_child_init" ) ) {
	function bijan_child_init() {
		load_theme_textdomain( 'bijan',  trailingslashit( get_template_directory() ) . 'languages' );
	}
}
add_action( 'init', 'bijan_child_init' );

if( !function_exists( "bijan_child_enqueue_styles" ) ) {
	function bijan_child_enqueue_styles() {
		include( trailingslashit( get_template_directory() ) . "inc/Scripts.php" );
		wp_enqueue_style( 'bijan-parent-rtl', trailingslashit( get_template_directory_uri() ) . 'rtl.css' );
		wp_enqueue_style( 'bijan-child-style', BIJAN_CHILD_URI . 'style.css', [], BIJAN_CHILD_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'bijan_child_enqueue_styles' );
/***************************************************************************/