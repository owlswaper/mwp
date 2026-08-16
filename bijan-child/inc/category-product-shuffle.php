<?php
/**
 * Stable, cache-friendly product ordering for product category archives.
 *
 * The order changes once per 12-hour window, but remains deterministic inside
 * that window so pagination and cached responses never reshuffle per request.
 */

defined( 'ABSPATH' ) || exit;

final class Bijan_Category_Product_Shuffle {
	private const INTERVAL       = 12 * HOUR_IN_SECONDS;
	private const QUERY_MARKER   = '_bijan_category_shuffle_seed';
	private const CRON_HOOK      = 'bijan_rotate_category_product_shuffle';
	private const BUCKET_OPTION  = 'bijan_category_product_shuffle_bucket';
	private const VERSION_OPTION = 'bijan_category_product_shuffle_cache_version';
	private const ROTATION_LOCK  = 'bijan_category_product_shuffle_rotation_lock';
	private const CACHE_VERSION  = '2';

	public static function init() {
		add_action( 'pre_get_posts', [ __CLASS__, 'mark_category_query' ], 999 );
		add_filter( 'posts_clauses', [ __CLASS__, 'apply_stable_order' ], 999, 2 );
		add_action( 'init', [ __CLASS__, 'schedule_rotation' ], 20 );
		add_action( self::CRON_HOOK, [ __CLASS__, 'rotate_cache' ] );
		add_action( 'send_headers', [ __CLASS__, 'send_category_cache_headers' ], 999 );
		add_action( 'wp_head', [ __CLASS__, 'print_browser_cache_guard' ], 1 );
	}

	private static function current_bucket() {
		return (int) floor( time() / self::INTERVAL );
	}

	private static function has_manual_order() {
		return isset( $_GET['sort'] ) || isset( $_GET['orderby'] );
	}

	public static function mark_category_query( $query ) {
		if (
			is_admin()
			|| ! $query instanceof WP_Query
			|| ! $query->is_main_query()
			|| ! $query->is_tax( 'product_cat' )
			|| $query->is_feed()
			|| self::has_manual_order()
		) {
			return;
		}

		$term_id = absint( get_queried_object_id() );
		if ( ! $term_id ) {
			return;
		}

		// Each category receives its own stable permutation for this time bucket.
		$seed = sprintf( '%u', crc32( self::current_bucket() . ':' . $term_id ) );
		$query->set( self::QUERY_MARKER, $seed );
	}

	public static function apply_stable_order( $clauses, $query ) {
		$seed = $query instanceof WP_Query ? $query->get( self::QUERY_MARKER ) : '';
		if ( '' === $seed || ! ctype_digit( (string) $seed ) ) {
			return $clauses;
		}

		global $wpdb;
		$seed               = (string) absint( $seed );
		$clauses['orderby'] = "CRC32(CONCAT({$wpdb->posts}.ID, '-', '{$seed}')) ASC, {$wpdb->posts}.ID ASC";

		return $clauses;
	}

	/**
	 * Browsers must revalidate category HTML instead of keeping a stale page for
	 * 24 hours. FlyingPress/CDN may still cache it until the bucket boundary.
	 */
	public static function send_category_cache_headers() {
		if ( ! is_tax( 'product_cat' ) || headers_sent() ) {
			return;
		}

		$seconds_to_boundary = max(
			1,
			( ( self::current_bucket() + 1 ) * self::INTERVAL ) - time()
		);

		header( 'Cache-Control: no-cache, must-revalidate, max-age=0', true );
		header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT', true );
		header( 'CDN-Cache-Control: public, max-age=' . $seconds_to_boundary . ', must-revalidate', true );
	}

	/**
	 * Server-level FlyingPress rules can override PHP headers. This tiny guard
	 * detects an HTML document retained by the browser across a bucket boundary
	 * and forces one real reload. It adds no network request during normal views.
	 */
	public static function print_browser_cache_guard() {
		if ( ! is_tax( 'product_cat' ) ) {
			return;
		}

		$bucket   = self::current_bucket();
		$interval = self::INTERVAL;
		?>
		<script id="bijan-category-cache-guard">(()=>{const b=<?php echo (int) $bucket; ?>,i=<?php echo (int) $interval; ?>,n=Math.floor(Date.now()/1000/i);if(n===b)return;const k="bijan-category-reload-"+n;try{if(sessionStorage.getItem(k))return;sessionStorage.setItem(k,"1")}catch(e){}location.reload()})();</script>
		<?php
	}

	public static function schedule_rotation() {
		$current_bucket = self::current_bucket();
		$stored_bucket  = get_option( self::BUCKET_OPTION, null );
		$cache_version  = (string) get_option( self::VERSION_OPTION, '' );

		if ( null === $stored_bucket ) {
			add_option( self::BUCKET_OPTION, $current_bucket, '', false );
		}

		// A version change performs one targeted purge so old 24-hour responses
		// are replaced with responses carrying the new browser-cache headers.
		if ( self::CACHE_VERSION !== $cache_version || (int) $stored_bucket !== $current_bucket ) {
			self::rotate_cache();
		}

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			$next_boundary = ( $current_bucket + 1 ) * self::INTERVAL;
			wp_schedule_event( $next_boundary, 'twicedaily', self::CRON_HOOK );
		}
	}

	public static function rotate_cache() {
		$bucket         = self::current_bucket();
		$stored_bucket  = (int) get_option( self::BUCKET_OPTION, -1 );
		$cache_version  = (string) get_option( self::VERSION_OPTION, '' );

		if ( $stored_bucket === $bucket && self::CACHE_VERSION === $cache_version ) {
			return;
		}

		// Avoid duplicate purge/preload jobs if cron and a normal request arrive
		// together at the bucket boundary.
		if ( get_transient( self::ROTATION_LOCK ) ) {
			return;
		}
		set_transient( self::ROTATION_LOCK, 1, 5 * MINUTE_IN_SECONDS );

		$term_ids = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'ids',
		] );

		if ( is_wp_error( $term_ids ) ) {
			return;
		}

		if ( ! $term_ids ) {
			self::finish_rotation( $bucket );
			return;
		}

		clean_term_cache( array_map( 'absint', $term_ids ), 'product_cat' );
		$urls = self::category_page_urls( $term_ids );

		// Targeted integrations; no site-wide cache flush is performed.
		if ( function_exists( 'rocket_clean_files' ) ) {
			rocket_clean_files( $urls );
		}

		if ( ! self::refresh_flyingpress_cache( $urls ) ) {
			// Keep the old bucket value so a later request retries safely.
			return;
		}

		foreach ( $urls as $url ) {
			if ( function_exists( 'w3tc_flush_url' ) ) {
				w3tc_flush_url( $url );
			}
			do_action( 'litespeed_purge_url', $url );
		}

		/**
		 * Lets a host or another cache plugin purge these archive URLs without
		 * coupling the theme to a site-wide destructive cache flush.
		 */
		do_action( 'bijan_category_shuffle_purge_urls', $urls, $bucket );
		self::finish_rotation( $bucket );
	}

	/**
	 * Purge and warm only category archive pages in FlyingPress. Its Cloudflare
	 * integration propagates the same purge to the edge cache when enabled.
	 */
	private static function refresh_flyingpress_cache( $urls ) {
		if (
			! class_exists( '\\FlyingPress\\Purge' )
			|| ! is_callable( [ '\\FlyingPress\\Purge', 'purge_urls' ] )
		) {
			return true;
		}

		try {
			\FlyingPress\Purge::purge_urls( $urls );

			if (
				class_exists( '\\FlyingPress\\Preload' )
				&& is_callable( [ '\\FlyingPress\\Preload', 'preload_urls' ] )
			) {
				\FlyingPress\Preload::preload_urls( $urls );
			}
		} catch ( Throwable $exception ) {
			do_action( 'bijan_category_shuffle_flyingpress_error', $exception, $urls );
			return false;
		}

		return true;
	}

	private static function finish_rotation( $bucket ) {
		update_option( self::BUCKET_OPTION, $bucket, false );
		update_option( self::VERSION_OPTION, self::CACHE_VERSION, false );
		delete_transient( self::ROTATION_LOCK );
	}

	private static function category_page_urls( $term_ids ) {
		global $wp_rewrite;

		$urls     = [];
		$per_page = function_exists( 'wc_get_default_product_rows_per_page' )
			? wc_get_default_product_rows_per_page() * wc_get_default_products_per_row()
			: absint( get_option( 'posts_per_page', 12 ) );
		$per_page = max( 1, (int) apply_filters( 'loop_shop_per_page', $per_page ) );

		foreach ( $term_ids as $term_id ) {
			$term = get_term( $term_id, 'product_cat' );
			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			$url = get_term_link( $term );
			if ( is_wp_error( $url ) ) {
				continue;
			}

			$urls[]       = $url;
			$product_count = (int) $term->count;
			$children      = get_term_children( $term->term_id, 'product_cat' );
			if ( ! is_wp_error( $children ) ) {
				foreach ( $children as $child_id ) {
					$child = get_term( $child_id, 'product_cat' );
					if ( $child && ! is_wp_error( $child ) ) {
						$product_count += (int) $child->count;
					}
				}
			}

			$pages = max( 1, (int) ceil( $product_count / $per_page ) );
			for ( $page = 2; $page <= $pages; $page++ ) {
				$urls[] = $wp_rewrite && $wp_rewrite->using_permalinks()
					? trailingslashit( $url ) . user_trailingslashit( 'page/' . $page, 'paged' )
					: add_query_arg( 'paged', $page, $url );
			}
		}

		return array_values( array_unique( array_filter( $urls ) ) );
	}
}

Bijan_Category_Product_Shuffle::init();
