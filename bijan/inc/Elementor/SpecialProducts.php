<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;
use Bijan\ElementorControls\Slider;
use Bijan\Utils\Archive;

class SpecialProducts extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_special_products';
	}

	public function get_title() {
		return esc_html__( 'Special Products (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['slider', 'slide', 'post', 'shop', 'product', 'محصول', 'مطلب', 'فروشگاه', "آرشیو", "بلاگ", 'اسلایدر', 'اسلاید'];
	}

	private function query_controls() {
		$this->start_controls_section( // content_section
			'query_settings_section',
			[
				'label'	=> esc_html__( 'Query settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // notice
			'notice',
			[
				'type'			=> \Elementor\Controls_Manager::NOTICE,
				'notice_type'	=> 'info',
				'content'		=> esc_html__( 'This widget show only on-sales products.', 'bijan' ),
			]
		);

		$this->add_control( // query_type
			'query_type',
			[
				'label'		=> esc_html__( 'Query type', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'custom',
				'separator'	=> 'before',
				'options'	=> [
					'latests'		=> esc_html__( 'Latests products', 'bijan' ),
					'custom'		=> esc_html__( 'Custom', 'bijan' ),
					'current_query'	=> esc_html__( 'Current Query', 'bijan' ),
					'by_id'			=> esc_html__( 'Manual Selection', 'bijan' ),
				],
			]
		);

		$this->start_controls_tabs( 'tabs_post_archive_queries' );

		$this->start_controls_tab( // Includes
			'tab_post_archive_includes',
			[
				'label'			=> esc_html__( 'Includes', 'bijan' ),
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
			]
		);

		$this->add_control( // query_include_ids
			'query_include_ids',
			[
				'label'			=> esc_html__( "Search & Select", 'bijan' ),
				'description'	=> esc_html__( 'Select posts that you want to include', 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'		=> [
						'post_type'	=> 'product',
					],
				],
				'condition'		=> [
					'query_type!'	=> 'current_query',
				],
			]
		);

		$this->add_control( // query_include_author
			'query_include_author',
			[
				'label'			=> esc_html__( "Seller", 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_AUTHOR,
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // query_include_category
			'query_include_category',
			[
				'label'			=> esc_html__( "Category", 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => 'product_cat',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // query_include_tag
			'query_include_tag',
			[
				'label'			=> esc_html__( "Tag", 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => 'product_tag',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( // Excludes
			'tab_post_archive_excludes',
			[
				'label'			=> esc_html__( 'Excludes', 'bijan' ),
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
			]
		);

		$this->add_control( // ignore_sticky_posts
			'ignore_sticky_posts',
			[
				'label'			=> esc_html__( 'Ignore sticky posts', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query']
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // query_exclude_ids
			'query_exclude_ids',
			[
				'label'			=> esc_html__( "Search & Select", 'bijan' ),
				'description'	=> esc_html__( 'Select posts that you want to exclude', 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'		=> [
						'post_type'	=> 'product',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // query_exclude_author
			'query_exclude_author',
			[
				'label'			=> esc_html__( "Author", 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_AUTHOR,
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // query_exclude_category
			'query_exclude_category',
			[
				'label'			=> esc_html__( "Category", 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => 'product_cat',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // query_exclude_tag
			'query_exclude_tag',
			[
				'label'			=> esc_html__( "Tag", 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => 'product_tag',
					],
				],
				'condition'		=> [
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control( // query_date
			'query_date',
			[
				'label'		=> esc_html__( 'Date', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'anytime',
				'separator'	=> 'before',
				'options'	=> [
					'anytime'	=> esc_html__( 'All', 'bijan' ),
					'today'		=> esc_html__( 'Past Day', 'bijan' ),
					'week'		=> esc_html__( 'Past Week', 'bijan' ),
					'month'		=> esc_html__( 'Past Month', 'bijan' ),
					'quarter'	=> esc_html__( 'Past Quarter', 'bijan' ),
					'year'		=> esc_html__( 'Past Year', 'bijan' ),
					'exact'		=> esc_html__( 'Custom', 'bijan' ),
				],
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			]
		);

		$this->add_control( // query_date_before
			'query_date_before',
			[
				'label'			=> esc_html__( 'Before', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::DATE_TIME,
				'placeholder'	=> esc_html__( 'Choose', 'bijan' ),
				'condition'		=> [
					'query_date'	=> 'exact',
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'description'	=> esc_html__( 'Setting a ‘Before’ date will show all the posts published until the chosen date (inclusive).', 'bijan' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // query_date_after
			'query_date_after',
			[
				'label'			=> esc_html__( 'After', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::DATE_TIME,
				'placeholder'	=> esc_html__( 'Choose', 'bijan' ),
				'condition'		=> [
					'query_date'	=> 'exact',
					'query_type!'	=> ['by_id', 'current_query'],
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'description'	=> esc_html__( 'Setting an ‘After’ date will show all the posts published until the chosen date (inclusive).', 'bijan' ),
			]
		);

		$this->add_control( // orderby
			'orderby',
			[
				'label'		=> esc_html__( 'Order By', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'post_date',
				'options'	=> Archive::order_by( true ),
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			]
		);

		$this->add_control( // order
			'order',
			[
				'label'		=> esc_html__( 'Order', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'desc',
				'options'	=> [
					'asc'	=> esc_html__( 'ASC', 'bijan' ),
					'desc'	=> esc_html__( 'DESC', 'bijan' ),
				],
				'condition'		=> [
					'query_type!'	=> 'current_query'
				],
			]
		);

		$this->add_control( // no_posts_message
			'no_posts_message',
			[
				'label'			=> esc_html__( 'No products Message', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'dynamic'		=> [
					'active'	=> true,
				],
				'separator'		=> 'before',
			]
		);

		$this->end_controls_section();
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
				'default'		=> 10,
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
				'default'		=> 'yes',
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
				'desktop_slider'	=> [
					'default'	=> 'yes',
				],
				'desktop_cols'		=> [
					'default'	=> 4
				],
				'tablet_slider'		=> [
					'default'	=> 'yes',
				],
				'tablet_slides_space'	=> [
					'default'	=> 0
				],
				'mobile_slider'		=> [
					'default'	=> 'yes',
				],
				'mobile_slides_space'	=> [
					'default'	=> 0,
				],
			]
		] );
		Slider::options_controls( $this, [], true );
		$this->query_controls();
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

		ElementorControls::general_style_controls( $this, [ // product_shadow
			'prefix'	=> 'product_shadow_',
			
			'section'	=> [
				'name'	=> 'product_shadow_section',
				'label'	=> esc_html__( 'Product shadow', 'bijan' ),
			],

			'excludes'			=> ['padding', 'margin', 'background', 'border', 'border_radius', 'box_shadow'],
			'hover_excludes'	=> ['padding', 'margin', 'background', 'border', 'border_radius', 'box_shadow'],

			'controls'	=> [
				'shadow_color'	=> [
					'_responsive'	=> true,
					'label'		=> esc_html__( 'Shadow color', 'bijan' ),
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .products-style-3'	=> '--product-shadow-color: {{VALUE}};',
					],
					'hover_selectors'	=> [
						'{{WRAPPER}} .products-style-3'	=> '--product-shadow-color-hover: {{VALUE}};',
					]
				]
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

		ElementorControls::text_style_controls( $this, '.woocommerce-loop-product__title', 'product_title_', esc_html__( "Product title", 'bijan' ), '.product:hover .woocommerce-loop-product__title' );
		ElementorControls::text_style_controls( $this, '.price del bdi', 'product_sale_price_', esc_html__( "Product sale price", 'bijan' ), '.product:hover .price del bdi' );
		ElementorControls::text_style_controls( $this, '.price ins bdi, {{WRAPPER}} .price > .amount bdi', 'product_regular_price_', esc_html__( "Product regular price", 'bijan' ), '.product:hover .price ins bdi, {{WRAPPER}} .product:hover .price > .amount bdi' );

		ElementorControls::product_discount_progress_style_controls(
			$this,
			'.product-progress',
			'product_progress_',
			__( "Product progress style", 'bijan' ),
			"{{WRAPPER}} .product:hover .product-progress"
		);
		ElementorControls::product_discount_progress_style_controls(
			$this,
			'.product-progress-line',
			'product_progress_line_',
			__( "Product progress line style", 'bijan' ),
			"{{WRAPPER}} .product:hover .product-progress-line"
		);

		ElementorControls::pagination_style_controls( $this, true );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$settings['only_on_sales'] = true;
		$settings['style'] = 'products-style-3';
		$settings['special_products'] = true;
		Archive::products( $settings );
	}
}