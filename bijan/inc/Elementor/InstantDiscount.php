<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;
use Bijan\ElementorControls\Slider;
use Bijan\Utils;
use Bijan\Utils\Product;

class InstantDiscount extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_instant_discount';
	}

	public function get_title() {
		return esc_html__( 'Instant Discount (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-slides';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['slider', 'slide', 'discount', 'shop', 'product', 'اسلایدر', 'اسلاید', 'تخفیف', 'فروشگاه', 'محصول', 'خرید'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title
			'title',
			[
				'label'			=> esc_html__( 'Title', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Instant Discount', 'bijan' ),
				'separator'		=> 'after', 
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		Slider::autoplay_controls( $this );

		$this->add_control( // products
			'products',
			[
				'label'			=> esc_html__( "Search & Select", 'bijan' ),
				'description'	=> esc_html__( 'Select products that you want to include', 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'separator'		=> 'before',
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'		=> [
						'post_type'	=> 'product',
					],
				],
			]
		);

		$this->end_controls_section();
	}

	private function product_image_style_controls() {
		$selector = "{{WRAPPER}} .instant-discount-item-img img";
		$hover_selector = "{{WRAPPER}} .instant-discount-item:hover .instant-discount-item-img img";
		$prefix = 'product_image_';

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( 'Product image style', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( "tabs_{$prefix}style" );

		$this->start_controls_tab( // Normal
			"tab_{$prefix}normal",
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);

		ElementorControls::padding( $this, "{$prefix}padding", $selector );
		ElementorControls::margin( $this, "{$prefix}margin", $selector );
		ElementorControls::background( $this, "{$prefix}background", $selector );
		ElementorControls::border( $this, "{$prefix}border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow", $selector );
		ElementorControls::css_filters( $this, "{$prefix}css_filters", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label'	=> esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::padding( $this, "{$prefix}padding_hover", $hover_selector );
		ElementorControls::margin( $this, "{$prefix}margin_hover", $hover_selector );
		ElementorControls::background( $this, "{$prefix}background_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow_hover", $hover_selector );
		ElementorControls::css_filters( $this, "{$prefix}css_filters_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::text_style_controls(
			$this,
			'.instant-discount-title',
			'title_',
			__( "Title style", 'bijan' ),
			"{{WRAPPER}} .instant-discount-wrap:hover .instant-discount-title"
		);
		$this->product_image_style_controls();
		ElementorControls::text_style_controls(
			$this,
			'.instant-discount-item-name',
			'item_name_',
			__( "Product name style", 'bijan' ),
			"{{WRAPPER}} .instant-discount-item:hover .instant-discount-item-name"
		);
		ElementorControls::text_style_controls(
			$this,
			'.instant-discount-item-price bdi',
			'item_price_',
			__( "Product price style", 'bijan' ),
			"{{WRAPPER}} .instant-discount-item:hover .instant-discount-item-price bdi"
		);
		ElementorControls::product_discount_progress_style_controls(
			$this,
			'.product-progress',
			'product_progress_',
			__( "Product progress style", 'bijan' ),
			"{{WRAPPER}} .instant-discount-item:hover .product-progress"
		);
		ElementorControls::product_discount_progress_style_controls(
			$this,
			'.product-progress-line',
			'product_progress_line_',
			__( "Product progress line style", 'bijan' ),
			"{{WRAPPER}} .instant-discount-item:hover .product-progress-line"
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$args = [
			'title'		=> wp_kses_post( $settings['title'] ),
			'autoplay'	=> Utils::to_bool( $settings['autoplay'] ) && !empty( absint( $settings['autoplay_time'] ) ) ? absint( $settings['autoplay_time'] ) : 0,
			'products'	=> [],
		];

		if( !empty( $settings['products'] ) ) {
			foreach( $settings['products'] as $product_id ) {
				$product = wc_get_product( $product_id );
				if( empty( $product ) || is_wp_error( $product ) || !is_object( $product ) || get_post_type( $product_id ) != 'product' ) continue;
				$instant_discount_details = Product::get_instant_discount( $product_id );
				
				if( !method_exists( $product, 'get_name' ) ) continue;
				$args['products'][$product_id] = [
					'name'		=> $product->get_name(),
					'img'		=> $product->get_image( [130, 130] ),
					'total'		=> $instant_discount_details['total'],
					'remaining'	=> $instant_discount_details['remaining'],
					'price'		=> $product->get_price(),
				];
			}
		}

		get_template_part( "templates/components/instant_discount", null, $args );
	}
}