<?php

use Bijan\Utils;
use Bijan\Utils\Options;

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

if( !function_exists( "bijan_wc_pagination_args" ) ) {
	function bijan_wc_pagination_args( $args ) {
		$is_rtl = is_rtl();
		$args['prev_text'] = $is_rtl ? "<i class='bijan-icon-right'></i>" : "<i class='bijan-icon-left'></i>";
		$args['prev_text'] .= esc_html_x( "Previous", 'Pagination', 'bijan' );
		$args['next_text'] = esc_html_x( "Next", 'Pagination', 'bijan' );
		$args['next_text'] .= $is_rtl ? "<i class='bijan-icon-left'></i>" : "<i class='bijan-icon-right'></i>";
		return $args;
	}
}
add_filter( 'woocommerce_pagination_args', 'bijan_wc_pagination_args' );

if( !function_exists( "bijan_wc_template_loop_product_link_open" ) ) {
	function bijan_wc_template_loop_product_link_open() {
		global $product;

		$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );

		echo '<a href="' . esc_url( $link ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link" title="' . esc_attr( $product->get_name() ) . '">';
	}
}

if( !function_exists( "bijan_wc_loop_add_to_cart_args" ) ) {
	function bijan_wc_loop_add_to_cart_args( $args ) {
		$args['class'] .= " button-transparent";
		return $args;
	}
}
add_filter( 'woocommerce_loop_add_to_cart_args', 'bijan_wc_loop_add_to_cart_args' );

if( !function_exists( "bijan_wc_catalog_orderby" ) ) {
	function bijan_wc_catalog_orderby( $args ) {
		$args['menu_order']	= __( 'Default sorting', 'bijan' );
		$args['popularity']	= __( 'Popularity', 'bijan' );
		$args['rating']		= __( 'Average rating', 'bijan' );
		$args['date']		= __( 'Latest', 'bijan' );
		$args['price']		= __( 'Price: low to high', 'bijan' );
		$args['price-desc']	= __( 'Price: high to low', 'bijan' );
		return $args;
	}
}
add_filter( 'woocommerce_catalog_orderby', 'bijan_wc_catalog_orderby' );

/////////////////// Move out of stocks
if( !function_exists( "bijan_wc_move_out_of_stock_to_end_clauses" ) ) {
	function bijan_wc_move_out_of_stock_to_end_clauses( $args ) {
		global $wpdb;
		$args['join'] .= " LEFT JOIN {$wpdb->postmeta} AS bmt1 ON ({$wpdb->posts}.ID=bmt1.post_id)";
		$args['join'] .= " LEFT JOIN {$wpdb->postmeta} AS bmt2 ON ({$wpdb->posts}.ID=bmt2.post_id)";
		$args['join'] .= " LEFT JOIN {$wpdb->postmeta} AS bmt3 ON ({$wpdb->posts}.ID=bmt3.post_id)";

		$args['where'] .= " AND ( bmt1.meta_key = '_stock_status' AND ( ( bmt2.meta_key = '_price' OR bmt3.post_id IS NULL ) ) )";

		$default_orderby = $args['orderby'];
		$args['orderby'] = " bmt1.meta_value ASC, bmt1.meta_value+0 ASC";
		if( $default_orderby ) {
			$args['orderby'] .= ", {$default_orderby}";
		}

		$args['distinct'] = 'DISTINCT';

		return $args;
	}
}

if( !function_exists( "bijan_wc_move_out_of_stock_to_end" ) ) {
	function bijan_wc_move_out_of_stock_to_end( $query ) {
		static $executed = false;
		if( !$executed ) {
			$options = Options::get_options( [
				'wc-move-out-of-stock-to-end'	=> false,
			] );
			if( !Utils::to_bool( $options['wc-move-out-of-stock-to-end'] ) ) return;

			if( isset( $_GET['instock'] ) && Utils::to_bool( $_GET['instock'] ) ) return;

			add_filter( 'posts_clauses', 'bijan_wc_move_out_of_stock_to_end_clauses' );
			$executed = true;
		}
	}
}
add_filter( 'woocommerce_product_query', 'bijan_wc_move_out_of_stock_to_end' );

if( !function_exists( "bijan_wc_move_out_of_stock_to_end_custom" ) ) {
	function bijan_wc_move_out_of_stock_to_end_custom( $pieces, $query ) {
		if( isset( $query->query_vars['move_out_of_stocks_to_end'] ) && Utils::to_bool( $query->query_vars['move_out_of_stocks_to_end'] ) ) {
			$pieces = bijan_wc_move_out_of_stock_to_end_clauses( $pieces );
		}

		return $pieces;
	}
}
add_filter( 'posts_clauses_request', 'bijan_wc_move_out_of_stock_to_end_custom', 10, 2 );

if( !function_exists( "bijan_wc_only_in_stocks" ) ) {
	function bijan_wc_only_in_stocks( $query ) {
		if( isset( $query->query_vars['only_in_stocks'] ) && Utils::to_bool( $query->query_vars['only_in_stocks'] ) ) {
			bijan_wc_custom_filters( $query );
		}
	}
}
add_action( 'pre_get_posts', 'bijan_wc_only_in_stocks' );

/////////////////// Archive descriptions
if( !function_exists( "bijan_wc_taxonomy_archive_description_top" ) ) {
	function bijan_wc_taxonomy_archive_description_top() {
		$options = Options::get_options( [
			'wc-show-archive-description'	=> true,
			'wc-archive-description-bottom'	=> true,
		] );
		if( !$options['wc-show-archive-description'] ) return;
		if( $options['wc-archive-description-bottom'] ) return;

		// Code from woocommerce_taxonomy_archive_description()
		if( is_product_taxonomy() && 0 === absint( get_query_var( 'paged' ) ) ) {
			$term = get_queried_object();

			if( $term ) {
				$term_description = apply_filters( 'woocommerce_taxonomy_archive_description_raw', $term->description, $term );

				if( !empty( $term_description ) ) {
					$descriptions_location = $options['wc-archive-description-bottom'] ? 'bottom' : 'top';
					echo '<div class="term-description term-description-' . $descriptions_location . '">' . wc_format_content( wp_kses_post( $term_description ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}
}
add_action( 'bijan/wc/archive/start_primary', 'bijan_wc_taxonomy_archive_description_top', 10 );

if( !function_exists( "bijan_wc_product_archive_description_top" ) ) {
	function bijan_wc_product_archive_description_top() {
		$options = Options::get_options( [
			'wc-show-archive-description'	=> true,
			'wc-archive-description-bottom'	=> true,
		] );
		if( !$options['wc-show-archive-description'] ) return;
		if( $options['wc-archive-description-bottom'] ) return;

		// Don't display the description on search results page.
		if ( is_search() ) {
			return;
		}

		if ( is_post_type_archive( 'product' ) && in_array( absint( get_query_var( 'paged' ) ), array( 0, 1 ), true ) ) {
			$shop_page = get_post( wc_get_page_id( 'shop' ) );
			if ( $shop_page ) {

				$allowed_html = wp_kses_allowed_html( 'post' );

				// This is needed for the search product block to work.
				$allowed_html = array_merge(
					$allowed_html,
					array(
						'form'   => array(
							'action'         => true,
							'accept'         => true,
							'accept-charset' => true,
							'enctype'        => true,
							'method'         => true,
							'name'           => true,
							'target'         => true,
						),

						'input'  => array(
							'type'        => true,
							'id'          => true,
							'class'       => true,
							'placeholder' => true,
							'name'        => true,
							'value'       => true,
						),

						'button' => array(
							'type'  => true,
							'class' => true,
							'label' => true,
						),

						'svg'    => array(
							'hidden'    => true,
							'role'      => true,
							'focusable' => true,
							'xmlns'     => true,
							'width'     => true,
							'height'    => true,
							'viewbox'   => true,
						),
						'path'   => array(
							'd' => true,
						),
					)
				);

				$description = wc_format_content( wp_kses( $shop_page->post_content, $allowed_html ) );
				if( $description ) {
					$descriptions_location = $options['wc-archive-description-bottom'] ? 'bottom' : 'top';
					echo '<div class="page-description page-description-' . $descriptions_location . '">' . $description . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}
}
add_action( 'bijan/wc/archive/start_primary', 'bijan_wc_product_archive_description_top', 10 );

if( !function_exists( "bijan_wc_taxonomy_archive_description_bottom" ) ) {
	function bijan_wc_taxonomy_archive_description_bottom() {
		$options = Options::get_options( [
			'wc-show-archive-description'	=> true,
			'wc-archive-description-bottom'	=> true,
		] );
		if( !Utils::to_bool( $options['wc-show-archive-description'] ) ) return;
		if( !Utils::to_bool( $options['wc-archive-description-bottom'] ) ) return;

		woocommerce_taxonomy_archive_description();
	}
}
add_action( 'bijan/wc/archive/end_primary', 'bijan_wc_taxonomy_archive_description_bottom', 10 );

if( !function_exists( "bijan_wc_product_archive_description_bottom" ) ) {
	function bijan_wc_product_archive_description_bottom() {
		$options = Options::get_options( [
			'wc-show-archive-description'	=> true,
			'wc-archive-description-bottom'	=> true,
		] );
		if( !Utils::to_bool( $options['wc-show-archive-description'] ) ) return;
		if( !Utils::to_bool( $options['wc-archive-description-bottom'] ) ) return;

		woocommerce_product_archive_description();
	}
}
add_action( 'bijan/wc/archive/end_primary', 'bijan_wc_product_archive_description_bottom', 10 );

// Special products page
if( !function_exists( "bijan_wc_special_products_page_title" ) ) {
	function bijan_wc_special_products_page_title( $page_title ) {
		if( is_shop() && isset( $_GET['special-products'] ) ) {
			$page_title = esc_html__( "Special offers", 'bijan' );
		}
		return $page_title;
	}
}
add_filter( 'woocommerce_page_title', 'bijan_wc_special_products_page_title' );

if( !function_exists( "bijan_wc_custom_orderby" ) ) {
	function bijan_wc_custom_orderby( $query, $query_vars ) {
		$custom_orders = ['price', 'popularity', 'rating', 'sales'];
		if( !empty( $query_vars['orderby'] ) && in_array( $query_vars['orderby'], $custom_orders ) ) {
			$orderby = $query_vars['orderby'];
			$order = $query_vars['order'] ?? 'ASC';
			if( $orderby == 'price' ) {
				$query['orderby'] = 'meta_value_num';
				$query['meta_key'] = '_price';
				$query['order'] = $order;
			} elseif( $orderby == 'popularity' ) {
				$query['orderby'] = 'meta_value_num';
				$query['meta_key'] = 'total_sales';
				$query['order'] = $order;
			} elseif( $orderby == 'rating' ) {
				$query['orderby'] = 'meta_value_num';
				$query['meta_key'] = '_wc_average_rating';
				$query['order'] = $order;
			} elseif( $orderby == 'sales' ) {
				$query['orderby'] = 'meta_value_num';
				$query['meta_key'] = 'total_sales';
				$query['order'] = $order;
			}
		}

		return $query;
	}
}
add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'bijan_wc_custom_orderby', 10, 2 );