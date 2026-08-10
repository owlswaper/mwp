<?php

use Bijan\Utils;
use Bijan\Utils\Archive;
use Bijan\Utils\Options;

if( !function_exists( 'bijan_single_share' ) ) {
	function bijan_single_share() {
		// srt: Screen reader text
		return [
			'telegram'	=> [
				'icon'	=> 'bijan-icon-telegram',
				'srt'	=> esc_html__( 'Telegram', 'bijan' ),
				'url'	=> "https://telegram.me/share/url",
				'args'	=> [
					'text'	=> urlencode( get_the_title() ),
					'url'	=> urlencode( get_the_permalink() ),
				],
			],
			'x'	=> [
				'icon'	=> 'bijan-icon-x',
				'srt'	=> esc_html_x( 'X', "X social", 'bijan' ),
				'url'	=> "http://twitter.com/intent/tweet",
				'args'	=> [
					'text'	=> urlencode( get_the_title() ),
					'url'	=> urlencode( get_the_permalink() ),
				],
			],
			'linkedin'	=> [
				'icon'	=> 'bijan-icon-linkedin',
				'srt'	=> esc_html__( 'Linkedin', 'bijan' ),
				'url'	=> "https://www.linkedin.com/sharing/share-offsite",
				'args'	=> [
					'url'	=> urlencode( get_the_permalink() ),
				],
			],
			'facebook'	=> [
				'icon'	=> 'bijan-icon-facebook',
				'srt'	=> esc_html__( 'Facebook', 'bijan' ),
				'url'	=> "https://www.facebook.com/sharer/sharer.php",
				'args'	=> [
					'u'	=> urlencode( get_the_permalink() ),
				],
			],
		];
	}
}

if( !function_exists( 'bijan_breadcrumb' ) ) {
	function bijan_breadcrumb() {
		if( is_front_page() ) return;

		echo '<div id="breadcrumb-wrap">';
		if( function_exists( 'rank_math_the_breadcrumbs' ) && class_exists( "\RankMath\Helper" ) && method_exists( "\RankMath\Helper", 'is_breadcrumbs_enabled' ) && \RankMath\Helper::is_breadcrumbs_enabled() ) {
			rank_math_the_breadcrumbs();
			echo "</div>";
			return;
		}

		if( function_exists('yoast_breadcrumb') ) {
			yoast_breadcrumb( '<p id="breadcrumbs">','</p>' );
			echo "</div>";
			return;
		}

		// Key for the label and value for url link
		$parts = [
			get_bloginfo( 'name' )	=> home_url(),
		];
		// Add queries
		if( is_page() ) {
			$parts[get_the_title()] = get_the_permalink();
		} else if( is_404() ) {
			$parts[esc_html__( "404", 'bijan' )] = '#';
		} else if( is_archive() ) {
			if( !is_post_type_archive() ) {
				$post_type = Utils::get_archive_post_type();
				$archive_link = '';
				if( $post_type == 'product' ) {
					$archive_title = __( 'Shop', 'bijan' );
				} else {
					$query_object = get_queried_object();
					if( is_a( $query_object, 'WP_Term' ) ) {
						if( $query_object->taxonomy == 'product_brand' ) {
							$shop_link = get_permalink( wc_get_page_id( 'shop' ) );
							$parts[__( 'Shop', 'bijan' )] = $shop_link;
							$archive_link = $shop_link;
							$options = Options::get_options( [
								'wc-brands-page-id'	=> 0,
							] );
							if( $options['wc-brands-page-id'] ) {
								$archive_link = get_permalink( $options['wc-brands-page-id'] );
							}
						}
						$archive_title = get_taxonomy( $query_object->taxonomy )->label;
					} else {
						$archive_title = get_post_type_object( $post_type )->labels->name;
					}
				}
				$parts[$archive_title] = $archive_link ? $archive_link : get_post_type_archive_link( $post_type );
			}
			$archive_title = '';
			if( Utils::is_wc_active() && is_woocommerce() ) {
				$archive_title = woocommerce_page_title( false );
			} else {
				$archive_title = get_the_archive_title();
			}
			$parts[$archive_title] = '#';
		} else if( is_search() ) {
			$parts[get_search_query( true )] = '#';
		} else if( is_singular() ) {
			$categories = [];
			if( is_singular( 'post' ) ) {
				$blog_page = get_option( 'page_for_posts', 0 );
				if( $blog_page ) {
					$parts[__('Blog', 'bijan')] = get_permalink( $blog_page );
				}
				$categories = get_the_category();
			} else if( is_singular( 'product' ) ) {
				$parts[__( 'Shop', 'bijan' )] = get_permalink( wc_get_page_id( 'shop' ) );
				$product = wc_get_product( get_the_ID() );
				$categories = $product->get_category_ids();
				if( !empty( $categories ) ) {
					foreach( $categories as $index => $category_id ) {
						$categories[$index] = get_term( $category_id );
					}
				}
			} else {
				$post_type = get_post_type();
				$post_type = get_post_type_object( $post_type );
				$parts[$post_type->label] = get_post_type_archive_link( $post_type->name );
			}
			if( !empty( $categories ) ) {
				$parts[$categories[0]->name] = get_term_link( $categories[0] );
			}
			$parts[get_the_title()] = get_the_permalink();
		} else if( is_home() ) {
			$page_id = get_option( 'page_for_posts' );
			$title = get_the_title( $page_id );
			$parts[$title] = get_the_permalink();
		}
		
		$last_key = array_key_last( $parts );
		$result_parts = [];
		foreach( $parts as $label => $url ) {
			$classes = 'breadcrumb-item';
			if( $label == $last_key ) {
				$classes .= " breadcrumb-item-active line-clamp line-clamp-1";
			}
			if( $url !== '#' ) {
				$result_parts[] = "<a href=\"{$url}\" class=\"{$classes}\">{$label}</a>";
			} else {
				$result_parts[] = "<span class=\"{$classes}\">{$label}</span>";
			}
		}

		$separator = is_rtl() ? '<i class="bijan-icon-left"></i>' : '<i class="bijan-icon-right"></i>';
		
		echo '<p id="bijan-breadcrumbs">' . implode( '<span class="breadcrumb-separator">' . $separator . '</span>', $result_parts ) . "</p></div>";
	}
}

if( !function_exists( 'bijan_archive_title' ) ) {
	function bijan_archive_title( $echo = true ) {
		$text = '';
		if( is_home() || is_front_page() ) {
			$text = get_bloginfo( 'name' );
		} else if( is_archive() ) {
			$text = get_the_archive_title();
		} else if( is_search() ) {
			$text = wp_title( '', false );
		} else {
			if( is_404() ) {
				$text = esc_html__( "404", 'bijan' );
			} else {
				$text = get_the_title();
			}
		}
		if( $echo ) echo $text;

		return $text;
	}
}

if( !function_exists( 'bijan_post_thumbnail' ) ) {
	function bijan_post_thumbnail( $post_or_product = null, $size = null, $anchor = true ) {
		echo '<figure class="post-thumbnail">';
		if( $post_or_product === null ) {
			if( post_password_required() || is_attachment() || !has_post_thumbnail() ) {
				echo '</figure>';
				return;
			}
		}
		$is_product = $post_or_product !== null && is_a( $post_or_product, 'WC_Product' );
		if( is_numeric( $post_or_product ) ) {
			$post_link = get_the_permalink( $post_or_product );
		} else {
			$post_link = '';
			if( $is_product ) {
				$post_link = get_the_permalink( $post_or_product->get_id() );
			} else {
				$post_link = get_the_permalink();
			}
		}
		?>
		<?php if( $anchor ) { ?>
			<a href="<?php echo $post_link ?>" aria-hidden="true">
		<?php } ?>
			<?php
			if( $is_product ) {
				$size = $size === null ? 'woocommerce_thumbnail' : $size;
				echo $post_or_product->get_image( $size );
			} else {
				$size = $size === null ? 'post-thumbnail' : $size;
				$attrs = ['alt' => get_the_title()];
				if( $post_or_product === null ) {
					the_post_thumbnail( $size, $attrs );
				} else {
					echo get_the_post_thumbnail( $post_or_product, $size, $attrs );
				}
			}
			?>
		<?php if( $anchor ) { ?>
			</a>
		<?php } ?>
		<?php
		echo '</figure>';
	}
}

if( !function_exists( 'bijan_get_post_title' ) ) {
	function bijan_get_post_title( $post_or_product = null ) {
		if( $post_or_product === null ) {
			$title = get_the_title();
		} else {
			$is_product = is_a( $post_or_product, 'WC_Product' );
			$title = $is_product ? $post_or_product->get_title() : get_the_title( $post_or_product );
		}
		return $title;
	}
}

if( !function_exists( "bijan_get_post_views" ) ) {
	function bijan_get_post_views( $post_id = 0 ) {
		$post_id = absint( $post_id );
		if( $post_id === 0 && !is_singular() ) return 0;

		if( $post_id === 0 ) {
			$post_id = get_the_ID();
		}
		return absint( get_post_meta( $post_id, '_views', true ) );
	}
}

if( !function_exists( "bijan_add_post_views" ) ) {
	function bijan_add_post_views( $post_id = 0 ) {
		$post_id = absint( $post_id );
		if( $post_id === 0 && !is_singular() ) return 0;
		if( is_front_page() || is_home() || is_page() ) return 0;

		if( $post_id === 0 ) {
			$post_id = get_the_ID();
		}

		$views = bijan_get_post_views( $post_id );
		$views++;
		update_post_meta( $post_id, '_views', $views );
	}
}
add_action( 'wp_head', 'bijan_add_post_views' );

if( !function_exists( "bijan_custom_order_archive" ) ) {
	function bijan_custom_order_archive( $query ) {
		// ticket issue: mjkhajeh - https://www.rtl-theme.com/dashboard/#/ticket/982270
		if( ( is_admin() && !wp_doing_ajax() ) || is_admin() ) { // Run on AJAX and just frontend
			return;
		}

		if( $query->is_main_query() ) {
			$options = Options::get_options( [
				'default_archive_sort'	=> 'newest',
			] );
			
			$sorts = Archive::sorts();
			$sort = !empty( $_GET['sort'] ) ? Utils::convert_chars( $_GET['sort'] ) : $options['default_archive_sort'];
			$sort = isset( $sorts[$sort] ) ? $sort : $options['default_archive_sort'];

			if( $sort !== 'newest' ) {
				$orderby = '';
				$order = 'DESC';
				switch( $sort ) {
					case 'oldest':
						$orderby = 'date';
						$order = 'ASC';
						break;
					case 'most-view':
						if( $query->get( 'meta_key' ) ) {
							$meta_query = $query->get( 'meta_query' );
							if( $meta_query === '' ) {
								$meta_query = [];
							}
							$custom_meta_args = [
								'key'	=> $query->get( 'meta_key' ),
							];
							if( $query->get( 'meta_value' ) !== null ) {
								$custom_meta_args['value'] = $query->get( 'meta_value' );
							}
							if( $query->get( 'meta_compare' ) !== null ) {
								$custom_meta_args['compare'] = $query->get( 'meta_compare' );
							}
							$meta_query[] = $custom_meta_args;
							$query->set( 'meta_query', $meta_query );
						}
						$orderby = 'meta_value_num ID';
						$order = 'ASC';
						$query->set( 'meta_key', '_views' );
						break;
					
					case 'title-asc':
						$orderby = 'title';
						$order = 'ASC';
						break;
					case 'title-desc':
						$orderby = 'title';
						break;
				}
				$query->set( 'orderby', $orderby );
				$query->set( 'order', $order );
			}
		}
	}
}
add_action( 'pre_get_posts', 'bijan_custom_order_archive', 99 );

if( !function_exists( 'bijan_views_order_clauses' ) ) {
	function bijan_views_order_clauses( $clauses, $query ) {
		// ticket issue: mjkhajeh - https://www.rtl-theme.com/dashboard/#/ticket/982270
		if( ( is_admin() && !wp_doing_ajax() ) || is_admin() ) { // Run on AJAX and just frontend
			return $clauses;
		}

		global $wpdb;

		if ( !$query->is_main_query() || $query->get( 'meta_key' ) !== '_views' ) {
			return $clauses;
		}

		$meta_key = '_views';

		// حذف هر اتصال اضافی به postmeta که ممکن است باعث محدود شدن نتایج شود
		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS viewpm ON ({$wpdb->posts}.ID = viewpm.post_id AND viewpm.meta_key = '{$meta_key}')";

		// حذف شرطی که پست‌هایی که meta_key نداشته باشند را فیلتر می‌کند
		$clauses['where'] = str_replace( "{$wpdb->postmeta}.meta_key = '{$meta_key}'", "1=1", $clauses['where'] );

		// حذف شرط اضافی که پست‌هایی که viewpm.post_id ندارند را می‌آورد (چون LEFT JOIN به‌تنهایی کافی است)
		// بنابراین این خط را نیاز نداریم:
		// $clauses['where'] .= " AND (viewpm.meta_key = '{$meta_key}' OR viewpm.post_id IS NULL)";

		// اصلاح مرتب‌سازی برای استفاده از viewpm
		if ( isset( $clauses['orderby'] ) ) {
			$clauses['orderby'] = explode( ",", $clauses['orderby'] );
			foreach ( $clauses['orderby'] as &$orderby ) {
				$orderby = trim( $orderby );
				if ( strpos( $orderby, "{$wpdb->postmeta}.meta_value+0" ) !== false ) {
					$direction = stripos( $orderby, 'desc' ) !== false ? 'DESC' : 'ASC';
					$orderby = "viewpm.meta_value+0 {$direction}";
				}
			}
			$clauses['orderby'] = implode( ", ", $clauses['orderby'] );
		}

		return $clauses;
	}
}
add_filter( 'posts_clauses', 'bijan_views_order_clauses', 2, 10 );