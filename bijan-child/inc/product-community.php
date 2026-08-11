<?php
/**
 * Modern product reviews and questions.
 *
 * Reviews and questions use WordPress comments so moderation, authorship and
 * product relationships remain native and portable.
 */

defined( 'ABSPATH' ) || exit;

final class Bijan_Product_Community {
	const REVIEW_TYPE   = 'bijan_review';
	const QUESTION_TYPE = 'bijan_question';
	const ANSWER_TYPE   = 'bijan_answer';
	const NONCE_ACTION  = 'bijan_product_community';
	const PAGE_SIZE     = 8;
	const MAX_IMAGES    = 4;
	const MAX_IMAGE_SIZE = 5242880; // 5 MB.

	public static function init() {
		add_action( 'wp', [ __CLASS__, 'replace_product_sections' ], 99 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ], 30 );
		add_action( 'wp_ajax_bijan_submit_product_review', [ __CLASS__, 'submit_review' ] );
		add_action( 'wp_ajax_nopriv_bijan_submit_product_review', [ __CLASS__, 'login_required' ] );
		add_action( 'wp_ajax_bijan_submit_product_question', [ __CLASS__, 'submit_question' ] );
		add_action( 'wp_ajax_nopriv_bijan_submit_product_question', [ __CLASS__, 'login_required' ] );
		add_action( 'wp_ajax_bijan_toggle_review_helpful', [ __CLASS__, 'toggle_helpful' ] );
		add_action( 'wp_ajax_nopriv_bijan_toggle_review_helpful', [ __CLASS__, 'login_required' ] );
		add_filter( 'preprocess_comment', [ __CLASS__, 'block_guest_product_comments' ] );
		add_action( 'transition_comment_status', [ __CLASS__, 'invalidate_review_stats' ], 10, 3 );
		add_action( 'delete_comment', [ __CLASS__, 'cleanup_deleted_review' ], 10, 2 );
		add_action( 'save_post_product', [ __CLASS__, 'clear_related_cache' ] );
		add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ], 30 );
		add_action( 'admin_post_bijan_community_action', [ __CLASS__, 'admin_action' ] );
	}

	public static function replace_product_sections() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		// Everything that used to appear below the description area.
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'bijan_wc_single_comments', 19 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		remove_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products', 20 );
		remove_action( 'woocommerce_product_after_tabs', 'bijan_wc_product_footer' );

		// Old WooCommerce counters are based on another comment type.
		remove_action( 'woocommerce_single_product_summary', 'bijan_wc_single_head_rating', 7 );
		remove_action( 'woocommerce_single_product_summary', 'bijan_wc_single_head_comments', 8 );
		add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_product_head_stats' ], 7 );

		add_action( 'woocommerce_after_single_product_summary', [ __CLASS__, 'render_smart_related' ], 9 );
		add_action( 'woocommerce_after_single_product_summary', [ __CLASS__, 'render' ], 19 );
	}

	public static function clear_related_cache( $product_id ) {
		delete_transient( 'bijan_smart_related_' . absint( $product_id ) );
	}

	private static function related_query( $term_ids, $exclude_ids, $limit, $include_children ) {
		if ( ! $term_ids || $limit < 1 ) {
			return [];
		}

		$tax_query = [
			[
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => array_map( 'absint', $term_ids ),
				'operator'         => 'IN',
				'include_children' => (bool) $include_children,
			],
		];
		if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
			$visibility = wc_get_product_visibility_term_ids();
			$hidden     = array_filter( [
				$visibility['exclude-from-catalog'] ?? 0,
				'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ? ( $visibility['outofstock'] ?? 0 ) : 0,
			] );
			if ( $hidden ) {
				$tax_query[] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array_map( 'absint', $hidden ),
					'operator' => 'NOT IN',
				];
			}
		}

		$query = new WP_Query( [
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'fields'                 => 'ids',
			'posts_per_page'         => $limit,
			'post__not_in'           => array_map( 'absint', $exclude_ids ),
			'tax_query'              => $tax_query,
			'orderby'                => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return array_map( 'absint', $query->posts );
	}

	private static function smart_related_ids( $product_id, $limit = 12 ) {
		$cache_key = 'bijan_smart_related_' . absint( $product_id );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return array_slice( array_map( 'absint', $cached ), 0, $limit );
		}

		$terms = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'all' ] );
		if ( is_wp_error( $terms ) || ! $terms ) {
			set_transient( $cache_key, [], 6 * HOUR_IN_SECONDS );
			return [];
		}

		$assigned_ids = array_map( 'absint', wp_list_pluck( $terms, 'term_id' ) );
		$leaf_ids     = $assigned_ids;
		foreach ( $assigned_ids as $possible_parent ) {
			foreach ( $assigned_ids as $possible_child ) {
				if ( $possible_parent !== $possible_child && in_array( $possible_parent, get_ancestors( $possible_child, 'product_cat', 'taxonomy' ), true ) ) {
					$leaf_ids = array_values( array_diff( $leaf_ids, [ $possible_parent ] ) );
					break;
				}
			}
		}

		$found   = [];
		$exclude = [ $product_id ];
		$append  = static function ( $ids ) use ( &$found, &$exclude, $limit ) {
			foreach ( $ids as $id ) {
				if ( ! in_array( $id, $exclude, true ) ) {
					$found[]   = $id;
					$exclude[] = $id;
				}
				if ( count( $found ) >= $limit ) {
					break;
				}
			}
		};

		// First: products explicitly assigned to the same, most specific category.
		$append( self::related_query( $leaf_ids, $exclude, $limit, false ) );

		// Second: descendants of that category, useful when the current product is assigned to a parent category.
		if ( count( $found ) < $limit ) {
			$append( self::related_query( $leaf_ids, $exclude, $limit - count( $found ), true ) );
		}

		// Finally walk upwards one level at a time. Sibling categories only enter through their closest parent.
		$parents = array_values( array_unique( array_filter( array_map( static function ( $term_id ) {
			$term = get_term( $term_id, 'product_cat' );
			return $term instanceof WP_Term ? absint( $term->parent ) : 0;
		}, $leaf_ids ) ) ) );
		while ( count( $found ) < $limit && $parents ) {
			$append( self::related_query( $parents, $exclude, $limit - count( $found ), true ) );
			$next_parents = [];
			foreach ( $parents as $parent_id ) {
				$term = get_term( $parent_id, 'product_cat' );
				if ( $term instanceof WP_Term && $term->parent ) {
					$next_parents[] = absint( $term->parent );
				}
			}
			$parents = array_values( array_unique( $next_parents ) );
		}

		$found = array_slice( $found, 0, $limit );
		set_transient( $cache_key, $found, 6 * HOUR_IN_SECONDS );
		return $found;
	}

	public static function render_smart_related() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$ids = self::smart_related_ids( $product->get_id(), 12 );
		if ( ! $ids ) {
			return;
		}
		if ( function_exists( '_prime_post_caches' ) ) {
			_prime_post_caches( $ids, true, true );
		}

		$original_product = $product;
		$old_loop_props   = wc_get_loop_prop( 'bijan_loop_props' );
		wc_set_loop_prop( 'bijan_loop_props', [
			'style'                   => 'products-style-2',
			'special_products'        => false,
			'second-image-hover-show' => false,
		] );
		?>
		<section class="bc-smart-related product-section" aria-labelledby="bc-related-title">
			<header class="bc-related-head">
				<div><span>انتخاب‌های نزدیک به همین محصول</span><h2 id="bc-related-title">محصولات مرتبط</h2></div>
				<div class="bc-related-nav"><button type="button" data-related-next aria-label="محصولات بعدی">‹</button><button type="button" data-related-prev aria-label="محصولات قبلی">›</button></div>
			</header>
			<div class="bc-related-viewport">
				<ul class="products products-style-2 bc-related-track">
					<?php foreach ( $ids as $related_id ) :
						$post_object = get_post( $related_id );
						if ( ! $post_object ) { continue; }
						setup_postdata( $GLOBALS['post'] = $post_object );
						$GLOBALS['product'] = wc_get_product( $related_id );
						if ( $GLOBALS['product'] ) { wc_get_template_part( 'content', 'product' ); }
					endforeach; ?>
				</ul>
			</div>
		</section>
		<?php
		wp_reset_postdata();
		$GLOBALS['product'] = $original_product;
		wc_set_loop_prop( 'bijan_loop_props', is_array( $old_loop_props ) ? $old_loop_props : [] );
	}

	public static function enqueue_assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		$version  = defined( 'BIJAN_CHILD_VERSION' ) ? BIJAN_CHILD_VERSION : '1.0.0';
		$uri      = trailingslashit( get_stylesheet_directory_uri() );
		$dir      = trailingslashit( get_stylesheet_directory() );
		$css_file = $dir . 'assets/product-community.css';
		$js_file  = $dir . 'assets/product-community.js';
		$css_ver  = is_readable( $css_file ) ? (string) filemtime( $css_file ) : $version;
		$js_ver   = is_readable( $js_file ) ? (string) filemtime( $js_file ) : $version;

		wp_enqueue_style( 'bijan-product-community', $uri . 'assets/product-community.css', [], $css_ver );
		wp_enqueue_script( 'bijan-product-community', $uri . 'assets/product-community.js', [ 'jquery' ], $js_ver, true );
		wp_localize_script(
			'bijan-product-community',
			'bijanCommunity',
			[
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( self::NONCE_ACTION ),
				'productId' => get_queried_object_id(),
				'loggedIn'  => is_user_logged_in(),
				'maxImages' => self::MAX_IMAGES,
				'maxSize'   => self::MAX_IMAGE_SIZE,
				'i18n'      => [
					'network'      => 'ارتباط با سرور برقرار نشد. لطفاً دوباره تلاش کنید.',
					'fileCount'    => 'حداکثر ۴ تصویر می‌توانید انتخاب کنید.',
					'fileSize'     => 'حجم هر تصویر باید کمتر از ۵ مگابایت باشد.',
					'fileType'     => 'فقط تصویر با فرمت JPG، PNG یا WebP مجاز است.',
					'required'     => 'لطفاً متن را کامل کنید.',
					'sending'      => 'در حال ارسال…',
					'removeImage'  => 'حذف تصویر',
				],
			]
		);
	}

	private static function product_id_from_request() {
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( ! $product_id || 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
			wp_send_json_error( [ 'message' => 'محصول معتبر نیست.' ], 400 );
		}
		return $product_id;
	}

	private static function verify_request() {
		if ( ! is_user_logged_in() ) {
			self::login_required();
		}
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
	}

	public static function login_required() {
		wp_send_json_error( [ 'message' => 'برای ثبت محتوا ابتدا وارد حساب خود شوید.', 'login_required' => true ], 401 );
	}

	public static function block_guest_product_comments( $comment_data ) {
		$post_id = isset( $comment_data['comment_post_ID'] ) ? absint( $comment_data['comment_post_ID'] ) : 0;
		if ( $post_id && 'product' === get_post_type( $post_id ) && ! is_user_logged_in() ) {
			wp_die(
				esc_html( 'برای ثبت نظر یا پرسش محصول ابتدا وارد حساب خود شوید.' ),
				esc_html( 'ورود به حساب کاربری' ),
				[ 'response' => 403, 'back_link' => true ]
			);
		}
		return $comment_data;
	}

	private static function current_user_comment( $product_id, $content, $type ) {
		$user = wp_get_current_user();
		return [
			'comment_post_ID'      => $product_id,
			'comment_author'       => $user->display_name ?: $user->user_login,
			'comment_author_email' => $user->user_email,
			'comment_content'      => $content,
			'comment_type'         => $type,
			'comment_parent'       => 0,
			'user_id'              => $user->ID,
			'comment_approved'      => 0,
			'comment_agent'         => 'Bijan Product Community',
		];
	}

	private static function clean_list( $value ) {
		if ( is_array( $value ) ) {
			$items = wp_unslash( $value );
		} else {
			$value = is_string( $value ) ? wp_unslash( $value ) : '';
			$items = preg_split( '/\r\n|\r|\n/', $value );
		}
		$items = array_filter( $items, 'is_scalar' );
		$items = array_map( 'sanitize_text_field', $items );
		$items = array_values( array_filter( array_map( 'trim', $items ) ) );
		$items = array_slice( array_unique( $items ), 0, 6 );
		return array_map( static function ( $item ) {
			return function_exists( 'mb_substr' ) ? mb_substr( $item, 0, 120 ) : substr( $item, 0, 120 );
		}, $items );
	}

	public static function submit_review() {
		self::verify_request();
		$product_id = self::product_id_from_request();
		$user_id    = get_current_user_id();
		$score      = isset( $_POST['score'] ) ? absint( $_POST['score'] ) : 0;
		$content    = isset( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';

		if ( $score < 1 || $score > 5 ) {
			wp_send_json_error( [ 'message' => 'لطفاً امتیازی بین ۱ تا ۵ ستاره انتخاب کنید.' ], 422 );
		}
		if ( self::text_length( $content ) < 10 || self::text_length( $content ) > 3000 ) {
			wp_send_json_error( [ 'message' => 'متن نظر باید بین ۱۰ تا ۳۰۰۰ نویسه باشد.' ], 422 );
		}

		$existing = get_comments( [
			'post_id' => $product_id,
			'user_id' => $user_id,
			'type'    => self::REVIEW_TYPE,
			'status'  => 'all',
			'count'   => true,
		] );
		if ( $existing ) {
			wp_send_json_error( [ 'message' => 'شما قبلاً برای این محصول نظر ثبت کرده‌اید.' ], 409 );
		}

		$comment_id = wp_insert_comment( self::current_user_comment( $product_id, $content, self::REVIEW_TYPE ) );
		if ( ! $comment_id ) {
			wp_send_json_error( [ 'message' => 'ثبت نظر انجام نشد. دوباره تلاش کنید.' ], 500 );
		}

		update_comment_meta( $comment_id, '_bijan_score_5', $score );
		update_comment_meta( $comment_id, '_bijan_strengths', self::clean_list( $_POST['strengths'] ?? '' ) );
		update_comment_meta( $comment_id, '_bijan_weaknesses', self::clean_list( $_POST['weaknesses'] ?? '' ) );

		$upload_result = self::handle_images( $comment_id, $product_id );
		if ( is_wp_error( $upload_result ) ) {
			wp_delete_comment( $comment_id, true );
			wp_send_json_error( [ 'message' => $upload_result->get_error_message() ], 422 );
		}
		update_comment_meta( $comment_id, '_bijan_images', $upload_result );

		wp_send_json_success( [ 'message' => 'نظر شما ثبت شد و پس از بررسی نمایش داده می‌شود.' ] );
	}

	private static function handle_images( $comment_id, $product_id ) {
		if ( empty( $_FILES['images']['name'] ) || ! is_array( $_FILES['images']['name'] ) ) {
			return [];
		}

		$files = $_FILES['images'];
		$names = array_values( array_filter( $files['name'] ) );
		if ( count( $names ) > self::MAX_IMAGES ) {
			return new WP_Error( 'too_many_images', 'حداکثر ۴ تصویر می‌توانید ارسال کنید.' );
		}

		$allowed = [ 'image/jpeg', 'image/png', 'image/webp' ];
		foreach ( $files['name'] as $index => $name ) {
			if ( ! $name ) {
				continue;
			}
			if ( ! empty( $files['error'][ $index ] ) || $files['size'][ $index ] > self::MAX_IMAGE_SIZE ) {
				return new WP_Error( 'invalid_image', 'یکی از تصاویر نامعتبر یا بزرگ‌تر از ۵ مگابایت است.' );
			}
			$file_check = wp_check_filetype_and_ext( $files['tmp_name'][ $index ], $name );
			if ( empty( $file_check['type'] ) || ! in_array( $file_check['type'], $allowed, true ) ) {
				return new WP_Error( 'invalid_image_type', 'فقط تصاویر JPG، PNG و WebP مجاز هستند.' );
			}
			$image_info = @getimagesize( $files['tmp_name'][ $index ] );
			if ( ! $image_info || ( (int) $image_info[0] * (int) $image_info[1] ) > 25000000 ) {
				return new WP_Error( 'invalid_image_dimensions', 'ابعاد یکی از تصاویر بیش از حد مجاز است.' );
			}
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_ids = [];
		foreach ( $files['name'] as $index => $name ) {
			if ( ! $name ) {
				continue;
			}
			$_FILES['bijan_community_image'] = [
				'name'     => sanitize_file_name( $name ),
				'type'     => $files['type'][ $index ],
				'tmp_name' => $files['tmp_name'][ $index ],
				'error'    => $files['error'][ $index ],
				'size'     => $files['size'][ $index ],
			];
			$attachment_id = media_handle_upload( 'bijan_community_image', $product_id, [], [ 'test_form' => false ] );
			if ( is_wp_error( $attachment_id ) ) {
				foreach ( $attachment_ids as $uploaded_id ) {
					wp_delete_attachment( $uploaded_id, true );
				}
				unset( $_FILES['bijan_community_image'] );
				return new WP_Error( 'upload_failed', 'بارگذاری یکی از تصاویر انجام نشد.' );
			}
			update_post_meta( $attachment_id, '_bijan_review_comment_id', $comment_id );
			$attachment_ids[] = $attachment_id;
		}
		unset( $_FILES['bijan_community_image'] );
		return $attachment_ids;
	}

	public static function submit_question() {
		self::verify_request();
		$product_id = self::product_id_from_request();
		$user_id    = get_current_user_id();
		$content    = isset( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';

		if ( self::text_length( $content ) < 10 || self::text_length( $content ) > 1200 ) {
			wp_send_json_error( [ 'message' => 'متن پرسش باید بین ۱۰ تا ۱۲۰۰ نویسه باشد.' ], 422 );
		}
		$rate_key = 'bijan_question_' . $user_id . '_' . $product_id;
		if ( get_transient( $rate_key ) ) {
			wp_send_json_error( [ 'message' => 'لطفاً کمی بعد پرسش بعدی را ثبت کنید.' ], 429 );
		}

		$comment_id = wp_insert_comment( self::current_user_comment( $product_id, $content, self::QUESTION_TYPE ) );
		if ( ! $comment_id ) {
			wp_send_json_error( [ 'message' => 'ثبت پرسش انجام نشد. دوباره تلاش کنید.' ], 500 );
		}
		set_transient( $rate_key, 1, MINUTE_IN_SECONDS );
		wp_send_json_success( [ 'message' => 'پرسش شما ثبت شد و پس از پاسخ پشتیبانی نمایش داده می‌شود.' ] );
	}

	public static function toggle_helpful() {
		self::verify_request();
		$comment_id = isset( $_POST['comment_id'] ) ? absint( $_POST['comment_id'] ) : 0;
		$comment    = get_comment( $comment_id );
		if ( ! $comment || self::REVIEW_TYPE !== $comment->comment_type || '1' !== (string) $comment->comment_approved ) {
			wp_send_json_error( [ 'message' => 'نظر معتبر نیست.' ], 404 );
		}

		$user_id = get_current_user_id();
		$voters  = array_map( 'absint', (array) get_comment_meta( $comment_id, '_bijan_helpful_users', true ) );
		$active  = ! in_array( $user_id, $voters, true );
		if ( $active ) {
			$voters[] = $user_id;
		} else {
			$voters = array_values( array_diff( $voters, [ $user_id ] ) );
		}
		update_comment_meta( $comment_id, '_bijan_helpful_users', array_values( array_unique( $voters ) ) );
		wp_send_json_success( [ 'count' => count( $voters ), 'active' => $active ] );
	}

	private static function text_length( $text ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( trim( $text ) ) : strlen( trim( $text ) );
	}

	private static function reviews( $product_id, $page = 1 ) {
		return get_comments( [
			'post_id' => $product_id,
			'type'    => self::REVIEW_TYPE,
			'status'  => 'approve',
			'parent'  => 0,
			'number'  => self::PAGE_SIZE,
			'offset'  => ( max( 1, $page ) - 1 ) * self::PAGE_SIZE,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
		] );
	}

	private static function questions( $product_id, $page = 1 ) {
		return get_comments( [
			'post_id' => $product_id,
			'type'    => self::QUESTION_TYPE,
			'status'  => 'approve',
			'parent'  => 0,
			'number'  => self::PAGE_SIZE,
			'offset'  => ( max( 1, $page ) - 1 ) * self::PAGE_SIZE,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
		] );
	}

	private static function count( $product_id, $type, $status = 'approve' ) {
		return (int) get_comments( [
			'post_id' => $product_id,
			'type'    => $type,
			'status'  => $status,
			'parent'  => 0,
			'count'   => true,
		] );
	}

	private static function score_stats( $product_id ) {
		$cache_key = 'bijan_review_stats_5_' . absint( $product_id );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) && isset( $cached['count'], $cached['average'], $cached['dist'] ) ) {
			return $cached;
		}
		$ids = get_comments( [
			'post_id' => $product_id,
			'type'    => self::REVIEW_TYPE,
			'status'  => 'approve',
			'parent'  => 0,
			'fields'  => 'ids',
			'number'  => 0,
		] );
		$total = 0;
		$dist  = array_fill( 1, 5, 0 );
		foreach ( $ids as $id ) {
			$score = self::review_score( $id );
			$total += $score;
			$dist[ min( 5, max( 1, (int) round( $score ) ) ) ]++;
		}
		$stats = [
			'count'   => count( $ids ),
			'average' => $ids ? round( $total / count( $ids ), 1 ) : 0,
			'dist'    => $dist,
		];
		set_transient( $cache_key, $stats, 15 * MINUTE_IN_SECONDS );
		return $stats;
	}

	/**
	 * Return every review on the five-star scale while keeping old 10-point data valid.
	 */
	private static function review_score( $comment_id ) {
		$score_5 = get_comment_meta( $comment_id, '_bijan_score_5', true );
		if ( '' !== $score_5 ) {
			return min( 5, max( 1, absint( $score_5 ) ) );
		}

		$legacy_score = absint( get_comment_meta( $comment_id, '_bijan_score_10', true ) );
		return $legacy_score ? min( 5, max( 1, round( $legacy_score / 2, 1 ) ) ) : 1;
	}

	public static function cleanup_deleted_review( $comment_id, $comment = null ) {
		$comment = $comment ?: get_comment( $comment_id );
		if ( ! $comment || self::REVIEW_TYPE !== $comment->comment_type ) {
			return;
		}
		delete_transient( 'bijan_review_stats_' . absint( $comment->comment_post_ID ) );
		delete_transient( 'bijan_review_stats_5_' . absint( $comment->comment_post_ID ) );
		foreach ( array_map( 'absint', (array) get_comment_meta( $comment_id, '_bijan_images', true ) ) as $attachment_id ) {
			if ( (int) get_post_meta( $attachment_id, '_bijan_review_comment_id', true ) === (int) $comment_id ) {
				wp_delete_attachment( $attachment_id, true );
			}
		}
	}

	public static function invalidate_review_stats( $new_status, $old_status, $comment ) {
		if ( $comment instanceof WP_Comment && self::REVIEW_TYPE === $comment->comment_type && $new_status !== $old_status ) {
			delete_transient( 'bijan_review_stats_' . absint( $comment->comment_post_ID ) );
			delete_transient( 'bijan_review_stats_5_' . absint( $comment->comment_post_ID ) );
		}
	}

	public static function render_product_head_stats() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$stats = self::score_stats( $product->get_id() );
		?>
		<div class="product-head-meta bijan-community-head-stat">
			<i class="bijan-icon-star-2 active"></i>
			<a href="#product-community" class="product-meta-value"><?php echo esc_html( $stats['count'] ? $stats['average'] . ' از ۵' : 'بدون امتیاز' ); ?></a>
		</div>
		<div class="product-head-meta bijan-community-head-stat">
			<i class="bijan-icon-messages"></i>
			<a href="#product-community" class="post-meta-value"><?php echo esc_html( number_format_i18n( $stats['count'] ) . ' نظر' ); ?></a>
		</div>
		<?php
	}

	public static function render() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$product_id    = $product->get_id();
		$stats          = self::score_stats( $product_id );
		$question_count = self::count( $product_id, self::QUESTION_TYPE );
		$review_page    = min( max( 1, absint( $_GET['review_page'] ?? 1 ) ), max( 1, (int) ceil( $stats['count'] / self::PAGE_SIZE ) ) );
		$question_page  = min( max( 1, absint( $_GET['question_page'] ?? 1 ) ), max( 1, (int) ceil( $question_count / self::PAGE_SIZE ) ) );
		$reviews       = self::reviews( $product_id, $review_page );
		$questions     = self::questions( $product_id, $question_page );
		?>
		<section id="product-community" class="bijan-community product-section" aria-labelledby="community-title">
			<header class="bc-heading">
				<div>
					<span class="bc-eyebrow">تجربه واقعی کاربران</span>
					<h2 id="community-title">نظرها و پرسش‌های این محصول</h2>
					<p>تجربه‌تان به انتخاب بهتر دیگران کمک می‌کند.</p>
				</div>
				<div class="bc-heading-actions">
					<?php self::action_button( 'review', 'ثبت نظر و امتیاز', 'bc-button bc-button-primary' ); ?>
					<?php self::action_button( 'question', 'ثبت پرسش', 'bc-button bc-button-secondary' ); ?>
				</div>
			</header>

			<nav class="bc-tabs" aria-label="نظرها و پرسش‌ها">
				<button type="button" class="bc-tab is-active" data-tab="reviews" aria-selected="true">
					نظر کاربران <span><?php echo esc_html( number_format_i18n( $stats['count'] ) ); ?></span>
				</button>
				<button type="button" class="bc-tab" data-tab="questions" aria-selected="false">
					پرسش و پاسخ <span><?php echo esc_html( number_format_i18n( $question_count ) ); ?></span>
				</button>
			</nav>

			<div class="bc-panel is-active" data-panel="reviews">
				<div class="bc-review-layout">
					<aside class="bc-score-card"><?php self::render_score_card( $stats ); ?></aside>
					<div class="bc-feed">
						<?php if ( $reviews ) : ?>
							<?php foreach ( $reviews as $review ) { self::render_review( $review, $product_id ); } ?>
							<?php self::pagination( $stats['count'], $review_page, 'review_page', 'reviews' ); ?>
						<?php else : ?>
							<?php self::empty_state( 'review', 'هنوز نظری ثبت نشده', 'اولین نفری باشید که تجربه‌اش را درباره این محصول می‌نویسد.' ); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="bc-panel" data-panel="questions" hidden>
				<div class="bc-question-intro">
					<div><strong>سؤالی درباره این محصول دارید؟</strong><span>پشتیبانی پس از بررسی پاسخ می‌دهد و پرسش همراه پاسخ منتشر می‌شود.</span></div>
					<?php self::action_button( 'question', 'پرسیدن سؤال', 'bc-button bc-button-primary' ); ?>
				</div>
				<div class="bc-questions">
					<?php if ( $questions ) : ?>
						<?php foreach ( $questions as $question ) { self::render_question( $question ); } ?>
						<?php self::pagination( $question_count, $question_page, 'question_page', 'questions' ); ?>
					<?php else : ?>
						<?php self::empty_state( 'question', 'پرسش پاسخ‌داده‌شده‌ای نیست', 'اگر نکته‌ای درباره محصول برایتان مبهم است، همین حالا بپرسید.' ); ?>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php self::render_modal( $product_id ); ?>
		<?php
	}

	private static function action_button( $type, $label, $class ) {
		if ( is_user_logged_in() ) {
			printf( '<button type="button" class="%1$s" data-community-open="%2$s">%3$s</button>', esc_attr( $class ), esc_attr( $type ), esc_html( $label ) );
		} else {
			printf( '<a href="?login" class="%1$s showlogin" data-community-login>%2$s</a>', esc_attr( $class ), esc_html( $label ) );
		}
	}

	private static function render_score_card( $stats ) {
		$count = max( 1, $stats['count'] );
		?>
		<div class="bc-score-main">
			<strong><?php echo esc_html( number_format_i18n( $stats['average'], 1 ) ); ?></strong><span>از ۵</span>
		</div>
		<div class="bc-score-caption"><?php echo $stats['count'] ? esc_html( 'بر اساس ' . number_format_i18n( $stats['count'] ) . ' نظر' ) : 'هنوز امتیازی ثبت نشده'; ?></div>
		<div class="bc-score-bars" aria-label="توزیع امتیازها">
			<?php for ( $score = 5; $score >= 1; $score-- ) : $percent = $stats['count'] ? round( ( $stats['dist'][ $score ] / $count ) * 100 ) : 0; ?>
				<div class="bc-score-row"><span><?php echo esc_html( number_format_i18n( $score ) ); ?></span><i><b style="width:<?php echo esc_attr( $percent ); ?>%"></b></i><small><?php echo esc_html( number_format_i18n( $stats['dist'][ $score ] ) ); ?></small></div>
			<?php endfor; ?>
		</div>
		<?php self::action_button( 'review', 'تجربه‌ام را می‌نویسم', 'bc-button bc-button-primary bc-score-cta' ); ?>
		<?php
	}

	private static function render_review( $review, $product_id ) {
		$score      = self::review_score( $review->comment_ID );
		$score_text = number_format_i18n( $score, (float) floor( $score ) === (float) $score ? 0 : 1 );
		$strengths  = (array) get_comment_meta( $review->comment_ID, '_bijan_strengths', true );
		$weaknesses = (array) get_comment_meta( $review->comment_ID, '_bijan_weaknesses', true );
		$images     = array_map( 'absint', (array) get_comment_meta( $review->comment_ID, '_bijan_images', true ) );
		$voters     = array_map( 'absint', (array) get_comment_meta( $review->comment_ID, '_bijan_helpful_users', true ) );
		$is_helpful = is_user_logged_in() && in_array( get_current_user_id(), $voters, true );
		$verified   = function_exists( 'wc_customer_bought_product' ) && wc_customer_bought_product( $review->comment_author_email, $review->user_id, $product_id );
		?>
		<article class="bc-review-card">
			<header class="bc-card-head">
				<div class="bc-author">
					<?php echo get_avatar( $review, 48, '', '', [ 'class' => 'bc-avatar' ] ); ?>
					<div><strong><?php echo esc_html( $review->comment_author ); ?></strong><span><?php echo esc_html( human_time_diff( get_comment_time( 'U', true, false, $review ), current_time( 'timestamp', true ) ) . ' پیش' ); ?></span></div>
					<?php if ( $verified ) : ?><em class="bc-verified">خریدار محصول</em><?php endif; ?>
				</div>
				<div class="bc-score-badge bc-score-<?php echo esc_attr( $score >= 4 ? 'good' : ( $score >= 3 ? 'mid' : 'bad' ) ); ?>" aria-label="<?php echo esc_attr( $score_text . ' از ۵ ستاره' ); ?>"><i aria-hidden="true">★</i><strong><?php echo esc_html( $score_text ); ?></strong><span>/۵</span></div>
			</header>
			<div class="bc-review-text"><?php echo wpautop( esc_html( $review->comment_content ) ); ?></div>
			<?php self::render_points( $strengths, $weaknesses ); ?>
			<?php if ( $images ) : ?>
				<div class="bc-review-images">
					<?php foreach ( $images as $image_id ) : $full = wp_get_attachment_image_url( $image_id, 'full' ); if ( ! $full ) { continue; } ?>
						<a href="<?php echo esc_url( $full ); ?>" target="_blank" rel="noopener" aria-label="مشاهده تصویر نظر"><?php echo wp_get_attachment_image( $image_id, 'thumbnail', false, [ 'loading' => 'lazy' ] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<footer class="bc-card-footer">
				<span>این نظر مفید بود؟</span>
				<?php if ( is_user_logged_in() ) : ?>
					<button type="button" class="bc-helpful<?php echo $is_helpful ? ' is-active' : ''; ?>" data-comment-id="<?php echo esc_attr( $review->comment_ID ); ?>" aria-pressed="<?php echo $is_helpful ? 'true' : 'false'; ?>">بله <b><?php echo esc_html( number_format_i18n( count( $voters ) ) ); ?></b></button>
				<?php else : ?>
					<a href="?login" class="bc-helpful showlogin">بله <b><?php echo esc_html( number_format_i18n( count( $voters ) ) ); ?></b></a>
				<?php endif; ?>
			</footer>
		</article>
		<?php
	}

	private static function render_points( $strengths, $weaknesses ) {
		if ( ! $strengths && ! $weaknesses ) {
			return;
		}
		?>
		<div class="bc-points">
			<?php if ( $strengths ) : ?><div class="bc-point-list bc-pros"><strong>نقاط قوت</strong><?php foreach ( $strengths as $item ) : ?><span><?php echo esc_html( $item ); ?></span><?php endforeach; ?></div><?php endif; ?>
			<?php if ( $weaknesses ) : ?><div class="bc-point-list bc-cons"><strong>نقاط ضعف</strong><?php foreach ( $weaknesses as $item ) : ?><span><?php echo esc_html( $item ); ?></span><?php endforeach; ?></div><?php endif; ?>
		</div>
		<?php
	}

	private static function render_question( $question ) {
		$answers = get_comments( [
			'post_id' => $question->comment_post_ID,
			'parent'  => $question->comment_ID,
			'type'    => self::ANSWER_TYPE,
			'status'  => 'approve',
			'orderby' => 'comment_date_gmt',
			'order'   => 'ASC',
		] );
		if ( ! $answers ) {
			return;
		}
		?>
		<article class="bc-question-card">
			<div class="bc-qa-row bc-question-row"><span class="bc-qa-icon">؟</span><div><small>پرسش <?php echo esc_html( $question->comment_author ); ?></small><p><?php echo nl2br( esc_html( $question->comment_content ) ); ?></p></div></div>
			<?php foreach ( $answers as $answer ) : ?>
				<div class="bc-qa-row bc-answer-row"><span class="bc-qa-icon">✓</span><div><small>پاسخ پشتیبانی</small><p><?php echo nl2br( esc_html( $answer->comment_content ) ); ?></p></div></div>
			<?php endforeach; ?>
		</article>
		<?php
	}

	private static function empty_state( $type, $title, $text ) {
		?>
		<div class="bc-empty"><span><?php echo 'review' === $type ? '☆' : '؟'; ?></span><strong><?php echo esc_html( $title ); ?></strong><p><?php echo esc_html( $text ); ?></p><?php self::action_button( $type, 'review' === $type ? 'ثبت اولین نظر' : 'ثبت اولین پرسش', 'bc-button bc-button-primary' ); ?></div>
		<?php
	}

	private static function pagination( $total, $page, $query_key, $tab ) {
		$pages = (int) ceil( $total / self::PAGE_SIZE );
		if ( $pages < 2 ) {
			return;
		}
		echo '<nav class="bc-pagination" aria-label="صفحه‌بندی">';
		echo wp_kses_post( paginate_links( [
			'base'      => add_query_arg( $query_key, '%#%', get_permalink() ) . '#product-community',
			'format'    => '',
			'current'   => $page,
			'total'     => $pages,
			'prev_text' => 'قبلی',
			'next_text' => 'بعدی',
			'add_args'  => [ 'community_tab' => $tab ],
		] ) );
		echo '</nav>';
	}

	private static function render_modal( $product_id ) {
		if ( ! is_user_logged_in() ) {
			return;
		}
		?>
		<div class="bc-modal" id="bc-community-modal" hidden aria-hidden="true">
			<button type="button" class="bc-modal-backdrop" data-community-close aria-label="بستن پنجره" tabindex="-1"></button>
			<div class="bc-modal-dialog" role="dialog" aria-modal="true" aria-label="ثبت نظر یا پرسش محصول" tabindex="-1">
				<div class="bc-modal-topbar">
					<strong>نظر و پرسش محصول</strong>
					<button type="button" class="bc-modal-close" data-community-close aria-label="بستن پنجره"><span aria-hidden="true">×</span></button>
				</div>
				<div class="bc-modal-body">
				<div class="bc-form-view" data-form-view="review">
					<header><span>تجربه شما</span><h3 id="bc-modal-title">ثبت نظر برای این محصول</h3><p>امتیاز بدهید و نکته‌ای را بنویسید که واقعاً به انتخاب دیگران کمک کند.</p></header>
					<form id="bc-review-form" enctype="multipart/form-data">
						<input type="hidden" name="action" value="bijan_submit_product_review"><input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( self::NONCE_ACTION ) ); ?>"><input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
						<fieldset class="bc-rating-field">
							<legend>امتیاز شما</legend>
							<div class="bc-rating-copy"><strong>از تجربه‌تان چند ستاره می‌دهید؟</strong><span>روی یکی از ستاره‌ها بزنید.</span></div>
							<output><strong>۵</strong><small>از ۵</small></output>
							<div class="bc-star-picker" role="radiogroup" aria-label="انتخاب امتیاز از ۵ ستاره">
								<?php for ( $score = 1; $score <= 5; $score++ ) : ?>
									<input id="bc-score-<?php echo esc_attr( $score ); ?>" name="score" type="radio" value="<?php echo esc_attr( $score ); ?>"<?php checked( 5, $score ); ?>>
									<label for="bc-score-<?php echo esc_attr( $score ); ?>" data-score="<?php echo esc_attr( $score ); ?>" aria-label="<?php echo esc_attr( $score . ' ستاره' ); ?>">★</label>
								<?php endfor; ?>
							</div>
						</fieldset>
						<div class="bc-points-editor">
							<section class="bc-point-editor bc-point-editor-pro" data-point-group="strengths">
								<header><div><strong>نقاط قوت</strong><small>اختیاری؛ کوتاه و مشخص</small></div><button type="button" data-point-add="strengths"><span aria-hidden="true">+</span> افزودن</button></header>
								<div class="bc-point-inputs" data-point-list="strengths"></div><p class="bc-point-empty">اگر نکته مثبتی دارید با دکمه «افزودن» بنویسید.</p>
							</section>
							<section class="bc-point-editor bc-point-editor-con" data-point-group="weaknesses">
								<header><div><strong>نقاط ضعف</strong><small>اختیاری؛ منصفانه و کاربردی</small></div><button type="button" data-point-add="weaknesses"><span aria-hidden="true">+</span> افزودن</button></header>
								<div class="bc-point-inputs" data-point-list="weaknesses"></div><p class="bc-point-empty">اگر ایرادی دیده‌اید با دکمه «افزودن» بنویسید.</p>
							</section>
						</div>
						<label class="bc-field"><span>نظر شما</span><textarea name="content" rows="6" minlength="10" maxlength="3000" required placeholder="تجربه استفاده، کیفیت و نکاتی که برای خریداران دیگر مفید است…"></textarea><small class="bc-counter"><b>۰</b> / ۳۰۰۰</small></label>
						<div class="bc-upload"><input id="bc-review-images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple hidden><label for="bc-review-images"><span>＋</span><strong>افزودن تصویر</strong><small>حداکثر ۴ تصویر، هرکدام تا ۵ مگابایت</small></label><div class="bc-image-preview"></div></div>
						<div class="bc-form-message" role="status" aria-live="polite"></div><button type="submit" class="bc-button bc-button-primary bc-submit">ثبت نظر</button>
					</form>
				</div>
				<div class="bc-form-view" data-form-view="question" hidden>
					<header><span>پرسش از پشتیبانی</span><h3>چه چیزی درباره محصول مبهم است؟</h3><p>سؤال را مستقیم و با جزئیات لازم بنویسید تا پاسخ دقیق‌تری بگیرید.</p></header>
					<form id="bc-question-form">
						<input type="hidden" name="action" value="bijan_submit_product_question"><input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( self::NONCE_ACTION ) ); ?>"><input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
						<label class="bc-field"><span>متن پرسش</span><textarea name="content" rows="6" minlength="10" maxlength="1200" required placeholder="مثلاً آیا این محصول با … سازگار است؟"></textarea><small class="bc-counter"><b>۰</b> / ۱۲۰۰</small></label>
						<div class="bc-form-note"><strong>بعد از ثبت چه می‌شود؟</strong><span>پشتیبانی پاسخ را بررسی می‌کند و پرسش همراه پاسخ برای کاربران نمایش داده می‌شود.</span></div><div class="bc-form-message" role="status" aria-live="polite"></div><button type="submit" class="bc-button bc-button-primary bc-submit">ثبت پرسش</button>
					</form>
				</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function admin_menu() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
		$pending = self::admin_pending_count();
		$label   = 'نظرات و پرسش‌ها';
		if ( $pending ) {
			$label .= ' <span class="awaiting-mod count-' . absint( $pending ) . '"><span class="pending-count">' . number_format_i18n( $pending ) . '</span></span>';
		}
		add_submenu_page( 'woocommerce', 'نظرات و پرسش‌های محصولات', $label, 'moderate_comments', 'bijan-product-community', [ __CLASS__, 'admin_page' ] );
	}

	private static function admin_pending_count() {
		return (int) get_comments( [ 'type__in' => [ self::REVIEW_TYPE, self::QUESTION_TYPE ], 'status' => 'hold', 'count' => true ] );
	}

	public static function admin_action() {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			wp_die( 'دسترسی کافی ندارید.' );
		}
		$comment_id = absint( $_POST['comment_id'] ?? $_GET['comment_id'] ?? 0 );
		$operation  = sanitize_key( $_POST['operation'] ?? $_GET['operation'] ?? '' );
		check_admin_referer( 'bijan_community_' . $comment_id );
		$comment = get_comment( $comment_id );
		if ( ! $comment || ! in_array( $comment->comment_type, [ self::REVIEW_TYPE, self::QUESTION_TYPE ], true ) ) {
			wp_die( 'مورد انتخاب‌شده معتبر نیست.' );
		}

		$message = 'updated';
		if ( 'approve' === $operation && self::REVIEW_TYPE === $comment->comment_type ) {
			wp_set_comment_status( $comment_id, 'approve' );
			delete_transient( 'bijan_review_stats_' . absint( $comment->comment_post_ID ) );
			delete_transient( 'bijan_review_stats_5_' . absint( $comment->comment_post_ID ) );
		} elseif ( 'trash' === $operation ) {
			wp_trash_comment( $comment_id );
			if ( self::REVIEW_TYPE === $comment->comment_type ) {
				delete_transient( 'bijan_review_stats_' . absint( $comment->comment_post_ID ) );
				delete_transient( 'bijan_review_stats_5_' . absint( $comment->comment_post_ID ) );
			}
			$message = 'trashed';
		} elseif ( 'answer' === $operation && self::QUESTION_TYPE === $comment->comment_type ) {
			$answer = sanitize_textarea_field( wp_unslash( $_POST['answer'] ?? '' ) );
			if ( self::text_length( $answer ) < 2 || self::text_length( $answer ) > 3000 ) {
				wp_die( 'متن پاسخ باید بین ۲ تا ۳۰۰۰ نویسه باشد.' );
			}
			$user = wp_get_current_user();
			$answer_id = wp_insert_comment( [
				'comment_post_ID'      => $comment->comment_post_ID,
				'comment_parent'       => $comment_id,
				'comment_author'       => $user->display_name ?: $user->user_login,
				'comment_author_email' => $user->user_email,
				'comment_content'      => $answer,
				'comment_type'         => self::ANSWER_TYPE,
				'user_id'              => $user->ID,
				'comment_approved'      => 1,
			] );
			if ( ! $answer_id ) {
				wp_die( 'پاسخ ذخیره نشد.' );
			}
			wp_set_comment_status( $comment_id, 'approve' );
			$message = 'answered';
		} else {
			wp_die( 'عملیات معتبر نیست.' );
		}

		wp_safe_redirect( add_query_arg( [ 'page' => 'bijan-product-community', 'community_message' => $message ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function admin_page() {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			return;
		}
		$type = sanitize_key( $_GET['view'] ?? 'pending' );
		$args = [
			'type__in' => [ self::REVIEW_TYPE, self::QUESTION_TYPE ],
			'status'   => 'all',
			'number'   => 100,
			'orderby'  => 'comment_date_gmt',
			'order'    => 'DESC',
		];
		if ( 'reviews' === $type ) {
			$args['type'] = self::REVIEW_TYPE;
			unset( $args['type__in'] );
		} elseif ( 'questions' === $type ) {
			$args['type'] = self::QUESTION_TYPE;
			unset( $args['type__in'] );
		} else {
			$args['status'] = 'hold';
		}
		$items = get_comments( $args );
		$message = sanitize_key( $_GET['community_message'] ?? '' );
		?>
		<div class="wrap bc-admin-wrap">
			<h1>نظرات و پرسش‌های محصولات</h1>
			<?php if ( $message ) : ?><div class="notice notice-success is-dismissible"><p><?php echo 'answered' === $message ? 'پاسخ ثبت و پرسش منتشر شد.' : ( 'trashed' === $message ? 'مورد انتخاب‌شده به زباله‌دان منتقل شد.' : 'نظر تأیید و منتشر شد.' ); ?></p></div><?php endif; ?>
			<nav class="nav-tab-wrapper">
				<a class="nav-tab <?php echo 'pending' === $type ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=bijan-product-community&view=pending' ) ); ?>">در انتظار بررسی <span class="count"><?php echo esc_html( number_format_i18n( self::admin_pending_count() ) ); ?></span></a>
				<a class="nav-tab <?php echo 'reviews' === $type ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=bijan-product-community&view=reviews' ) ); ?>">همه نظرها</a>
				<a class="nav-tab <?php echo 'questions' === $type ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=bijan-product-community&view=questions' ) ); ?>">همه پرسش‌ها</a>
			</nav>
			<div class="bc-admin-list">
			<?php if ( ! $items ) : ?><div class="bc-admin-empty">موردی برای نمایش وجود ندارد.</div><?php endif; ?>
			<?php foreach ( $items as $item ) : self::admin_item( $item ); endforeach; ?>
			</div>
		</div>
		<style>
		.bc-admin-list{display:grid;gap:14px;max-width:1100px;margin-top:20px}.bc-admin-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px;box-shadow:0 1px 2px rgba(0,0,0,.04)}.bc-admin-head{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.bc-admin-head strong{font-size:15px}.bc-admin-badge{padding:3px 9px;border-radius:20px;background:#e8f1ff;color:#135e96}.bc-admin-status{margin-inline-start:auto;color:#646970}.bc-admin-content{font-size:14px;line-height:1.9;margin:14px 0;padding:12px;background:#f6f7f7;border-radius:7px}.bc-admin-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.bc-admin-answer{display:flex;gap:8px;width:100%;margin-top:10px}.bc-admin-answer textarea{flex:1;min-height:76px}.bc-admin-images{display:flex;gap:8px;margin:10px 0}.bc-admin-images img{width:72px;height:72px;object-fit:cover;border-radius:7px}.bc-admin-empty{padding:40px;text-align:center;background:#fff;border-radius:10px}.bc-admin-meta{color:#646970;margin-top:5px}
		</style>
		<?php
	}

	private static function admin_item( $item ) {
		$is_question = self::QUESTION_TYPE === $item->comment_type;
		$product     = get_post( $item->comment_post_ID );
		$nonce_url   = wp_nonce_url( admin_url( 'admin-post.php?action=bijan_community_action&comment_id=' . $item->comment_ID ), 'bijan_community_' . $item->comment_ID );
		$strengths   = $is_question ? [] : (array) get_comment_meta( $item->comment_ID, '_bijan_strengths', true );
		$weaknesses  = $is_question ? [] : (array) get_comment_meta( $item->comment_ID, '_bijan_weaknesses', true );
		$answers     = $is_question ? get_comments( [ 'parent' => $item->comment_ID, 'type' => self::ANSWER_TYPE, 'status' => 'approve' ] ) : [];
		?>
		<article class="bc-admin-card">
			<div class="bc-admin-head"><span class="bc-admin-badge"><?php echo $is_question ? 'پرسش' : 'نظر'; ?></span><strong><?php echo esc_html( $item->comment_author ); ?></strong><span>برای <a href="<?php echo esc_url( get_edit_post_link( $item->comment_post_ID ) ); ?>"><?php echo esc_html( $product ? $product->post_title : 'محصول حذف‌شده' ); ?></a></span><span class="bc-admin-status"><?php echo '1' === (string) $item->comment_approved ? 'منتشرشده' : 'در انتظار بررسی'; ?></span></div>
			<div class="bc-admin-meta"><?php echo esc_html( get_comment_date( 'Y/m/d - H:i', $item ) ); ?><?php if ( ! $is_question ) : ?> — امتیاز: <?php echo esc_html( number_format_i18n( self::review_score( $item->comment_ID ), 1 ) ); ?> از ۵<?php endif; ?></div>
			<div class="bc-admin-content"><?php echo nl2br( esc_html( $item->comment_content ) ); ?></div>
			<?php if ( $strengths ) : ?><div class="bc-admin-meta"><strong>نقاط قوت:</strong> <?php echo esc_html( implode( '، ', $strengths ) ); ?></div><?php endif; ?>
			<?php if ( $weaknesses ) : ?><div class="bc-admin-meta"><strong>نقاط ضعف:</strong> <?php echo esc_html( implode( '، ', $weaknesses ) ); ?></div><?php endif; ?>
			<?php foreach ( $answers as $answer ) : ?><div class="bc-admin-content"><strong>پاسخ فعلی:</strong><br><?php echo nl2br( esc_html( $answer->comment_content ) ); ?></div><?php endforeach; ?>
			<?php if ( ! $is_question ) : $images = (array) get_comment_meta( $item->comment_ID, '_bijan_images', true ); if ( $images ) : ?><div class="bc-admin-images"><?php foreach ( $images as $image_id ) { echo wp_get_attachment_image( $image_id, 'thumbnail' ); } ?></div><?php endif; endif; ?>
			<div class="bc-admin-actions">
				<?php if ( ! $is_question && '1' !== (string) $item->comment_approved ) : ?><a class="button button-primary" href="<?php echo esc_url( add_query_arg( 'operation', 'approve', $nonce_url ) ); ?>">تأیید و انتشار</a><?php endif; ?>
				<a class="button" href="<?php echo esc_url( add_query_arg( 'operation', 'trash', $nonce_url ) ); ?>" onclick="return confirm('این مورد به زباله‌دان منتقل شود؟')">حذف</a>
				<?php if ( $is_question ) : ?>
					<form class="bc-admin-answer" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="bijan_community_action"><input type="hidden" name="operation" value="answer"><input type="hidden" name="comment_id" value="<?php echo esc_attr( $item->comment_ID ); ?>"><?php wp_nonce_field( 'bijan_community_' . $item->comment_ID ); ?><textarea name="answer" maxlength="3000" required placeholder="پاسخ پشتیبانی را بنویسید…"></textarea><button class="button button-primary" type="submit">ثبت پاسخ و انتشار</button></form>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}
}

Bijan_Product_Community::init();
