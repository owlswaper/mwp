<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls\Slider;
use Bijan\Utils;
use Bijan\ElementorControls;

class ThumbnailSlider extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_thumbnail_slider';
	}

	public function get_title() {
		return esc_html__( 'Thumbnail Slider (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-post-slider';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['slider', 'slide', 'اسلایدر', 'اسلاید'];
	}

	private function slides_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Slides', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Image', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'description'	=> esc_html__( 'Image height: 358px', 'bijan' ),
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // img_link
			'img_link',
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
						'img_link'	=> [
							'url'	=> home_url(),
						],
					],
					[
						'img'		=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'img_link'	=> [
							'url'	=> home_url(),
						],
					],
				],
			]
		);

		$this->end_controls_section();
	}

	private function slider_controls() {
		$this->start_controls_section( // content_section
			'slider_section',
			[
				'label'	=> esc_html__( 'Slider settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		Slider::autoplay_controls( $this );

		$this->add_control( // loop
			'loop',
			[
				'label'			=> esc_html__( 'Loop', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // show_scrollbar
			'show_scrollbar',
			[
				'label'			=> esc_html__( 'Show scrollbar', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // desktop_slider_height
			'desktop_slider_height',
			[
				'label'		=> esc_html__( "Desktop slider height (px)", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 100,
				'default'	=> 360,
				'selectors'	=> [
					'{{WRAPPER}} .bijan-thumbnail-slider-wrap'	=> 'height: {{VALUE}}px',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->slides_controls();
		$this->slider_controls();

		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_slides'	=> [
					'default'	=> 4,
				],
				'desktop_slides_space'	=> [
					'default'	=> 12,
				],
				'tablet_slides'	=> [
					'default'	=> 4,
					'separator'	=> 'before',
				],
				'tablet_slides_space'	=> [
					'default'	=> 12,
				],
				'mobile_slides'	=> [
					'default'	=> 4,
					'separator'	=> 'before',
				],
				'mobile_slides_space'	=> [
					'default'	=> 12,
				],
				'grayscale_thumbs'	=> [
					'_position'		=> 9999,
					'label'			=> esc_html__( "Grayscale thumbnails", 'bijan' ),
					'type'			=> \Elementor\Controls_Manager::SWITCHER,
					'label_on'		=> esc_html__( 'Yes', 'bijan' ),
					'label_off'		=> esc_html__( 'No', 'bijan' ),
					'return_value'	=> 'yes',
					'default'		=> 'yes',
					'separator'		=> 'before',
				],
			],
			'excludes'	=> ['desktop_slider', 'desktop_slides_type', 'desktop_cols','tablet_slider', 'tablet_slides_type', 'tablet_cols', 'mobile_slider', 'mobile_slides_type', 'mobile_cols'],
			'section'	=> [
				'name'	=> 'display_thumbnails_settings_section',
				'label'	=> esc_html__( 'Thumbnails settings', 'bijan' ),
			],
		] );

		ElementorControls::general_style_controls( $this, [ // thumbnail_slider_main_image_
			'prefix'			=> 'thumbnail_slider_main_image_',
			'base_selector'		=> '.bijan-thumbnail-slider-wrap',
			'selector'			=> '.bijan-main-slider img',
			'hover_type'		=> 'normal',

			'section'	=> [
				'name'	=> 'thumbnail_slider_main_image_styling',
				'label'	=> esc_html__( 'Main image style', 'bijan' ),
			],

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // thumbnail_slider_thumbnail_image_
			'prefix'			=> 'thumbnail_slider_thumbnail_image_',
			'base_selector'		=> '.bijan-thumbnail-slider-wrap',
			'selector'			=> '.bijan-thumbnail-slider img',
			'hover_type'		=> 'normal',

			'section'	=> [
				'name'	=> 'thumbnail_slider_thumbnail_image_styling',
				'label'	=> esc_html__( 'Thumbnail image style', 'bijan' ),
			],

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // thumbnail_slider_scrollbar_
			'prefix'		=> 'thumbnail_slider_scrollbar_',
			'base_selector'	=> '.swiper-scrollbar',

			'section'	=> [
				'name'		=> 'thumbnail_slider_scrollbar_styling',
				'label'		=> esc_html__( 'Scrollbar style', 'bijan' ),
				'condition'	=> [
					'show_scrollbar'	=> 'yes',
				]
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // thumbnail_slider_scrollbar_track_
			'prefix'		=> 'thumbnail_slider_scrollbar_track_',
			'base_selector'	=> '.swiper-scrollbar',

			'section'	=> [
				'name'		=> 'thumbnail_slider_scrollbar_track_styling',
				'label'		=> esc_html__( 'Scrollbar track style', 'bijan' ),
				'condition'	=> [
					'show_scrollbar'	=> 'yes',
				]
			],

			'mode'	=> 'wrap',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = [
			'autoplay'				=> Utils::to_bool( $settings['autoplay'] ) && !empty( absint( $settings['autoplay_time'] ) ) ? absint( $settings['autoplay_time'] ) : 0,
			'loop'					=> Utils::to_bool( $settings['loop'] ),
			'items'					=> [],
			'grayscale_thumbs'		=> Utils::to_bool( $settings['grayscale_thumbs'] ),
			'show_scrollbar'		=> Utils::to_bool( $settings['show_scrollbar'] ),
			'desktop_slides'		=> Utils::convert_chars( $settings['desktop_slides'], true, 'absint' ),
			'desktop_slides_space'	=> Utils::convert_chars( $settings['desktop_slides_space'], true, 'absint' ),
			'tablet_slides'			=> Utils::convert_chars( $settings['tablet_slides'], true, 'absint' ),
			'tablet_slides_space'	=> Utils::convert_chars( $settings['tablet_slides_space'], true, 'absint' ),
			'mobile_slides'			=> Utils::convert_chars( $settings['mobile_slides'], true, 'absint' ),
			'mobile_slides_space'	=> Utils::convert_chars( $settings['mobile_slides_space'], true, 'absint' ),
		];

		if( !empty( $settings['items'] ) ) {
			foreach( $settings['items'] as $item ) {
				$args['items'][] = [
					'img'	=> !empty( $item['img']['id'] ) ? $item['img']['id'] : $item['img']['url'],
					'link'	=> $item['img_link'],
				];
			}
		}

		get_template_part( "templates/components/thumbnail_slider", null, $args );
	}
}