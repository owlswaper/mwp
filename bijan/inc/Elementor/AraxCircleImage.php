<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;

class AraxCircleImage extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_arax_circle_image';
	}

	public function get_title() {
		return esc_html__( 'Arax Circle Image (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-image';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['circle', 'image', 'picture', 'دایره', 'تصویر', 'عکس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // image
			'image',
			[
				'label'		=> esc_html__( 'Choose Image', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::MEDIA,
				'default'	=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control( // show_dots
			'show_dots',
			[
				'label'			=> esc_html__( 'Show dots', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'bijan' ),
				'label_off'		=> esc_html__( 'Hide', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_responsive_control( // circle_padding
			'circle_padding',
			[
				'type'			=> \Elementor\Controls_Manager::DIMENSIONS,
				'label'			=> esc_html__( 'Circle padding', 'bijan' ),
				'size_units'	=> ['px', 'rem', 'custom'],
				'separator'		=> 'before',
				'selectors'		=> [
					'{{WRAPPER}} .arax-circle'	=> '--circle-inner:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // circle_
			'prefix'		=> 'circle_',
			'base_selector'	=> '.arax-circle',
			
			'section'	=> [
				'name'	=> 'circle_section',
				'label'	=> esc_html__( 'Circle', 'bijan' ),
			],

			'excludes'			=> ['text_align', 'padding'],
			'hover_excludes'	=> ['text_align', 'padding'],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // circle_inner_
			'prefix'			=> 'circle_inner_',
			'selector'			=> '.arax-circle::after',
			'hover_selector'	=> '.arax-circle:hover::after',
			
			'section'	=> [
				'name'	=> 'circle_inner_section',
				'label'	=> esc_html__( 'Inner circle', 'bijan' ),
			],

			'excludes'			=> ['text_align', 'margin', 'padding', 'border_radius'],
			'hover_excludes'	=> ['text_align', 'margin', 'padding', 'border_radius'],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // image_
			'prefix'		=> 'image_',
			'base_selector'	=> '.arax-circle',
			'selector'		=> 'img',
			
			'section'	=> [
				'name'	=> 'image_section',
				'label'	=> esc_html__( 'Image style', 'bijan' ),
			],

			'mode'	=> 'image',
		] );
	}

	protected function render() {
		get_template_part( "templates/components/arax-circle-image", null, $this->get_settings_for_display() );
	}
}