<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls\Slider;
use Bijan\Utils;
use Bijan\ElementorControls;

class Testimonials extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_testimonials';
	}

	public function get_title() {
		return esc_html__( 'Testimonials (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-blockquote';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['testimonials', 'slider', 'گروه', 'اسلایدر', 'نظر', 'مشتری'];
	}

	private function items_settings_controls() {
		$this->start_controls_section( // content_section
			'items_settings_section',
			[
				'label'	=> esc_html__( 'Items', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Default image', 'bijan' ),
				'description'	=> esc_html__( 'Size: 90px*90px', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'		=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // name
			'name',
			[
				'label'			=> esc_html__( 'Name', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Name', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // position
			'position',
			[
				'label'			=> esc_html__( 'Position', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Position', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // text
			'text',
			[
				'label'			=> esc_html__( 'Text', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::WYSIWYG,
				'default'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
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
				'title_field' => '{{{ name }}}',
			]
		);

		$this->end_controls_section();
	}

	private function slider_settings_controls() {
		$this->start_controls_section( // content_section
			'slider_settings_section',
			[
				'label'	=> esc_html__( 'Slider settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // loop
			'loop',
			[
				'label'			=> esc_html__( 'Loop', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			]
		);

		Slider::autoplay_controls( $this );

		$this->add_control( // show_nav
			'show_nav',
			[
				'label'			=> esc_html__( 'Show nav buttons', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'bijan' ),
				'label_off'		=> esc_html__( 'Hide', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_settings_controls();
		$this->slider_settings_controls();

		ElementorControls::general_style_controls( $this, [ // item_bg
			'prefix'	=> 'item_bg_',
			
			'section'	=> [
				'name'	=> 'item_bg_section',
				'label'	=> esc_html__( 'Item background', 'bijan' ),
			],

			'excludes'			=> ['padding', 'margin', 'background', 'border', 'border_radius', 'box_shadow'],
			'hover_excludes'	=> ['padding', 'margin', 'background', 'border', 'border_radius', 'box_shadow'],

			'controls'	=> [
				'bg_color'	=> [
					'_responsive'	=> true,
					'label'		=> esc_html__( 'Background color', 'bijan' ),
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .testimonials-slider'	=> '--item-bg-color: {{VALUE}};',
					],
					'hover_selectors'	=> [
						'{{WRAPPER}} .testimonials-slider .testimonial-item:hover'	=> '--item-bg-color: {{VALUE}};',
					]
				]
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::text_style_controls( $this, '.testimonial-name', 'item_name_', __( "Name style", 'bijan' ), "{{WRAPPER}} .testimonial-item:hover .testimonial-name" );
		ElementorControls::text_style_controls( $this, '.testimonial-position', 'item_position_', __( "Position style", 'bijan' ), "{{WRAPPER}} .testimonial-item:hover .testimonial-position" );
		ElementorControls::text_style_controls( $this, '.testimonial-text', 'item_text_', __( "Text style", 'bijan' ), "{{WRAPPER}} .testimonial-item:hover .testimonial-text" );

		ElementorControls::general_style_controls( $this, [ // item_image
			'prefix'		=> 'item_image_',
			'base_selector'	=> '.testimonial-item',
			'selector'		=> '.testimonial-image img',
			
			'section'	=> [
				'name'	=> 'item_image_section',
				'label'	=> esc_html__( 'Item image style', 'bijan' ),
			],

			'mode'	=> 'image',
		] );

		Slider::arrows_style_controls( $this, '.bijan-slider-nav-btn' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$settings['autoplay'] = Utils::to_bool( $settings['autoplay'] ) && !empty( absint( $settings['autoplay_time'] ) ) ? absint( $settings['autoplay_time'] ) : 0;
		get_template_part( "templates/components/testimonials", null, $settings );
	}
}