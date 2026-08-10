<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls\Slider;
use Bijan\Utils\Archive;
use Bijan\ElementorControls;

class Products4 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_products_4';
	}

	public function get_title() {
		return esc_html__( 'Products 4 (Grid) (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['slider', 'grid', 'slide', 'post', 'shop', 'product', 'محصول', 'مطلب', 'فروشگاه', "آرشیو", "بلاگ", 'اسلایدر', 'اسلاید', 'گرید'];
	}

	private function pagination_controls() {
		$this->start_controls_section( // content_section
			'pagination_section',
			[
				'label'		=> esc_html__( 'Pagination settings', 'bijan' ),
				'tab'		=> \Elementor\Controls_Manager::TAB_CONTENT,
				'condition'	=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
			]
		);

		$this->add_control( // ppp
			'ppp',
			[
				'label'			=> esc_html__( 'Products count', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 1,
				'default'		=> 8,
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // offset
			'offset',
			[
				'label'			=> esc_html__( 'Offset', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'min'			=> 0,
				'default'		=> 0,
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // show_pagination
			'show_pagination',
			[
				'label'			=> esc_html__( 'Show pagination', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_cols'	=> [
					'default'	=> 4,
				],
				'tablet_slider'	=> [
					'default'	=> 'yes'
				],
				'tablet_slides_space'	=> [
					'default'	=> 24
				],
				'mobile_slider'	=> [
					'default'	=> 'yes'
				],
				'mobile_slides_space'	=> [
					'default'	=> 24,
				]
			],
		] );
		Slider::options_controls( $this, [], true );
		ElementorControls::query_controls( $this, true );
		$this->pagination_controls();

		ElementorControls::general_style_controls( $this, [ // product
			'prefix'		=> 'product_',
			'base_selector'	=> '.product',
			
			'section'	=> [
				'name'	=> 'product_section',
				'label'	=> esc_html__( 'Product style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // product_img
			'prefix'		=> 'product_img_',
			'base_selector'	=> '.product',
			'selector'		=> '.attachment-woocommerce_thumbnail',
			
			'section'	=> [
				'name'	=> 'product_img_section',
				'label'	=> esc_html__( 'Product image style', 'bijan' ),
			],

			'mode'	=> 'img',
		] );

		ElementorControls::general_style_controls( $this, [ // wishlist_icon
			'prefix'		=> 'wishlist_icon_',
			'base_selector'	=> '.product',
			'selector'		=> '.wishlist-button',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'	=> 'wishlist_icon_section',
				'label'	=> esc_html__( 'Wishlist button style', 'bijan' ),
			],

			'mode'	=> 'icon'
		] );

		ElementorControls::text_style_controls( $this, '.woocommerce-loop-product__title', 'product_title_', esc_html__( "Product title", 'bijan' ), '.product:hover .woocommerce-loop-product__title' );
		ElementorControls::text_style_controls( $this, '.price del bdi', 'product_sale_price_', esc_html__( "Product sale price", 'bijan' ), '.product:hover .price del bdi' );
		ElementorControls::text_style_controls( $this, '.price ins bdi, {{WRAPPER}} .price > .amount bdi', 'product_regular_price_', esc_html__( "Product regular price", 'bijan' ), '.product:hover .price ins bdi, {{WRAPPER}} .product:hover .price > .amount bdi' );

		ElementorControls::general_style_controls( $this, [ // add_to_cart
			'prefix'		=> 'add_to_cart_',
			'base_selector'	=> '.product',
			'selector'		=> '.add_to_cart_button',
			'hover_type'	=> 'normal',

			'section'	=> [
				'name'	=> 'add_to_cart_section',
				'label'	=> esc_html__( 'Add to cart button style', 'bijan' ),
			],

			'mode'	=> 'icon'
		] );

		ElementorControls::pagination_style_controls( $this, true );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$settings['style'] = 'products-style-grid';
		Archive::products( $settings );
	}
}