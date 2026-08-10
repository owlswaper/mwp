<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;
use Bijan\Utils;
use Bijan\Utils\Elementor;

class Ribbon extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_ribbon';
	}

	public function get_title() {
		return esc_html__( 'Ribbon (Bijan)', 'bijan' );
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
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ) . '<br>' . esc_html__( "To color a portion of text, enclose the text in { and }. Example: {percentage}", 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> __( 'Lorem {ipsum}', 'bijan' ),
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
				'label'			=> esc_html__( 'Title tag', 'bijan' ),
				'label_block'	=> true,
				'default'		=> 'h2',
				'options'		=> Utils::custom_tags()
			]
		);

		$this->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'bijan' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'library'	=> 'bijan-icon',
					'value'		=> 'bijan-icon-discount-shape',
				],
			]
		);

		$this->add_control( // link
			'link',
			[
				'label'			=> esc_html__( 'Link', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::URL,
				'label_block'	=> true,
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		
		ElementorControls::general_style_controls( $this, [ // wrap_
			'prefix'	=> 'wrap_',
			'selector'	=> '.bijan-ribbon',
			
			'section'	=> [
				'name'	=> 'wrap_section',
				'label'	=> esc_html__( 'Wrap', 'bijan' ),
			],

			'excludes'	=> ['text_align'],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // triangles_
			'prefix'			=> 'triangles_',
			'base_selector'		=> '.bijan-ribbon',
			'selector'			=> '.bijan-ribbon-triangles::after, {{WRAPPER}} .bijan-ribbon .bijan-ribbon-triangles::before',
			'hover_selector'	=> '{{WRAPPER}} .bijan-ribbon:hover .bijan-ribbon-triangles::after, {{WRAPPER}} .bijan-ribbon:hover .bijan-ribbon-triangles::before',
			
			'section'	=> [
				'name'	=> 'triangles_section',
				'label'	=> esc_html__( 'Triangles', 'bijan' ),
			],

			'excludes'	=> ['text_align', 'padding', 'margin'],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // ribbon_
			'prefix'		=> 'ribbon_',
			'base_selector'	=> '.bijan-ribbon',
			'selector'		=> '.bijan-ribbon-content',
			
			'section'	=> [
				'name'	=> 'ribbon_section',
				'label'	=> esc_html__( 'Ribbon', 'bijan' ),
			],

			'excludes'	=> ['margin', 'text_align'],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // icon_
			'prefix'		=> 'icon_',
			'base_selector'	=> '.bijan-ribbon',
			'selector'		=> '.bijan-ribbon-icon',
			
			'section'	=> [
				'name'	=> 'icon_section',
				'label'	=> esc_html__( 'Icon', 'bijan' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // text_
			'prefix'		=> 'text_',
			'base_selector'	=> '.bijan-ribbon',
			'selector'		=> '.bijan-ribbon-title',
			
			'section'	=> [
				'name'	=> 'text_section',
				'label'	=> esc_html__( 'Text', 'bijan' ),
			],

			'excludes'	=> ['text_align'],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // text_special_
			'prefix'		=> 'text_special_',
			'base_selector'	=> '.bijan-ribbon',
			'selector'		=> '.bijan-ribbon-title span',
			
			'section'	=> [
				'name'	=> 'text_special_section',
				'label'	=> esc_html__( 'Special text', 'bijan' ),
			],

			'excludes'	=> ['margin', 'text_align'],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		get_template_part( "templates/components/ribbon", null, $this->get_settings_for_display() );
	}
}