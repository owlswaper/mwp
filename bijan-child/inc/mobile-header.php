<?php
/**
 * Mobile header enhancements.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Find the site's contact page without hard-coding a database ID.
 *
 * @return string
 */
function clz_contact_page_url() {
	static $url = null;

	if ( null !== $url ) {
		return $url;
	}

	$paths = array( 'contact-us', 'contact', 'تماس-با-ما' );

	foreach ( $paths as $path ) {
		$page = get_page_by_path( $path, OBJECT, 'page' );

		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$url = (string) get_permalink( $page );

			return $url;
		}
	}

	$possible_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			's'              => 'تماس',
			'no_found_rows'  => true,
		)
	);

	foreach ( $possible_pages as $page ) {
		if ( false !== strpos( (string) $page->post_title, 'تماس' ) ) {
			$url = (string) get_permalink( $page );

			return $url;
		}
	}

	$url = home_url( '/contact-us/' );

	return $url;
}

/**
 * Load the small state-sync script after the parent theme's main script.
 */
function clz_enqueue_mobile_header_script() {
	$file = trailingslashit( get_stylesheet_directory() ) . 'assets/mobile-header.js';
	$url  = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/mobile-header.js';

	wp_enqueue_script(
		'clz-mobile-header',
		$url,
		array( 'bijan' ),
		file_exists( $file ) ? (string) filemtime( $file ) : BIJAN_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'clz_enqueue_mobile_header_script', 30 );
