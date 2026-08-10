<?php
namespace Bijan\AJAX;

use Bijan\AJAX;
use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\WC;

class Search extends AJAX {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	public function query() {
		$this->set_request_data();
		$text = sanitize_text_field( $this->data['text'] );

		$options = Options::get_options( [
			'header-search-post-types'	=> ['post', 'product'],
		] );

		$available_post_types = $options['header-search-post-types'];

		if( !empty( $this->data['post_type'] ) ) {
			$available_post_types = [$this->data['post_type']];
		}

		$_post_types = [];
		foreach( $available_post_types as $post_type ) {
			$_post_types[$post_type] = get_post_type_object( $post_type )->labels->name;
		}

		$args = [
			's'					=> $text,
			'posts_per_page'	=> 12,
			'post_type'			=> $available_post_types,
		];

		if( !empty( $this->data['args'] ) ) {
			$_custom_args = json_decode( stripslashes( $this->data['args'] ), true );
			$custom_args = [];
			if( !empty( $_custom_args['exclude'] ) ) {
				$custom_args['post__not_in'] = $_custom_args['exclude'];
			}
			$args = Utils::check_default( $args, $custom_args );
		}

		$query = new \WP_Query( $args );
		$posts = [];
		if( $query->have_posts() ) {
			WC::apply_custom_toman( false );
			while( $query->have_posts() ) {
				$query->the_post();

				$extra = [];

				$post_type = get_post_type();
				$img = '';
				if( $post_type === 'product' ) {
					$product = wc_get_product( get_the_ID() );
					$img = $product->get_image( 'thumbnail' );
					$extra['price'] = $product->get_price_html();
				} else {
					$img = get_the_post_thumbnail( get_the_ID(), 'thumbnail' );
					$extra['date'] = get_the_date();
				}

				$posts[] = [
					'id'		=> get_the_ID(),
					'title'		=> get_the_title(),
					'permalink'	=> get_the_permalink(),
					'image'		=> $img,
					'post_type'	=> $post_type,
					'extra'		=> $extra,
				];
			}
			wp_reset_postdata();

			// Filter post types by founded posts
			$post_types = [];
			foreach( array_unique( wp_list_pluck( $posts, 'post_type' ) ) as $post_type ) {
				$post_types[$post_type] = $_post_types[$post_type];
			}

			get_template_part( "templates/header/search", 'results', [
				'post_types'	=> $post_types,
				'posts'			=> $posts,
				'text'			=> $text,
			] );
			WC::apply_custom_toman( true );
		} else {
			get_template_part( "templates/header/search", 'empty' );
		}
		die;
	}
}