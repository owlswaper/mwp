<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;
use Bijan\ElementorControls\SectionTitle as ElementorControlsSectionTitle;

class SectionTitle2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_section_title_2';
	}

	public function get_title() {
		return esc_html__( 'Section Title 2 (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['title', 'section', 'عنوان', 'بخش'];
	}

	private function icon_style_controls() {
		$selector = "{{WRAPPER}} .section-title-2-wrap";
		$hover_selector = "{{WRAPPER}} .section-title-2-wrap:hover .section-title-2-icon";
		$prefix = 'section_title_2_icon_';

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( 'Section title icon style', 'bijan' ),
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
		ElementorControlsSectionTitle::settings( $this, [
			'prefix'	=> '',
			'excludes'	=> ['divider'],
			'controls'	=> [
				'link'		=> [
					'separator'	=> '',
				],
				'subtitle'	=> [
					'label'			=> esc_html__( 'Subtitle', 'bijan' ),
					'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
					'label_block'	=> true,
					'type'			=> \Elementor\Controls_Manager::TEXT,
					'default'		=> esc_html__( 'Lorem ipsum', 'bijan' ),
					'ai'			=> [
						'type'		=> 'text',
						'language'	=> 'html',
					],
					'dynamic'		=> [
						'active'	=> true,
					],
				],
				'align'			=> [
					'_responsive'	=> true,
					'label'			=> esc_html__( 'Alignment', 'bijan' ),
					'type'			=> \Elementor\Controls_Manager::CHOOSE,
					'options'		=> [
						'left'		=> [
							'title'	=> esc_html__( 'Left', 'bijan' ),
							'icon'	=> 'eicon-text-align-left',
						],
						'center'	=> [
							'title'	=> esc_html__( 'Center', 'bijan' ),
							'icon'	=> 'eicon-text-align-center',
						],
						'right'		=> [
							'title'	=> esc_html__( 'Right', 'bijan' ),
							'icon'	=> 'eicon-text-align-right',
						],
					],
					'default'		=> is_rtl() ? 'right' : 'left',
				],
			]
		] );

		$this->icon_style_controls();
		ElementorControls::text_style_controls(
			$this,
			'.section-title-2-title',
			'title_',
			__( "Title style", 'bijan' ),
			"{{WRAPPER}} .section-title-2-wrap:hover .section-title-2-title"
		);
		ElementorControls::text_style_controls(
			$this,
			'.section-title-2-subtitle',
			'subtitle_',
			__( "Subtitle style", 'bijan' ),
			"{{WRAPPER}} .section-title-2-wrap:hover .section-title-2-subtitle"
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
	
		get_template_part( "templates/components/section_title", "2", $settings );
	}
}