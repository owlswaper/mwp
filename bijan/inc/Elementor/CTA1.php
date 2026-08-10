<?php
namespace Bijan\Elementor;

use Bijan\Utils;
use Bijan\Utils\Elementor;
use Bijan\ElementorControls as ElementorControls;
use Bijan\ElementorControls\Button as ElementorControlsButton;

class CTA1 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_cta1';
	}

	public function get_title() {
		return esc_html__( 'Call to Action 1 (Bijan)', 'bijan' );
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
				'default'		=> __( 'Bijan <span>club</span>', 'bijan' ),
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
				'default'		=> esc_html__( 'A large store with great facilities', 'bijan' ),
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

		$this->add_control( // description
			'description',
			[
				'label'			=> esc_html__( 'Description', 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::TEXTAREA,
				'rows'			=> 5,
				'separator'		=> 'before',
				'default'		=> esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'bijan' ),
			]
		);

		$this->add_control( // show_button
			'show_button',
			[
				'label'			=> esc_html__( 'Show button', 'bijan' ),
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
		ElementorControlsButton::settings( $this, [
			'section'	=> [
				'condition'	=> [
					'show_button'	=> 'yes'
				]
			],
			'excludes'	=> [
				'type',
				'align'
			],
			'controls'	=> [
				'text'	=> [
					'default'	=> esc_html__( 'Go to club', 'bijan' )
				],
				'style'	=> [
					'default'	=> 'rounded'
				],
			],
		] );

		ElementorControls::general_style_controls( $this, [ // wrap
			'prefix'		=> 'wrap_',
			'base_selector'	=> '.bijan-cta-1',
			
			'section'	=> [
				'name'	=> 'wrap_section',
				'label'	=> esc_html__( 'Wrap style', 'bijan' ),
			],

			'excludes'			=> ['background'],
			'hover_excludes'	=> ['background'],

			'mode'	=> 'wrap',
		] );

		ElementorControls::text_style_controls( $this, '.bijan-cta-1-title', 'title_', esc_html__( "Title", 'bijan' ), '.bijan-cta-1:hover .bijan-cta-1-title' );
		ElementorControls::text_style_controls( $this, '.bijan-cta-1-subtitle', 'subtitle_', esc_html__( "Subtitle", 'bijan' ), '.bijan-cta-1:hover .bijan-cta-1-subtitle' );

		ElementorControls::general_style_controls( $this, [ // img
			'prefix'		=> 'img_',
			'base_selector'	=> '.bijan-cta-1',
			'selector'		=> '.bijan-cta-1-image-wrap img',
			
			'section'	=> [
				'name'	=> 'img_section',
				'label'	=> esc_html__( 'Image', 'bijan' ),
			],

			'mode'	=> 'img',
		] );

		ElementorControls::text_style_controls( $this, '.bijan-cta-1-description', 'description_', esc_html__( "Description", 'bijan' ), '.bijan-cta-1:hover .bijan-cta-1-description' );

		ElementorControls::general_style_controls( $this, [ // button
			'prefix'		=> 'button_',
			'base_selector'	=> '.bijan-cta-1',
			'selector'		=> '.button',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'		=> 'button_section',
				'label'		=> esc_html__( 'Button', 'bijan' ),
				'condition'	=> [
					'show_button'	=> 'yes'
				],
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // button_text
			'prefix'			=> 'button_text_',
			'base_selector'		=> '.bijan-cta-1',
			'selector'			=> '.button-text',
			'hover_selector'	=> '.bijan-cta-1 .button:hover .button-text',
			
			'section'	=> [
				'name'		=> 'button_text_section',
				'label'		=> esc_html__( 'Button text', 'bijan' ),
				'condition'	=> [
					'show_button'	=> 'yes'
				],
			],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		get_template_part( "templates/components/cta", '1', $this->get_settings_for_display() );
	}
}