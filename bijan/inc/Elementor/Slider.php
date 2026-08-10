<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls\Slider as ElementorControlsSlider;
use Bijan\Utils;
use Bijan\ElementorControls;

class Slider extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_slider';
	}

	public function get_title() {
		return esc_html__( 'Slider (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-post-slider';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['slider', 'slide', 'اسلایدر', 'اسلاید'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Slides', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Image', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'description'	=> esc_html__( 'Image height: 358px', 'bijan' ),
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // mobile_img
			'mobile_img',
			[
				'label'			=> esc_html__( 'Mobile Image', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'description'	=> esc_html__( 'Image height: 618px', 'bijan' ),
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // img_link
			'img_link',
			[
				'label'			=> esc_html__( 'Link', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::URL,
				'label_block'	=> true,
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // items
			'items',
			[
				'label'			=> __( "Items", 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::REPEATER,
				'fields'		=> $repeater->get_controls(),
				'default'		=> [
					[
						'img'			=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'mobile_img'	=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'img_link'		=> [
							'url'	=> home_url(),
						],
					],
					[
						'img'			=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'mobile_img'	=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'img_link'		=> [
							'url'	=> home_url(),
						],
					],
				],
			]
		);

		$this->end_controls_section();
	}

	private function image_style_controls() {
		$selector = "{{WRAPPER}} .slider-item img";
		$hover_selector = "{{WRAPPER}} .slider-item:hover img";
		$prefix = 'image_';

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( 'Image style', 'bijan' ),
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
		ElementorControlsSlider::options_controls( $this, [
			'excludes'	=> ['show_dots']
		] );

		$this->image_style_controls();
		ElementorControlsSlider::arrows_style_controls( $this, '.bijan-slider-nav-btn' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = [
			'autoplay'		=> Utils::to_bool( $settings['autoplay'] ) && !empty( absint( $settings['autoplay_time'] ) ) ? absint( $settings['autoplay_time'] ) : 0,
			'loop'			=> Utils::to_bool( $settings['loop'] ),
			'show_arrows'	=> Utils::to_bool( $settings['show_arrows'] ),
			'items'			=> [],
		];

		if( !empty( $settings['items'] ) ) {
			foreach( $settings['items'] as $item ) {
				$args['items'][] = [
					'img'			=> !empty( $item['img']['id'] ) ? $item['img']['id'] : $item['img']['url'],
					'mobile_img'	=> !empty( $item['mobile_img']['id'] ) ? $item['mobile_img']['id'] : $item['mobile_img']['url'],
					'link'			=> $item['img_link'],
				];
			}
		}

		get_template_part( "templates/components/slider", null, $args );
	}
}