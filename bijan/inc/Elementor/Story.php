<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls\Slider;
use Bijan\Utils\Archive;
use Bijan\Utils\Elementor;
use Bijan\Utils\Story as UtilsStory;
use Bijan\ElementorControls;

class Story extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_story';
	}

	public function get_title() {
		return esc_html__( 'Story (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-info-circle-o';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['story', 'instagram', 'استوری', 'اینستاگرام'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Items', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // query_type
			'query_type',
			[
				'label'		=> esc_html__( 'Query type', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'latests',
				'options'	=> [
					'latests'	=> esc_html__( 'Latests stories', 'bijan' ),
					'by_id'		=> esc_html__( 'Manual Selection', 'bijan' ),
				],
			]
		);

		$this->add_control( // items
			'items',
			[
				'label'			=> esc_html__( "Search & Select", 'bijan' ),
				'description'	=> esc_html__( 'Select posts that you want to include', 'bijan' ),
				'label_block'	=> true,
				'multiple'		=> true,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'		=> [
						'post_type'	=> 'story',
					],
				],
				'condition'		=> [
					'query_type'	=> 'by_id'
				]
			]
		);

		$this->add_control( // grayscale
			'grayscale',
			[
				'label'			=> esc_html__( 'Grayscale images', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // ppp
			'ppp',
			[
				'label'		=> esc_html__( 'Count', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 12,
				'condition'	=> [
					'query_type'	=> 'latests'
				],
			]
		);

		$this->add_control( // offset
			'offset',
			[
				'label'		=> esc_html__( 'Offset', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 0,
				'condition'	=> [
					'query_type'	=> 'latests'
				],
			]
		);

		$this->add_control( // orderby
			'orderby',
			[
				'label'		=> esc_html__( 'Order By', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'post_date',
				'options'	=> Archive::order_by( false, ['widget', 'comment_count'] ),
			]
		);

		$this->add_control( // order
			'order',
			[
				'label'		=> esc_html__( 'Order', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'multiple'	=> false,
				'default'	=> 'desc',
				'options'	=> [
					'asc'	=> esc_html__( 'ASC', 'bijan' ),
					'desc'	=> esc_html__( 'DESC', 'bijan' ),
				],
			]
		);

		$this->end_controls_section();
	}

	private function image_style_controls() {
		$selector = "{{WRAPPER}} .story-item-small-img img";
		$hover_selector = "{{WRAPPER}} .story-item:hover .story-item-small-img img";
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

		ElementorControls::padding( $this, "{$prefix}padding", $selector );
		ElementorControls::margin( $this, "{$prefix}margin", $selector );
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

		ElementorControls::padding( $this, "{$prefix}padding_hover", $hover_selector );
		ElementorControls::margin( $this, "{$prefix}margin_hover", $hover_selector );
		ElementorControls::background( $this, "{$prefix}background_hover", $hover_selector );
		ElementorControls::border( $this, "{$prefix}border_hover", $hover_selector );
		ElementorControls::border_radius( $this, "{$prefix}border_radius_hover", $hover_selector );
		ElementorControls::box_shadow( $this, "{$prefix}box_shadow_hover", $hover_selector );
		ElementorControls::css_filters( $this, "{$prefix}css_filters_hover", $hover_selector );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function item_border_style_controls() {
		$selector = "{{WRAPPER}} .story-item-small-img::after";
		$hover_selector = "{{WRAPPER}} .story-item:hover .story-item-small-img::after";
		$prefix = 'item_border_';

		$this->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> esc_html__( 'Story border style', 'bijan' ),
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

		ElementorControls::background( $this, "{$prefix}background", $selector, [
			'types'	=> ['classic', 'gradient']
		] );

		$this->end_controls_tab();

		$this->start_controls_tab( // Hover
			"tab_{$prefix}hover",
			[
				'label'	=> esc_html__( 'Hover', 'bijan' ),
			]
		);

		ElementorControls::background( $this, "{$prefix}background_hover", $hover_selector, [
			'types'	=> ['classic', 'gradient']
		] );

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		Slider::settings_controls( $this, [
			'controls'	=> [
				'desktop_slides'	=> [
					'default'	=> 12
				],
				'tablet_slides_type'	=> [
					'default'	=> 'auto'
				],
				'tablet_slides'	=> [
					'default'	=> 8
				],
				'mobile_slides_type'	=> [
					'default'	=> 'auto'
				],
				'mobile_slides'	=> [
					'default'	=> 4
				],
			],
			'excludes'	=> [
				'autoplay',
				'autoplay_time',
				'show_arrows',
				'loop'
			],
		] );

		$this->image_style_controls();
		ElementorControls::text_style_controls( $this, '.story-item-title', 'title', esc_html__( 'Item title', 'bijan' ), '{{WRAPPER}} .story-item:hover .story-item-title' );
		$this->item_border_style_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$args = [
			'items'				=> [],
			'grayscale'			=> $settings['grayscale'],
			'desktop_slides_type'	=> $settings['desktop_slides_type'],
			'desktop_slide_count'	=> $settings['desktop_slides'],
			'tablet_slides_type'	=> $settings['tablet_slides_type'],
			'tablet_slide_count'	=> $settings['tablet_slides'],
			'mobile_slides_type'	=> $settings['mobile_slides_type'],
			'mobile_slide_count'	=> $settings['mobile_slides'],
		];

		$posts_args = [
			'post_type'	=> 'story',
			'orderby'	=> $settings['orderby'],
			'order'		=> $settings['order'],
		];
		if( $settings['query_type'] == 'by_id' ) {
			$posts_args['post__in'] = $settings['items'];
		} else {
			$posts_args['numberposts'] = $settings['ppp'];
			$posts_args['offset'] = $settings['offset'];
		}
		$posts = get_posts( $posts_args );
		if( !empty( $posts ) ) {
			$args['items'] = array_map( fn( $post ) => UtilsStory::get( $post ), $posts );
		}

		get_template_part( "templates/components/story", null, $settings );
	}
}