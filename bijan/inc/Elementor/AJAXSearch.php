<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;

class AJAXSearch extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_ajax_search';
	}

	public function get_title() {
		return esc_html__( 'AJAX Search (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['search', 'ajax', 'جستجو', 'سرچ'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // placeholder
			"placeholder",
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Placeholder', 'bijan' ),
				'label_block'	=> true,
				'default'		=> esc_html__( 'Search...', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // show_icon
			"show_icon",
			[
				'label'			=> esc_html__( "Show search icon", 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // icon
			"icon",
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'bijan' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'bijan-icon-search-normal',
					'library'	=> 'bijan-icon'
				],
				'condition'		=> [
					'show_icon'	=> 'yes'
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // form_
			'prefix'		=> 'form_',
			'base_selector'	=> '.bijan-search',
			
			'section'	=> [
				'name'	=> 'form_section',
				'label'	=> esc_html__( 'Form style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // input_
			'prefix'		=> 'input_',
			'base_selector'	=> '.bijan-search-field',
			
			'section'	=> [
				'name'	=> 'input_section',
				'label'	=> esc_html__( 'Input style', 'bijan' ),
			],

			'controls'	=> [
				'placeholder_color'	=> [
					'label'		=> esc_html__( 'Placeholder color', 'bijan' ),
					'type'		=> \Elementor\Controls_Manager::COLOR,
					'selectors'	=> [
						'{{WRAPPER}} .bijan-search-field::placeholder'	=> 'color: {{VALUE}};'
					],
				],
			],
			'excludes'	=> ['text_shadow'],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_
			'prefix'		=> 'icon_',
			'base_selector'	=> '.bijan-search',
			'selector'		=> '.bijan-search-icon',
			
			'section'	=> [
				'name'	=> 'icon_section',
				'label'	=> esc_html__( 'Icon style', 'bijan' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // popover_
			'prefix'		=> 'popover_',
			'base_selector'	=> '.bijan-popover',
			
			'section'	=> [
				'name'	=> 'popover_section',
				'label'	=> esc_html__( 'Popover style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/ajax-search", null, $settings );
	}
}