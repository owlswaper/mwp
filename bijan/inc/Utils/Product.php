<?php
namespace Bijan\Utils;

use Automattic\WooCommerce\Enums\ProductType;

use Bijan\Utils;

use MJ\Whitebox\Utils\Posts as WhiteboxPosts;

class Product extends Utils {
	private static $options = [];

	public static function default_options() {
		return [
			'instant_discount_total'		=> 0,
			'instant_discount_remaining'	=> 0,
			'bijan_notes'					=> '',
			'video_id'						=> 0,
		];
	}

	public static function slider_image_html( $attachment_id, $main_image = false ) {
		if( empty( $attachment_id ) ) return '';
		
		$options = Options::get_options( [
			'wc-lightbox'	=> true,
		] );

		$html_attrs = [
			'classes'	=> ['woocommerce-product-gallery__image', 'swiper-slide'],
			'data-id'	=> $attachment_id,
		];

		$tag = $options['wc-lightbox'] ? 'a' : 'div';

		$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
		$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
		$image_size        = apply_filters( 'woocommerce_gallery_image_size', $main_image ? 'woocommerce_single' : $thumbnail_size );
		$full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );

		if( wp_attachment_is_image( $attachment_id ) ) {
			$full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
			$content             = wp_get_attachment_image(
				$attachment_id,
				$image_size,
				false,
				apply_filters(
					'woocommerce_gallery_image_html_attachment_image_params',
					array(
						'title'                   => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
						'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
						'data-src'                => esc_url( $full_src[0] ),
						'data-large_image'        => esc_url( $full_src[0] ),
						'data-large_image_width'  => esc_attr( $full_src[1] ),
						'data-large_image_height' => esc_attr( $full_src[2] ),
						'class'                   => esc_attr( $main_image ? 'wp-post-image' : '' ),
					),
					$attachment_id,
					$image_size,
					$main_image
				)
			);

			if( $options['wc-lightbox'] ) {
				$html_attrs['href'] = $full_src[0];
				$html_attrs['data-lg-size'] = "{$full_src[1]}-{$full_src[2]}";
			}

			$html_attrs = Utils::get_html_attributes( $html_attrs );
		} else { // Video
			$product_options = Product::get_options( get_the_ID() );
			$content = '';
			if( $product_options['video_id'] ) {
				$video_url = wp_get_attachment_url( $product_options['video_id'] );
				$html_attrs['classes'][] = 'video-item';
				$video_data = wp_read_video_metadata( get_attached_file( $product_options['video_id'] ) );
				if( $options['wc-lightbox'] ) {
					$html_attrs['href'] = $video_url;
					$html_attrs['data-lg-size'] = "{$video_data['width']}-{$video_data['height']}";
					$html_attrs['data-video'] = [
						'source'	=> [
							[
								'src'	=> $video_url,
								'type'	=> $video_data['mime_type']
							],
						],
						'attributes'	=> [
							'controls'		=> true,
							'playsinline'	=> true,
							'preload'		=> false,
						],
					];
					$html_attrs = stripslashes( Utils::get_html_attributes( $html_attrs ) );
				}
				$content = '<video muted><source src="' . $video_url . '"></video>';
			}
		}

		$html = sprintf( "<%s %s>%s</%s>", $tag, $html_attrs, $content, $tag );

		return $html;
	}

	/**
	 * Get instant discount details
	 *
	 * @param int $product_id
	 * @return array [
	 * 		total		=> int
	 * 		remaining	=> int
	 * ]
	 */
	public static function get_instant_discount( int $product_id ) {
		$product_options = self::get_options( $product_id );
		$total = $product_options['instant_discount_total'];
		$remaining = $product_options['instant_discount_remaining'];
		
		if( $remaining < 0 ) {
			$remaining = 0;
			update_post_meta( $product_id, '_instant_discount_remaining', 0 );
		}

		return [
			'total'		=> $total,
			'remaining'	=> $remaining,
		];
	}

	public static function update_instant_discount( int $product_id, int $total, int $remaining ) {
		if( $remaining < 0 ) {
			$remaining = 0;
		}

		self::save_options( [
			'instant_discount_total'		=> $total,
			'instant_discount_remaining'	=> $remaining,
		], $product_id );
	}

	public static function get_default_props() {
		$settings = Options::get_options( [
			'default_wc_products_style'		=> 'products-style-1',
			'wc-second-image-hover-show'	=> false,
		] );

		$desktop_columns = absint( wc_get_loop_prop( 'columns' ) );
		if( $desktop_columns === 0 ) {
			$desktop_columns = 4;
		}

		return [
			'style'						=> $settings['default_wc_products_style'],
			'second-image-hover-show'	=> $settings['wc-second-image-hover-show'],

			'desktop_slider'		=> false,
			'desktop_slides_type'	=> 'auto',
			'desktop_slides'		=> $desktop_columns,
			'desktop_slides_space'	=> 0,
			'desktop_cols'			=> $desktop_columns,
			'desktop_row_gap'		=> 16,
			'desktop_column_gap'	=> 16,
			
			'tablet_slider'			=> false,
			'tablet_slides_type'	=> 'auto',
			'tablet_slides'			=> 4,
			'tablet_slides_space'	=> 0,
			'tablet_cols'			=> 2,
			'tablet_row_gap'		=> 16,
			'tablet_column_gap'		=> 16,

			'mobile_slider'			=> false,
			'mobile_slides_type'	=> 'auto',
			'mobile_slides'			=> 4,
			'mobile_slides_space'	=> 0,
			'mobile_cols'			=> 2,
			'mobile_row_gap'		=> 16,
			'mobile_column_gap'		=> 16,

			'special_products'		=> false,
		];
	}

	public static function get_loop_props() {
		$props = wc_get_loop_prop( 'bijan_loop_props' );
		if( !is_array( $props ) ) $props = [];

		if( !empty( $_GET['products-style'] ) ) {
			$style = parent::convert_chars( $_GET['products-style'] );
			$props['style'] = substr( "products-style-", 0 ) !== 0 ? "products-style-{$style}" : $style;
		}

		$default_props = self::get_default_props();

		$props = parent::check_default( $props, $default_props );

		wc_set_loop_prop( 'bijan_loop_props', $props );

		return $props;
	}

	public static function get_featured_attributes( int $product_id ) {
		static $featured_attrs = null;
		if( $featured_attrs === null ) {
			$featured_attrs = get_post_meta( $product_id, '_bijan_featured_attrs', true );
			if( !is_array( $featured_attrs ) ) $featured_attrs = [];
			$updated = false;

			if( !empty( $featured_attrs ) ) {
				$product = wc_get_product( $product_id );
				$product_attrs = $product->get_attributes();
				if( !empty( $product_attrs ) ) {
					$new_featured_attrs = array_intersect( $featured_attrs, array_map( fn( $attr ) => $attr->get_name(), $product_attrs ) );
					if( $featured_attrs != $new_featured_attrs ) {
						$featured_attrs = $new_featured_attrs;
						$updated = true;
					}
				} else {
					$featured_attrs = [];
					$updated = true;
				}
			}

			if( $updated ) {
				self::update_featured_attributes( $product_id, $featured_attrs );
			}
		}

		return $featured_attrs;
	}

	public static function update_featured_attributes( int $product_id, array $attributes ) {
		update_post_meta( $product_id, '_bijan_featured_attrs', $attributes );
	}

	public static function default_icons_args() {
		$icons = self::default_icons();
		return [
			[
				'type'		=> '',
				'icon'		=> $icons[0], // File id or file uri
				'title'		=> esc_html__( 'Express delivery', 'bijan' ),
				'subtitle'	=> esc_html__( 'Delivery in less than 2 hours', 'bijan' ),
				'link'		=> '',
			],
			[
				'type'		=> '',
				'icon'		=> $icons[1], // File id or file uri
				'title'		=> esc_html__( 'Cash on delivery', 'bijan' ),
				'subtitle'	=> esc_html__( 'Take delivery then pay', 'bijan' ),
				'link'		=> '',
			],
			[
				'type'		=> '',
				'icon'		=> $icons[2], // File id or file uri
				'title'		=> esc_html__( '7 days a week, 24 hours', 'bijan' ),
				'subtitle'	=> esc_html__( 'Online support', 'bijan' ),
				'link'		=> '',
			],
			[
				'type'		=> '',
				'icon'		=> $icons[3], // File id or file uri
				'title'		=> esc_html__( 'Product return guarantee', 'bijan' ),
				'subtitle'	=> esc_html__( 'Product return guarantee up to 7 days', 'bijan' ),
				'link'		=> '',
			],
		];
	}

	private static function check_default_icon( $icon_data, $index ) {
		$defaults = self::default_icons_args();
		$icon = parent::check_default( $icon_data, $defaults[$index], ['icon'] );
		if( !$icon['icon'] ) {
			$icon['icon'] = $defaults[$index]['icon'];
		}
		return $icon;
	}

	/**
	 * Get icons from settings
	 *
	 * @param  integer $index Start from 1
	 * @return array
	 */
	public static function get_icons_from_settings( int $index ) {
		$default_icons_args = self::default_icons_args();
		$options = Options::get_options( [
			"wc-product-icon-{$index}"			=> true,
			"wc-product-icon-{$index}-img"		=> [
				'url'	=> $default_icons_args[$index-1]['icon']
			],
			"wc-product-icon-{$index}-title"		=> $default_icons_args[$index-1]['title'],
			"wc-product-icon-{$index}-subtitle"	=> $default_icons_args[$index-1]['subtitle'],
			"wc-product-icon-{$index}-link"		=> $default_icons_args[$index-1]['link'],
		] );
		if( !isset( $options["wc-product-icon-{$index}-img"]['url'] ) ) {
			$options["wc-product-icon-{$index}-img"]['url'] = $default_icons_args[$index-1]['icon'];
		}
		$icon_data = [
			'icon'		=> $options["wc-product-icon-{$index}-img"]['url'],
			'title'		=> $options["wc-product-icon-{$index}-title"],
			'subtitle'	=> $options["wc-product-icon-{$index}-subtitle"],
			'link'		=> $options["wc-product-icon-{$index}-link"],
		];
		return $icon_data;
	}

	public static function get_icons( int $product_id ) : array {
		$icons = get_post_meta( $product_id, '_bijan_icons', true );
		if( !is_array( $icons ) ) $icons = [];

		for( $index = 0; $index <= 3; $index++ ) {
			if( !isset( $icons[$index] ) ) $icons[$index] = [];

			$icon_data = self::check_default_icon( $icons[$index], $index );
			if( !$icon_data['type'] ) { // From settings
				$icon_data = array_merge( $icon_data, self::get_icons_from_settings( $index+1 ) );
			}

			$icons[$index] = $icon_data;
		}

		return $icons;
	}

	public static function save_icons( int $product_id, array $icons ) {
		for( $index = 0; $index <= 3; $index++ ) {
			if( !isset( $icons[$index] ) ) $icons[$index] = [];

			$icon_data = self::check_default_icon( $icons[$index], $index );
			$icon_data['type'] = Utils::convert_chars( $icon_data['type'] );
			$icon_data['icon'] = Utils::convert_chars( $icon_data['icon'] );
			$icon_data['title'] = Utils::convert_chars( $icon_data['title'] );
			$icon_data['subtitle'] = Utils::convert_chars( $icon_data['subtitle'] );
			$icon_data['link'] = sanitize_url( $icon_data['link'], ['http', 'https'] );

			$icons[$index] = $icon_data;
		}
		update_post_meta( $product_id, '_bijan_icons', $icons );
	}

	public static function default_icons() {
		return [
			BIJAN_URI . "assets/img/shop-icons/express-delivery.svg",
			BIJAN_URI . "assets/img/shop-icons/cash-on-delivery.svg",
			BIJAN_URI . "assets/img/shop-icons/support.svg",
			BIJAN_URI . "assets/img/shop-icons/days-return.svg",
		];
	}

	public static function get_options( $post_id = null ) {
		$post_id = WhiteboxPosts::get_post_id( $post_id );
		if( !isset( self::$options[$post_id] ) || !is_array( self::$options[$post_id] ) ) {
			self::$options[$post_id] = WhiteboxPosts::get_post_options( self::default_options(), $post_id );
		}

		self::$options[$post_id]['notes'] = self::$options[$post_id]['bijan_notes'];

		return self::$options[$post_id];
	}

	public static function save_options( array $options, $post_id = null ) {
		if( WC::get_product_type_by_id( $post_id ) == ProductType::VARIATION ) {
			self::save_options( $options, wc_get_product( $post_id )->get_parent_id() );
			return;
		}
		WhiteboxPosts::save_post_options( $options, self::default_options(), $post_id );
		if( !isset( self::$options[$post_id] ) || !is_array( self::$options[$post_id] ) ) {
			self::$options[$post_id] = [];
		}
		self::$options[$post_id] = array_merge( self::$options[$post_id], $options );
	}
}