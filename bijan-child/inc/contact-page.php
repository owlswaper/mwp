<?php
/**
 * Action-first contact page routing and assets.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Match common contact-page slugs as well as an existing Persian page title.
 *
 * @return bool
 */
function clz_is_contact_page() {
	if ( ! is_page() ) {
		return false;
	}

	$page = get_queried_object();

	if ( ! $page instanceof WP_Post ) {
		return false;
	}

	if ( in_array( $page->post_name, array( 'contact-us', 'contact', 'تماس-با-ما' ), true ) ) {
		return true;
	}

	return false !== strpos( (string) $page->post_title, 'تماس' );
}

/**
 * Use the child theme's dedicated contact template automatically.
 */
function clz_contact_page_template( $template ) {
	if ( ! clz_is_contact_page() ) {
		return $template;
	}

	$contact_template = trailingslashit( get_stylesheet_directory() ) . 'page-contact.php';

	return file_exists( $contact_template ) ? $contact_template : $template;
}
add_filter( 'template_include', 'clz_contact_page_template', 99 );

/**
 * Load visual styles only for the contact page.
 */
function clz_enqueue_contact_page_styles() {
	if ( ! clz_is_contact_page() ) {
		return;
	}

	$file = trailingslashit( get_stylesheet_directory() ) . 'assets/contact-page.css';
	$url  = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/contact-page.css';

	wp_enqueue_style(
		'clz-contact-page',
		$url,
		array( 'bijan-child-style' ),
		file_exists( $file ) ? (string) filemtime( $file ) : BIJAN_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'clz_enqueue_contact_page_styles', 30 );
