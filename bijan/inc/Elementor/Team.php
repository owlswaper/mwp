<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;

class Team extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_team';
	}

	public function get_title() {
		return esc_html__( 'Team (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['team', 'user', 'person', 'تیم', 'کاربر', "اعضا"];
	}

	private function items_controls() {
		$this->start_controls_section( // content_section
			'items_section',
			[
				'label'	=> esc_html__( 'Items', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Image', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // name
			'name',
			[
				'label'			=> esc_html__( "Name", 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label_block'	=> true,
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // position
			'position',
			[
				'label'			=> esc_html__( "Position", 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label_block'	=> true,
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // link
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

		$this->add_control( // items
			'items',
			[
				'label'			=> __( "Items", 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::REPEATER,
				'fields'		=> $repeater->get_controls(),
				'default'		=> [
					[
						'img'		=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'link'	=> [
							'url'	=> home_url(),
						],
					],
					[
						'img'		=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'link'	=> [
							'url'	=> home_url(),
						],
					],
				],
			]
		);

		$this->end_controls_section();
	}

	private function image_style_controls() {
		$selector = "{{WRAPPER}} .team-item img";
		$hover_selector = "{{WRAPPER}} .team-item:hover img";
		$prefix = 'image_';

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( 'Image style', 'bijan' ),
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

		ElementorControls::background( $this, "{$prefix}background", $selector );
		ElementorControls::border( $this, "{$prefix}border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow", $selector );
		ElementorControls::css_filters( $this, "{$prefix}css_filters", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label'	=> esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::background( $this, "{$prefix}background_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow_hover", $hover_selector );
		ElementorControls::css_filters( $this, "{$prefix}css_filters_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_controls();
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_slider'		=> [
					'default'	=> 'yes',
				],
				'desktop_slides'	=> [
					'default'	=> 6
				],
				'tablet_slider'		=> [
					'default'	=> 'yes',
				],
				'mobile_slider'		=> [
					'default'	=> 'yes',
				],
			]
		] );

		$this->image_style_controls();
		ElementorControls::general_style_controls( $this, [ // texts_wrap
			'prefix'		=> 'texts_wrap_',
			'base_selector'	=> '.team-item',
			'selector'		=> '.team-item-texts',
			
			'section'	=> [
				'name'	=> 'texts_wrap_section',
				'label'	=> esc_html__( 'Texts wrap style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::text_style_controls( $this, '.team-item-name', 'name_', esc_html__( "Name style", 'bijan' ), '{{WRAPPER}} .team-item:hover .team-item-name' );
		ElementorControls::text_style_controls( $this, '.team-item-position', 'position_', esc_html__( "Position style", 'bijan' ), '{{WRAPPER}} .team-item:hover .team-item-position' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/team", null, $settings );
	}
}