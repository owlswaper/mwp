<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls\Slider;
use Bijan\Utils\Elementor;
use Bijan\ElementorControls;

class CategoriesSlider extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_categories_slider';
	}

	public function get_title() {
		return esc_html__( 'Categories Slider (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-favorite';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['category', 'categories', 'دسته بندی', 'دسته‌بندی', 'دسته بندی ها', 'دسته‌بندی ها'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Items', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Item icon', 'bijan' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'bijan-icon-flash',
					'library'	=> 'bijan-icon'
				],
			]
		);

		$repeater->add_control( // term_id
			'term_id',
			[
				'label'			=> esc_html__( "Item", 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> false,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_TAX,
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // items
			'items',
			[
				'label'		=> esc_html__( 'Items', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::REPEATER,
				'fields'	=> $repeater->get_controls(),
			]
		);

		$this->end_controls_section();
	}

	private function item_wrap_style_controls() {
		$selector = "{{WRAPPER}} .category-item-wrap";
		$hover_selector = "{{WRAPPER}} .category-item:hover .category-item-wrap";
		$prefix = "item_";

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( "Item style", 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( "tabs_{$prefix}_style" );

		$this->start_controls_tab( // Normal
			"tab_{$prefix}_normal",
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);

		ElementorControls::padding( $this, "{$prefix}padding", $selector );
		ElementorControls::background( $this, "{$prefix}background", $selector );
		ElementorControls::border( $this, "{$prefix}border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}_hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::padding( $this, "{$prefix}padding_hover", $hover_selector );
		ElementorControls::background( $this, "{$prefix}background_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function item_inner_style_controls() {
		$selector = "{{WRAPPER}} .category-item-inner";
		$hover_selector = "{{WRAPPER}} .category-item:hover .category-item-inner";
		$prefix = "item_inner_";

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( "Item inner style", 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( "tabs_{$prefix}_style" );

		$this->start_controls_tab( // Normal
			"tab_{$prefix}_normal",
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);

		ElementorControls::padding( $this, "{$prefix}padding", $selector );
		ElementorControls::background( $this, "{$prefix}background", $selector );
		ElementorControls::border( $this, "{$prefix}border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}_hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::padding( $this, "{$prefix}padding_hover", $hover_selector );
		ElementorControls::background( $this, "{$prefix}background_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function item_icon_style_controls() {
		$selector = "{{WRAPPER}} .category-icon";
		$hover_selector = "{{WRAPPER}} .category-item:hover .category-icon";
		$prefix = "item_icon_";

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( 'Category icon style', 'bijan' ),
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
		ElementorControls::color( $this, "{$prefix}color", $selector );
		ElementorControls::icon_size( $this, "{$prefix}icon_size", $selector );
		ElementorControls::border( $this, "{$prefix}border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow", $selector );
		ElementorControls::text_shadow( $this, "{$prefix}text_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::padding( $this, "{$prefix}padding_hover", $hover_selector );
		ElementorControls::margin( $this, "{$prefix}margin_hover", $hover_selector );
		ElementorControls::background( $this, "{$prefix}background_hover", $hover_selector );
		ElementorControls::color( $this, "{$prefix}color_hover", $hover_selector );
		ElementorControls::icon_size( $this, "{$prefix}icon_size_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow_hover", $hover_selector );
		ElementorControls::text_shadow( $this, "{$prefix}text_shadow_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	} 

	protected function register_controls() {
		$this->settings_controls();
		Slider::settings_controls( $this, [
			'excludes'	=> [
				'desktop_slides_type',
				'desktop_slides',
				'desktop_slides_space',
				'tablet_slides_type',
				'tablet_slides',
				'tablet_slides_space',
				'mobile_slides_type',
				'mobile_slides',
				'mobile_slides_space',
				'loop',
				'autoplay',
				'autoplay_time',
			],
			'controls'	=> [
				'show_arrows'	=> [
					'separator'	=> 'default'
				]
			],
		] );

		$this->item_wrap_style_controls();
		$this->item_inner_style_controls();
		$this->item_icon_style_controls();
		ElementorControls::text_style_controls( $this, '.category-title', 'category_title', esc_html__( "Category title", 'bijan' ), "{{WRAPPER}} .category-item:hover .category-icon" );
		Slider::arrows_style_controls( $this, '.bijan-slider-nav-btn' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/categories_slider", null, $settings );
	}
}