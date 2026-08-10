<?php

use Bijan\Utils;
use Bijan\Utils\UI;
use Bijan\Utils\WC;

// Filters
if( !function_exists( "bijan_wc_price_filter_additional_options" ) ) {
	function bijan_wc_price_filter_additional_options() {
		$options = [
			'instock'	=> esc_html__( 'Instock products', 'bijan' ),
			'onsale'	=> esc_html__( 'On-sale products', 'bijan' ),
		];

		if( isset( $_GET['special-products'] ) ) {
			$_GET['onsale'] = true;
		}

		?>
		<div class="bijan_filter_additional_options">
			<?php
			foreach( $options as $id => $label ) {
				UI::filter_radio( $label, $id, true );
			}
			?>
		</div>
		<?php
	}
}
add_action( 'woocommerce_widget_price_filter_end', 'bijan_wc_price_filter_additional_options' );

if( !function_exists( "bijan_wc_filter_nav_link" ) ) {
	function bijan_wc_filter_nav_link( $link, $term = null, $taxonomy = null ) {
		if( !empty( $_GET ) ) {
			$query_args = $_GET;
			$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
			$query_args = Utils::unset( $query_args, [$filter_name] );
			$link = add_query_arg( $query_args, $link );
			$link = str_replace( '%2C', ',', $link );
		}

		return $link;
	}
}
add_filter( 'woocommerce_layered_nav_link', 'bijan_wc_filter_nav_link', 10, 3 );

// Change HTML for colors in filter widget
if( !function_exists( "bijan_wc_filter_term_html" ) ) {
	function bijan_wc_filter_term_html( $term_html, $term, $link ) {
		$taxonomy_id = wc_attribute_taxonomy_id_by_name( $term->taxonomy );
		$taxonomy_options = WC::get_attribute_settings( $taxonomy_id );
		if( $taxonomy_options['display_type'] == 'color' ) {
			$color = WC::get_term_color( $term->term_id );
			$background_value = $color['color_1'];
			if( !empty( $color['color_2'] ) ) {
				$direction = $color['direction'] == 'vertical' ? 'to right' : 'to bottom';
				$background_value = "linear-gradient({$direction}, {$color['color_1']} 50%, {$color['color_2']} 50%)";
			}
			if( $link ) {
				$term_html = '<a class="bijan_filter bijan-filter-color-wrap bijan-title-wrap" rel="nofollow" href="' . esc_url( $link ) . '"><span class="bijan-filter-color" style="background-color: ' . $background_value . '"></span>' . UI::title( $term->name, 'span', 'style-2', false ) . '</a>';
			} else { // Currently selected
				$term_html = '<div class="bijan_filter bijan-filter-color-wrap bijan-title-wrap"><span class="bijan-filter-color" style="background-color: ' . $background_value . '"></span>' . UI::title( $term->name, 'span', 'style-2', false ) . '</div>';
			}
		}

		return $term_html;
	}
}
add_filter( 'woocommerce_layered_nav_term_html', 'bijan_wc_filter_term_html', 10, 3 );

// Apply instock & onsale filter
if( !function_exists( "bijan_wc_custom_filters" ) ) {
	function bijan_wc_custom_filters( $query ) {
		if( !$query->is_post_type_archive( 'product' ) ) return;
	
		$meta_query = $query->get( 'meta_query' ) ?: array();

		// Filter products that are in stock
		if( !empty( $_GET['instock'] ) || ( isset( $query->query_vars['only_in_stocks'] ) && Utils::to_bool( $query->query_vars['only_in_stocks'] ) ) ) {
			$meta_query[] = array(
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '='
			);
		}

		// Apply the modified meta query
		if( !empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}

		// Filter products that are on sale
		if( !empty( $_GET['onsale'] ) || isset( $_GET['special-products'] ) ) {
			$post__in = $query->get( 'post__in' ) ?: [];
			$product_ids_on_sale = wc_get_product_ids_on_sale();
			$query->set( 'post__in', array_unique( array_merge( $post__in, $product_ids_on_sale ) ) );
		}
	}
}
if( !is_admin() ) {
	add_action( 'pre_get_posts', 'bijan_wc_custom_filters' );
}