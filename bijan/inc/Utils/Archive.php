<?php
namespace Bijan\Utils;

use Bijan\Utils;

class Archive extends Utils {
	public static $defaults = [
		'post_type'		=> 'post',
		'query_type'	=> 'custom',
		
		'only_on_sales'				=> false,
		'query_include_ids'			=> [],
		'query_include_author'		=> [],
		'query_include_category'	=> [],
		'query_include_tag'			=> [],
		
		'ignore_sticky_posts'		=> true,
		'query_exclude_ids'			=> [],
		'query_exclude_author'		=> [],
		'query_exclude_category'	=> [],
		'query_exclude_tag'			=> [],
		
		'query_date'		=> 'anytime',
		'query_date_before'	=> '',
		'query_date_after'	=> '',

		'orderby'	=> 'post_date',
		'order'		=> 'desc',

		'ppp'				=> 9,
		'offset'			=> 0,
		'show_pagination'	=> true,

		'no_posts_message'	=> '',

		'desktop_slider'		=> false,
		'desktop_slides_type'	=> 'count',
		'desktop_slides'		=> 4,
		'desktop_cols'			=> 5,
		
		'tablet_slider'			=> true,
		'tablet_slides_type'	=> 'auto',
		'tablet_slides'			=> 2,
		'tablet_cols'			=> 2,

		'mobile_slider'			=> true,
		'mobile_slides_type'	=> 'auto',
		'mobile_slides'			=> 2,
		'mobile_cols'			=> 2,
	];

	private static $query_types = ['latests', 'custom', 'current_query', 'by_id'];
	private static $date_types = ['anytime', 'today', 'week', 'month', 'quarter', 'year', 'exact'];

	public static function sorts() {
		return [
			'newest'		=> _x( "Newest", 'Archive sort item', 'bijan' ),
			'oldest'		=> _x( "Oldest", 'Archive sort item', 'bijan' ),
			'most-view'		=> _x( "Most visited", 'Archive sort item', 'bijan' ),
			'title-asc'		=> _x( "Ascending title", 'Archive sort item', 'bijan' ),
			'title-desc'	=> _x( "Descending title", 'Archive sort item', 'bijan' ),
		];
	}

	public static function order_by( $wc = false, $excludes = [] ) {
		if( !$wc ) {
			$orderby = [
				'post_date'		=> esc_html__( 'Date', 'bijan' ),
				'post_title'	=> esc_html__( 'Title', 'bijan' ),
				'modified'		=> esc_html__( 'Last Modified', 'bijan' ),
				'comment_count'	=> esc_html__( 'Comment Count', 'bijan' ),
				'rand'			=> esc_html__( 'Random', 'bijan' ),
			];
		} else {
			$orderby = [
				'ID'			=> esc_html__( 'ID', 'bijan' ),
				'name'			=> esc_html__( 'Product name', 'bijan' ),
				'type'			=> esc_html__( 'Product type', 'bijan' ),
				'post_date'		=> esc_html__( 'Date', 'bijan' ),
				'modified'		=> esc_html__( 'Last Modified', 'bijan' ),
				'price'			=> esc_html__( 'Price', 'bijan' ),
				'popularity'	=> esc_html__( 'Popularity', 'bijan' ),
				'rating'		=> esc_html__( 'Rating', 'bijan' ),
				'sales'			=> esc_html__( 'Sales', 'bijan' ),
				'rand'			=> esc_html__( 'Random', 'bijan' ),
			];
		}
		if( !empty( $excludes ) ) {
			$orderby = parent::unset( $orderby, $excludes );
		}
		return $orderby;
	}

	public static function get_query_type( array $settings ) {
		return parent::ensure_values_in_array( parent::convert_chars( $settings['query_type'] ), self::$query_types, self::$defaults['query_type'] );
	}

	public static function get_show_pagination( array $settings ) {
		return isset( $settings['show_pagination'] ) ? parent::to_bool( $settings['show_pagination'] ) : self::$defaults['show_pagination'];
	}

	public static function get_paged() {
		$paged = 1;
		if( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif( get_query_var( 'page' ) ) { // 'page' is used instead of 'paged' on Static Front Page
			$paged = get_query_var( 'page' );
		}
		return $paged;
	}

	public static function prepare_query( array $settings, bool $wc = false ) {
		$wp_query_args = [];

		$post_type = !empty( $settings['post_type'] ) ? parent::convert_chars( $settings['post_type'] ) : self::$defaults['post_type'];
		$query_type = self::get_query_type( $settings );

		$only_on_sales = $wc ? parent::to_bool( $settings['only_on_sales'] ) : false;
		$includes_posts = ( $query_type != 'current_query' && !empty( $settings['query_include_ids'] ) ) ? array_filter( array_map( fn( $value ) => parent::convert_chars( $value, true, 'absint' ), $settings['query_include_ids'] ) ) : [];
		$includes_authors = ( !in_array( $query_type, ['by_id', 'current_query'] ) && !empty( $settings['query_include_author'] ) ) ? array_filter( array_map( fn( $value ) => parent::convert_chars( $value, true, 'absint' ), $settings['query_include_author'] ) ) : [];
		$includes_categories = ( !in_array( $query_type, ['by_id', 'current_query'] ) && !empty( $settings['query_include_category'] ) ) ? array_filter( array_map( fn( $value ) => parent::convert_chars( $value, true, 'absint' ), $settings['query_include_category'] ) ) : [];
		$includes_tags = ( !in_array( $query_type, ['by_id', 'current_query'] ) && !empty( $settings['query_include_tag'] ) ) ? array_filter( array_map( fn( $value ) => parent::convert_chars( $value, true, 'absint' ), $settings['query_include_tag'] ) ) : [];
		
		$ignore_sticky_posts = !empty( $settings['ignore_sticky_posts'] ) && parent::to_bool( $settings['ignore_sticky_posts'] );
		$excludes_posts = ( $query_type != 'current_query' && !empty( $settings['query_exclude_ids'] ) ) ? array_filter( array_map( fn( $value ) => parent::convert_chars( $value, true, 'absint' ), $settings['query_exclude_ids'] ) ) : [];
		$excludes_authors = ( !in_array( $query_type, ['by_id', 'current_query'] ) && !empty( $settings['query_exclude_author'] ) ) ? array_filter( array_map( fn( $value ) => parent::convert_chars( $value, true, 'absint' ), $settings['query_exclude_author'] ) ) : [];
		$excludes_categories = ( !in_array( $query_type, ['by_id', 'current_query'] ) && !empty( $settings['query_exclude_category'] ) ) ? array_filter( array_map( fn( $value ) => parent::convert_chars( $value, true, 'absint' ), $settings['query_exclude_category'] ) ) : [];
		$excludes_tags = ( !in_array( $query_type, ['by_id', 'current_query'] ) && !empty( $settings['query_exclude_tag'] ) ) ? array_filter( array_map( fn( $value ) => parent::convert_chars( $value, true, 'absint' ), $settings['query_exclude_tag'] ) ) : [];
		
		$date_type = parent::ensure_values_in_array( parent::convert_chars( $settings['query_date'] ), self::$date_types, 'anytime' );
		$date_before = ( !in_array( $query_type, ['by_id', 'current_query'] ) && $date_type === 'exact' ) ? parent::convert_chars( $settings['query_date_before'] ) : '';
		$date_after = ( !in_array( $query_type, ['by_id', 'current_query'] ) && $date_type === 'exact' ) ? parent::convert_chars( $settings['query_date_after'] ) : '';
		
		$orderby = parent::ensure_values_in_array( parent::convert_chars( $settings['orderby'] ), array_keys( self::order_by( $wc ) ), 'date' );
		$order = parent::ensure_values_in_array( parent::convert_chars( $settings['order'] ), ['desc', 'asc'], 'desc' );
		
		$ppp = parent::convert_chars( $settings['ppp'], true, 'absint' );
		$offset = parent::convert_chars( $settings['offset'], true, 'absint' );
		$show_pagination = self::get_show_pagination( $settings );

		if( $query_type != 'current_query' ) {
			if( !$wc ) {
				$wp_query_args = [
					'post_type'				=> $post_type,
					'order'					=> strtoupper( $order ),
					'orderby'				=> $orderby,
					'posts_per_page'		=> $ppp,
					'ignore_sticky_posts'	=> $ignore_sticky_posts,
					'post_status'			=> 'publish',
				];
			} else {
				$wp_query_args = [
					'order'		=> strtoupper( $order ),
					'orderby'	=> $orderby,
					'limit'		=> $ppp,
					'status'	=> 'publish',
				];
				if( $only_on_sales ) {
					$includes_posts = array_unique( array_merge( $includes_posts, wc_get_product_ids_on_sale() ) );
				}
			}
			if( !empty( $_GET['sort'] ) && !in_array( $_GET['sort'], array_keys( self::sorts() ) ) ) {
				$sort = parent::convert_chars( $_GET['sort'] );
				if( $sort === 'newest' ) {
					$wp_query_args['orderby'] = 'post_date';
				} else if( $sort === 'oldest' ) {
					$wp_query_args['orderby'] = 'post_date';
					$wp_query_args['order'] = 'ASC';
				} else if( $sort === 'most-view' ) {
					$wp_query_args['orderby'] = 'meta_value_num ID';
					$wp_query_args['order'] = 'DESC';
					$wp_query_args['meta_key'] = '_views';
				} else if( $sort === 'title-asc' ) {
					$wp_query_args['orderby'] = 'post_title';
					$wp_query_args['order'] = 'ASC';
				} else if( $sort === 'title-desc' ) {
					$wp_query_args['orderby'] = 'post_title';
					$wp_query_args['order'] = 'DESC';
				}
			}
			if( !empty( $includes_posts ) ) {
				$wp_query_args['post__in'] = $includes_posts;
			}
			if( !empty( $excludes_posts ) ) {
				$wp_query_args['post__not_in'] = $excludes_posts;
			}
			if( !empty( $includes_authors ) ) {
				$wp_query_args['author__in'] = $includes_authors;
			}
			if( !empty( $includes_categories ) ) {
				$wp_query_args['category__in'] = $includes_categories;
			}
			if( !empty( $includes_tags ) ) {
				$wp_query_args['tag__in'] = $includes_tags;
			}
			if( !empty( $excludes_authors ) ) {
				$wp_query_args['author__not_in'] = $excludes_authors;
			}
			if( !empty( $excludes_categories ) ) {
				$wp_query_args['category__not_in'] = $excludes_categories;
			}
			if( !empty( $excludes_tags ) ) {
				$wp_query_args['tag__not_in'] = $excludes_tags;
			}
			// Prepare date before and after
			if( $date_type != 'anytime' ) {
				$date_query = [];
				if( !$wc ) {
					if( $date_type === 'today' ) {
						$date_query['after'] = '-1 day';
					}
					switch( $date_type ) {
						case 'today':
							$date_query['after'] = '-1 day';
							break;
						case 'week':
							$date_query['after'] = '-1 week';
							break;
						case 'month':
							$date_query['after'] = '-1 month';
							break;
						case 'quarter':
							$date_query['after'] = '-3 month';
							break;
						case 'year':
							$date_query['after'] = '-1 year';
							break;
						case 'exact':
							if( !empty( $date_after ) ) {
								$date_query['after'] = $date_after;
							}
							if( !empty( $date_before ) ) {
								$date_query['before'] = $date_before;
							}
							$date_query['inclusive'] = true;
							break;
					}
					$wp_query_args['date_query'] = $date_query;
				} else {
					$date_query = '';
					if( $date_type === 'today' ) {
						$date_query = wp_date( "Y-m-d", strtotime( '-1 day' ) ) . '...' . wp_date( "Y-m-d" );
					} else if( $date_type === 'week' ) {
						$date_query = wp_date( "Y-m-d", strtotime( '-1 week' ) ) . '...' . wp_date( "Y-m-d" );
					} else if( $date_type ==='month' ) {
						$date_query = wp_date( "Y-m-d", strtotime( '-1 month' ) ) . "...". wp_date( "Y-m-d" );
					} else if( $date_type === 'quarter' ) {
						$date_query = wp_date( "Y-m-d", strtotime( '-3 month' ) ) . "...". wp_date( "Y-m-d" );
					} else if( $date_type === 'year' ) {
						$date_query = wp_date( "Y-m-d", strtotime( '-1 year' ) ) . "...". wp_date( "Y-m-d" );
					} else if( $date_type === 'exact' ) {
						if( !empty( $date_after ) && !empty( $date_before ) ) {
							$date_query = "{$date_before}...{$date_after}";
						} else {
							if( !empty( $date_after ) ) {
								$date_query = ">={$date_after}";
							}
							if( !empty( $date_before ) ) {
								$date_query = "<={$date_before}";
							}
						}
					}
					if( $date_query !== '' ) {
						$wp_query_args['date_created'] = $date_query;
					}
				}
			}
			if( !empty( $offset ) ) {
				$wp_query_args['offset'] = $offset;
			}

			if( $show_pagination ) {
				if( !$wc ) {
					$wp_query_args['paged'] = self::get_paged();
				} else {
					$wp_query_args['page'] = self::get_paged();
					$wp_query_args['paginate'] = true;
				}
			} else {
				$wp_query_args['no_found_rows'] = true;
			}
		}
		
		if( !empty( $wp_query_args['category__in'] ) && !empty( $wp_query_args['category__not_in'] ) ) {
			$wp_query_args['category__in'] = array_diff( $wp_query_args['category__in'], $wp_query_args['category__not_in'] );
		}
		if( !empty( $wp_query_args['tag__in'] ) && !empty( $wp_query_args['tag__not_in'] ) ) {
			$wp_query_args['tag__in'] = array_diff( $wp_query_args['tag__in'], $wp_query_args['tag__not_in'] );
		}
		if( !empty( $wp_query_args['author__in'] ) && !empty( $wp_query_args['author__not_in'] ) ) {
			$wp_query_args['author__in'] = array_diff( $wp_query_args['author__in'], $wp_query_args['author__not_in'] );
		}
		if( !empty( $wp_query_args['post__in'] ) && !empty( $wp_query_args['post__not_in'] ) ) {
			$wp_query_args['post__in'] = array_diff( $wp_query_args['post__in'], $wp_query_args['post__not_in'] );
		}

		if( $wc ) {
			$wc_args_map = [
				'post__in'			=> 'include',
				'post__not_in'		=> 'exclude',
				'posts_per_page'	=> 'limit',
				'category__in'		=> 'product_category_id',
				'category__not_in'	=> 'product_category_id_not',
				'tag__in'			=> 'product_tag_id',
				'tag__not_in'		=> 'product_tag_id_not',
				'post_status'		=> 'status',
			];
			foreach( $wc_args_map as $wp_arg => $wc_arg ) {
				if( isset( $wp_query_args[$wp_arg] ) ) {
					$wp_query_args[$wc_arg] = $wp_query_args[$wp_arg];
					unset( $wp_query_args[$wp_arg] );
				}
			}
		}

		return $wp_query_args;
	}

	public static function get_no_posts_message( array $settings ) {
		return wp_kses_post( $settings['no_posts_message'] );
	}

	public static function prepare_slider_slides( array $settings ) {
		$settings['desktop_slides'] = $settings['desktop_slides_type'] === 'count' ? $settings['desktop_slides'] : 'auto';
		$settings['tablet_slides'] = $settings['tablet_slides_type'] === 'count' ? $settings['tablet_slides'] : 'auto';
		$settings['mobile_slides'] = $settings['mobile_slides_type'] === 'count' ? $settings['mobile_slides'] : 'auto';
		return $settings;
	}

	public static function posts( array $settings, bool $echo = true, string $return = 'html' ) {
		$html = '';

		$settings = parent::check_default( $settings, self::$defaults );

		$query_type = self::get_query_type( $settings );
		$show_pagination = self::get_show_pagination( $settings );
		$query_args = self::prepare_query( $settings );
		$no_posts_message = self::get_no_posts_message( $settings );
		
		$have_posts = false;
		if( $query_type != 'current_query' ) {
			$query = new \WP_Query( $query_args );
			$have_posts = $query->have_posts();
		} else {
			$have_posts = have_posts();
		}
		
		if( empty( $settings['grid-mode'] ) ) {
			$display_attributes = Elementor::get_display_attributes( $settings );
		}
		$attributes = [
			'class'			=> [
				'bijan-slider-wrap',
				'posts-wrap',
			],
		];
		if( !empty( $display_attributes['args'] ) ) {
			$attributes['data-settings'] = $display_attributes['args'];
		}
		if( !empty( $display_attributes['style'] ) ) {
			$attributes['style'] = $display_attributes['style'];
		}
		if( !empty( $settings['grid-mode'] ) ) {
			$attributes['class'][] = 'posts-grid-mode';
		}
		
		$html .= '<div ' . Utils::get_html_attributes( $attributes ) . '>';
			$wrap_classes = ["list-posts", 'wrapper'];
			if( !empty( $display_attributes['classes'] ) ) {
				$wrap_classes = array_merge( $wrap_classes, $display_attributes['classes'] );
			}
			$html .= '<div class="' . parent::prepare_html_classes( $wrap_classes ) . '">';
			if( $echo ) {
				echo $html;
			}
			if( $have_posts ) {
				$is_empty = false;
				if( !$echo ) {
					ob_start();
				}
				while( $have_posts ) {
					if( $query_type != 'current_query' ) {
						$query->the_post();
					} else {
						the_post();
					}
					get_template_part( "templates/archives/post", null, $settings );
					
					$have_posts = $query_type != 'current_query' ? $query->have_posts() : have_posts();
				}
				if( $show_pagination ) {
					if( $query_type != 'current_query' ) {
						get_template_part( "templates/archives/pagination", 'custom', [
							'query'			=> $query,
							'paged'			=> self::get_paged(),
							'aria_label'	=> esc_attr__( "Posts", 'bijan' ),
						] );
					} else {
						get_template_part( "templates/archives/pagination" );
					}
				}
				if( $query_type != 'current_query' ) {
					wp_reset_postdata();
				}
				if( !$echo ) {
					$html .= ob_get_clean();
				}
			} else {
				$is_empty = true;
				$html .= "<div class=\"bijan-no-posts-message\">{$no_posts_message}</div>";
			}
			$html .= "</div>";
		$html .= "</div>";

		if( $return == 'html' ) {
			return $html;
		} else {
			return [
				'html'		=> $html,
				'is_empty'	=> $is_empty,
			];
		}
	}

	public static function products( array $settings ) {
		$settings['post_type'] = 'product';

		$settings = parent::check_default( $settings, self::$defaults );

		$query_type = self::get_query_type( $settings );
		$show_pagination = self::get_show_pagination( $settings );
		$query_args = self::prepare_query( $settings, true );
		$no_posts_message = self::get_no_posts_message( $settings );

		$settings = parent::check_default( $settings, Product::get_default_props() );

		$loop_props = [
			'style'					=> !empty( $settings['style'] ) ? $settings['style'] : 'products-style-1',

			'desktop_slider'		=> $settings['desktop_slider'],
			'desktop_slides_type'	=> $settings['desktop_slides_type'],
			'desktop_slides'		=> $settings['desktop_slides'],
			'desktop_slides_space'	=> $settings['desktop_slides_space'],
			'desktop_cols'			=> $settings['desktop_cols'],
			'desktop_row_gap'		=> $settings['desktop_row_gap'],
			'desktop_column_gap'	=> $settings['desktop_column_gap'],
			
			'tablet_slider'			=> $settings['tablet_slider'],
			'tablet_slides_type'	=> $settings['tablet_slides_type'],
			'tablet_slides'			=> $settings['tablet_slides'],
			'tablet_slides_space'	=> $settings['tablet_slides_space'],
			'tablet_cols'			=> $settings['tablet_cols'],
			'tablet_row_gap'		=> $settings['tablet_row_gap'],
			'tablet_column_gap'		=> $settings['tablet_column_gap'],

			'mobile_slider'			=> $settings['mobile_slider'],
			'mobile_slides_type'	=> $settings['mobile_slides_type'],
			'mobile_slides'			=> $settings['mobile_slides'],
			'mobile_slides_space'	=> $settings['mobile_slides_space'],
			'mobile_cols'			=> $settings['mobile_cols'],
			'mobile_row_gap'		=> $settings['mobile_row_gap'],
			'mobile_column_gap'		=> $settings['mobile_column_gap'],

			'show_arrows'			=> $settings['show_arrows'],
			'loop'					=> $settings['loop'],
			'autoplay'				=> $settings['autoplay'],
			'autoplay_time'			=> $settings['autoplay_time'],

			'special_products'		=> !empty( $settings['special_products'] ),
		];

		if( $query_type != 'current_query' ) {
			if( !empty( $settings['only_in_stocks'] ) && Utils::to_bool( $settings['only_in_stocks'] ) ) {
				$query_args['only_in_stocks'] = true;
			}
			if( empty( $query_args['only_in_stocks'] ) ) {
				if( !empty( $settings['move_out_of_stocks_to_end'] ) && Utils::to_bool( $settings['move_out_of_stocks_to_end'] ) ) {
					$query_args['move_out_of_stocks_to_end'] = true;
				}
			}
			$products_query = wc_get_products( $query_args );
			if( is_object( $products_query ) ) {
				$products = $products_query->products;
			} else {
				$products = $products_query;
			}
			$have_posts = !empty( $products );
			wc_set_loop_prop( 'bijan_loop_props', $loop_props );

			wc_set_loop_prop( 'current_page', self::get_paged() );
			wc_set_loop_prop( 'is_paginated', $show_pagination );
			wc_set_loop_prop('per_page', $query_args['limit'] );
			if( $show_pagination ) {
				wc_set_loop_prop('total', $products_query->total );
				wc_set_loop_prop('total_pages', $products_query->max_num_pages);
			}
		} else {
			$have_posts = wc_get_loop_prop( 'total' ) !== 0;
			wc_set_loop_prop( 'bijan_loop_props', $loop_props );
		}
		woocommerce_product_loop_start();

		if( $have_posts ) {
			$index = 0;
			if( $query_type != 'current_query' ) {
				foreach( $products as $index => $product ) {
					$post_object = get_post( $product->get_id() );
					setup_postdata( $GLOBALS['post'] =& $post_object );
					$GLOBALS['product'] = $product;
					/**
					 * Hook: woocommerce_shop_loop.
					 */
					do_action( 'woocommerce_shop_loop' );
		
					wc_get_template_part( 'content', 'product' );
				}
			} else {
				while( have_posts() ) {
					the_post();
		
					/**
					 * Hook: woocommerce_shop_loop.
					 */
					do_action( 'woocommerce_shop_loop' );
		
					wc_get_template_part( 'content', 'product' );
				}
			}
		} else {
			echo "<li class=\"bijan-no-posts-message bijan-no-products-message\">{$no_posts_message}</li>";
		}

		if( $query_type != 'current_query' ) {
			wp_reset_postdata();
		}
		woocommerce_product_loop_end();
		do_action( 'woocommerce_after_shop_loop' );
	}
}