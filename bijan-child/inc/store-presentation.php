<?php
/** One-time setup through Bijan's own settings and WordPress menu locations. */
defined( 'ABSPATH' ) || exit;

function clz_setup_native_footer_menu( $menu_id, $name, $slugs ) {
	$menu = $menu_id ? wp_get_nav_menu_object( $menu_id ) : false;
	if ( ! $menu ) { $menu = wp_get_nav_menu_object( $name ); }
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) { return $menu_id; }
	} else { $menu_id = (int) $menu->term_id; }
	$items = wp_get_nav_menu_items( $menu_id );
	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( ! $page || 'publish' !== $page->post_status ) { return new WP_Error( 'clz_missing_page', 'برگه اطلاعات فروشگاه هنوز آماده نیست.' ); }
		$exists = false;
		foreach ( (array) $items as $item ) {
			if ( ! is_object( $item ) ) { continue; }
			$attributes = clz_information_menu_links( array( 'href' => $item->url ) );
			if ( ( 'page' === $item->object && (int) $item->object_id === (int) $page->ID ) || untrailingslashit( $attributes['href'] ) === untrailingslashit( get_permalink( $page ) ) ) { $exists = true; break; }
		}
		if ( $exists ) { continue; }
		$id = wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-object-id' => $page->ID, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-title' => clz_information_pages()[ $slug ]['title'], 'menu-item-status' => 'publish' ) );
		if ( is_wp_error( $id ) || ! $id ) { return new WP_Error( 'clz_menu_failed', 'ثبت لینک در فهرست انجام نشد.' ); }
	}
	return (int) $menu_id;
}

function clz_install_store_presentation() {
	$version = '2026-10-01-2';
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_theme_options' ) || get_option( 'clz_store_presentation_version' ) === $version ) { return; }
	if ( ! get_option( 'clz_information_version' ) ) { return; }
	$lock = (int) get_option( 'clz_store_presentation_lock' );
	if ( $lock && time() - $lock < 120 ) { return; }
	if ( $lock ) { delete_option( 'clz_store_presentation_lock' ); }
	if ( ! add_option( 'clz_store_presentation_lock', time(), '', false ) ) { return; }
	try {
		$contact = get_page_by_path( 'contact', OBJECT, 'page' );
		if ( ! $contact || 'publish' !== $contact->post_status ) { return; }
		if ( get_post_meta( $contact->ID, '_clz_contact_design_version', true ) !== $version ) {
			add_post_meta( $contact->ID, '_clz_contact_before_redesign', wp_slash( $contact->post_content ), true );
			$id = wp_update_post( wp_slash( array( 'ID' => $contact->ID, 'post_content' => clz_information_seed_content( 'contact' ) ) ), true );
			if ( is_wp_error( $id ) || ! $id ) { return; }
			update_post_meta( $id, '_clz_contact_design_version', $version );
		}
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations = is_array( $locations ) ? $locations : array();
		$options = get_option( 'bijan', array() );
		if ( ! is_array( $options ) ) { return; }
		$keys = array_flip( array( 'show_footer', 'footer_menu_count', 'footer_show_menu1', 'footer_show_menu2', 'footer_menu1_title', 'footer_menu2_title', 'footer_menu1_icon', 'footer_menu2_icon', 'my-account-welcome' ) );
		add_option( 'clz_native_presentation_original', array( 'settings' => array_intersect_key( $options, $keys ), 'locations' => $locations ), '', false );
		$guide = clz_setup_native_footer_menu( $locations['footer-menu'] ?? 0, 'کلوز - راهنمای خرید', array( 'shipping', 'returns', 'faq', 'terms' ) );
		$store_menu_id = (int) ( $locations['footer-contact-menu'] ?? 0 );
		if ( ! is_wp_error( $guide ) && $store_menu_id === $guide ) { $store_menu_id = 0; }
		$store = clz_setup_native_footer_menu( $store_menu_id, 'کلوز - درباره و پشتیبانی', array( 'about', 'contact', 'privacy', 'licenses' ) );
		if ( is_wp_error( $guide ) || is_wp_error( $store ) ) { return; }
		$locations['footer-menu'] = $guide;
		$locations['footer-contact-menu'] = $store;
		set_theme_mod( 'nav_menu_locations', $locations );
		$options['show_footer'] = true;
		$options['footer_menu_count'] = max( 2, (int) ( $options['footer_menu_count'] ?? 2 ) );
		$options['footer_show_menu1'] = true;
		$options['footer_show_menu2'] = true;
		if ( empty( $options['footer_menu1_title'] ) || in_array( $options['footer_menu1_title'], array( 'Menu', 'منو', 'فهرست' ), true ) ) { $options['footer_menu1_title'] = 'راهنمای خرید'; }
		if ( empty( $options['footer_menu2_title'] ) || in_array( $options['footer_menu2_title'], array( 'Contact us', 'تماس با ما' ), true ) ) { $options['footer_menu2_title'] = 'کلوز و پشتیبانی'; }
		$options['footer_menu1_icon'] = $options['footer_menu1_icon'] ?? 'bijan-icon-grid';
		$options['footer_menu2_icon'] = $options['footer_menu2_icon'] ?? 'bijan-icon-call';
		$options['my-account-welcome'] = 'به فروشگاه کلوز خوش آمدید.';
		update_option( 'bijan', $options );
		$GLOBALS['bijan'] = $options;
		update_option( 'clz_store_presentation_version', $version, false );
	} finally { delete_option( 'clz_store_presentation_lock' ); }
}
add_action( 'admin_init', 'clz_install_store_presentation', 40 );

function clz_account_welcome_styles() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) { return; }
	$file = trailingslashit( get_stylesheet_directory() ) . 'assets/account-welcome.css';
	wp_enqueue_style( 'clz-account-welcome', trailingslashit( get_stylesheet_directory_uri() ) . 'assets/account-welcome.css', array( 'bijan-child-style' ), (string) filemtime( $file ) );
}
add_action( 'wp_enqueue_scripts', 'clz_account_welcome_styles', 30 );
