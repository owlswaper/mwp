<?php
namespace Bijan\Elementor;

use Bijan\Utils;
use Bijan\ElementorControls;
use Bijan\ElementorControls\Slider;

class ProIcon2 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_pro_icon_2';
	}

	public function get_title() {
		return esc_html__( 'Pro Icon 2 (Bijan)', 'bijan' );
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

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // icon_type
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
				'default'	=> 'icon',
				'toggle'	=> false,
			]
		);

		$repeater->add_control( // img
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

		$repeater->add_control( // icon
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

		$repeater->add_control( // title
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

		$repeater->add_control( // subtitle
			'subtitle',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Subtitle', 'bijan' ),
				'label_block'	=> true,
				'default'		=> __( 'Lorem ipsum', 'bijan' ),
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

		$repeater->add_control( // link
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

		$this->add_control( // items
			'items',
			[
				'label'			=> __( "Items", 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::REPEATER,
				'fields'		=> $repeater->get_controls(),
				'default'		=> [
					[
						'icon_type'	=> 'icon',
						'img'		=> [
							'default'	=> [
								'url'	=> \Elementor\Utils::get_placeholder_image_src(),
							]
						],
						'icon'		=> [
							'default'	=> [
								'library'	=> 'bijan-icon',
								'value'		=> 'bijan-icon-user',
							]
						],
						'title'		=> __( 'Title', 'bijan' ),
						'subtitle'	=> __( 'Lorem ipsum', 'bijan' ),
					],
					[
						'icon_type'	=> 'icon',
						'img'		=> [
							'default'	=> [
								'url'	=> \Elementor\Utils::get_placeholder_image_src(),
							]
						],
						'icon'		=> [
							'default'	=> [
								'library'	=> 'bijan-icon',
								'value'		=> 'bijan-icon-user',
							]
						],
						'title'		=> __( 'Title', 'bijan' ),
						'subtitle'	=> __( 'Lorem ipsum', 'bijan' ),
					],
					[
						'icon_type'	=> 'icon',
						'img'		=> [
							'default'	=> [
								'url'	=> \Elementor\Utils::get_placeholder_image_src(),
							]
						],
						'icon'		=> [
							'default'	=> [
								'library'	=> 'bijan-icon',
								'value'		=> 'bijan-icon-user',
							]
						],
						'title'		=> __( 'Title', 'bijan' ),
						'subtitle'	=> __( 'Lorem ipsum', 'bijan' ),
					],
					[
						'icon_type'	=> 'icon',
						'img'		=> [
							'default'	=> [
								'url'	=> \Elementor\Utils::get_placeholder_image_src(),
							]
						],
						'icon'		=> [
							'default'	=> [
								'library'	=> 'bijan-icon',
								'value'		=> 'bijan-icon-user',
							]
						],
						'title'		=> __( 'Title', 'bijan' ),
						'subtitle'	=> __( 'Lorem ipsum', 'bijan' ),
					],
					[
						'icon_type'	=> 'icon',
						'img'		=> [
							'default'	=> [
								'url'	=> \Elementor\Utils::get_placeholder_image_src(),
							]
						],
						'icon'		=> [
							'default'	=> [
								'library'	=> 'bijan-icon',
								'value'		=> 'bijan-icon-user',
							]
						],
						'title'		=> __( 'Title', 'bijan' ),
						'subtitle'	=> __( 'Lorem ipsum', 'bijan' ),
					],
				],
			]
		);

		$this->add_control( // tag
			'title_tag',
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Title tag', 'bijan' ),
				'label_block'	=> true,
				'default'		=> 'div',
				'options'		=> Utils::custom_tags(),
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_slides'	=> [
					'default'	=> 5,
				],
				'desktop_slides_space'	=> [
					'default'	=> 46,
				],
				'desktop_column_gap'	=> [
					'default'	=> 46,
				],
				'tablet_slides_space'	=> [
					'default'	=> 46,
				],
				'tablet_column_gap'	=> [
					'default'	=> 46,
				],
				'mobile_slides_space'	=> [
					'default'	=> 46,
				],
				'mobile_column_gap'	=> [
					'default'	=> 46,
				],
			]
		] );
		Slider::options_controls( $this, [
			'excludes'	=> ['show_dots'],
		], true );

		ElementorControls::general_style_controls( $this, [ // item_wrap_
			'prefix'	=> 'item_wrap_',
			'selector'	=> '.pro-icon-2',
			
			'section'	=> [
				'name'	=> 'item_wrap_section',
				'label'	=> esc_html__( 'Item wrap', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // item_icon_
			'prefix'		=> 'item_icon_',
			'base_selector'	=> '.pro-icon-2',
			'selector'		=> '.pro-icon-2-icon-wrap i',
			
			'section'	=> [
				'name'	=> 'item_icon_section',
				'label'	=> esc_html__( 'Icon', 'bijan' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // item_image_
			'prefix'		=> 'item_image_',
			'base_selector'	=> '.pro-icon-2',
			'selector'		=> '.pro-icon-2-icon-wrap img',
			
			'section'	=> [
				'name'	=> 'item_image_section',
				'label'	=> esc_html__( 'Image', 'bijan' ),
			],

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // item_texts_
			'prefix'		=> 'item_texts_',
			'base_selector'	=> '.pro-icon-2',
			'selector'		=> '.pro-icon-2-texts',
			
			'section'	=> [
				'name'	=> 'item_texts_section',
				'label'	=> esc_html__( 'Texts', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // item_title_
			'prefix'		=> 'item_title_',
			'base_selector'	=> '.pro-icon-2',
			'selector'		=> '.pro-icon-2-title',
			
			'section'	=> [
				'name'	=> 'item_title_section',
				'label'	=> esc_html__( 'Title', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // item_subtitle_
			'prefix'		=> 'item_subtitle_',
			'base_selector'	=> '.pro-icon-2',
			'selector'		=> '.pro-icon-2-subtitle',
			
			'section'	=> [
				'name'	=> 'item_subtitle_section',
				'label'	=> esc_html__( 'Subtitle', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		get_template_part( "templates/components/proicon_2", null, $this->get_settings_for_display() );
	}
}