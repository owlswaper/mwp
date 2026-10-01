<?php
/** Enamad validates the originating shop; noreferrer must not suppress it. */
defined( 'ABSPATH' ) || exit;

function clz_fix_enamad_referrer_html( $html ) {
	if ( ! is_string( $html ) || false === stripos( $html, 'trustseal.enamad.ir' ) ) { return $html; }
	$processor = new WP_HTML_Tag_Processor( $html );
	while ( $processor->next_tag() ) {
		$tag = $processor->get_tag();
		$attribute = 'A' === $tag ? 'href' : ( 'IMG' === $tag ? 'src' : '' );
		if ( ! $attribute ) { continue; }
		$url = $processor->get_attribute( $attribute );
		if ( ! is_string( $url ) || 'trustseal.enamad.ir' !== strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) ) ) { continue; }
		$processor->set_attribute( 'referrerpolicy', 'origin' );
		if ( 'A' === $tag ) {
			$rel = preg_split( '/\s+/', trim( (string) $processor->get_attribute( 'rel' ) ), -1, PREG_SPLIT_NO_EMPTY );
			$rel = array_values( array_filter( $rel, static function ( $value ) { return 'noreferrer' !== strtolower( $value ); } ) );
			if ( ! in_array( 'noopener', array_map( 'strtolower', $rel ), true ) ) { $rel[] = 'noopener'; }
			$processor->set_attribute( 'rel', implode( ' ', $rel ) );
		} else {
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
			if ( '717705' === (string) ( $query['id'] ?? '' ) && 'UEnBF1rDytYpVBldZUl8B2x8IG25l7jI' === ( $query['Code'] ?? '' ) ) {
				$processor->set_attribute( 'code', $query['Code'] );
			}
		}
	}
	return $processor->get_updated_html();
}

/** Repair stored native footer settings once, without overriding their render. */
function clz_install_enamad_referrer_fix() {
	$version = '2026-10-02-referrer-1';
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_theme_options' ) || get_option( 'clz_enamad_referrer_version' ) === $version ) { return; }
	$lock = (int) get_option( 'clz_enamad_referrer_lock' );
	if ( $lock && time() - $lock < 120 ) { return; }
	if ( $lock ) { delete_option( 'clz_enamad_referrer_lock' ); }
	if ( ! add_option( 'clz_enamad_referrer_lock', time(), '', false ) ) { return; }
	try {
		$options = get_option( 'bijan', array() );
		if ( ! is_array( $options ) ) { return; }
		$original = $options;
		foreach ( array( 'footer_orgs_logo_items' => 'org_logos', 'footer_before_org_items' => 'before_org_logos', 'footer_after_org_items' => 'after_org_logos' ) as $key => $field ) {
			if ( ! isset( $options[ $key ][ $field ] ) || ! is_array( $options[ $key ][ $field ] ) ) { continue; }
			foreach ( $options[ $key ][ $field ] as $index => $html ) { $options[ $key ][ $field ][ $index ] = clz_fix_enamad_referrer_html( $html ); }
		}
		if ( $options !== $original ) {
			add_option( 'clz_enamad_footer_before_referrer_fix', array_intersect_key( $original, array_flip( array( 'footer_orgs_logo_items', 'footer_before_org_items', 'footer_after_org_items' ) ) ), '', false );
			update_option( 'bijan', $options );
			$GLOBALS['bijan'] = $options;
		}
		$page = get_page_by_path( 'licenses', OBJECT, 'page' );
		if ( $page ) {
			$content = clz_fix_enamad_referrer_html( $page->post_content );
			if ( $content !== $page->post_content ) {
				add_post_meta( $page->ID, '_clz_licenses_before_referrer_fix', wp_slash( $page->post_content ), true );
				$id = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ), true );
				if ( is_wp_error( $id ) || ! $id ) { return; }
			}
		}
		update_option( 'clz_enamad_referrer_version', $version, false );
	} finally { delete_option( 'clz_enamad_referrer_lock' ); }
}
add_action( 'admin_init', 'clz_install_enamad_referrer_fix', 60 );
