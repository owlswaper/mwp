<?php
namespace Bijan\Elementor;

use Bijan\Utils;
use Bijan\Utils\Elementor;
use Bijan\ElementorControls;

class CTA2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_cta2';
	}

	public function get_title() {
		return esc_html__( 'Call to Action 2 (Bijan)', 'bijan' );
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

		$this->add_control( // logo
			'logo',
			[
				'label'			=> esc_html__( 'Choose logo', 'bijan' ),
				'description'	=> esc_html__( 'Size: 28px*28px', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control( // title
			'title',
			[
				'label'			=> esc_html__( 'Title', 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> __( 'Lorem ipsum', 'bijan' ),
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
				'default'		=> esc_html__( 'Lorem ipsum dolor sit amet', 'bijan' ),
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

		$this->add_control( // link
			'link',
			[
				'label'		=> esc_html__( 'Link', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'separator'	=> 'after',
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // description
			'description',
			[
				'label'			=> esc_html__( 'Description', 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::TEXTAREA,
				'rows'			=> 5,
				'default'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'bijan' ),
			]
		);

		$this->add_control( // image
			'image',
			[
				'label'		=> esc_html__( 'Choose image', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::MEDIA,
				'default'	=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control( // mobile_image
			'mobile_image',
			[
				'label'		=> esc_html__( 'Mobile image', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::MEDIA,
				'separator'	=> 'after',
				'default'	=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control( // show_curve
			'show_curve',
			[
				'label'			=> esc_html__( 'Show curve', 'bijan' ),
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
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // wrap
			'prefix'		=> 'wrap_',
			'base_selector'	=> '.bijan-cta-2',
			
			'section'	=> [
				'name'	=> 'wrap_section',
				'label'	=> esc_html__( 'Wrap style', 'bijan' ),
			],

			'excludes'			=> ['background'],
			'hover_excludes'	=> ['background'],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // logo_wrap
			'prefix'		=> 'logo_wrap_',
			'base_selector'	=> '.bijan-cta-2',
			'selector'		=> '.bijan-cta-2-logo',
			
			'section'	=> [
				'name'	=> 'logo_wrap_section',
				'label'	=> esc_html__( 'Logo wrap', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // logo
			'prefix'		=> 'logo_',
			'base_selector'	=> '.bijan-cta-2',
			'selector'		=> '.bijan-cta-2-logo img',
			
			'section'	=> [
				'name'	=> 'logo_section',
				'label'	=> esc_html__( 'Logo', 'bijan' ),
			],

			'mode'	=> 'img',
		] );
		ElementorControls::text_style_controls( $this, '.bijan-cta-2-title', 'title_', esc_html__( 'Title style', 'bijan' ), '.bijan-cta-2:hover .bijan-cta-2-title' );
		ElementorControls::text_style_controls( $this, '.bijan-cta-2-subtitle', 'subtitle_', esc_html__( 'Subtitle style', 'bijan' ), '.bijan-cta-2:hover .bijan-cta-2-subtitle' );
		ElementorControls::text_style_controls( $this, '.bijan-cta-2-description, {{WRAPPER}} .bijan-cta-2-mobile-description', 'description_', esc_html__( 'Description style', 'bijan' ), '.bijan-cta-2:hover .bijan-cta-2-description, {{WRAPPER}} .bijan-cta-2:hover .bijan-cta-2-mobile-description' );

		ElementorControls::general_style_controls( $this, [ // img
			'prefix'		=> 'img_',
			'base_selector'	=> '.bijan-cta-2',
			'selector'		=> '.bijan-cta-2-image-wrap img',
			
			'section'	=> [
				'name'	=> 'img_section',
				'label'	=> esc_html__( 'Image', 'bijan' ),
			],

			'mode'	=> 'img',
		] );

		ElementorControls::general_style_controls( $this, [ // curve_arrow
			'prefix'		=> 'curve_arrow_',
			'base_selector'	=> '.bijan-cta-2.bijan-cta-2-with-curve',
			
			'section'	=> [
				'name'		=> 'curve_arrow_section',
				'label'		=> esc_html__( 'Curve arrow', 'bijan' ),
				'condition'	=> [
					'show_curve'	=> 'yes',
				],
			],

			'mode'	=> 'svg',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = [
			'logo'			=> !empty( $settings['logo']['id'] ) ? $settings['logo']['id'] : $settings['logo']['url'],
			'title'			=> wp_kses_post( $settings['title'] ),
			'title_tag'		=> $settings['title_tag'],
			'subtitle'		=> wp_kses_post( $settings['subtitle'] ),
			'link'			=> $settings['link'],
			'description'	=> wp_kses_post( $settings['description'] ),
			'image'			=> !empty( $settings['image']['id'] ) ? $settings['image']['id'] : $settings['image']['url'],
			'mobile_image'	=> !empty( $settings['mobile_image']['id'] ) ? $settings['mobile_image']['id'] : $settings['mobile_image']['url'],
			'show_curve'	=> Utils::to_bool( $settings['show_curve'] ),
		];
		
		get_template_part( "templates/components/cta", '2', $args );
	}
}