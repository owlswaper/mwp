<?php
/**
 * Link-free, native-query AJAX controls for WooCommerce product archives.
 *
 * Filter state is sent by POST to the clean archive URL. It is injected before
 * WordPress builds the main query, so WooCommerce, theme options and extensions
 * continue to operate on the real archive query instead of a parallel clone.
 *
 * @package BijanChild
 */

defined( 'ABSPATH' ) || exit;

final class Cloz_Archive_Ajax_Filters {
	private const REQUEST_KEY = 'cloz_archive_ajax';

	/** @var array<string, string|int> */
	private static $state = [];
	private static $capturing_sidebar = false;

	public static function init() {
		add_action( 'parse_request', [ __CLASS__, 'inject_posted_state' ], 1 );
		add_action( 'send_headers', [ __CLASS__, 'send_private_response_headers' ], PHP_INT_MAX );
		add_action( 'template_redirect', [ __CLASS__, 'redirect_legacy_filter_urls' ], 20 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ], 40 );
		add_filter( 'widget_output', [ __CLASS__, 'remove_filter_widget_links' ], PHP_INT_MAX, 3 );
		add_action( 'dynamic_sidebar_before', [ __CLASS__, 'start_sidebar_capture' ], 0, 2 );
		add_action( 'dynamic_sidebar_after', [ __CLASS__, 'finish_sidebar_capture' ], PHP_INT_MAX, 2 );
		add_filter( 'get_pagenum_link', [ __CLASS__, 'clean_pagination_link' ], PHP_INT_MAX );
	}

	/**
	 * Apply POSTed filters early enough for WooCommerce's native main query.
	 *
	 * @param WP $wp Current WordPress environment.
	 */
	public static function inject_posted_state( $wp ) {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			return;
		}

		if ( empty( $_POST[ self::REQUEST_KEY ] ) ) {
			return;
		}

		self::$state = self::sanitize_state( wp_unslash( $_POST ) );
		foreach ( self::$state as $key => $value ) {
			$_GET[ $key ]     = $value;
			$_REQUEST[ $key ] = $value;
		}

		$paged = max( 1, absint( self::$state['paged'] ?? 1 ) );
		$wp->query_vars['paged'] = $paged;
		if ( isset( self::$state['s'] ) ) {
			$wp->query_vars['s'] = self::$state['s'];
		}
	}

	public static function send_private_response_headers() {
		if ( ! self::is_ajax_filter_request() || headers_sent() ) {
			return;
		}

		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0', true );
		header( 'Vary: X-Cloz-Archive-Ajax', false );
	}

	/**
	 * Retire already-discovered parameter URLs with one permanent clean redirect.
	 */
	public static function redirect_legacy_filter_urls() {
		if ( self::is_ajax_filter_request() || ! self::is_product_archive() ) {
			return;
		}

		$filter_keys = self::filter_keys( wp_unslash( $_GET ) );
		if ( ! $filter_keys ) {
			return;
		}

		$target = remove_query_arg( $filter_keys );
		wp_safe_redirect( $target, 301, 'Cloz archive filters' );
		exit;
	}

	public static function enqueue_assets() {
		if ( ! self::is_product_archive() ) {
			return;
		}

		$dir = trailingslashit( get_stylesheet_directory() );
		$uri = trailingslashit( get_stylesheet_directory_uri() );
		$js  = $dir . 'assets/archive-ajax-filters.js';
		$css = $dir . 'assets/archive-ajax-filters.css';

		wp_enqueue_style(
			'cloz-archive-ajax-filters',
			$uri . 'assets/archive-ajax-filters.css',
			[],
			is_readable( $css ) ? (string) filemtime( $css ) : null
		);
		wp_enqueue_script(
			'cloz-archive-ajax-filters',
			$uri . 'assets/archive-ajax-filters.js',
			[ 'jquery' ],
			is_readable( $js ) ? (string) filemtime( $js ) : null,
			true
		);

		wp_localize_script( 'cloz-archive-ajax-filters', 'clozArchiveFilters', [
			'url'   => self::canonical_archive_url(),
			'state' => self::sanitize_state( wp_unslash( $_GET ) ),
			'key'   => self::REQUEST_KEY,
		] );
	}

	/**
	 * Replace URLs inside WooCommerce filter widgets with inert buttons carrying
	 * only sanitized state. Category-navigation and product links are untouched.
	 *
	 * @param string    $output Widget HTML.
	 * @param WP_Widget $widget Widget instance.
	 * @return string
	 */
	public static function remove_filter_widget_links( $output, $widget, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( ! self::is_product_archive() || ! is_object( $widget ) ) {
			return $output;
		}

		$filter_widgets = [
			'woocommerce_layered_nav',
			'woocommerce_price_filter',
			'woocommerce_rating_filter',
			'woocommerce_layered_nav_filters',
		];
		if ( empty( $widget->id_base ) || ! in_array( $widget->id_base, $filter_widgets, true ) ) {
			return $output;
		}

		return self::replace_filter_anchors( $output );
	}

	public static function start_sidebar_capture( $index, $has_widgets ) {
		if ( self::$capturing_sidebar || ! $has_widgets || 'sidebar-shop' !== (string) $index || ! self::is_product_archive() ) {
			return;
		}

		self::$capturing_sidebar = true;
		ob_start();
	}

	public static function finish_sidebar_capture( $index, $has_widgets ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( ! self::$capturing_sidebar || 'sidebar-shop' !== (string) $index ) {
			return;
		}

		$output = ob_get_clean();
		self::$capturing_sidebar = false;
		echo self::replace_filter_anchors( $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	private static function replace_filter_anchors( $output ) {
		$current_path = (string) wp_parse_url( self::canonical_archive_url(), PHP_URL_PATH );
		$has_active_filters = (bool) self::filter_keys( wp_unslash( $_GET ) );

		return (string) preg_replace_callback(
			'#<a\b([^>]*)>(.*?)</a>#is',
			static function ( $matches ) use ( $current_path, $has_active_filters ) {
				if ( ! preg_match( '#\shref\s*=\s*(["\'])(.*?)\1#is', $matches[1], $href_match ) ) {
					return $matches[0];
				}

				$query = [];
				$href = html_entity_decode( $href_match[2], ENT_QUOTES, 'UTF-8' );
				$query_string = wp_parse_url( $href, PHP_URL_QUERY );
				if ( is_string( $query_string ) ) {
					parse_str( $query_string, $query );
				}

				$state = self::sanitize_state( $query );
				$link_path = (string) wp_parse_url( $href, PHP_URL_PATH );
				$same_archive = untrailingslashit( $link_path ) === untrailingslashit( $current_path );
				if ( ! self::filter_keys( $query ) && ! ( $same_archive && $has_active_filters ) ) {
					return $matches[0];
				}

				$attrs = preg_replace( '#\s(?:href|rel|target)\s*=\s*(["\']).*?\1#is', '', $matches[1] );

				return sprintf(
					'<button type="button"%1$s data-cloz-archive-state="%2$s">%3$s</button>',
					$attrs,
					esc_attr( wp_json_encode( $state ) ),
					$matches[2]
				);
			},
			(string) $output
		);
	}

	/**
	 * Pagination stays crawlable only as clean category pages. Filter parameters
	 * are never emitted into its href values and clicks are handled with AJAX.
	 */
	public static function clean_pagination_link( $url ) {
		if ( ! self::is_product_archive() ) {
			return $url;
		}

		$query = [];
		$query_string = wp_parse_url( html_entity_decode( $url, ENT_QUOTES, 'UTF-8' ), PHP_URL_QUERY );
		if ( is_string( $query_string ) ) {
			parse_str( $query_string, $query );
		}

		$keys = self::filter_keys( $query );
		return $keys ? remove_query_arg( $keys, $url ) : $url;
	}

	private static function is_ajax_filter_request() {
		return 'POST' === strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) )
			&& ! empty( $_POST[ self::REQUEST_KEY ] );
	}

	private static function is_product_archive() {
		return function_exists( 'is_shop' )
			&& ( is_shop() || is_product_taxonomy() || is_post_type_archive( 'product' ) );
	}

	private static function canonical_archive_url() {
		if ( is_product_taxonomy() ) {
			$url = get_term_link( get_queried_object() );
			if ( ! is_wp_error( $url ) ) {
				return $url;
			}
		}

		$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
		return $shop_id > 0 ? get_permalink( $shop_id ) : home_url( '/' );
	}

	/**
	 * @param array<string, mixed> $source Raw state.
	 * @return array<string, string|int>
	 */
	private static function sanitize_state( $source ) {
		$state = [];
		if ( ! is_array( $source ) ) {
			return $state;
		}

		$orderby_options = function_exists( 'wc_get_catalog_ordering_args' )
			? array_keys( (array) apply_filters( 'woocommerce_catalog_orderby', [] ) )
			: [];
		$orderby_options = array_unique( array_merge(
			$orderby_options,
			[ 'menu_order', 'popularity', 'rating', 'date', 'price', 'price-desc' ]
		) );

		foreach ( $source as $raw_key => $raw_value ) {
			$key = sanitize_key( $raw_key );
			if ( self::REQUEST_KEY === $key || is_array( $raw_value ) ) {
				continue;
			}

			$value = trim( (string) $raw_value );
			if ( '' === $value ) {
				continue;
			}

			if ( 's' === $key ) {
				$value = sanitize_text_field( $value );
				if ( '' !== $value ) {
					$state[ $key ] = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 80 ) : substr( $value, 0, 80 );
				}
			} elseif ( 'orderby' === $key && in_array( $value, $orderby_options, true ) ) {
				$state[ $key ] = sanitize_text_field( $value );
			} elseif ( in_array( $key, [ 'min_price', 'max_price' ], true ) && preg_match( '/^\d+(?:\.\d+)?$/D', $value ) ) {
				$state[ $key ] = $value;
			} elseif ( in_array( $key, [ 'instock', 'onsale', 'special-products' ], true ) ) {
				$state[ $key ] = '1';
			} elseif ( in_array( $key, [ 'paged', 'product-page' ], true ) ) {
				$state['paged'] = min( 10000, max( 1, absint( $value ) ) );
			} elseif ( 'rating_filter' === $key ) {
				$ratings = array_filter( array_map( 'absint', explode( ',', $value ) ), static function ( $rating ) {
					return $rating >= 1 && $rating <= 5;
				} );
				if ( $ratings ) {
					$state[ $key ] = implode( ',', array_unique( $ratings ) );
				}
			} elseif ( 0 === strpos( $key, 'filter_' ) ) {
				$terms = array_filter( array_map( 'sanitize_title', explode( ',', $value ) ) );
				if ( $terms ) {
					$state[ $key ] = substr( implode( ',', array_unique( $terms ) ), 0, 500 );
				}
			} elseif ( 0 === strpos( $key, 'query_type_' ) && in_array( strtolower( $value ), [ 'and', 'or' ], true ) ) {
				$state[ $key ] = strtolower( $value );
			}
		}

		return $state;
	}

	/** @return string[] */
	private static function filter_keys( $source ) {
		return array_values( array_diff(
			array_keys( self::sanitize_state( is_array( $source ) ? $source : [] ) ),
			[ 'paged', 'special-products' ]
		) );
	}
}

Cloz_Archive_Ajax_Filters::init();
