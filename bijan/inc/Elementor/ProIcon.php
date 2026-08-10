<?php
namespace Bijan\Elementor;

use Bijan\Utils;
use Bijan\ElementorControls;

class ProIcon extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_pro_icon';
	}

	public function get_title() {
		return esc_html__( 'Pro Icon (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-toggle';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['icon', 'image', 'آیکون', 'آیکن', 'تصویر', 'عکس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // icon_type
			'icon_type',
			[
				'label'		=> esc_html__( 'Icon type', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'options'	=> [
					'image'	=> [
						'title'	=> esc_html__( 'Image', 'bijan' ),
						'icon'	=> 'eicon-image',
					],
					'icon'	=> [
						'title'	=> esc_html__( 'Icon', 'bijan' ),
						'icon'	=> 'eicon-posts-ticker',
					],
				],
				'default'	=> 'image',
				'toggle'	=> false,
			]
		);

		$this->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Image', 'bijan' ),
				'description'	=> esc_html__( 'Size: 72px*72px', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition'		=> [
					'icon_type'	=> 'image'
				],
			]
		);

		$this->add_control( // icon
			'icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'bijan' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'condition'		=> [
					'icon_type'	=> 'icon'
				],
			]
		);

		$this->add_control( // icon_align
			'icon_align',
			[
				'label'		=> esc_html__( 'Icon align', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::CHOOSE,
				'options'	=> [
					'top'	=> [
						'title'	=> esc_html__( 'Top', 'bijan' ),
						'icon'	=> 'eicon-justify-start-v',
					],
					'center'	=> [
						'title'	=> esc_html__( 'Center', 'bijan' ),
						'icon'	=> 'eicon-justify-center-v',
					],
					'bottom'	=> [
						'title'	=> esc_html__( 'Bottom', 'bijan' ),
						'icon'	=> 'eicon-justify-end-v',
					],
				],
				'default'	=> 'center',
				'toggle'	=> false,
				'condition'	=> [
					'icon_type'	=> 'icon'
				]
			]
		);

		$this->add_control( // title
			'title',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Title', 'bijan' ),
				'label_block'	=> true,
				'default'		=> __( 'Title', 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // tag
			'tag',
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Title tag', 'bijan' ),
				'label_block'	=> true,
				'default'		=> 'div',
				'options'		=> Utils::custom_tags(),
			]
		);

		$this->add_control( // subtitle
			'subtitle',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Subtitle', 'bijan' ),
				'label_block'	=> true,
				'default'		=> __( 'Subtitle', 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
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
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // hover_effect
			'hover_effect',
			[
				'label'			=> esc_html__( 'Hover effect', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	private function icon_wrap_style_controls() {
		$selector = "{{WRAPPER}} .proicon-img-wrap";
		$hover_selector = "{{WRAPPER}} .proicon:hover .proicon-img-wrap";
		$prefix = 'icon_wrap_';

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( 'Icon wrap style', 'bijan' ),
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

		ElementorControls::margin( $this, "{$prefix}margin", $selector );
		ElementorControls::padding( $this, "{$prefix}padding", $selector );
		ElementorControls::background( $this, "{$prefix}background", $selector );
		ElementorControls::border( $this, "{$prefix}border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, "{$prefix}margin_hover", $hover_selector );
		ElementorControls::padding( $this, "{$prefix}padding_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function image_style_controls() {
		$selector = "{{WRAPPER}} .proicon-img-wrap img";
		$hover_selector = "{{WRAPPER}} .proicon:hover .proicon-img-wrap img";
		$prefix = 'image_';

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'		=> esc_html__( 'Image style', 'bijan' ),
				'tab'		=> \Elementor\Controls_Manager::TAB_STYLE,
				'condition'	=> [
					'icon_type'	=> 'image'
				]
			]
		);

		$this->start_controls_tabs( "tabs_{$prefix}style" );

		$this->start_controls_tab( // Normal
			"tab_{$prefix}normal",
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, "{$prefix}margin", $selector );
		ElementorControls::padding( $this, "{$prefix}padding", $selector );
		ElementorControls::background( $this, "{$prefix}background", $selector );
		ElementorControls::border( $this, "{$prefix}border", $selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius", $selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow", $selector );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::margin( $this, "{$prefix}margin_hover", $hover_selector );
		ElementorControls::padding( $this, "{$prefix}padding_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function icon_style_controls() {
		$selector = "{{WRAPPER}} .proicon .proicon-icon";
		$hover_selector = "{{WRAPPER}} .proicon:hover .proicon-icon";
		$prefix = 'icon_';

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'		=> esc_html__( 'Icon style', 'bijan' ),
				'tab'		=> \Elementor\Controls_Manager::TAB_STYLE,
				'condition'	=> [
					'icon_type'	=> 'icon'
				],
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
		$this->settings_controls();

		$this->icon_wrap_style_controls();
		$this->image_style_controls();
		$this->icon_style_controls();
		ElementorControls::text_style_controls( $this, '.proicon-title', 'title_', __( "Title style", 'bijan' ), "{{WRAPPER}} .proicon:hover .proicon-title" );
		ElementorControls::text_style_controls( $this, '.proicon-subtitle', 'subtitle_', __( "Subtitle style", 'bijan' ), "{{WRAPPER}} .proicon:hover .proicon-subtitle" );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/proicon", null, $settings );
	}
}