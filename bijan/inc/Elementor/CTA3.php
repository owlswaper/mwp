<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;
use Bijan\Utils;
use Bijan\Utils\Elementor;
use Bijan\ElementorControls\Button as ElementorControlsButton;

class CTA3 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_cta3';
	}

	public function get_title() {
		return esc_html__( 'Call to Action 3 (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['call', 'action'];
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
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> __( 'Application <span>Bijan</span>', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // title_tag
			'title_tag',
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Tag', 'bijan' ),
				'label_block'	=> true,
				'default'		=> 'h3',
				'options'		=> Utils::custom_tags()
			]
		);

		$this->add_control( // subtitle
			'subtitle',
			[
				'label'			=> esc_html__( 'Subtitle', 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'All Bijan services will be in your hands...', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // image
			'image',
			[
				'label'			=> esc_html__( 'Choose Image', 'bijan' ),
				'description'	=> esc_html__( 'Size: 116px*116px', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'separator'		=> 'before',
				'default'		=> [
					'url'		=> \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->end_controls_section();
	}

	private function buttons_settings_controls() {
		$this->start_controls_section( // content_section
			'buttons_settings_section',
			[
				'label'	=> esc_html__( 'Buttons', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // button_mode
			'button_mode',
			[
				'label'		=> esc_html__( 'Border mode', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'market',
				'separator'	=> 'after',
				'options'	=> [
					'button'	=> esc_html__( 'Button', 'bijan' ),
					'market'	=> esc_html__( 'Market button', 'bijan' ),
				],
			]
		);

		$button_args = [
			'condition' => ['button_mode' => 'button']
		];
		$market_button_args = [
			'condition' => ['button_mode' => 'market']
		];
		ElementorControlsButton::controls( $repeater, [
			'excludes'	=> ['align'],
			'controls'	=> [
				'text'			=> $button_args,
				'link'			=> $button_args,
				'new_tab'		=> $button_args,
				'transparent'	=> $button_args,
				'type'			=> $button_args,
				'small'			=> $button_args,
				'icon'			=> $button_args,
				'icon_align'	=> $button_args,
				'style'			=> $button_args,
			],
		] );
		ElementorControls::market_button_controls( $repeater, [
			'controls'	=> [
				'market'		=> $market_button_args,
				'custom_icon'	=> $market_button_args,
				'top_text'		=> $market_button_args,
				'text'			=> $market_button_args,
				'link'			=> $market_button_args,
				'size'			=> $market_button_args,
			]
		] );

		$this->add_control( // buttons
			'buttons',
			[
				'label'		=> esc_html__( 'Buttons', 'textdomain' ),
				'type'		=> \Elementor\Controls_Manager::REPEATER,
				'fields'	=> $repeater->get_controls(),
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		$this->buttons_settings_controls();

		ElementorControls::general_style_controls( $this, [ // wrap
			'prefix'		=> 'wrap_',
			'base_selector'	=> '.bijan-cta-3',
			
			'section'	=> [
				'name'	=> 'wrap_section',
				'label'	=> esc_html__( 'Wrap style', 'bijan' ),
			],

			'excludes'			=> ['background'],
			'hover_excludes'	=> ['background'],

			'mode'	=> 'wrap',
		] );

		ElementorControls::text_style_controls( $this, '.bijan-cta-3-title', 'title_', esc_html__( "Title", 'bijan' ), '.bijan-cta-3:hover .bijan-cta-3-title' );
		ElementorControls::text_style_controls( $this, '.bijan-cta-3-subtitle', 'subtitle_', esc_html__( "Subtitle", 'bijan' ), '.bijan-cta-3:hover .bijan-cta-3-subtitle' );

		ElementorControls::general_style_controls( $this, [ // img
			'prefix'		=> 'img_',
			'base_selector'	=> '.bijan-cta-3',
			'selector'		=> '.bijan-cta-3-image-wrap img',
			
			'section'	=> [
				'name'	=> 'img_section',
				'label'	=> esc_html__( 'Image', 'bijan' ),
			],

			'mode'	=> 'img',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = [
			'title'		=> wp_kses_post( $settings['title'] ),
			'title_tag'	=> $settings['title_tag'],
			'subtitle'	=> wp_kses_post( $settings['subtitle'] ),
			'image'		=> !empty( $settings['image']['id'] ) ? $settings['image']['id'] : $settings['image']['url'],
			'buttons'	=> $settings['buttons'],
		];
		
		get_template_part( "templates/components/cta", '3', $args );
	}
}