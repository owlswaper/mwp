<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;

class MarketButton extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_market_button';
	}

	public function get_title() {
		return esc_html__( 'Market button (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-dual-button';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['button', 'market', 'app', 'mobile', 'icon', 'image', 'آیکون', 'آیکن', 'تصویر', 'عکس', "دکمه"];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		ElementorControls::market_button_controls( $this, [
			'prefix'	=> '',
		] );

		$this->end_controls_section();
	}

	private function button_styling_control() {
		$selector = "{{WRAPPER}} .market-button";
		$hover_selector = "{$selector}:hover";
		$prefix = 'market-button_';

		$this->start_controls_section(
			"tab_{$prefix}style_section",
			[
				'label'	=> esc_html__( 'Button style', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( "tab_{$prefix}style_tabs" );

		$this->start_controls_tab( // Normal
			"tab_{$prefix}normal",
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);
		
		ElementorControls::margin( $this, "{$prefix}_margin", $selector );
		ElementorControls::padding( $this, "{$prefix}_padding", $selector );
		ElementorControls::background( $this, "{$prefix}_background", $selector );
		ElementorControls::border( $this, "{$prefix}_border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}_border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}_box_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}_hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, "{$prefix}_margin_hover", $hover_selector );
		ElementorControls::padding( $this, "{$prefix}_padding_hover", $hover_selector );
		ElementorControls::background( $this, "{$prefix}_background_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}_border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}_border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}_box_shadow_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function image_styling_control() {
		$selector = "{{WRAPPER}} .market-button-icon-wrap img";
		$hover_selector = "{{WRAPPER}} .market-button:hover .market-button-icon-wrap img";
		$prefix = 'image_';

		$this->start_controls_section(
			"tab_{$prefix}style_section",
			[
				'label'	=> esc_html__( 'Image style', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( "tab_{$prefix}style_tabs" );

		$this->start_controls_tab( // Normal
			"tab_{$prefix}normal",
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, "{$prefix}_margin", $selector );
		ElementorControls::padding( $this, "{$prefix}_padding", $selector );
		ElementorControls::border( $this, "{$prefix}_border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}_border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}_box_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}_hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, "{$prefix}_margin_hover", $hover_selector );
		ElementorControls::padding( $this, "{$prefix}_padding_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}_border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}_border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}_box_shadow_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		$this->button_styling_control();
		$this->image_styling_control();
		ElementorControls::text_style_controls(
			$this,
			'.market-button-top-text',
			'top_text_',
			__( "Top text style", 'bijan' ),
			"{{WRAPPER}} .market-button:hover .market-button-top-text"
		);
		ElementorControls::text_style_controls(
			$this,
			'.market-button-text',
			'text_',
			__( "Top text style", 'bijan' ),
			"{{WRAPPER}} .market-button:hover .market-button-text"
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$args = [
			'market'		=> $settings['market'],
			'custom_icon'	=> !empty( $settings['custom_icon'] ) ? $settings['custom_icon']['id'] : 0,
			'top_text'		=> $settings['top_text'],
			'text'			=> $settings['text'],
			'link'			=> $settings['link'],
			'size'			=> $settings['size'],
		];
		get_template_part( "templates/components/market_button", null, $args );
	}
}