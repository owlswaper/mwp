<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;

class Brands extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_brands';
	}

	public function get_title() {
		return esc_html__( 'Brands (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['brands', 'logos', 'companies', 'لوگو', 'برند', 'شرکت'];
	}

	private function items_settings_controls() {
		$this->start_controls_section( // content_section
			'items_settings_section',
			[
				'label'	=> esc_html__( 'Items', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( // img
			'img',
			[
				'label'			=> esc_html__( 'Brand logo', 'bijan' ),
				'description'	=> esc_html__( 'Size: 58px*58px', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'default'		=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control( // name
			'name',
			[
				'label'			=> esc_html__( 'Name', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Name', 'bijan' ),
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
				'label'			=> esc_html__( 'Link', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::URL,
				'label_block'	=> true,
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
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
				'title_field' => '{{{ name }}}',
			]
		);

		$this->end_controls_section();
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // desktop_columns
			'desktop_columns',
			[
				'label'		=> __( "Desktop Column counts", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 5,
			]
		);

		$this->add_control( // desktop_row_gap
			'desktop_row_gap',
			[
				'label'		=> __( "Desktop rows gap", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 20,
			]
		);

		$this->add_control( // desktop_column_gap
			'desktop_column_gap',
			[
				'label'		=> __( "Desktop columns gap", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 20,
				'separator'	=> 'after',
			]
		);

		$this->add_control( // tablet_columns
			'tablet_columns',
			[
				'label'		=> __( "Tablet Column counts", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 3,
			]
		);

		$this->add_control( // tablet_row_gap
			'tablet_row_gap',
			[
				'label'		=> __( "Tablet rows gap", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 20,
			]
		);

		$this->add_control( // tablet_column_gap
			'tablet_column_gap',
			[
				'label'		=> __( "Tablet columns gap", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
				'separator'	=> 'after',
			]
		);

		$this->add_control( // mobile_columns
			'mobile_columns',
			[
				'label'		=> __( "Mobile Column counts", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 1,
				'default'	=> 3,
			]
		);

		$this->add_control( // mobile_row_gap
			'mobile_row_gap',
			[
				'label'		=> __( "Mobile rows gap", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 20,
			]
		);

		$this->add_control( // mobile_column_gap
			'mobile_column_gap',
			[
				'label'		=> __( "Mobile columns gap", 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 16,
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->items_settings_controls();
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // item
			'prefix'		=> 'item_',
			'base_selector'	=> '.bijan-brand',
			
			'section'	=> [
				'name'	=> 'item_section',
				'label'	=> esc_html__( 'Item style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // item_img
			'prefix'		=> 'item_img_',
			'base_selector'	=> '.bijan-brand',
			'selector'		=> 'img',
			
			'section'	=> [
				'name'	=> 'item_img_section',
				'label'	=> esc_html__( 'Item image style', 'bijan' ),
			],

			'mode'	=> 'image',
		] );

		ElementorControls::text_style_controls( $this, '.bijan-title', 'item_title_', esc_html__( "Item title", 'bijan' ), '.bijan-brand:hover .bijan-title' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/brands", null, $settings );
	}
}