<?php

use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\Product;
use Bijan\Utils\UI;
use Bijan\Utils\WC;

use Automattic\WooCommerce\Enums\ProductType;

// Single
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

if( !function_exists( "bijan_wc_single_excerpt_after_title" ) ) {
	function bijan_wc_single_excerpt_after_title() {
		$options = Options::get_options( [
			'wc-single-short-description'	=> true,
			'wc-single-short-pos'			=> 'after_add_to_cart',
		] );
		if( $options['wc-single-short-description'] && $options['wc-single-short-pos'] == 'after_title' ) {
			woocommerce_template_single_excerpt();
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_excerpt_after_title', 6 );

if( !function_exists( "bijan_wc_single_after_title_start" ) ) {
	function bijan_wc_single_after_title_start() {
		echo '<div id="product-head-after-title">';
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_after_title_start', 6 );

if( !function_exists( "bijan_wc_single_head_rating" ) ) {
	function bijan_wc_single_head_rating() {
		if( post_type_supports( 'product', 'comments' ) && wc_review_ratings_enabled() ) {
			global $product;
			$average = absint( $product->get_average_rating() );
			?>
			<div class="product-head-meta" id="product-head-stars">
				<i class="bijan-icon-star-2 active"></i>
				<a href="#reviews" class="product-meta-value"><?php printf( esc_html__( '%d score', 'bijan' ), $average ) ?></a>
			</div>
			<?php
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_head_rating', 7 );

if( !function_exists( "bijan_wc_single_head_comments" ) ) {
	function bijan_wc_single_head_comments() {
		if( post_type_supports( 'product', 'comments' ) && wc_review_ratings_enabled() ) {
			global $product;
			$review_count = $product->get_review_count();
			?>
			<div class="product-head-meta" id="product-head-comments">
				<i class="bijan-icon-messages"></i>
				<a href="#reviews" class="post-meta-value"><?php printf( esc_html__( '%s comment', 'bijan' ), $review_count ) ?></a>
			</div>
			<?php
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_head_comments', 8 );

if( !function_exists( "bijan_wc_single_head_add_to_wishlist" ) ) {
	function bijan_wc_single_head_add_to_wishlist() {
		$options = Options::get_options( [
			'wishlist'	=> true,
		] );
		if( !Utils::to_bool( $options['wishlist'] ) ) return;
		global $product;
		UI::product_wishlist( $product->get_id(), [
			'additional_classes'	=> ['product-head-meta'],
			'label'					=> esc_html__( "Wishlist", 'bijan' ),
		] );
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_head_add_to_wishlist', 9 );

if( !function_exists( "bijan_wc_single_after_title_end" ) ) {
	function bijan_wc_single_after_title_end() {
		echo "</div>"; // product-head-after-title
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_after_title_end', 19 );

if( !function_exists( "bijan_wc_single_excerpt_after_actions" ) ) {
	function bijan_wc_single_excerpt_after_actions() {
		$options = Options::get_options( [
			'wc-single-short-description'	=> true,
			'wc-single-short-pos'			=> 'after_add_to_cart',
		] );
		if( $options['wc-single-short-description'] && $options['wc-single-short-pos'] == 'after_actions' ) {
			woocommerce_template_single_excerpt();
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_excerpt_after_actions', 20 );

if( !function_exists( "bijan_wc_single_feature_attrs" ) ) {
	function bijan_wc_single_feature_attrs( $args = [] ) {
		if( !is_array( $args ) ) $args = [];
		$args = Utils::check_default( $args, [
			'title'							=> esc_html__( 'Some product features:', 'bijan' ),
			'show_all_attributes_btn'		=> true,
			'show_all_attributes_btn_text'	=> esc_html__( 'View all features', 'bijan' ),
		] );
		global $product;
		if( empty( $product ) ) return;
		$featured_attrs = Product::get_featured_attributes( $product->get_id() );
		if( !empty( $featured_attrs ) ) {
			$product_attrs = $product->get_attributes();
			$product_attrs = [];
			foreach( $product->get_attributes() as $attr ) {
				$product_attrs[$attr->get_name()] = $attr;
			}
			?>
			<div class="product-featured-attributes-wrap">
				<?php if( !empty( $args['title'] ) ) { ?>
					<div class="product-featured-attributes-label"><?php echo esc_html( $args['title'] ) ?></div>
				<?php } ?>
				<div class="product-featured-attributes">
					<?php
					$index = 0;
					foreach( $featured_attrs as $attr_name ) {
						$attr = $product_attrs[$attr_name];
						$label = $attr_name;
						$options = $attr->get_options();
						if( empty( $options ) ) continue;
						
						$option = $options[0];
						if( $attr->is_taxonomy() ) {
							$label = wc_attribute_label( $attr_name );
							$option = get_term( $option, $attr_name );
							if( !is_wp_error( $option ) ) {
								$option = $option->name;
							} else {
								continue;
							}
						}
						if( $index % 2 === 0 ) {
							?>
							<div class="product-featured-attribute-row">
						<?php } ?>
							<div class="product-featured-attribute">
								<div class="product-featured-attribute-label"><?php echo esc_html( $label ) ?>:</div>
								<div class="product-featured-attribute-option"><?php echo esc_html( $option ) ?></div>
							</div>
						<?php if( $index % 2 !== 0 || array_key_last( $featured_attrs ) == $index ) { ?>
							</div>
						<?php } ?>
						<?php
						$index++;
					}
					?>
				</div>
				<?php if( $args['show_all_attributes_btn'] ) { ?>
					<div class="product-featured-attributes-link">
						<?php echo esc_html( $args['show_all_attributes_btn_text'] ) ?>
						<i class="bijan-icon-arrow-<?php echo is_rtl() ? 'left' : 'right' ?>-2"></i>
					</div>
				<?php } ?>
			</div>
			<?php
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_feature_attrs', 20 );

if( !function_exists( "bijan_wc_single_excerpt_after_featured_attrs" ) ) {
	function bijan_wc_single_excerpt_after_featured_attrs() {
		$options = Options::get_options( [
			'wc-single-short-description'	=> true,
			'wc-single-short-pos'			=> 'after_add_to_cart',
		] );
		if( $options['wc-single-short-description'] && $options['wc-single-short-pos'] == 'after_featured_attrs' ) {
			woocommerce_template_single_excerpt();
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_excerpt_after_featured_attrs', 21 );

if( !function_exists( "bijan_wc_single_variation_options" ) ) {
	function bijan_wc_single_variation_options() {
		global $product;
		if( empty( $product ) ) return;
		if( $product->is_type( 'variable' ) ) {
			$attributes = $product->get_variation_attributes();
			?>
			<div class="product-head-variations">
				<?php
				foreach( $attributes as $attr_name => $options ) {
					$taxonomy_id = wc_attribute_taxonomy_id_by_name( $attr_name );
					$attr_settings = WC::get_attribute_settings( $taxonomy_id );
					wc_dropdown_variation_attribute_options( [
						'options'   			=> $options,
						'attribute' 			=> $attr_name,
						'product'   			=> $product,
						'bijan_custom_display'	=> $attr_settings['display_type'],
					] );
				}
				?>
			</div>
			<?php
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_variation_options', 30 );

if( !function_exists( "bijan_wc_single_excerpt_after_variations" ) ) {
	function bijan_wc_single_excerpt_after_variations() {
		$options = Options::get_options( [
			'wc-single-short-description'	=> true,
			'wc-single-short-pos'			=> 'after_add_to_cart',
		] );
		if( $options['wc-single-short-description'] && $options['wc-single-short-pos'] == 'after_variations' ) {
			woocommerce_template_single_excerpt();
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'bijan_wc_single_excerpt_after_variations', 31 );

add_action( 'woocommerce_product_meta_end', 'woocommerce_template_single_price', 100 );

if( !function_exists( "bijan_wc_custom_variation_attribute_dropdown" ) ) {
	function bijan_wc_custom_variation_attribute_dropdown( $html, $args ) {
		if( !empty( $args['bijan_custom_display'] ) ) {
			$default_options = $args['options'];
			$options = [];
			$attribute = $args['attribute'];
			$product = $args['product'];
			$selected = '';
			if ( $product && taxonomy_exists( $attribute ) ) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms(
					$product->get_id(),
					$attribute,
					array(
						'fields' => 'all',
					)
				);

				foreach( $terms as $term ) {
					if( in_array( $term->slug, $default_options, true ) ) {
						$options[$term->slug] = [
							'label'			=> esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ) ),
							'custom_value'	=> '',
							'selected'		=> sanitize_title( $args['selected'] ) == $term->slug
						];
						if( $args['bijan_custom_display'] == 'color' ) {
							$options[$term->slug]['custom_value'] = WC::get_term_color( $term->term_id );
						} else if( $args['bijan_custom_display'] == 'image' ) {
							$img_id = WC::get_term_img( $term->term_id );
							if( !$img_id ) {
								global $product;
								$img_id = $product->get_image_id();
								if( !$img_id ) {
									$img_id = get_option( 'woocommerce_placeholder_image', 0 );
								}
							}
							if( !wp_attachment_is_image( $img_id ) ) {
								$img_id = 0;
							}
							$options[$term->slug]['custom_value'] = $img_id;
						} else if( $args['bijan_custom_display'] == 'icon' ) {
							$options[$term->slug]['custom_value'] = WC::get_term_icon( $term->term_id );
						} else if( $args['bijan_custom_display'] == 'gradient' ) {
							$options[$term->slug]['custom_value'] = WC::get_term_gradient( $term->term_id );
						}
					}
				}
			} else {
				foreach( $default_options as $option ) {
					$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? $args['selected'] == sanitize_title( $option ) : $args['selected'] == $option;
					$options[esc_attr( $option )] = [
						'label'		=> esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ) ),
						'selected'	=> $selected
					];
				}
			}

			$attr_label = wc_attribute_label( $args['attribute'] );
			ob_start();
			?>
			<div class="product-head-variation product-head-variation-<?php echo $args['bijan_custom_display'] ?>-wrap" data-attr="<?php echo esc_attr( sanitize_title( $attribute ) ) ?>">
				<div class="product-head-variation-label">
					<span class="product-head-variation-label-text"><?php echo esc_html( $attr_label ) ?>:</span>
				</div>
				<div class="product-head-variation-items product-head-variation-<?php echo $args['bijan_custom_display'] ?>-items">
					<?php
					$classes = ['product-head-variation-item', "product-head-variation-{$args['bijan_custom_display']}", 'bijan-title-wrap'];
					if( $args['bijan_custom_display'] != 'select' ) {
						foreach( $options as $value => $option ) {
							$item_classes = $classes;
							if( $option['selected'] ) {
								$item_classes[] = 'selected';
							}
							if( $args['bijan_custom_display'] == 'color' ) {
								$background_value = $option['custom_value']['color_1'];
								if( !empty( $option['custom_value']['color_2'] ) ) {
									$direction = $option['custom_value']['direction'] == 'vertical' ? 'to right' : 'to bottom';
									$background_value = "linear-gradient({$direction}, {$option['custom_value']['color_1']} 50%, {$option['custom_value']['color_2']} 50%)";
								}
								?>
								<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" style="background:<?php echo $background_value ?>" data-value="<?php echo esc_attr( $value ) ?>">
									<?php UI::title( $option['label'], 'span', 'style-2' ) ?>
								</div>
								<?php
							} else if( $args['bijan_custom_display'] == 'image' ) {
								?>
								<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" data-value="<?php echo esc_attr( $value ) ?>">
									<?php echo wp_get_attachment_image( $option['custom_value'] ) ?>
									<?php UI::title( $option['label'], 'span', 'style-2' ) ?>
								</div>
								<?php
							} else if( $args['bijan_custom_display'] == 'radio' ) {
								?>
								<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" data-value="<?php echo esc_attr( $value ) ?>"><?php echo esc_html( $option['label'] ) ?></div>
								<?php
							} else if( $args['bijan_custom_display'] == 'icon' ) {
								?>
								<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" data-value="<?php echo esc_attr( $value ) ?>">
									<?php echo Utils::get_icon( $option['custom_value'] ) ?>
									<?php UI::title( $option['label'], 'span', 'style-2' ) ?>
								</div>
								<?php
							} else if( $args['bijan_custom_display'] == 'gradient' ) {
								?>
								<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" style="background:linear-gradient(<?php echo $option['custom_value']['angle'] ?>deg,<?php echo $option['custom_value']['color_1'] ?> 0%,<?php echo $option['custom_value']['color_2'] ?> 100%)" data-value="<?php echo esc_attr( $value ) ?>">
									<?php UI::title( $option['label'], 'span', 'style-2' ) ?>
								</div>
								<?php
							}
						}
					} else { // Select
						$dropdown_options = [];
						foreach( $options as $value => $option ) {
							$dropdown_options[$value] = $option['label'];
						}
						$item_classes = $classes;
						UI::dropdown( [
							'classes'		=> ['product-head-variation-select-wrap', 'product-attribute-dropdown'],
							'empty'			=> $args['show_option_none'],
							'current'		=> $selected,
							'options'		=> $dropdown_options,
							'placeholder'	=> $attr_label,
							'attrs'			=> [
								'data-attr'	=> sanitize_title( $attribute ),
							],
						] );
					}
					?>
				</div>
			</div>
			<?php
			$html = ob_get_clean();
		}

		return $html;
	}
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'bijan_wc_custom_variation_attribute_dropdown', 10, 2 );

if( !function_exists( "bijan_wc_stock_html" ) ) {
	function bijan_wc_stock_html( $html, $product ) {
		if( !$html ) {
			if( $product->is_type( ProductType::SIMPLE ) ) {
				if( $product->get_price() && $product->get_stock_status() !== 'outofstock' ) {
					$html = '<div class="stock instock">' . esc_html__( "In stock", 'woocommerce' ) . '</div>';
				} else {
					$html = '<div class="stock outofstock">' . esc_html__( "Out of stock", 'woocommerce' ) . '</div>';
				}
			}
		}
		return $html;
	}
}
add_filter( 'woocommerce_get_stock_html', 'bijan_wc_stock_html', 10, 2 );

if( !function_exists( 'bijan_wc_single_add_to_cart_text' ) ) {
	function bijan_wc_single_add_to_cart_text( $text ) {
		$options = Options::get_options( [
			'wc_add_to_cart_single_text'	=> __( 'Add to cart', 'bijan' )
		] );
		return $options['wc_add_to_cart_single_text'];
	}
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'bijan_wc_single_add_to_cart_text' );

if( !function_exists( "bijan_wc_single_excerpt_box_before_icons" ) ) {
	function bijan_wc_single_excerpt_box_before_icons() {
		$options = Options::get_options( [
			'wc-single-short-description'	=> true,
			'wc-single-short-pos'			=> 'after_add_to_cart',
		] );
		if( $options['wc-single-short-description'] && $options['wc-single-short-pos'] == 'box_before_icons' ) {
			?>
			<div class="product-section product-excerpt">
				<?php
				woocommerce_template_single_excerpt();
				?>
			</div>
			<?php
		}
	}
}
add_action( 'woocommerce_after_single_product_summary', 'bijan_wc_single_excerpt_box_before_icons', 4 );

if( !function_exists( "bijan_wc_single_product_icons" ) ) {
	function bijan_wc_single_product_icons() {
		$options = Options::get_options( [
			'wc-show-product-icons'	=> true,
		] );
		if( !Utils::to_bool( $options['wc-show-product-icons'] ) ) return;
		global $product;
		$icons = Product::get_icons( $product->get_id() );

		$id = 'product-icons';
		$uid = wp_unique_id();
		if( $uid > 1 ) {
			$id .= "-{$uid}";
		}
		?>
		<div class="product-section product-icons" id="<?php echo esc_attr( $id ) ?>">
			<?php
			foreach( $icons as $icon ) {
				$proicon_args = [
					'title'			=> $icon['title'],
					'subtitle'		=> $icon['subtitle'],
					'link'			=> [
						'url'	=> $icon['link']
					],
					'classes'		=> ['product-feature-icon'],
					'hover_effect'	=> $icon['type'] == 'default',
				];
				if( is_numeric( $icon['icon'] ) ) {
					$proicon_args['img'] = [
						'id'	=> $icon['icon']
					];
				} else {
					$proicon_args['img'] = [
						'url'	=> $icon['icon']
					];
				}
				if( $icon['type'] ) {
					$proicon_args['classes'][] = 'product-feature-icon-' . $icon['type'];
				} else {
					$proicon_args['classes'][] = 'product-feature-icon-settings';
				}
				get_template_part( "templates/components/proicon", null, $proicon_args );
			}
			?>
		</div>
		<?php
	}
}
add_action( 'woocommerce_after_single_product_summary', 'bijan_wc_single_product_icons', 5 );

if( !function_exists( "bijan_wc_single_excerpt_box_after_icons" ) ) {
	function bijan_wc_single_excerpt_box_after_icons() {
		$options = Options::get_options( [
			'wc-single-short-description'	=> true,
			'wc-single-short-pos'			=> 'after_add_to_cart',
		] );
		if( $options['wc-single-short-description'] && $options['wc-single-short-pos'] == 'box_after_icons' ) {
			?>
			<div class="product-section product-excerpt">
				<?php
				woocommerce_template_single_excerpt();
				?>
			</div>
			<?php
		}
	}
}
add_action( 'woocommerce_after_single_product_summary', 'bijan_wc_single_excerpt_box_after_icons', 6 );

if( !function_exists( "bijan_wc_tabs" ) ) {
	function bijan_wc_tabs( $tabs ) {
		return Utils::unset( $tabs, ['reviews'] );
	}
}
add_filter( 'woocommerce_product_tabs', 'bijan_wc_tabs' );
add_filter( 'woocommerce_product_description_heading', '__return_false' );
add_filter( 'woocommerce_product_additional_information_heading', '__return_false' );

if( !function_exists( 'bijan_wc_product_footer' ) ) {
	function bijan_wc_product_footer() {
	  global $product;
	  ?>
	  <div id="post-terms-wrap">
		<?php echo wc_get_product_category_list( $product->get_id(), ( is_rtl() ? "، " : ', ' ), '<div id="post-categories" class="post-terms posted_in"><span class="post-term-title">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . '</span>', '</div>' ); ?>
  
		<?php echo wc_get_product_tag_list( $product->get_id(), '', '<div id="post-tags" class="post-terms tagged_as"><span class="post-term-title">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . '</span>', '</div>' ); ?>
	  </div>
	  <?php
	}
  }
  add_action( 'woocommerce_product_after_tabs', 'bijan_wc_product_footer' );

  if( !function_exists( "bijan_wc_single_comments" ) ) {
	function bijan_wc_single_comments() {
		if ( ! comments_open() ) return;
		global $product;
		?>
		<section id="product-bottom">
			<div id="product-comments-section">
				<?php comments_template(); ?>
			</div>

			<div class="products products-style-2">
				<div id="product-mini" class="product">
					<div class="product-inner woocommerce-loop-product__link">
						<div id="product-mini-texts">
							<h2 id="product-mini-title" class="woocommerce-loop-product__title line-clamp line-clamp-2"><?php echo $product->get_title() ?></h2>
						</div>
						
						<div id="product-mini-image"><?php echo $product->get_image() ?></div>

						<div class="product-bottom">
							<div class="price">
								<?php echo $product->get_price_html() ?>
							</div>
						</div>
						
						<a href="#product-head" class="add_to_cart_button"><i class="bijan-icon-cart-add"></i></a>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
add_action( 'woocommerce_after_single_product_summary', 'bijan_wc_single_comments', 19 );

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

if( !function_exists( "bijan_wc_show_color_attr_in_table" ) ) {
	function bijan_wc_show_color_attr_in_table( $html, $attribute, $values ) {
		$options = Options::get_options( [
			'wc-single-show-color-in-attribute-value'	=> true,
		] );
		if( Utils::to_bool( $options['wc-single-show-color-in-attribute-value'] ) ) {
			if( $attribute->is_taxonomy() ) {
				$attr_settings = WC::get_attribute_settings( $attribute->get_taxonomy_object()->attribute_id );
				if( $attr_settings['display_type'] == 'color' ) {
					$terms = wc_get_product_terms( get_the_ID(), $attribute->get_name(), [ 'fields' => 'all' ] );
					$html = '<div class="bijan-product-color-attr-wrap">';
					foreach( $terms as $term ) {
						$color = WC::get_term_color( $term->term_id );
						$background_value = $color['color_1'];
						if( !empty( $color['color_2'] ) ) {
							$direction = $color['direction'] == 'vertical' ? 'to right' : 'to bottom';
							$background_value = "linear-gradient({$direction}, {$color['color_1']} 50%, {$color['color_2']} 50%)";
						}
						$html .= '<div class="bijan-product-color-attr-value bijan-title-wrap" style="background:' . $background_value . '" data-value="' . esc_attr( $term->name ) . '">' . UI::title( $term->name, 'span', 'style-2' ) . '</div>';
					}
					$html .= '</div>';
				}
			}
		}
		return $html;
	}
}
add_filter( 'woocommerce_attribute', 'bijan_wc_show_color_attr_in_table', 10, 3 );

if( !function_exists( "bijan_wc_single_price_chart_popup" ) ) {
	function bijan_wc_single_price_chart_popup() {
		if( !is_singular( 'product' ) ) return;
		$options = Options::get_options( [
			'wc-price-history'	=> true,
		] );
		if( !Utils::to_bool( $options['wc-price-history'] ) ) return;
		global $product;
		if( !$product->is_type( 'simple' ) ) return;
		get_template_part( 'woocommerce/single-product/price_history_popup' );
	}
}
add_action( 'wp_footer', 'bijan_wc_single_price_chart_popup' );

if( !function_exists( "bijan_wc_single_compare_popup" ) ) {
	function bijan_wc_single_compare_popup() {
		if( !is_singular( 'product' ) ) return;
		$options = Options::get_options( [
			'wc-compare'	=> true,
		] );
		if( !Utils::to_bool( $options['wc-compare'] ) ) return;
		get_template_part( 'templates/modals/compare/popup' );
	}
}
add_action( 'wp_footer', 'bijan_wc_single_compare_popup' );

/////////// Related products
add_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products', 20 );
if( !function_exists( "bijan_wc_related_products_args" ) ) {
	function bijan_wc_related_products_args( $args ) {
		$options = Options::get_options( [
			'wc-single-end-products-show'	=> true,
			'wc-single-end-products-type'	=> 'related',
			'wc-single-end-products-ppp'	=> 8,
		] );
		if( !Utils::to_bool( $options['wc-single-end-products-show'] ) || $options['wc-single-end-products-type'] != 'related' ) return [];

		$args['posts_per_page'] = $options['wc-single-end-products-ppp'];
		$args['columns'] = $args['posts_per_page'];

		return $args;
	}
}
add_filter( 'woocommerce_output_related_products_args', 'bijan_wc_related_products_args' );

if( !function_exists( "bijan_wc_related_products" ) ) {
	// Sometimes the related products has been cached. So we slice the array by this function
	function bijan_wc_related_products( $products ) {
		$options = Options::get_options( [
			'wc-single-end-products-show'	=> true,
			'wc-single-end-products-type'	=> 'related',
			'wc-single-end-products-ppp'	=> 8,
		] );
		if( !Utils::to_bool( $options['wc-single-end-products-show'] ) || $options['wc-single-end-products-type'] != 'related' ) return [];

		$products = array_slice( $products, 0, $options['wc-single-end-products-ppp'] );

		return $products;
	}
}
add_filter( 'woocommerce_related_products', 'bijan_wc_related_products' );

if( !function_exists( "bijan_wc_change_related_products_title" ) ) {
	function bijan_wc_change_related_products_title( $title ) {
		$options = Options::get_options( [
			'wc-single-end-products-show'	=> true,
			'wc-single-end-products-title'	=> esc_html__( 'Related products', 'bijan' ),
		] );
		return Utils::to_bool( $options['wc-single-end-products-show'] ) ? esc_html( $options['wc-single-end-products-title'] ) : '';
	}
}
add_filter( 'woocommerce_product_related_products_heading', 'bijan_wc_change_related_products_title' );