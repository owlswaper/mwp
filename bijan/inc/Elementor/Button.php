<?php
namespace Bijan\Elementor;

use Bijan\Utils\Elementor;
use Bijan\ElementorControls;
use Bijan\ElementorControls\Button as ElementorControlsButton;

class Button extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_button';
	}

	public function get_title() {
		return esc_html__( 'Button (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['button', 'link', 'دکمه', 'باتن', 'لینک',];
	}

	private function button_style_controls() {
		$selector = "{{WRAPPER}} .button";
		$hover_selector = "{$selector}:hover";

		$this->start_controls_section(
			'style_button_section',
			[
				'label'	=> esc_html__( 'Button style', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab( // Normal
			'tab_button_normal',
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, 'button_margin', $selector );
		ElementorControls::padding( $this, 'button_padding', $selector );
		ElementorControls::typography( $this, 'button_typography', $selector );
		ElementorControls::color( $this, 'button_color', $selector );
		ElementorControls::background( $this, 'button_background', $selector );
		ElementorControls::border( $this, 'button_border', $selector );
		ElementorControls::border_radius( $this, 'button_border_radius', $selector );
		ElementorControls::box_shadow( $this, 'button_shadow', $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, 'button_hover_margin', $hover_selector );
		ElementorControls::padding( $this, 'button_hover_padding', $hover_selector );
		ElementorControls::typography( $this, 'button_hover_typography', $hover_selector );
		ElementorControls::color( $this, 'button_hover_color', $hover_selector );
		ElementorControls::background( $this, 'button_hover_background', $hover_selector );
		ElementorControls::border( $this, 'button_hover_border', $hover_selector );
		ElementorControls::border_radius( $this, 'button_hover_border_radius', $hover_selector );
		ElementorControls::box_shadow( $this, 'button_hover_shadow', $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function text_style_controls( $label, $type ) {
		$selector = "{{WRAPPER}} .button-{$type}";
		$hover_selector = "{{WRAPPER}} .button:hover .button-{$type}";

		$this->start_controls_section(
			"style_button_{$type}_section",
			[
				'label'	=> sprintf( esc_html__( '%s style', 'bijan' ), $label ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( "tabs_button_{$type}_style" );

		$this->start_controls_tab( // Normal
			"tab_button_{$type}_normal",
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, "button_{$type}_margin", $selector );
		ElementorControls::padding( $this, "button_{$type}_padding", $selector );
		ElementorControls::typography( $this, "button_{$type}_typography", $selector );
		ElementorControls::color( $this, "button_{$type}_color", $selector );
		ElementorControls::background( $this, "button_{$type}_background", $selector );
		ElementorControls::border( $this, "button_{$type}_border", $selector );
		ElementorControls::border_radius( $this, "button_{$type}_border_radius", $selector );
		ElementorControls::box_shadow( $this, "button_{$type}_shadow", $selector );
		ElementorControls::text_shadow( $this, "button_{$type}_text_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_button_{$type}_hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, "button_{$type}_hover_margin", $hover_selector );
		ElementorControls::padding( $this, "button_{$type}_hover_padding", $hover_selector );
		ElementorControls::typography( $this, "button_{$type}_hover_typography", $hover_selector );
		ElementorControls::color( $this, "button_{$type}_hover_color", $hover_selector );
		ElementorControls::background( $this, "button_{$type}_hover_background", $hover_selector );
		ElementorControls::border( $this, "button_{$type}_hover_border", $hover_selector );
		ElementorControls::border_radius( $this, "button_{$type}_hover_border_radius", $hover_selector );
		ElementorControls::box_shadow( $this, "button_{$type}_hover_shadow", $hover_selector );
		ElementorControls::text_shadow( $this, "button_{$type}_hover_text_shadow", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_controls() {
		ElementorControlsButton::settings( $this );
		$this->button_style_controls();
		$this->text_style_controls( __( 'Icon', 'bijan' ), 'icon' );
		$this->text_style_controls( __( 'Text', 'bijan' ), 'text' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/button", null, Elementor::get_button_args( $settings, '' ) );
	}
}