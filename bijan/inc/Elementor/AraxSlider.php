<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;
use Bijan\ElementorControls\Slider;

class AraxSlider extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_arax_slider';
	}

	public function get_title() {
		return esc_html__( 'Arax Slider (Bijan)', 'bijan' );
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

	private function settings_controls() {
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
				'description'	=> esc_html__( 'Image size: 444px*444px', 'bijan' ),
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // thumb_img
			'thumb_img',
			[
				'label'			=> esc_html__( 'Thumbnail Image', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'description'	=> esc_html__( 'Image size: 80px*80px', 'bijan' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$repeater->add_control( // thumb_text
			'thumb_text',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Thumbnail text', 'bijan' ),
				'label_block'	=> true,
				'dynamic'		=> [
					'active'	=> true,
				],
				'ai'				=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
			]
		);

		$repeater->add_control( // show_badge
			'show_badge',
			[
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label'			=> esc_html__( 'Show badge', 'bijan' ),
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'separator'		=> 'before',
				'dynamic'		=> [
					'active'	=> true,
				],
				'ai'				=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
			]
		);

		$repeater->add_control( // badge_top_text
			'badge_top_text',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Badge top text', 'bijan' ),
				'label_block'	=> true,
				'dynamic'		=> [
					'active'	=> true,
				],
				'ai'				=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'condition'		=> [
					'show_badge'	=> 'yes'
				]
			]
		);
		
		$repeater->add_control( // badge_bottom_text
			'badge_bottom_text',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Badge bottom text', 'bijan' ),
				'label_block'	=> true,
				'dynamic'		=> [
					'active'	=> true,
				],
				'ai'				=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'condition'		=> [
					'show_badge'	=> 'yes'
				]
			]
		);

		$repeater->add_control( // badge_position
			'badge_position',
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Badge position', 'bijan' ),
				'label_block'	=> true,
				'separator'		=> 'after',
				'default'		=> 'center-center',
				'options'		=> [
					'top-start'		=> esc_html__( 'Top - Start', 'bijan' ),
					'top-center'	=> esc_html__( 'Top - Center', 'bijan' ),
					'top-end'		=> esc_html__( 'Top - End', 'bijan' ),

					'center-start'	=> esc_html__( 'Center - Start', 'bijan' ),
					'center-center'	=> esc_html__( 'Center - Center', 'bijan' ),
					'center-end'	=> esc_html__( 'Center - End', 'bijan' ),

					'bottom-start'	=> esc_html__( 'Bottom - Start', 'bijan' ),
					'bottom-center'	=> esc_html__( 'Bottom - Center', 'bijan' ),
					'bottom-end'	=> esc_html__( 'Bottom - End', 'bijan' ),
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'ai'				=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'condition'		=> [
					'show_badge'	=> 'yes'
				]
			]
		);

		$repeater->add_control( // badge_rotate
			'badge_rotate',
			[
				'type'			=> \Elementor\Controls_Manager::NUMBER,
				'label'			=> esc_html__( 'Badge rotate', 'bijan' ),
				'min'			=> -359,
				'max'			=> 360,
				'default'		=> is_rtl() ? -15 : 15,
				'dynamic'		=> [
					'active'	=> true,
				],
				'ai'				=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'condition'		=> [
					'show_badge'	=> 'yes'
				]
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
						'img'				=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'thumb_text'		=> esc_html__( "20% discount", 'bijan' ),
						'show_badge'		=> 'yes',
						'badge_top_text'	=> '7%',
						'badge_bottom_text'	=> esc_html__( "Discount", 'bijan' ),
						'link'				=> [
							'url'	=> home_url()
						]
					],
					[
						'img'				=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'thumb_text'		=> esc_html__( "20% discount", 'bijan' ),
						'show_badge'		=> 'yes',
						'badge_top_text'	=> '7%',
						'badge_bottom_text'	=> esc_html__( "Discount", 'bijan' ),
						'link'				=> [
							'url'	=> home_url()
						]
					],
					[
						'img'				=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'thumb_text'		=> esc_html__( "20% discount", 'bijan' ),
						'show_badge'		=> 'yes',
						'badge_top_text'	=> '7%',
						'badge_bottom_text'	=> esc_html__( "Discount", 'bijan' ),
						'link'				=> [
							'url'	=> home_url()
						]
					],
					[
						'img'				=> [
							'url'	=> \Elementor\Utils::get_placeholder_image_src(),
						],
						'thumb_text'		=> esc_html__( "20% discount", 'bijan' ),
						'show_badge'		=> 'yes',
						'badge_top_text'	=> '7%',
						'badge_bottom_text'	=> esc_html__( "Discount", 'bijan' ),
						'link'				=> [
							'url'	=> home_url()
						]
					],
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		Slider::options_controls( $this, [
			'controls'	=> [
				'next_arrow_icon'	=> [
					'default'	=> [
						'library'	=> 'bijan-icon',
						'value'		=> 'bijan-icon-arrow-left-4',
					]
				],
				'prev_arrow_icon'	=> [
					'default'	=> [
						'library'	=> 'bijan-icon',
						'value'		=> 'bijan-icon-arrow-right-4',
					]
				],
				'thumb_desktop_slides'	=> [
					'label'		=> esc_html__( "Desktop visible slides thumbnails", 'bijan' ),
					'type'		=> \Elementor\Controls_Manager::NUMBER,
					'min'		=> 1,
					'default'	=> 4,
					'separator'	=> 'before',
				],
				'badge_img'	=> [
					'label'			=> esc_html__( 'Badge image', 'bijan' ),
					'type'			=> \Elementor\Controls_Manager::MEDIA,
					'default'		=> [
						'url'	=> BIJAN_URI . "assets/img/slider-badge.svg",
					],
					'dynamic'		=> [
						'active'	=> true,
					],
				],
			],
			'excludes'	=> ['show_dots']
		] );

		ElementorControls::general_style_controls( $this, [ // main_slider_wrap_
			'prefix'	=> 'main_slider_wrap_',
			'selector'	=> '.arax-slider-main-wrap',
			
			'section'	=> [
				'name'	=> 'main_slider_wrap_section',
				'label'	=> esc_html__( 'Main slider wrap', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // main_slider_item_wrap_
			'prefix'	=> 'main_slider_item_wrap_',
			'selector'	=> '.arax-slider-item',
			
			'section'	=> [
				'name'	=> 'main_slider_item_wrap_section',
				'label'	=> esc_html__( 'Main slider item wrap', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // main_slider_image_
			'prefix'		=> 'main_slider_image_',
			'base_selector'	=> '.arax-slider-item',
			'selector'		=> '>img',
			
			'section'	=> [
				'name'	=> 'main_slider_image_section',
				'label'	=> esc_html__( 'Main slider image', 'bijan' ),
			],

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_badge_
			'prefix'		=> 'slider_badge_',
			'base_selector'	=> '.arax-slider-item',
			'selector'		=> '.arax-slider-item-badge',
			
			'section'	=> [
				'name'	=> 'slider_badge_section',
				'label'	=> esc_html__( 'Slider badge', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_badge_top_
			'prefix'		=> 'slider_badge_top_',
			'base_selector'	=> '.arax-slider-item-badge',
			'selector'		=> '.arax-slider-item-badge-top',
			
			'section'	=> [
				'name'	=> 'slider_badge_top_section',
				'label'	=> esc_html__( 'Slider badge top text', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // slider_badge_bottom_
			'prefix'		=> 'slider_badge_bottom_',
			'base_selector'	=> '.arax-slider-item-badge',
			'selector'		=> '.arax-slider-item-badge-bottom',
			
			'section'	=> [
				'name'	=> 'slider_badge_bottom_section',
				'label'	=> esc_html__( 'Slider badge bottom text', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
		Slider::arrows_style_controls( $this, '.bijan-slider-nav-btn' );

		// Thumbnail
		ElementorControls::general_style_controls( $this, [ // thumb_slider_wrap_
			'prefix'	=> 'thumb_slider_wrap_',
			'selector'	=> '.arax-slider-thumb-wrap',
			
			'section'	=> [
				'name'	=> 'thumb_slider_wrap_section',
				'label'	=> esc_html__( 'Thumbnail slider wrap', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // thumb_slider_item_wrap_
			'prefix'	=> 'thumb_slider_item_wrap_',
			'selector'	=> '.arax-slider-thumb',
			
			'section'	=> [
				'name'	=> 'thumb_slider_item_wrap_section',
				'label'	=> esc_html__( 'Thumbnail slider item wrap', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // thumb_slider_image_
			'prefix'		=> 'thumb_slider_image_',
			'base_selector'	=> '.arax-slider-thumb',
			'selector'		=> 'img',
			
			'section'	=> [
				'name'	=> 'thumb_slider_image_section',
				'label'	=> esc_html__( 'Thumbnail slider image', 'bijan' ),
			],

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // thumb_slider_text_
			'prefix'		=> 'thumb_slider_text_',
			'base_selector'	=> '.arax-slider-thumb',
			'selector'		=> '.arax-slider-thumb-text',
			
			'section'	=> [
				'name'	=> 'thumb_slider_text_section',
				'label'	=> esc_html__( 'Thumbnail slider text', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		get_template_part( "templates/components/arax_slider", null, $this->get_settings_for_display() );
	}
}