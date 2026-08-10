<?php
namespace Bijan\AJAX;

use Bijan\AJAX;
use Bijan\Utils;
use Bijan\Utils\Options;
use Bijan\Utils\WC\Compare as WCCompare;

use Automattic\WooCommerce\Enums\ProductType;
use Automattic\WooCommerce\Enums\ProductStockStatus;
use Bijan\Utils\WC;

class Compare extends AJAX {
	private $options = [];
	private $attr_labels = [];

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

	private function maybe_add_product_id_and_retrieve_ids() {
		if( !empty( $this->data['product_id'] ) ) {
			$product_id = Utils::convert_chars( $this->data['product_id'], true, 'absint' );
			$product_ids = WCCompare::add_product_to_cookie( $product_id );
		} else {
			$product_ids = WCCompare::get_products_from_cookie();
		}
		return $product_ids;
	}

	private function get_options() {
		if( empty( $this->options ) ) {
			$options = Options::get_options( [
				'wc-compare'				=> true,
				'wc-compare-image'			=> true,
				'wc-compare-price'			=> true,
				'wc-compare-excerpt'		=> true,
				'wc-compare-qty'			=> true,
				'wc-compare-weight'			=> true,
				'wc-compare-dimension'		=> true,
				'wc-compare-add-to-cart'	=> true,
			] );
			if( !$options['wc-compare'] ) {
				die( esc_html__( 'The product comparison feature is currently disabled.', 'bijan' ) );
			}

			if( $options['wc-compare-weight'] ) {
				$options['wc-compare-weight'] = wc_product_weight_enabled();
			}
			if( $options['wc-compare-dimension'] ) {
				$options['wc-compare-dimension'] = wc_product_dimensions_enabled();
			}

			$this->options = $options;
		}

		return $this->options;
	}

	private function get_product_attrs( $product ) {
		$options = $this->get_options();
		
		static $wc_placeholder_img = null;
		if( $options['wc-compare-image'] ) {
			if( $wc_placeholder_img === null ) {
				$wc_placeholder_img =  wc_placeholder_img();
			}
		}

		$attrs = [
			'id'	=> $product->get_id(),
			'name'	=> $product->get_name(),
			'link'	=> get_permalink( $product->get_id() ),
			'type'	=> $product->get_type(),
		];

		if( $options['wc-compare-price'] ) {
			WC::apply_custom_toman( true );
			$attrs['price'] = $product->get_price_html();
		}
		if( $options['wc-compare-excerpt'] ) {
			$attrs['excerpt'] = wpautop( $product->get_short_description() );
		}

		// Get children
		if( $options['wc-compare-image'] || $options['wc-compare-qty'] || $options['wc-compare-weight'] || $options['wc-compare-dimension'] ) {
			$child_products = [];
			if( $attrs['type'] == ProductType::VARIABLE || $attrs['type'] == ProductType::GROUPED ) {
				foreach( $product->get_children() as $child_product_id ) {
					$child_products[] = wc_get_product( $child_product_id );
				}
			}
		}
		
		if( $options['wc-compare-image'] ) {
			$main_product_img = $product->get_image_id() ? $product->get_image( 'woocommerce_thumbnail', ['class' => 'swiper-slide compare-popup-product-image'] ) : $wc_placeholder_img;
			if( $attrs['type'] == ProductType::SIMPLE ) {
				$attrs['img'] = [$main_product_img];
			} else if( $attrs['type'] == ProductType::VARIABLE || $attrs['type'] == ProductType::GROUPED ) {
				foreach( $child_products as $child_product ) {
					if( $child_product->get_image_id() ) {
						$attrs['img'][] = $child_product->get_image( 'woocommerce_thumbnail', ['class' => 'swiper-slide'] );
					}
				}
			}
		}

		if( $options['wc-compare-qty'] ) {
			$attrs['qty'] = [];
			$main_product_qty = '';
			if( $product->get_manage_stock() ) {
				if( in_array( $product->get_stock_status(), [ProductStockStatus::IN_STOCK, ProductStockStatus::LOW_STOCK] ) ) {
					$main_product_qty = $product->get_stock_quantity();
				} else {
					$main_product_qty = '0';
				}
				$main_product_qty = sprintf( _x( "<span>%s</span> items left", 'Compare', 'bijan' ), number_format_i18n( $main_product_qty, 0 ) );
			}

			if( $attrs['type'] == ProductType::SIMPLE ) {
				$attrs['qty'] = [$main_product_qty];
			} else if( $attrs['type'] == ProductType::VARIABLE || $attrs['type'] == ProductType::GROUPED ) {
				foreach( $child_products as $child_product ) {
					$child_qty = $child_product->get_name() . " : " . $main_product_qty;
					if( $child_product->get_manage_stock() ) {
						if( in_array( $child_product->get_stock_status(), [ProductStockStatus::IN_STOCK, ProductStockStatus::LOW_STOCK] ) ) {
							$child_qty = sprintf( _x( "%s : <span>%s</span> items left", 'Compare', 'bijan' ), $child_product->get_name(), number_format_i18n( $child_product->get_stock_quantity(), 0 ) );
						}
					}
					$attrs['qty'][] = $child_qty;
				}
			}
		}

		if( $options['wc-compare-weight'] ) {
			if( $attrs['type'] == ProductType::SIMPLE ) {
				$attrs['weight'] = [wc_format_weight( $product->get_weight() )];
			} else if( $attrs['type'] == ProductType::VARIABLE || $attrs['type'] == ProductType::GROUPED ) {
				foreach( $child_products as $child_product ) {
					$attrs['weight'][] = wc_format_weight( $child_product->get_weight() );
				}
			}
		}

		if( $options['wc-compare-dimension'] ) {
			// wc_format_dimensions will applied automatically
			if( $attrs['type'] == ProductType::SIMPLE ) {
				$attrs['dimensions'] = [$product->get_dimensions()];
			} else if( $attrs['type'] == ProductType::VARIABLE || $attrs['type'] == ProductType::GROUPED ) {
				foreach( $child_products as $child_product ) {
					$attrs['dimensions'][] = $child_product->get_dimensions();
				}
			}
		}

		if( $options['wc-compare-add-to-cart'] ) {
			ob_start();
			get_template_part( "templates/components/button", null, [
				'type'			=> 'bordered',
				'icon'			=> is_rtl() ? 'bijan-icon-arrow-left-3' : 'bijan-icon-arrow-right-3',
				'icon_align'	=> 'right',
				'link'			=> $attrs['link'],
				'small'			=> true,
				'text'			=> esc_html__( "Add to cart", 'bijan' ),
			] );
			$attrs['add-to-cart'] = [ob_get_clean()];
			ob_end_clean();
		}

		$attrs['other_attrs'] = [];
		foreach( $product->get_attributes() as $product_attr_key => $product_attr ) {
			if( $product_attr->is_taxonomy() ) {
				$taxonomy = $product_attr->get_name();
				$product_attr_name = get_taxonomy( $taxonomy )->labels->singular_name;
				$terms = wp_get_post_terms( $product->get_id(), $taxonomy );
				$product_attr_values = wp_list_pluck( $terms, 'name' );
			} else {
				$product_attr_name = $product_attr->get_name();
				$product_attr_values = $product_attr->get_options();
			}
			$attrs['other_attrs'][$product_attr_key] = [
				'name'		=> $product_attr_name,
				'values'	=> $product_attr_values,
			];
		}

		return $attrs;
	}

	public function html() {
		$this->set_request_data();
		$product_ids = $this->maybe_add_product_id_and_retrieve_ids();
		$options = $this->get_options();

		$rows = [];
		if( $options['wc-compare-image'] ) {
			$rows['img'] = [
				'label'		=> '',
				'values'	=> [],
			];
		} else {
			$rows['name'] = [
				'label'		=> '',
				'values'	=> [],
			];
		}
		if( $options['wc-compare-price'] ) {
			$rows['price'] = [
				'label'		=> esc_html__( 'Price', 'bijan' ),
				'values'	=> [],
			];
		}
		if( $options['wc-compare-excerpt'] ) {
			$rows['excerpt'] = [
				'label'		=> esc_html__( 'Description', 'bijan' ),
				'values'	=> [],
			];
		}
		if( $options['wc-compare-qty'] ) {
			$rows['qty'] = [
				'label'		=> esc_html__( 'Quantity', 'bijan' ),
				'values'	=> [],
			];
		}
		if( $options['wc-compare-weight'] ) {
			$rows['weight'] = [
				'label'		=> esc_html__( 'Weight', 'bijan' ),
				'values'	=> [],
			];
		}
		if( $options['wc-compare-dimension'] ) {
			$rows['dimensions'] = [
				'label'		=> esc_html__( 'Dimensions', 'bijan' ),
				'values'	=> [],
			];
		}
		if( $options['wc-compare-add-to-cart'] ) {
			$rows['add-to-cart'] = [
				'label'		=> esc_html__( "Add to cart", 'bijan' ),
				'values'	=> [],
			];
		}

		// Add attrs to rows
		$products_attrs = [];
		foreach( $product_ids as $product_id ) {
			$product_attrs = $this->get_product_attrs( wc_get_product( $product_id ) );
			$products_attrs[$product_id] = $product_attrs;
			foreach( $product_attrs as $attr_key => $attr_values ) {
				if( isset( $rows[$attr_key] ) ) {
					$rows[$attr_key]['values'][$product_id] = $attr_values;
				}
				if( $attr_key == 'other_attrs' ) {
					foreach( $attr_values as $other_attr_key => $other_attr_data ) {
						$rows[$other_attr_key]['label'] = $other_attr_data['name'];
						$rows[$other_attr_key]['values'][$product_id] = $other_attr_data['values'];
					}
				}
			}
			Utils::reposition_array_element( $rows, 'add-to-cart', 999 );
		}

		
		/**
		 * Ensures that each product ID in $product_ids has an entry in the 'values' array of each row in $rows.
		 * If a product ID is missing, it initializes its value to an empty string.
		 */
		foreach( $rows as $attr_key => &$row_data ) {
			foreach( $product_ids as $product_id ) {
				if( !isset( $row_data['values'][$product_id] ) ) {
					$row_data['values'][$product_id] = '';
				}
			}
		}
		unset( $row_data );

		$first_attr = array_key_first( $rows );

		$table_html_attrs = [
			'id'			=> 'compare-popup-table-wrap',
			'data-columns'	=> count( $product_ids )+1,
			'style'			=> [
				'--columns'		=> count( $product_ids )+1,
				'--products'	=> count( $product_ids )
			]
		];
		?>
		<div <?php echo Utils::get_html_attributes( $table_html_attrs ) ?>>
			<?php
			foreach( $rows as $attr_key => $row_data ) {
				$values = $row_data['values'];
				?>
				<div class="compare-popup-row" data-attr="<?php echo esc_attr( $attr_key ) ?>">
					<div class="compare-popup-attr-label"><?php echo esc_html( $row_data['label'] );?></div>
					<?php
					foreach( $values as $product_id => $product_attr_values ) {
						$classes = [
							'compare-popup-attr-value',
							'compare-popup-product',
							"compare-popup-product-{$product_id}",
							"compare-popup-product-type-{$product_attrs['type']}",
						];

						$item_html_attrs = [
							'class'				=> $classes,
							'attr-product-id'	=> $product_id,
						];
						?>
						<div <?php echo Utils::get_html_attributes( $item_html_attrs ) ?>>
							<?php if( $attr_key == $first_attr ) { ?>
								<div class="compare-popup-product-remove" data-product-id="<?php echo esc_attr( $product_id ) ?>">
									<i class="bijan-icon-close"></i>
									<span class="compare-popup-product-remove-label"><?php echo esc_html_x( 'Remove from list', 'compare', 'bijan' ) ?></span>
								</div>
							<?php } ?>
		
							<?php if( $attr_key == 'img' ) { ?>
								<div class="compare-popup-product-images swiper">
									<div class="swiper-wrapper">
										<?php echo implode( "", $product_attr_values ) ?>
									</div>
								</div>
								<div class="compare-popup-product-name line-clamp line-clamp-2"><?php echo esc_html( $products_attrs[$product_id]['name'] ) ?></div>
								<?php
							} else if( in_array( $attr_key, ['qty', 'weight', 'dimensions', 'add-to-cart'] ) ) {
								echo implode( "<br>", $product_attr_values );
							} else {
								if( is_array( $product_attr_values ) ) {
									?>
									<ul class="compare-popup-product-values-list">
										<?php foreach( $product_attr_values as $attr_value ) { ?>
											<li class="compare-popup-product-values-list-item"><?php echo $attr_value ?></li>
										<?php } ?>
									</ul>
									<?php
								} else {
									echo $product_attr_values;
								}
							}
							?>
						</div>
					<?php } ?>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		die;
	}
}