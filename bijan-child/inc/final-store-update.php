<?php
/** Apply the October 2 content/support revision to existing records once. */
defined( 'ABSPATH' ) || exit;
function clz_install_final_store_update() {
	$version = '2026-10-02-final-1';
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_theme_options' ) || get_option( 'clz_final_store_version' ) === $version ) { return; }
	$lock = (int) get_option( 'clz_final_store_lock' );
	if ( $lock && time() - $lock < 120 ) { return; }
	if ( $lock ) { delete_option( 'clz_final_store_lock' ); }
	if ( ! add_option( 'clz_final_store_lock', time(), '', false ) ) { return; }
	try {
		$patterns = array(
			'privacy' => array( '~<section><h2>اطلاعات چقدر نگه داشته می‌شود\؟</h2>.*?</section>~s', '~<section><h2>ورود و امنیت پیگیری سفارش</h2>.*?</section>~s' ),
			'faq' => array( '~<details><summary>چطور سفارش را پیگیری کنم و لینک رهگیری را ببینم\؟</summary>.*?</details>~s' ),
			'shipping' => array( '~<section id="clz-shipping-tracking">.*?</section>~s' ),
			'terms' => array( '~<p>(?:کد اختصاصی پیگیری|شماره سفارش در رسید).*?</p>~s' ),
			'contact' => array( '~<section class="clz-contact-channel" aria-labelledby="clz-contact-call">.*?</section>~s' ),
		);
		foreach ( $patterns as $slug => $sections ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( ! $page ) { return; }
			$seed = clz_information_seed_content( $slug );
			$content = $page->post_content;
			foreach ( $sections as $pattern ) {
				if ( 'contact' === $slug ) { $replacement = '[clz_contact_call]'; }
				elseif ( preg_match( $pattern, $seed, $match ) ) { $replacement = $match[0]; }
				else { continue; }
				$content = preg_replace_callback( $pattern, static function () use ( $replacement ) { return $replacement; }, $content );
			}
			if ( $content !== $page->post_content ) {
				add_post_meta( $page->ID, '_clz_before_final_store_update', wp_slash( $page->post_content ), true );
				$id = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ), true );
				if ( is_wp_error( $id ) || ! $id ) { return; }
			}
		}
		$options = get_option( 'bijan', array() );
		if ( ! is_array( $options ) ) { return; }
		$keys = array( 'footer_contact_info', 'footer_more_info_title', 'footer_more_info_subtitle', 'footer_contact_subtitle' );
		add_option( 'clz_footer_contact_before_final_update', array_intersect_key( $options, array_flip( $keys ) ), '', false );
		$options['footer_contact_info'] = array( '09981687867' );
		$contact = clz_store_contact_details();
		$options['footer_more_info_title'] = 'پشتیبانی کلوز';
		$options['footer_more_info_subtitle'] = 'برای راهنمایی خرید پیرسینگ و اکسسوری و پیگیری سفارش، با ما در تماس باشید.';
		$options['footer_contact_subtitle'] = $contact['hours'] . ' به وقت تهران، حتی جمعه‌ها. پاسخ تماس و پیامک: تا ۱ ساعت؛ پشتیبانی آنلاین و پیام‌رسان‌ها: ۱ تا ۲ ساعت در ساعات کاری.<br><a href="' . esc_url( $contact['chat'] ) . '" target="_blank" rel="noopener noreferrer">گفت‌وگوی آنلاین</a> · <a href="' . esc_url( $contact['telegram'] ) . '" target="_blank" rel="noopener noreferrer">تلگرام</a> · <a href="' . esc_url( $contact['bale'] ) . '" target="_blank" rel="noopener noreferrer">بله</a>';
		update_option( 'bijan', $options );
		$GLOBALS['bijan'] = $options;
		update_option( 'clz_final_store_version', $version, false );
	} finally { delete_option( 'clz_final_store_lock' ); }
}
add_action( 'admin_init', 'clz_install_final_store_update', 70 );
