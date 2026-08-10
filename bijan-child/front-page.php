<?php
/**
 * Single-file custom homepage for the Bijan child theme.
 * Contains helpers, server-side WooCommerce queries, inline CSS and lightweight JS.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-page helpers for the Bijan child theme.
 *
 * Place this file beside front-page.php.
 */

defined( 'ABSPATH' ) || exit;


if ( ! function_exists( 'clz_home_shop_url' ) ) {
	function clz_home_shop_url() {
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$url = wc_get_page_permalink( 'shop' );
			if ( $url ) {
				return $url;
			}
		}

		return home_url( '/shop/' );
	}
}

if ( ! function_exists( 'clz_home_blog_url' ) ) {
	function clz_home_blog_url() {
		$page_id = (int) get_option( 'page_for_posts' );

		return $page_id ? get_permalink( $page_id ) : home_url( '/blog/' );
	}
}

if ( ! function_exists( 'clz_home_placeholder' ) ) {
	/**
	 * Lightweight inline SVG fallback.
	 */
	function clz_home_placeholder( $label = 'CLOZE' ) {
		$label = wp_html_excerpt( wp_strip_all_tags( (string) $label ), 24, '' );
		$svg   = '<svg xmlns="http://www.w3.org/2000/svg" width="900" height="720" viewBox="0 0 900 720"><defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#f8fbfc"/><stop offset="1" stop-color="#e4f2f7"/></linearGradient></defs><rect width="900" height="720" rx="42" fill="url(#g)"/><circle cx="450" cy="298" r="105" fill="#fff" stroke="#d7e8ee" stroke-width="3"/><path d="M370 298c44-77 116-77 160 0-44 77-116 77-160 0Z" fill="#dcecf2"/><text x="450" y="515" text-anchor="middle" font-family="Arial" font-size="32" fill="#6c818b">' . esc_html( $label ) . '</text></svg>';

		return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode( $svg );
	}
}

if ( ! function_exists( 'clz_home_query_product_ids' ) ) {
	/**
	 * Query WooCommerce through its CRUD/query API, never through REST or raw SQL.
	 */
	function clz_home_query_product_ids( $args = array() ) {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$defaults = array(
			'status'       => 'publish',
			'visibility'   => 'visible',
			'stock_status' => 'instock',
			'return'       => 'ids',
			'limit'        => 60,
			'orderby'      => 'date',
			'order'        => 'DESC',
		);

		try {
			$ids = wc_get_products( wp_parse_args( $args, $defaults ) );
		} catch ( Throwable $e ) {
			return array();
		}

		return array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
	}
}

if ( ! function_exists( 'clz_home_products' ) ) {
	/**
	 * Hydrate valid visible products from IDs.
	 *
	 * @return WC_Product[]
	 */
	function clz_home_products( $ids ) {
		$products = array();
		$seen     = array();

		if ( ! function_exists( 'wc_get_product' ) ) {
			return $products;
		}

		foreach ( (array) $ids as $id ) {
			$product = wc_get_product( $id );

			if ( ! $product ) {
				continue;
			}

			if ( $product->is_type( 'variation' ) ) {
				$product = wc_get_product( $product->get_parent_id() );
			}

			if (
				! $product ||
				isset( $seen[ $product->get_id() ] ) ||
				! $product->is_visible() ||
				! $product->is_in_stock()
			) {
				continue;
			}

			$seen[ $product->get_id() ] = true;
			$products[]                 = $product;
		}

		return $products;
	}
}

if ( ! function_exists( 'clz_home_pick_ids' ) ) {
	/**
	 * Keep the first ranked products and rotate the rest deterministically each week.
	 */
	function clz_home_pick_ids( $pool, $count, &$used, $label, $head_count = 3 ) {
		$pool = array_values(
			array_filter(
				array_unique( array_map( 'absint', (array) $pool ) ),
				static function ( $id ) use ( $used ) {
					return $id && empty( $used[ $id ] );
				}
			)
		);

		if ( ! $pool || $count < 1 ) {
			return array();
		}

		$selected   = array_slice( $pool, 0, min( $head_count, $count ) );
		$candidates = array_slice( $pool, $head_count, max( 30, $count * 4 ) );
		$week       = wp_date( 'o-W' );

		$ranked = array();
		foreach ( $candidates as $index => $id ) {
			$random   = (float) sprintf( '%u', crc32( $label . '-' . $week . '-' . $id ) ) / 4294967295;
			$ranked[] = array(
				'id'    => $id,
				'score' => ( 0.72 / ( $index + 2 ) ) + ( 0.28 * $random ),
			);
		}

		usort(
			$ranked,
			static function ( $a, $b ) {
				return $b['score'] <=> $a['score'];
			}
		);

		foreach ( $ranked as $item ) {
			if ( count( $selected ) >= $count ) {
				break;
			}
			$selected[] = $item['id'];
		}

		foreach ( $pool as $id ) {
			if ( count( $selected ) >= $count ) {
				break;
			}
			if ( ! in_array( $id, $selected, true ) ) {
				$selected[] = $id;
			}
		}

		$selected = array_slice( array_values( array_unique( $selected ) ), 0, $count );

		foreach ( $selected as $id ) {
			$used[ $id ] = true;
		}

		return $selected;
	}
}

if ( ! function_exists( 'clz_home_term_ids' ) ) {
	function clz_home_term_ids( $limit = 18 ) {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return array();
		}

		$exclude = array_filter( array( (int) get_option( 'default_product_cat' ) ) );
		$terms   = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'number'     => max( 1, (int) $limit ),
				'orderby'    => 'count',
				'order'      => 'DESC',
				'exclude'    => $exclude,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$blocked = array( 'uncategorized', 'uncategorised' );
		$ids     = array();

		foreach ( $terms as $term ) {
			if ( in_array( $term->slug, $blocked, true ) ) {
				continue;
			}
			$ids[] = (int) $term->term_id;
		}

		return $ids;
	}
}

if ( ! function_exists( 'clz_home_post_ids' ) ) {
	function clz_home_post_ids( $count, $orderby = 'date', $exclude = array() ) {
		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => max( 1, (int) $count ),
				'post__not_in'        => array_map( 'absint', (array) $exclude ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'fields'              => 'ids',
				'orderby'             => 'comment_count' === $orderby
					? array( 'comment_count' => 'DESC', 'date' => 'DESC' )
					: 'date',
				'order'               => 'DESC',
			)
		);

		return array_map( 'absint', $query->posts );
	}
}

if ( ! function_exists( 'clz_home_dataset' ) ) {
	/**
	 * Build and briefly cache only IDs. Prices, stock and markup remain server-rendered.
	 */
	function clz_home_dataset() {
		$key  = 'clz_home_ids_v5_' . wp_date( 'o-W' );
		$data = get_transient( $key );

		if ( is_array( $data ) ) {
			return $data;
		}

		$data = array(
			'hero'               => array(),
			'latest'             => array(),
			'sale'               => array(),
			'popular'            => array(),
			'weekly'             => array(),
			'daily'              => array(),
			'categories'         => array(),
			'popular_categories' => array(),
			'popular_posts'      => array(),
			'latest_posts'       => array(),
		);

		$data['categories']         = clz_home_term_ids( 18 );
		$data['popular_categories'] = array_slice( $data['categories'], 0, 4 );
		$data['popular_posts']      = clz_home_post_ids( 5, 'comment_count' );
		$data['latest_posts']       = clz_home_post_ids( 8, 'date', $data['popular_posts'] );

		if ( ! function_exists( 'wc_get_products' ) ) {
			set_transient( $key, $data, 5 * MINUTE_IN_SECONDS );
			return $data;
		}

		$latest_pool  = clz_home_query_product_ids( array( 'limit' => 70, 'orderby' => 'date' ) );
		$catalog_pool = clz_home_query_product_ids( array( 'limit' => 120, 'orderby' => 'modified' ) );
		$featured     = clz_home_query_product_ids( array( 'limit' => 50, 'featured' => true ) );
		$sale_ids     = function_exists( 'wc_get_product_ids_on_sale' ) ? wc_get_product_ids_on_sale() : array();
		$sale_pool    = $sale_ids
			? clz_home_query_product_ids(
				array(
					'limit'   => 70,
					'include' => array_slice( array_values( array_unique( array_map( 'absint', $sale_ids ) ) ), 0, 300 ),
					'orderby' => 'date',
				)
			)
			: array();

		$popular_products = clz_home_products( $catalog_pool );
		usort(
			$popular_products,
			static function ( $a, $b ) {
				$sales = $b->get_total_sales() <=> $a->get_total_sales();
				if ( 0 !== $sales ) {
					return $sales;
				}

				$reviews = $b->get_review_count() <=> $a->get_review_count();
				if ( 0 !== $reviews ) {
					return $reviews;
				}

				return (float) $b->get_average_rating() <=> (float) $a->get_average_rating();
			}
		);
		$popular_pool = array_map(
			static function ( $product ) {
				return $product->get_id();
			},
			$popular_products
		);

		$used            = array();
		$data['latest']  = clz_home_pick_ids( array_merge( $latest_pool, $catalog_pool ), 10, $used, 'latest', 4 );
		$data['sale']    = clz_home_pick_ids( $sale_pool, 10, $used, 'sale', 3 );
		$data['popular'] = clz_home_pick_ids( array_merge( $popular_pool, $catalog_pool ), 7, $used, 'popular', 4 );
		$data['daily']   = clz_home_pick_ids( array_merge( $featured, $popular_pool, $latest_pool ), 8, $used, 'daily', 3 );
		$data['weekly']  = array_slice( array_values( array_unique( $popular_pool ) ), 0, 10 );
		$data['hero']    = array_slice(
			array_values( array_unique( array_merge( $data['latest'], $data['popular'], $data['sale'] ) ) ),
			0,
			3
		);

		set_transient( $key, $data, 5 * MINUTE_IN_SECONDS );

		return $data;
	}
}

if ( ! function_exists( 'clz_home_product_image' ) ) {
	function clz_home_product_image( $product, $size = 'woocommerce_thumbnail', $priority = false, $class = '' ) {
		$attrs = array(
			'class'    => $class,
			'alt'      => $product->get_name(),
			'decoding' => 'async',
			'loading'  => $priority ? 'eager' : 'lazy',
			'sizes'    => '(max-width:680px) 76vw, 320px',
		);

		if ( $priority ) {
			$attrs['fetchpriority'] = 'high';
		}

		$image_id = $product->get_image_id();
		if ( $image_id ) {
			return wp_get_attachment_image( $image_id, $size, false, $attrs );
		}

		$src = function_exists( 'wc_placeholder_img_src' )
			? wc_placeholder_img_src( $size )
			: clz_home_placeholder( $product->get_name() );

		return sprintf(
			'<img class="%1$s" src="%2$s" alt="%3$s" loading="%4$s" decoding="async">',
			esc_attr( $class ),
			esc_url( $src ),
			esc_attr( $product->get_name() ),
			$priority ? 'eager' : 'lazy'
		);
	}
}

if ( ! function_exists( 'clz_home_term_image' ) ) {
	function clz_home_term_image( $term, $size = 'woocommerce_thumbnail', $class = '' ) {
		$image_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

		if ( $image_id ) {
			return wp_get_attachment_image(
				$image_id,
				$size,
				false,
				array(
					'class'    => $class,
					'alt'      => $term->name,
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '(max-width:680px) 42vw, 220px',
				)
			);
		}

		return sprintf(
			'<img class="%1$s" src="%2$s" alt="%3$s" loading="lazy" decoding="async">',
			esc_attr( $class ),
			esc_url( clz_home_placeholder( $term->name ) ),
			esc_attr( $term->name )
		);
	}
}

if ( ! function_exists( 'clz_home_term_image_url' ) ) {
	function clz_home_term_image_url( $term, $size = 'large' ) {
		$image_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		$url      = $image_id ? wp_get_attachment_image_url( $image_id, $size ) : '';

		return $url ?: clz_home_placeholder( $term->name );
	}
}

if ( ! function_exists( 'clz_home_discount_percent' ) ) {
	function clz_home_discount_percent( $product ) {
		if ( ! $product->is_on_sale() ) {
			return 0;
		}

		if ( $product->is_type( 'variable' ) ) {
			$regular = (float) $product->get_variation_regular_price( 'min', true );
			$sale    = (float) $product->get_variation_sale_price( 'min', true );
		} else {
			$regular = (float) $product->get_regular_price();
			$sale    = (float) $product->get_sale_price();
		}

		return $regular > 0 && $sale >= 0 && $sale < $regular
			? max( 1, (int) round( ( 1 - ( $sale / $regular ) ) * 100 ) )
			: 0;
	}
}

if ( ! function_exists( 'clz_home_product_card' ) ) {
	function clz_home_product_card( $product, $badge = '' ) {
		if ( ! $product ) {
			return;
		}

		$url      = $product->get_permalink();
		$discount = clz_home_discount_percent( $product );
		$badge    = $badge ?: ( $discount ? sprintf( '٪%s', number_format_i18n( $discount ) ) : '' );
		$rating   = (float) $product->get_average_rating();
		$reviews  = (int) $product->get_review_count();
		$price    = $product->get_price_html();
		?>
		<article class="clz-product-card">
			<div class="clz-product-media">
				<a href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
					<?php echo clz_home_product_image( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<?php if ( $badge ) : ?>
					<span class="clz-badge<?php echo $discount ? ' clz-badge-sale' : ''; ?>"><?php echo esc_html( $badge ); ?></span>
				<?php endif; ?>
				<button class="clz-wish js-clz-wish" type="button" data-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="افزودن به علاقه‌مندی‌ها">♡</button>
			</div>
			<div class="clz-product-body">
				<h3 class="clz-product-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
				<div class="clz-rating">
					<span><?php echo $rating ? '★ ' . esc_html( number_format_i18n( $rating, 1 ) ) : 'بدون امتیاز'; ?></span>
					<span><?php echo esc_html( number_format_i18n( $reviews ) ); ?> نظر</span>
				</div>
				<div class="clz-price-row">
					<div class="clz-price"><?php echo $price ? wp_kses_post( $price ) : 'تماس بگیرید'; ?></div>
					<a class="clz-view-dot" href="<?php echo esc_url( $url ); ?>" aria-label="مشاهده محصول">←</a>
				</div>
			</div>
		</article>
		<?php
	}
}

if ( ! function_exists( 'clz_home_category_card' ) ) {
	function clz_home_category_card( $term ) {
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			return;
		}
		?>
		<a class="clz-category" href="<?php echo esc_url( $link ); ?>">
			<span class="clz-category-media"><?php echo clz_home_term_image( $term ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<strong><?php echo esc_html( $term->name ); ?></strong>
			<small><?php echo esc_html( number_format_i18n( $term->count ) ); ?> محصول</small>
		</a>
		<?php
	}
}

if ( ! function_exists( 'clz_home_rank_item' ) ) {
	function clz_home_rank_item( $product, $index ) {
		?>
		<a class="clz-rank-item" href="<?php echo esc_url( $product->get_permalink() ); ?>">
			<span class="clz-rank-no"><?php echo esc_html( number_format_i18n( $index + 1 ) ); ?></span>
			<span class="clz-rank-img"><?php echo clz_home_product_image( $product, 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="clz-rank-info">
				<strong><?php echo esc_html( $product->get_name() ); ?></strong>
				<small><?php echo esc_html( number_format_i18n( (float) $product->get_average_rating(), 1 ) ); ?> از ۵</small>
			</span>
			<span class="clz-rank-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</a>
		<?php
	}
}

if ( ! function_exists( 'clz_home_post_image' ) ) {
	function clz_home_post_image( $post_id, $size, $class = '', $priority = false ) {
		if ( has_post_thumbnail( $post_id ) ) {
			return get_the_post_thumbnail(
				$post_id,
				$size,
				array(
					'class'         => $class,
					'loading'       => $priority ? 'eager' : 'lazy',
					'decoding'      => 'async',
					'fetchpriority' => $priority ? 'high' : 'auto',
				)
			);
		}

		return sprintf(
			'<img class="%1$s" src="%2$s" alt="%3$s" loading="%4$s" decoding="async">',
			esc_attr( $class ),
			esc_url( clz_home_placeholder( get_the_title( $post_id ) ) ),
			esc_attr( get_the_title( $post_id ) ),
			$priority ? 'eager' : 'lazy'
		);
	}
}

if ( ! function_exists( 'clz_home_excerpt' ) ) {
	function clz_home_excerpt( $post_id, $words = 20 ) {
		$text = get_the_excerpt( $post_id );
		if ( ! $text ) {
			$text = get_post_field( 'post_content', $post_id );
		}

		return wp_trim_words( wp_strip_all_tags( $text ), $words, '…' );
	}
}

/*
 * Keep the stylesheet in this file while still printing it inside <head>.
 */
$clz_home_css = <<<'CLZCSS'
/**
 * CLOZE homepage — server-rendered WordPress/WooCommerce layout.
 */

#cloze-home-v2{
	--clz-bg:#fff;
	--clz-ink:#102a36;
	--clz-muted:#687b85;
	--clz-line:#e4edf1;
	--clz-blue:#1383ad;
	--clz-blue-dark:#0d607f;
	--clz-ice:#edf8fc;
	--clz-lilac:#f1eef9;
	--clz-mint:#edf8f3;
	--clz-rose:#fbf0f3;
	--clz-shadow:0 24px 70px rgba(15,55,72,.10);
	--clz-shadow-soft:0 12px 34px rgba(15,55,72,.08);
	--clz-ease:cubic-bezier(.2,.8,.2,1);
	--clz-max:1420px;
	position:relative;
	direction:rtl;
	isolation:isolate;
	overflow:hidden;
	color:var(--clz-ink);
	background:radial-gradient(circle at 94% 2%,rgba(207,238,249,.58),transparent 19rem),var(--clz-bg);
	font-family:inherit,Tahoma,Arial,sans-serif
}
#cloze-home-v2 *{box-sizing:border-box}
#cloze-home-v2 a{color:inherit;text-decoration:none}
#cloze-home-v2 img{display:block;max-width:100%}
#cloze-home-v2 button{font:inherit}
#cloze-home-v2 .clz-container{width:min(var(--clz-max),calc(100% - 48px));margin-inline:auto}
#cloze-home-v2 .clz-section{position:relative;padding:78px 0}
#cloze-home-v2 .clz-soft-band{background:linear-gradient(180deg,#f6fbfd,#edf7fb)}
#cloze-home-v2 .clz-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:28px;margin-bottom:30px}
#cloze-home-v2 .clz-heading h2{margin:0;font-size:clamp(28px,3.3vw,46px);line-height:1.25;letter-spacing:-1.7px}
#cloze-home-v2 .clz-heading p{max-width:650px;margin:8px 0 0;color:var(--clz-muted);font-size:13px;line-height:1.9}
#cloze-home-v2 .clz-link{display:inline-flex;align-items:center;gap:8px;color:var(--clz-blue-dark);font-size:13px;font-weight:900;white-space:nowrap;transition:.2s}
#cloze-home-v2 .clz-link:hover{gap:12px}
#cloze-home-v2 .clz-eyebrow{display:inline-flex;width:max-content;align-items:center;gap:7px;padding:8px 13px;border:1px solid rgba(19,131,173,.1);border-radius:999px;background:rgba(237,248,252,.82);color:var(--clz-blue-dark);font-size:11px;font-weight:900;backdrop-filter:blur(8px)}
#cloze-home-v2 .clz-btn{min-height:54px;padding:0 23px;border:1px solid transparent;border-radius:17px;display:inline-flex;align-items:center;justify-content:center;gap:9px;font-size:13px;font-weight:900;transition:.22s var(--clz-ease)}
#cloze-home-v2 .clz-btn:hover{transform:translateY(-2px)}
#cloze-home-v2 .clz-btn-primary{color:#fff;background:linear-gradient(135deg,var(--clz-ink),#1b4658);box-shadow:0 15px 34px rgba(16,42,54,.2)}
#cloze-home-v2 .clz-btn-light{border-color:var(--clz-line);background:rgba(255,255,255,.74);backdrop-filter:blur(10px)}

/* Hero */
#cloze-home-v2 .clz-hero{position:relative;min-height:720px;padding:44px 0 82px;display:flex;align-items:center}
#cloze-home-v2 .clz-hero:before,#cloze-home-v2 .clz-hero:after{content:"";position:absolute;border-radius:50%;pointer-events:none;z-index:-1}
#cloze-home-v2 .clz-hero:before{width:590px;height:590px;right:-260px;top:-120px;background:radial-gradient(circle,rgba(195,235,249,.68),transparent 68%)}
#cloze-home-v2 .clz-hero:after{width:480px;height:480px;left:-230px;bottom:-170px;background:radial-gradient(circle,rgba(236,229,251,.64),transparent 70%)}
#cloze-home-v2 .clz-hero-grid{display:grid;grid-template-columns:minmax(0,.95fr) minmax(470px,1.05fr);gap:72px;align-items:center}
#cloze-home-v2 .clz-hero-copy{padding:28px 0;position:relative;z-index:4}
#cloze-home-v2 .clz-hero h1{max-width:760px;margin:20px 0 24px;font-size:clamp(52px,5.7vw,84px);line-height:1.08;letter-spacing:-4.1px}
#cloze-home-v2 .clz-hero h1 span{display:block;color:var(--clz-blue)}
#cloze-home-v2 .clz-hero-copy>p{max-width:650px;margin:0;color:#617680;font-size:17px;line-height:2.05}
#cloze-home-v2 .clz-hero-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:31px}
#cloze-home-v2 .clz-hero-assurances{display:flex;flex-wrap:wrap;gap:12px 22px;margin-top:27px;color:#536a75;font-size:11px;font-weight:800}
#cloze-home-v2 .clz-hero-assurances span{display:inline-flex;align-items:center;gap:7px}
#cloze-home-v2 .clz-hero-assurances i{width:21px;height:21px;border-radius:50%;display:grid;place-items:center;background:#e8f6f2;color:#228269;font-style:normal;font-size:10px}
#cloze-home-v2 .clz-hero-showcase{height:590px;position:relative;isolation:isolate}
#cloze-home-v2 .clz-hero-showcase:before{content:"";position:absolute;inset:46px 18px 34px 58px;border-radius:52px;background:linear-gradient(145deg,#eef9fd,#e2f3fa 55%,#f5effb);box-shadow:inset 0 0 0 1px rgba(255,255,255,.75),0 34px 90px rgba(25,91,116,.13);z-index:-2}
#cloze-home-v2 .clz-hero-glow{position:absolute;width:300px;height:300px;right:110px;top:110px;border-radius:50%;background:rgba(255,255,255,.82);filter:blur(8px);z-index:-1}
#cloze-home-v2 .clz-hero-word{position:absolute;left:26px;top:52px;writing-mode:vertical-rl;transform:rotate(180deg);font-size:68px;line-height:1;font-weight:950;letter-spacing:8px;color:rgba(16,42,54,.055);user-select:none}
#cloze-home-v2 .clz-hero-main-product{position:absolute;right:52px;top:18px;width:min(68%,430px);height:520px;overflow:hidden;border:1px solid rgba(225,237,242,.94);border-radius:38px;background:#fff;box-shadow:0 30px 70px rgba(25,76,98,.16);transition:.32s var(--clz-ease);z-index:2}
#cloze-home-v2 a.clz-hero-main-product:hover{transform:translateY(-7px) rotate(-.3deg)}
#cloze-home-v2 .clz-hero-product-label{position:absolute;right:17px;top:17px;z-index:3;padding:8px 12px;border-radius:999px;background:rgba(16,42,54,.88);color:#fff;font-size:10px;font-weight:900;backdrop-filter:blur(10px)}
#cloze-home-v2 .clz-hero-main-media{height:385px;overflow:hidden;background:linear-gradient(150deg,#f7fbfc,#eaf6fa)}
#cloze-home-v2 .clz-hero-main-media img{width:100%;height:100%;object-fit:cover;transition:.5s var(--clz-ease)}
#cloze-home-v2 a.clz-hero-main-product:hover img{transform:scale(1.035)}
#cloze-home-v2 .clz-hero-product-meta{padding:20px 22px;display:grid;gap:5px}
#cloze-home-v2 .clz-hero-product-meta small{color:var(--clz-blue);font-size:10px;font-weight:900}
#cloze-home-v2 .clz-hero-product-meta strong{font-size:15px;line-height:1.75;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#cloze-home-v2 .clz-hero-product-meta span{color:var(--clz-muted);font-size:10px;font-weight:800}
#cloze-home-v2 .clz-hero-mini-product{position:absolute;width:176px;padding:10px;border:1px solid rgba(225,237,242,.95);border-radius:24px;background:rgba(255,255,255,.93);box-shadow:0 18px 45px rgba(25,76,98,.13);backdrop-filter:blur(14px);transition:.28s var(--clz-ease);z-index:3}
#cloze-home-v2 .clz-hero-mini-product:hover{transform:translateY(-5px)}
#cloze-home-v2 .clz-hero-mini-product img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:17px;background:var(--clz-ice)}
#cloze-home-v2 .clz-hero-mini-product span{display:block;padding:10px 4px 3px;font-size:10px;line-height:1.6;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#cloze-home-v2 .clz-hero-mini-one{left:0;top:104px;transform:rotate(-2deg)}
#cloze-home-v2 .clz-hero-mini-one:hover{transform:translateY(-5px) rotate(-2deg)}
#cloze-home-v2 .clz-hero-mini-two{left:20px;bottom:20px;transform:rotate(2deg)}
#cloze-home-v2 .clz-hero-mini-two:hover{transform:translateY(-5px) rotate(2deg)}
#cloze-home-v2 .clz-hero-note{position:absolute;right:20px;bottom:2px;z-index:4;display:flex;align-items:center;gap:9px;padding:11px 15px;border-radius:15px;background:var(--clz-ink);color:#fff;font-size:10px;font-weight:800;box-shadow:0 13px 30px rgba(16,42,54,.18)}
#cloze-home-v2 .clz-hero-note b{color:#9ed8eb;font-size:17px}

/* Categories */
#cloze-home-v2 .clz-hscroll{display:flex;gap:14px;overflow-x:auto;scroll-snap-type:x proximity;overscroll-behavior-inline:contain;padding:5px 2px 17px;scrollbar-width:none}
#cloze-home-v2 .clz-hscroll::-webkit-scrollbar,#cloze-home-v2 .clz-product-shelf::-webkit-scrollbar{display:none}
#cloze-home-v2 .clz-category{flex:0 0 154px;padding:13px;border:1px solid var(--clz-line);border-radius:24px;background:#fff;text-align:center;scroll-snap-align:start;transition:.23s var(--clz-ease)}
#cloze-home-v2 .clz-category:hover{transform:translateY(-5px);border-color:#d8e8ef;box-shadow:var(--clz-shadow-soft)}
#cloze-home-v2 .clz-category-media{height:112px;overflow:hidden;border-radius:18px;background:var(--clz-ice);display:grid;place-items:center}
#cloze-home-v2 .clz-category-media img{width:100%;height:100%;object-fit:cover}
#cloze-home-v2 .clz-category strong{display:block;margin:13px 0 5px;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#cloze-home-v2 .clz-category small{color:var(--clz-muted);font-size:10px}

/* Products */
#cloze-home-v2 .clz-products-latest{background:linear-gradient(180deg,#fff,#fbfdfe)}
#cloze-home-v2 .clz-product-shelf{display:flex;gap:18px;overflow-x:auto;scroll-snap-type:x proximity;overscroll-behavior-inline:contain;padding:4px 2px 24px;scrollbar-width:none}
#cloze-home-v2 .clz-product-card{flex:0 0 calc((100% - 72px)/5);min-width:220px;position:relative;overflow:hidden;border:1px solid #e3ecef;border-radius:26px;background:#fff;box-shadow:0 5px 18px rgba(15,55,72,.025);scroll-snap-align:start;transition:.24s var(--clz-ease)}
#cloze-home-v2 .clz-product-card:hover{transform:translateY(-5px);border-color:#dce9ef;box-shadow:var(--clz-shadow)}
#cloze-home-v2 .clz-product-media{position:relative;overflow:hidden;aspect-ratio:1;background:linear-gradient(145deg,#f7fbfc,#eaf6fa)}
#cloze-home-v2 .clz-product-media>a{display:block;width:100%;height:100%}
#cloze-home-v2 .clz-product-media img{width:100%;height:100%;object-fit:cover;backface-visibility:hidden;transition:.35s var(--clz-ease)}
#cloze-home-v2 .clz-product-card:hover .clz-product-media img{transform:scale(1.035)}
#cloze-home-v2 .clz-badge{position:absolute;right:10px;top:10px;z-index:2;padding:6px 9px;border:1px solid rgba(255,255,255,.72);border-radius:999px;background:rgba(255,255,255,.9);font-size:9px;font-weight:900;backdrop-filter:blur(8px)}
#cloze-home-v2 .clz-badge-sale{background:#fff0f2;color:#a64d5a}
#cloze-home-v2 .clz-wish{position:absolute;left:10px;top:10px;z-index:3;width:34px;height:34px;padding:0;border:1px solid rgba(255,255,255,.9);border-radius:12px;background:rgba(255,255,255,.86);display:grid;place-items:center;color:#667b86;font-size:17px;cursor:pointer}
#cloze-home-v2 .clz-wish.active{background:#fff0f2;color:#ad5260}
#cloze-home-v2 .clz-product-body{padding:16px 16px 18px}
#cloze-home-v2 .clz-product-title{height:46px;margin:0 0 9px;overflow:hidden;font-size:13px;line-height:1.75}
#cloze-home-v2 .clz-rating{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:9px;color:var(--clz-muted);font-size:10px}
#cloze-home-v2 .clz-rating span:first-child{color:#b8842c;font-weight:900}
#cloze-home-v2 .clz-price-row{min-height:44px;display:flex;align-items:flex-end;justify-content:space-between;gap:9px}
#cloze-home-v2 .clz-price{font-size:14px;font-weight:900}
#cloze-home-v2 .clz-price del{display:block;margin-bottom:3px;color:#a6b1b7;font-size:10px;font-weight:600}
#cloze-home-v2 .clz-price ins{text-decoration:none}
#cloze-home-v2 .clz-view-dot{width:30px;height:30px;flex:none;border-radius:11px;background:var(--clz-ice);display:grid;place-items:center;color:var(--clz-blue-dark);font-weight:900;transition:.2s}
#cloze-home-v2 .clz-product-card:hover .clz-view-dot{background:var(--clz-ink);color:#fff}
#cloze-home-v2 .clz-product-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
#cloze-home-v2 .clz-product-grid .clz-product-card{min-width:0;flex:none}
#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child{grid-column:span 2;display:grid;grid-template-columns:1.08fr .92fr;min-height:390px}
#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child .clz-product-media{min-height:390px;aspect-ratio:auto}
#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child .clz-product-body{padding:28px;display:flex;flex-direction:column;justify-content:flex-end}
#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child .clz-product-title{height:auto;max-height:76px;font-size:20px}
#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child .clz-price{font-size:18px}

/* Sale */
#cloze-home-v2 .clz-sale-section{padding-top:24px}
#cloze-home-v2 .clz-sale-wrap{position:relative;overflow:hidden;padding:38px;border-radius:42px;background:radial-gradient(circle at 12% 0%,rgba(95,190,225,.22),transparent 22rem),linear-gradient(145deg,#102b37,#173e4e);box-shadow:0 32px 80px rgba(16,42,54,.15)}
#cloze-home-v2 .clz-sale-wrap:after{content:"";position:absolute;width:420px;height:420px;left:-170px;top:-220px;border-radius:50%;background:rgba(255,255,255,.035)}
#cloze-home-v2 .clz-sale-top{position:relative;z-index:1;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between;gap:22px}
#cloze-home-v2 .clz-sale-copy h2{margin:8px 0;font-size:clamp(28px,3.5vw,45px);letter-spacing:-1.6px;color:#fff}
#cloze-home-v2 .clz-sale-copy p{margin:0;color:rgba(255,255,255,.62);font-size:13px}
#cloze-home-v2 .clz-sale-wrap .clz-eyebrow{border-color:rgba(255,255,255,.1);background:rgba(255,255,255,.1);color:#b9e6f4}
#cloze-home-v2 .clz-countdown{display:flex;gap:7px;direction:ltr}
#cloze-home-v2 .clz-time{width:64px;height:64px;border:1px solid rgba(255,255,255,.1);border-radius:18px;background:rgba(255,255,255,.1);display:grid;place-items:center;align-content:center;color:#fff;backdrop-filter:blur(12px)}
#cloze-home-v2 .clz-time strong{font-size:18px}
#cloze-home-v2 .clz-time span{margin-top:3px;color:rgba(255,255,255,.55);font-size:8px}
#cloze-home-v2 .clz-sale-wrap .clz-product-card{border:0;box-shadow:0 15px 38px rgba(3,17,23,.16)}

/* Why */
#cloze-home-v2 .clz-why-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
#cloze-home-v2 .clz-why{padding:24px;border:1px solid rgba(221,235,240,.9);border-radius:25px;background:rgba(255,255,255,.78);backdrop-filter:blur(8px);transition:.22s var(--clz-ease)}
#cloze-home-v2 .clz-why:hover{transform:translateY(-4px);box-shadow:var(--clz-shadow-soft)}
#cloze-home-v2 .clz-why-icon{width:47px;height:47px;margin-bottom:18px;border-radius:16px;display:grid;place-items:center;font-size:20px}
#cloze-home-v2 .clz-why:nth-child(1) .clz-why-icon{background:var(--clz-ice)}
#cloze-home-v2 .clz-why:nth-child(2) .clz-why-icon{background:var(--clz-mint)}
#cloze-home-v2 .clz-why:nth-child(3) .clz-why-icon{background:var(--clz-lilac)}
#cloze-home-v2 .clz-why:nth-child(4) .clz-why-icon{background:var(--clz-rose)}
#cloze-home-v2 .clz-why h3{margin:0 0 9px;font-size:15px}
#cloze-home-v2 .clz-why p{margin:0;color:var(--clz-muted);font-size:12px;line-height:1.9}

/* Bento */
#cloze-home-v2 .clz-bento{display:grid;grid-template-columns:1.2fr .8fr .8fr;grid-template-rows:220px 190px;gap:18px}
#cloze-home-v2 .clz-bento-card{position:relative;overflow:hidden;isolation:isolate;padding:24px;border:1px solid rgba(23,45,58,.05);border-radius:32px;display:flex;flex-direction:column;justify-content:flex-end;transition:.24s var(--clz-ease)}
#cloze-home-v2 .clz-bento-card:nth-child(4n+1){background:var(--clz-ice)}
#cloze-home-v2 .clz-bento-card:nth-child(4n+2){background:var(--clz-lilac)}
#cloze-home-v2 .clz-bento-card:nth-child(4n+3){background:var(--clz-mint)}
#cloze-home-v2 .clz-bento-card:nth-child(4n+4){background:var(--clz-rose)}
#cloze-home-v2 .clz-bento-card:first-child{grid-row:span 2}
#cloze-home-v2 .clz-bento-card:nth-child(4){grid-column:span 2}
#cloze-home-v2 .clz-bento-card:hover{transform:translateY(-4px)}
#cloze-home-v2 .clz-bento-card:after{content:"";position:absolute;width:260px;height:260px;left:-42px;top:-48px;border-radius:50%;background:var(--cat-img) center/cover no-repeat;opacity:.34;filter:saturate(.75);z-index:-1}
#cloze-home-v2 .clz-bento-card h3{margin:0 0 6px;font-size:clamp(20px,2.2vw,30px)}
#cloze-home-v2 .clz-bento-card p{margin:0;color:var(--clz-muted);font-size:11px}

/* Ranking */
#cloze-home-v2 .clz-ranking-shell{display:grid;grid-template-columns:340px 1fr;gap:30px;align-items:start}
#cloze-home-v2 .clz-ranking-intro{position:sticky;top:24px;overflow:hidden;padding:34px;border-radius:36px;background:linear-gradient(155deg,#102a36,#1c4a5d);color:#fff}
#cloze-home-v2 .clz-ranking-intro:after{content:"10";position:absolute;left:-8px;bottom:-48px;font-size:170px;line-height:1;font-weight:950;color:rgba(255,255,255,.055)}
#cloze-home-v2 .clz-ranking-intro h2{position:relative;z-index:1;margin:14px 0 12px;font-size:35px;line-height:1.25;letter-spacing:-1.5px}
#cloze-home-v2 .clz-ranking-intro p{position:relative;z-index:1;margin:0;color:rgba(255,255,255,.65);font-size:12px;line-height:2}
#cloze-home-v2 .clz-ranking-list{display:grid;grid-template-columns:1fr 1fr;gap:10px}
#cloze-home-v2 .clz-rank-item{display:grid;grid-template-columns:50px 72px minmax(0,1fr) auto;align-items:center;gap:12px;padding:12px;border:1px solid var(--clz-line);border-radius:22px;background:#fff;transition:.22s var(--clz-ease)}
#cloze-home-v2 .clz-rank-item:hover{transform:translateX(-4px);box-shadow:var(--clz-shadow-soft)}
#cloze-home-v2 .clz-rank-no{font-size:29px;font-weight:950;color:#c6d6dd;text-align:center}
#cloze-home-v2 .clz-rank-item:nth-child(-n+3) .clz-rank-no{color:var(--clz-blue)}
#cloze-home-v2 .clz-rank-img{width:72px;height:72px;overflow:hidden;border-radius:15px;background:var(--clz-ice)}
#cloze-home-v2 .clz-rank-img img{width:100%;height:100%;object-fit:cover}
#cloze-home-v2 .clz-rank-info{min-width:0;display:grid;gap:5px}
#cloze-home-v2 .clz-rank-info strong{font-size:12px;line-height:1.65;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#cloze-home-v2 .clz-rank-info small{color:var(--clz-muted);font-size:10px}
#cloze-home-v2 .clz-rank-price{font-size:11px;font-weight:900;white-space:nowrap}
#cloze-home-v2 .clz-rank-price del{display:none}
#cloze-home-v2 .clz-rank-price ins{text-decoration:none}

/* Daily and support */
#cloze-home-v2 .clz-daily-grid{display:grid;grid-template-columns:330px minmax(0,1fr);gap:28px;align-items:stretch}
#cloze-home-v2 .clz-daily-note{min-height:360px;padding:32px;border:1px solid #ece8f5;border-radius:36px;background:linear-gradient(155deg,#eeeafb,#fff);display:flex;flex-direction:column;justify-content:space-between}
#cloze-home-v2 .clz-daily-note h2{margin:14px 0 12px;font-size:34px;line-height:1.3;letter-spacing:-1.4px}
#cloze-home-v2 .clz-daily-note p{margin:0;color:var(--clz-muted);font-size:12px;line-height:1.95}
#cloze-home-v2 .clz-tip{margin-top:25px;padding:14px;border-radius:17px;background:rgba(255,255,255,.72);font-size:11px;line-height:1.8}
#cloze-home-v2 .clz-support{position:relative;overflow:hidden;padding:46px;border:1px solid var(--clz-line);border-radius:40px;background:linear-gradient(135deg,#fff,#f5fbfd);box-shadow:var(--clz-shadow-soft);display:grid;grid-template-columns:1fr auto;align-items:center;gap:28px}
#cloze-home-v2 .clz-support:before{content:"";position:absolute;width:330px;height:330px;left:-140px;top:-150px;border-radius:50%;background:var(--clz-ice)}
#cloze-home-v2 .clz-support>div{position:relative;z-index:1}
#cloze-home-v2 .clz-support h2{margin:10px 0 9px;font-size:clamp(27px,3.6vw,47px);line-height:1.3;letter-spacing:-1.6px}
#cloze-home-v2 .clz-support p{max-width:730px;margin:0;color:var(--clz-muted);font-size:13px;line-height:1.9}
#cloze-home-v2 .clz-support-action{min-width:220px;display:grid;gap:9px;text-align:center}
#cloze-home-v2 .clz-support-action small{color:var(--clz-muted);font-size:10px}

/* Articles */
#cloze-home-v2 .clz-popular-articles{display:grid;grid-template-columns:1.25fr .75fr;gap:14px}
#cloze-home-v2 .clz-article-feature{position:relative;min-height:460px;overflow:hidden;border:1px solid var(--clz-line);border-radius:36px;background:var(--clz-ice)}
#cloze-home-v2 .clz-article-feature>img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
#cloze-home-v2 .clz-article-feature:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 22%,rgba(11,29,39,.9))}
#cloze-home-v2 .clz-article-feature-content{position:absolute;inset:auto 0 0;z-index:2;padding:29px;color:#fff}
#cloze-home-v2 .clz-article-feature h3{margin:10px 0;font-size:clamp(23px,3vw,39px);line-height:1.45}
#cloze-home-v2 .clz-article-feature p{max-width:700px;margin:0;color:rgba(255,255,255,.72);font-size:12px;line-height:1.9}
#cloze-home-v2 .clz-article-meta{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.72);font-size:10px}
#cloze-home-v2 .clz-popular-side{display:grid;gap:10px}
#cloze-home-v2 .clz-article-row{display:grid;grid-template-columns:116px minmax(0,1fr);gap:13px;padding:10px;border:1px solid var(--clz-line);border-radius:20px;background:#fff;transition:.22s var(--clz-ease)}
#cloze-home-v2 .clz-article-row:hover{transform:translateX(-4px);box-shadow:var(--clz-shadow-soft)}
#cloze-home-v2 .clz-article-row>img{width:116px;height:92px;object-fit:cover;border-radius:14px;background:var(--clz-ice)}
#cloze-home-v2 .clz-article-row>span{min-width:0;display:grid;align-content:center;gap:5px}
#cloze-home-v2 .clz-article-row small,#cloze-home-v2 .clz-article-row em{color:var(--clz-muted);font-size:9px;font-style:normal}
#cloze-home-v2 .clz-article-row strong{font-size:12.5px;line-height:1.75}
#cloze-home-v2 .clz-latest-articles{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
#cloze-home-v2 .clz-article-card{overflow:hidden;border:1px solid var(--clz-line);border-radius:27px;background:#fff;transition:.22s var(--clz-ease)}
#cloze-home-v2 .clz-article-card:hover{transform:translateY(-5px);box-shadow:var(--clz-shadow)}
#cloze-home-v2 .clz-article-card>a>img{width:100%;aspect-ratio:16/10;object-fit:cover;background:var(--clz-ice)}
#cloze-home-v2 .clz-article-card-body{padding:17px}
#cloze-home-v2 .clz-article-card small{color:var(--clz-muted);font-size:9px}
#cloze-home-v2 .clz-article-card h3{margin:8px 0 10px;font-size:14px;line-height:1.8}
#cloze-home-v2 .clz-article-card p{margin:0;color:var(--clz-muted);font-size:11px;line-height:1.85}

/* FAQ */
#cloze-home-v2 .clz-faq-grid{display:grid;grid-template-columns:350px 1fr;gap:34px;align-items:start}
#cloze-home-v2 .clz-faq-intro{position:sticky;top:24px}
#cloze-home-v2 .clz-faq-intro h2{margin:14px 0 11px;font-size:38px;line-height:1.35;letter-spacing:-1.4px}
#cloze-home-v2 .clz-faq-intro p{color:var(--clz-muted);font-size:12px;line-height:2}
#cloze-home-v2 .clz-faq-list{display:grid;gap:9px}
#cloze-home-v2 .clz-faq-item{overflow:hidden;border:1px solid var(--clz-line);border-radius:21px;background:#fff}
#cloze-home-v2 .clz-faq-q{width:100%;padding:18px 19px;border:0;background:transparent;display:flex;align-items:center;justify-content:space-between;gap:14px;text-align:right;color:var(--clz-ink);font-weight:900;cursor:pointer}
#cloze-home-v2 .clz-faq-plus{width:30px;height:30px;flex:none;border-radius:10px;background:var(--clz-ice);display:grid;place-items:center;color:var(--clz-blue);transition:.2s}
#cloze-home-v2 .clz-faq-a{display:grid;grid-template-rows:0fr;transition:.25s var(--clz-ease)}
#cloze-home-v2 .clz-faq-a>div{overflow:hidden}
#cloze-home-v2 .clz-faq-a p{margin:0;padding:0 19px 19px;color:var(--clz-muted);font-size:12px;line-height:2}
#cloze-home-v2 .clz-faq-item.open .clz-faq-a{grid-template-rows:1fr}
#cloze-home-v2 .clz-faq-item.open .clz-faq-plus{transform:rotate(45deg);background:var(--clz-ink);color:#fff}

/* Utility */
#cloze-home-v2 .clz-message{grid-column:1/-1;width:100%;padding:24px;border:1px dashed #dbe7ec;border-radius:20px;background:#fbfdfe;color:var(--clz-muted);font-size:12px;line-height:1.9;text-align:center}
#cloze-home-v2 .clz-toast{position:fixed;left:20px;bottom:20px;z-index:99999;padding:11px 15px;border-radius:14px;background:var(--clz-ink);color:#fff;font-size:11px;box-shadow:var(--clz-shadow);transform:translateY(30px);opacity:0;pointer-events:none;transition:.25s}
#cloze-home-v2 .clz-toast.show{transform:translateY(0);opacity:1}

@media(max-width:1180px){
	#cloze-home-v2 .clz-hero-grid{grid-template-columns:minmax(0,.9fr) minmax(430px,1.1fr);gap:38px}
	#cloze-home-v2 .clz-hero h1{font-size:clamp(48px,5.6vw,70px)}
	#cloze-home-v2 .clz-product-grid{grid-template-columns:repeat(3,1fr)}
	#cloze-home-v2 .clz-latest-articles{grid-template-columns:repeat(3,1fr)}
	#cloze-home-v2 .clz-ranking-list{grid-template-columns:1fr}
}
@media(max-width:960px){
	#cloze-home-v2 .clz-container{width:min(var(--clz-max),calc(100% - 34px))}
	#cloze-home-v2 .clz-section{padding:62px 0}
	#cloze-home-v2 .clz-hero{min-height:auto;padding:28px 0 65px}
	#cloze-home-v2 .clz-hero-grid{grid-template-columns:1fr;gap:24px}
	#cloze-home-v2 .clz-hero-copy{max-width:760px;padding:20px 0 0}
	#cloze-home-v2 .clz-hero-showcase{width:100%;max-width:650px;height:535px;margin-inline:auto}
	#cloze-home-v2 .clz-hero-main-product{right:70px;width:min(68%,420px);height:470px}
	#cloze-home-v2 .clz-hero-main-media{height:338px}
	#cloze-home-v2 .clz-hero-mini-one{left:10px}
	#cloze-home-v2 .clz-hero-mini-two{left:30px}
	#cloze-home-v2 .clz-product-grid{grid-template-columns:repeat(2,1fr)}
	#cloze-home-v2 .clz-why-grid{grid-template-columns:repeat(2,1fr)}
	#cloze-home-v2 .clz-bento{grid-template-columns:1fr 1fr;grid-template-rows:260px 190px 190px}
	#cloze-home-v2 .clz-bento-card:first-child{grid-column:span 2;grid-row:auto}
	#cloze-home-v2 .clz-bento-card:nth-child(4){grid-column:span 2}
	#cloze-home-v2 .clz-ranking-shell{grid-template-columns:1fr}
	#cloze-home-v2 .clz-ranking-intro,#cloze-home-v2 .clz-faq-intro{position:relative;top:auto}
	#cloze-home-v2 .clz-ranking-list{grid-template-columns:1fr 1fr}
	#cloze-home-v2 .clz-daily-grid,#cloze-home-v2 .clz-popular-articles,#cloze-home-v2 .clz-faq-grid{grid-template-columns:1fr}
	#cloze-home-v2 .clz-daily-note{min-height:auto}
	#cloze-home-v2 .clz-popular-side{grid-template-columns:1fr 1fr}
}
@media(max-width:680px){
	#cloze-home-v2 .clz-container{width:min(100% - 22px,var(--clz-max))}
	#cloze-home-v2 .clz-section{padding:50px 0}
	#cloze-home-v2 .clz-heading{display:grid;gap:12px;margin-bottom:22px}
	#cloze-home-v2 .clz-heading h2{font-size:30px;letter-spacing:-1.1px}
	#cloze-home-v2 .clz-link{justify-self:start}
	#cloze-home-v2 .clz-hero{padding:18px 0 48px}
	#cloze-home-v2 .clz-hero h1{margin:15px 0 17px;font-size:clamp(39px,12.5vw,55px);line-height:1.12;letter-spacing:-2.4px}
	#cloze-home-v2 .clz-hero h1 span{display:inline}
	#cloze-home-v2 .clz-hero-copy>p{font-size:13px;line-height:1.95}
	#cloze-home-v2 .clz-hero-actions{display:grid;grid-template-columns:1fr 1fr;margin-top:22px}
	#cloze-home-v2 .clz-btn{min-height:49px;padding:0 10px;border-radius:15px;font-size:11px}
	#cloze-home-v2 .clz-hero-assurances{display:grid;grid-template-columns:1fr;gap:9px;margin-top:19px;font-size:10px}
	#cloze-home-v2 .clz-hero-showcase{height:405px}
	#cloze-home-v2 .clz-hero-showcase:before{inset:36px 0 18px 16px;border-radius:34px}
	#cloze-home-v2 .clz-hero-word{left:3px;top:52px;font-size:42px}
	#cloze-home-v2 .clz-hero-main-product{right:14px;top:8px;width:70%;height:360px;border-radius:27px}
	#cloze-home-v2 .clz-hero-main-media{height:265px}
	#cloze-home-v2 .clz-hero-product-meta{padding:13px 15px}
	#cloze-home-v2 .clz-hero-product-meta strong{font-size:12px}
	#cloze-home-v2 .clz-hero-product-label{right:11px;top:11px;padding:6px 9px;font-size:8px}
	#cloze-home-v2 .clz-hero-mini-product{width:116px;padding:7px;border-radius:18px}
	#cloze-home-v2 .clz-hero-mini-product img{border-radius:13px}
	#cloze-home-v2 .clz-hero-mini-product span{padding-top:7px;font-size:8px}
	#cloze-home-v2 .clz-hero-mini-one{left:0;top:65px}
	#cloze-home-v2 .clz-hero-mini-two{left:8px;bottom:6px}
	#cloze-home-v2 .clz-hero-note{right:2px;bottom:0;padding:8px 10px;font-size:8px}
	#cloze-home-v2 .clz-category{flex-basis:122px;padding:10px;border-radius:20px}
	#cloze-home-v2 .clz-category-media{height:91px;border-radius:15px}
	#cloze-home-v2 .clz-product-shelf{gap:11px;margin-left:-11px;padding-left:11px}
	#cloze-home-v2 .clz-product-card{flex:0 0 76vw;min-width:0;max-width:282px;border-radius:21px}
	#cloze-home-v2 .clz-product-body{padding:12px}
	#cloze-home-v2 .clz-sale-section{padding-top:10px}
	#cloze-home-v2 .clz-sale-wrap{padding:22px 12px;border-radius:29px}
	#cloze-home-v2 .clz-sale-top{display:grid;align-items:start;gap:16px}
	#cloze-home-v2 .clz-sale-copy h2{font-size:31px}
	#cloze-home-v2 .clz-sale-wrap .clz-product-shelf{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;overflow:visible;margin:0;padding:0}
	#cloze-home-v2 .clz-sale-wrap .clz-product-card{min-width:0;max-width:none;flex:none;border-radius:18px}
	#cloze-home-v2 .clz-sale-wrap .clz-product-title{height:36px;font-size:10.5px}
	#cloze-home-v2 .clz-sale-wrap .clz-rating{display:none}
	#cloze-home-v2 .clz-sale-wrap .clz-price{font-size:11px}
	#cloze-home-v2 .clz-product-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
	#cloze-home-v2 .clz-product-grid .clz-product-card{min-width:0;max-width:none;border-radius:20px}
	#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child{grid-column:1/-1;grid-template-columns:44% 56%;min-height:220px}
	#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child .clz-product-media{min-height:220px}
	#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child .clz-product-body{padding:16px}
	#cloze-home-v2 .clz-popular-grid .clz-product-card:first-child .clz-product-title{font-size:15px}
	#cloze-home-v2 .clz-product-grid .clz-product-card:not(:first-child) .clz-rating{display:none}
	#cloze-home-v2 .clz-product-grid .clz-product-card:not(:first-child) .clz-product-title{height:36px;font-size:10.5px}
	#cloze-home-v2 .clz-why-grid{gap:9px}
	#cloze-home-v2 .clz-why{padding:16px;border-radius:20px}
	#cloze-home-v2 .clz-why-icon{width:40px;height:40px;margin-bottom:13px}
	#cloze-home-v2 .clz-why h3{font-size:12px}
	#cloze-home-v2 .clz-why p{font-size:10px;line-height:1.8}
	#cloze-home-v2 .clz-bento{grid-template-columns:1fr;grid-template-rows:none;gap:10px}
	#cloze-home-v2 .clz-bento-card,#cloze-home-v2 .clz-bento-card:first-child,#cloze-home-v2 .clz-bento-card:nth-child(4){grid-column:auto;min-height:150px;padding:18px;border-radius:23px}
	#cloze-home-v2 .clz-bento-card:nth-child(even){align-items:flex-end;text-align:left}
	#cloze-home-v2 .clz-bento-card:nth-child(even):after{right:-35px;left:auto}
	#cloze-home-v2 .clz-ranking-intro,#cloze-home-v2 .clz-daily-note{padding:23px;border-radius:27px}
	#cloze-home-v2 .clz-ranking-list{grid-template-columns:1fr}
	#cloze-home-v2 .clz-rank-item{grid-template-columns:36px 62px minmax(0,1fr);gap:9px;border-radius:17px}
	#cloze-home-v2 .clz-rank-img{width:62px;height:62px}
	#cloze-home-v2 .clz-rank-price{grid-column:3;justify-self:start}
	#cloze-home-v2 .clz-support{grid-template-columns:1fr;padding:23px;border-radius:26px}
	#cloze-home-v2 .clz-support-action{min-width:0}
	#cloze-home-v2 .clz-article-feature{min-height:370px;border-radius:24px}
	#cloze-home-v2 .clz-article-feature-content{padding:20px}
	#cloze-home-v2 .clz-popular-side{grid-template-columns:1fr}
	#cloze-home-v2 .clz-article-row{grid-template-columns:94px minmax(0,1fr);padding:8px;border-radius:17px}
	#cloze-home-v2 .clz-article-row>img{width:94px;height:82px}
	#cloze-home-v2 .clz-latest-articles{display:flex;gap:10px;overflow-x:auto;margin-left:-11px;padding:3px 2px 15px 11px;scroll-snap-type:x mandatory;scrollbar-width:none}
	#cloze-home-v2 .clz-latest-articles::-webkit-scrollbar{display:none}
	#cloze-home-v2 .clz-article-card{flex:0 0 82vw;max-width:320px;border-radius:21px;scroll-snap-align:start}
	#cloze-home-v2 .clz-faq-intro h2{font-size:30px}
	#cloze-home-v2 .clz-faq-q{padding:16px;font-size:12px}
	#cloze-home-v2 .clz-faq-a p{padding:0 16px 16px;font-size:11px}
}
@media(prefers-reduced-motion:reduce){
	#cloze-home-v2 *,#cloze-home-v2 *:before,#cloze-home-v2 *:after{animation:none!important;scroll-behavior:auto!important;transition-duration:.01ms!important}
}
CLZCSS;

wp_register_style( 'clz-home-inline', false, array(), null );
wp_enqueue_style( 'clz-home-inline' );
wp_add_inline_style( 'clz-home-inline', $clz_home_css );

get_header();

$data               = function_exists( 'clz_home_dataset' ) ? clz_home_dataset() : array();
$shop_url           = function_exists( 'clz_home_shop_url' ) ? clz_home_shop_url() : home_url( '/shop/' );
$blog_url           = function_exists( 'clz_home_blog_url' ) ? clz_home_blog_url() : home_url( '/blog/' );
$support_url        = apply_filters( 'clz_home_support_url', 'https://www.goftino.com/c/h6q2ir' );
$hero_products      = function_exists( 'clz_home_products' ) ? clz_home_products( $data['hero'] ?? array() ) : array();
$latest_products    = function_exists( 'clz_home_products' ) ? clz_home_products( $data['latest'] ?? array() ) : array();
$sale_products      = function_exists( 'clz_home_products' ) ? clz_home_products( $data['sale'] ?? array() ) : array();
$popular_products   = function_exists( 'clz_home_products' ) ? clz_home_products( $data['popular'] ?? array() ) : array();
$weekly_products    = function_exists( 'clz_home_products' ) ? clz_home_products( $data['weekly'] ?? array() ) : array();
$daily_products     = function_exists( 'clz_home_products' ) ? clz_home_products( $data['daily'] ?? array() ) : array();
$categories = array_values(
	array_filter(
		array_map( 'get_term', $data['categories'] ?? array() ),
		static function ( $term ) {
			return $term instanceof WP_Term;
		}
	)
);
$popular_categories = array_values(
	array_filter(
		array_map( 'get_term', $data['popular_categories'] ?? array() ),
		static function ( $term ) {
			return $term instanceof WP_Term;
		}
	)
);
$popular_posts = array_values(
	array_filter(
		array_map( 'get_post', $data['popular_posts'] ?? array() ),
		static function ( $post ) {
			return $post instanceof WP_Post;
		}
	)
);
$latest_posts = array_values(
	array_filter(
		array_map( 'get_post', $data['latest_posts'] ?? array() ),
		static function ( $post ) {
			return $post instanceof WP_Post;
		}
	)
);
$hero_main          = $hero_products[0] ?? null;
$hero_mini_one      = $hero_products[1] ?? $hero_main;
$hero_mini_two      = $hero_products[2] ?? $hero_mini_one;
?>

<main id="primary" class="site-main">
	<div id="cloze-home-v2" dir="rtl">

		<section class="clz-hero" aria-labelledby="clzHeroTitle">
			<div class="clz-container clz-hero-grid">
				<div class="clz-hero-copy">
					<span class="clz-eyebrow">فروشگاه تخصصی پیرسینگ و اکسسوری</span>
					<h1 id="clzHeroTitle">جزئیاتی که <span>استایل تو را امضا می‌کنند.</span></h1>
					<p>از مدل‌های مینیمال روزمره تا انتخاب‌های خاص؛ با مشخصات دقیق، تصاویر واضح و تجربه‌ای سریع برای پیدا کردن اکسسوری مناسب خودت.</p>

					<div class="clz-hero-actions">
						<a class="clz-btn clz-btn-primary" href="#clz-new-products">شروع خرید <span>←</span></a>
						<a class="clz-btn clz-btn-light" href="#clz-categories">مشاهده دسته‌بندی‌ها</a>
					</div>

					<div class="clz-hero-assurances" aria-label="مزیت‌های خرید از کلوز">
						<span><i>✓</i> مشخصات و سایزبندی دقیق</span>
						<span><i>✓</i> انتخاب مناسب پوست حساس</span>
						<span><i>✓</i> بسته‌بندی امن و پشتیبانی</span>
					</div>
				</div>

				<div class="clz-hero-showcase" aria-label="محصولات منتخب فروشگاه">
					<div class="clz-hero-glow" aria-hidden="true"></div>
					<div class="clz-hero-word" aria-hidden="true">CLOZE</div>

					<?php if ( $hero_main ) : ?>
						<a class="clz-hero-main-product" href="<?php echo esc_url( $hero_main->get_permalink() ); ?>">
							<span class="clz-hero-product-label">انتخاب ویژه</span>
							<div class="clz-hero-main-media">
								<?php echo clz_home_product_image( $hero_main, 'woocommerce_single', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
							<div class="clz-hero-product-meta">
								<small>پیشنهاد امروز</small>
								<strong><?php echo esc_html( $hero_main->get_name() ); ?></strong>
								<span>مشاهده محصول ←</span>
							</div>
						</a>
					<?php else : ?>
						<div class="clz-hero-main-product clz-hero-empty">
							<div class="clz-hero-main-media"><img src="<?php echo esc_url( clz_home_placeholder( 'CLOZE' ) ); ?>" alt="کلوز"></div>
							<div class="clz-hero-product-meta"><small>فروشگاه کلوز</small><strong>محصولات منتخب به‌زودی نمایش داده می‌شوند</strong></div>
						</div>
					<?php endif; ?>

					<?php if ( $hero_mini_one ) : ?>
						<a class="clz-hero-mini-product clz-hero-mini-one" href="<?php echo esc_url( $hero_mini_one->get_permalink() ); ?>">
							<?php echo clz_home_product_image( $hero_mini_one, 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $hero_mini_one->get_name() ); ?></span>
						</a>
					<?php endif; ?>

					<?php if ( $hero_mini_two ) : ?>
						<a class="clz-hero-mini-product clz-hero-mini-two" href="<?php echo esc_url( $hero_mini_two->get_permalink() ); ?>">
							<?php echo clz_home_product_image( $hero_mini_two, 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $hero_mini_two->get_name() ); ?></span>
						</a>
					<?php endif; ?>

					<div class="clz-hero-note"><b>✦</b><span>هر جزئیات کوچک، یک تفاوت بزرگ</span></div>
				</div>
			</div>
		</section>

		<section class="clz-section" id="clz-categories">
			<div class="clz-container">
				<header class="clz-heading">
					<div><h2>دسته‌بندی‌ها</h2><p>مدل موردنظرت را سریع‌تر از مسیر دسته‌بندی مناسب پیدا کن.</p></div>
					<a class="clz-link" href="<?php echo esc_url( $shop_url ); ?>">همه دسته‌ها ←</a>
				</header>

				<div class="clz-hscroll">
					<?php if ( $categories ) : ?>
						<?php foreach ( $categories as $term ) : ?>
							<?php clz_home_category_card( $term ); ?>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="clz-message">دسته‌بندی فعالی برای نمایش وجود ندارد.</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="clz-section clz-products-latest" id="clz-new-products">
			<div class="clz-container">
				<header class="clz-heading">
					<div><h2>جدیدترین محصولات</h2><p>منتخبی از تازه‌رسیده‌های موجود فروشگاه.</p></div>
					<a class="clz-link" href="<?php echo esc_url( $shop_url ); ?>">مشاهده همه ←</a>
				</header>

				<div class="clz-product-shelf">
					<?php if ( $latest_products ) : ?>
						<?php foreach ( $latest_products as $index => $product ) : ?>
							<?php clz_home_product_card( $product, $index < 3 ? 'تازه‌رسیده' : 'جدید' ); ?>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="clz-message">محصول موجود تازه‌ای پیدا نشد.</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ( $sale_products ) : ?>
			<section class="clz-section clz-sale-section" id="clz-discounts">
				<div class="clz-container">
					<div class="clz-sale-wrap">
						<header class="clz-sale-top">
							<div class="clz-sale-copy">
								<span class="clz-eyebrow">قیمت‌های بهتر</span>
								<h2>محصولات تخفیف‌دار</h2>
								<p>فقط کالاهای موجود و دارای تخفیف واقعی.</p>
							</div>
							<div class="clz-countdown" aria-label="زمان باقی‌مانده تا پایان امروز">
								<div class="clz-time"><strong data-clz-hours>۰۰</strong><span>ساعت</span></div>
								<div class="clz-time"><strong data-clz-minutes>۰۰</strong><span>دقیقه</span></div>
								<div class="clz-time"><strong data-clz-seconds>۰۰</strong><span>ثانیه</span></div>
							</div>
						</header>

						<div class="clz-product-shelf">
							<?php foreach ( $sale_products as $product ) : ?>
								<?php clz_home_product_card( $product ); ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<section class="clz-section" id="clz-popular-products">
			<div class="clz-container">
				<header class="clz-heading">
					<div><h2>محبوب‌ترین محصولات</h2><p>بر اساس فروش، امتیاز و بازخورد مشتری‌ها.</p></div>
					<a class="clz-link" href="<?php echo esc_url( $shop_url ); ?>">دیدن فروشگاه ←</a>
				</header>

				<div class="clz-product-grid clz-popular-grid">
					<?php if ( $popular_products ) : ?>
						<?php foreach ( $popular_products as $index => $product ) : ?>
							<?php clz_home_product_card( $product, 'انتخاب ' . number_format_i18n( $index + 1 ) ); ?>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="clz-message">برای نمایش محصولات محبوب داده کافی وجود ندارد.</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="clz-section clz-soft-band" id="clz-why">
			<div class="clz-container">
				<header class="clz-heading"><div><h2>چرا کلوز؟</h2><p>چون در خرید اکسسوری، جزئیات کوچک تعیین‌کننده‌اند.</p></div></header>
				<div class="clz-why-grid">
					<article class="clz-why"><div class="clz-why-icon">✦</div><h3>اطلاعات دقیق محصول</h3><p>جنس، ابعاد، طول میله و ویژگی‌های اصلی را پیش از خرید واضح می‌بینی.</p></article>
					<article class="clz-why"><div class="clz-why-icon">♢</div><h3>انتخاب ساده‌تر</h3><p>دسته‌بندی و توضیحات روشن کمک می‌کنند سریع‌تر به مدل مناسب برسی.</p></article>
					<article class="clz-why"><div class="clz-why-icon">↺</div><h3>خرید بدون سردرگمی</h3><p>در مسیر انتخاب، پشتیبانی واقعی کنار توست تا پاسخ مشخص بگیری.</p></article>
					<article class="clz-why"><div class="clz-why-icon">⌁</div><h3>بسته‌بندی امن</h3><p>سفارش با بسته‌بندی مناسب و محافظت‌شده برایت آماده می‌شود.</p></article>
				</div>
			</div>
		</section>

		<?php if ( $popular_categories ) : ?>
			<section class="clz-section" id="clz-popular-categories">
				<div class="clz-container">
					<header class="clz-heading">
						<div><h2>دسته‌های پرطرفدار</h2><p>چند مسیر سریع برای کشف مدل‌های محبوب.</p></div>
						<a class="clz-link" href="<?php echo esc_url( $shop_url ); ?>">همه محصولات ←</a>
					</header>

					<div class="clz-bento">
						<?php foreach ( $popular_categories as $term ) : ?>
							<?php
							$link = get_term_link( $term );
							if ( is_wp_error( $link ) ) {
								continue;
							}
							$image = clz_home_term_image_url( $term );
							?>
							<a class="clz-bento-card" href="<?php echo esc_url( $link ); ?>" style="<?php echo esc_attr( "--cat-img:url('" . esc_url_raw( $image ) . "')" ); ?>">
								<h3><?php echo esc_html( $term->name ); ?></h3>
								<p><?php echo esc_html( number_format_i18n( $term->count ) ); ?> محصول برای انتخاب</p>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $weekly_products ) : ?>
			<section class="clz-section" id="clz-weekly-ranking">
				<div class="clz-container clz-ranking-shell">
					<aside class="clz-ranking-intro">
						<span class="clz-eyebrow">رتبه‌بندی هفتگی</span>
						<h2>۱۰ محبوب این هفته</h2>
						<p>چیدمان بر اساس فروش و بازخورد محصولات موجود فروشگاه.</p>
					</aside>
					<div class="clz-ranking-list">
						<?php foreach ( $weekly_products as $index => $product ) : ?>
							<?php clz_home_rank_item( $product, $index ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $daily_products ) : ?>
			<section class="clz-section clz-soft-band" id="clz-daily">
				<div class="clz-container clz-daily-grid">
					<aside class="clz-daily-note">
						<div><span class="clz-eyebrow">پیشنهادهای روز</span><h2>برای امروز چه انتخابی داریم؟</h2><p>ترکیبی از محصولات ویژه، محبوب و تازه‌رسیده؛ بدون کالای ناموجود.</p></div>
						<div class="clz-tip"><strong>نکته انتخاب:</strong><br>برای استفاده روزمره، مدل‌های سبک و ساده معمولاً راحت‌ترند.</div>
					</aside>
					<div class="clz-product-shelf">
						<?php foreach ( $daily_products as $index => $product ) : ?>
							<?php clz_home_product_card( $product, array( 'پیشنهاد امروز', 'منتخب کلوز', 'خاص', 'پیشنهاد' )[ $index % 4 ] ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<section class="clz-section" id="clz-support">
			<div class="clz-container">
				<div class="clz-support">
					<div>
						<span class="clz-eyebrow">پشتیبانی آنلاین</span>
						<h2>برای انتخاب سایز یا مدل شک داری؟</h2>
						<p>سؤال، عکس یا مشخصات مدل موردنظرت را برای پشتیبانی بفرست تا انتخاب دقیق‌تری داشته باشی.</p>
					</div>
					<div class="clz-support-action">
						<a class="clz-btn clz-btn-primary" href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer">شروع گفت‌وگو ←</a>
						<small>باز شدن در پنجره جدید</small>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $popular_posts ) : ?>
			<section class="clz-section" id="clz-popular-articles">
				<div class="clz-container">
					<header class="clz-heading">
						<div><h2>محبوب‌ترین مقالات</h2><p>مطالبی که گفت‌وگو و تعامل بیشتری داشته‌اند.</p></div>
						<a class="clz-link" href="<?php echo esc_url( $blog_url ); ?>">ورود به مجله ←</a>
					</header>

					<div class="clz-popular-articles">
						<?php $featured_post = array_shift( $popular_posts ); ?>
						<a class="clz-article-feature" href="<?php echo esc_url( get_permalink( $featured_post ) ); ?>">
							<?php echo clz_home_post_image( $featured_post->ID, 'large', '', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<div class="clz-article-feature-content">
								<div class="clz-article-meta"><span><?php echo esc_html( get_the_date( '', $featured_post ) ); ?></span><span>•</span><span><?php echo esc_html( number_format_i18n( get_comments_number( $featured_post ) ) ); ?> دیدگاه</span></div>
								<h3><?php echo esc_html( get_the_title( $featured_post ) ); ?></h3>
								<p><?php echo esc_html( clz_home_excerpt( $featured_post->ID, 24 ) ); ?></p>
							</div>
						</a>

						<div class="clz-popular-side">
							<?php foreach ( $popular_posts as $post_item ) : ?>
								<a class="clz-article-row" href="<?php echo esc_url( get_permalink( $post_item ) ); ?>">
									<?php echo clz_home_post_image( $post_item->ID, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span>
										<small><?php echo esc_html( get_the_date( '', $post_item ) ); ?></small>
										<strong><?php echo esc_html( get_the_title( $post_item ) ); ?></strong>
										<em><?php echo esc_html( number_format_i18n( get_comments_number( $post_item ) ) ); ?> دیدگاه</em>
									</span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $latest_posts ) : ?>
			<section class="clz-section" id="clz-latest-articles">
				<div class="clz-container">
					<header class="clz-heading">
						<div><h2>تازه‌ترین مقالات</h2><p>راهنماهای جدید برای انتخاب، استفاده و نگهداری بهتر.</p></div>
						<a class="clz-link" href="<?php echo esc_url( $blog_url ); ?>">همه مطالب ←</a>
					</header>

					<div class="clz-latest-articles">
						<?php foreach ( $latest_posts as $post_item ) : ?>
							<article class="clz-article-card">
								<a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>">
									<?php echo clz_home_post_image( $post_item->ID, 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
								<div class="clz-article-card-body">
									<small><?php echo esc_html( get_the_date( '', $post_item ) ); ?></small>
									<h3><a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>"><?php echo esc_html( get_the_title( $post_item ) ); ?></a></h3>
									<p><?php echo esc_html( clz_home_excerpt( $post_item->ID, 17 ) ); ?></p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<section class="clz-section" id="clz-faq">
			<div class="clz-container clz-faq-grid">
				<div class="clz-faq-intro">
					<span class="clz-eyebrow">سؤال‌های پرتکرار</span>
					<h2>قبل از خرید چه چیزهایی باید بدانم؟</h2>
					<p>پاسخ کوتاه و روشن به سؤال‌هایی که بیشتر از همه از پشتیبانی پرسیده می‌شوند.</p>
				</div>

				<div class="clz-faq-list">
					<article class="clz-faq-item open">
						<button class="clz-faq-q" type="button" aria-expanded="true"><span>از کجا بفهمم یک پیرسینگ برای پوست حساس مناسب است؟</span><span class="clz-faq-plus">＋</span></button>
						<div class="clz-faq-a"><div><p>در صفحه محصول جنس بدنه و توضیحات مربوط به حساسیت را بررسی کن. برای انتخاب دقیق‌تر، پیش از خرید از پشتیبانی راهنمایی بگیر.</p></div></div>
					</article>
					<article class="clz-faq-item">
						<button class="clz-faq-q" type="button" aria-expanded="false"><span>چطور طول میله مناسب را انتخاب کنم؟</span><span class="clz-faq-plus">＋</span></button>
						<div class="clz-faq-a"><div><p>طول مناسب به محل استفاده و ضخامت بافت بستگی دارد. اندازه مدل فعلی یا عکس آن برای راهنمایی دقیق‌تر مفید است.</p></div></div>
					</article>
					<article class="clz-faq-item">
						<button class="clz-faq-q" type="button" aria-expanded="false"><span>محصولات ناموجود در صفحه اصلی نمایش داده می‌شوند؟</span><span class="clz-faq-plus">＋</span></button>
						<div class="clz-faq-a"><div><p>خیر. کوئری سمت سرور فقط محصولاتی را نمایش می‌دهد که در ووکامرس موجود و قابل مشاهده باشند.</p></div></div>
					</article>
					<article class="clz-faq-item">
						<button class="clz-faq-q" type="button" aria-expanded="false"><span>سفارش‌ها چه زمانی ارسال می‌شوند؟</span><span class="clz-faq-plus">＋</span></button>
						<div class="clz-faq-a"><div><p>زمان پردازش و ارسال به تنظیمات فروشگاه و شهر مقصد بستگی دارد. جزئیات نهایی را در صفحه قوانین ارسال درج کن.</p></div></div>
					</article>
				</div>
			</div>
		</section>

		<div class="clz-toast" role="status" aria-live="polite"></div>
	</div>
</main>

<script>
(() => {
	'use strict';
	const root = document.getElementById('cloze-home-v2');
	if (!root) return;

	const digits = '۰۱۲۳۴۵۶۷۸۹';
	const fa = value => String(value).replace(/\d/g, d => digits[d]);
	const toast = root.querySelector('.clz-toast');
	let toastTimer;

	const showToast = text => {
		if (!toast) return;
		toast.textContent = text;
		toast.classList.add('show');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(() => toast.classList.remove('show'), 1800);
	};

	const key = 'clz-home-wishlist';
	const readWishlist = () => {
		try { return JSON.parse(localStorage.getItem(key) || '[]').map(Number); }
		catch (e) { return []; }
	};
	const paintWishlist = () => {
		const saved = new Set(readWishlist());
		root.querySelectorAll('.js-clz-wish').forEach(button => {
			const active = saved.has(Number(button.dataset.id));
			button.classList.toggle('active', active);
			button.textContent = active ? '♥' : '♡';
			button.setAttribute('aria-label', active ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها');
		});
	};

	root.addEventListener('click', event => {
		const wish = event.target.closest('.js-clz-wish');
		if (wish) {
			event.preventDefault();
			const id = Number(wish.dataset.id);
			const current = readWishlist();
			const exists = current.includes(id);
			const next = exists ? current.filter(item => item !== id) : [...current, id];
			try { localStorage.setItem(key, JSON.stringify([...new Set(next)])); } catch (e) {}
			paintWishlist();
			showToast(exists ? 'از علاقه‌مندی‌ها حذف شد' : 'به علاقه‌مندی‌ها اضافه شد');
			return;
		}

		const faq = event.target.closest('.clz-faq-q');
		if (faq) {
			const item = faq.closest('.clz-faq-item');
			root.querySelectorAll('.clz-faq-item').forEach(other => {
				if (other !== item) {
					other.classList.remove('open');
					other.querySelector('.clz-faq-q')?.setAttribute('aria-expanded', 'false');
				}
			});
			const open = item.classList.toggle('open');
			faq.setAttribute('aria-expanded', String(open));
		}
	});

	const updateCountdown = () => {
		const now = new Date();
		const end = new Date(now);
		end.setHours(24, 0, 0, 0);
		const remaining = Math.max(0, Math.floor((end - now) / 1000));
		const values = [
			Math.floor(remaining / 3600),
			Math.floor((remaining % 3600) / 60),
			remaining % 60
		];
		['hours', 'minutes', 'seconds'].forEach((name, index) => {
			const element = root.querySelector(`[data-clz-${name}]`);
			if (element) element.textContent = fa(String(values[index]).padStart(2, '0'));
		});
	};

	paintWishlist();
	updateCountdown();
	setInterval(updateCountdown, 1000);
})();
</script>

<?php get_footer(); ?>
