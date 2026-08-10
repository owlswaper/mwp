<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;

class FeaturedAttributes extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_featured_attributes';
	}

	public function get_title() {
		return esc_html__( 'Featured attributes (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return ['bijan_single_product'];
	}

	public function get_keywords() {
		return ['product', 'woocommerce', 'single', 'attribute', 'featured', 'محصول', 'ویژگی', 'ووکامرس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title
			'title',
			[
				'label'			=> esc_html__( 'Title', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Some product features:', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // show_all_attributes_btn
			'show_all_attributes_btn',
			[
				'label'			=> esc_html__( 'Show all attributes button', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'bijan' ),
				'label_off'		=> esc_html__( 'Hide', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // show_all_attributes_btn_text
			'show_all_attributes_btn_text',
			[
				'label'			=> esc_html__( 'Show all attributes button text', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'View all features', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'		=> [
					'show_all_attributes_btn'	=> 'yes'
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::text_style_controls( $this, '.product-featured-attributes-label', 'title_', esc_html__( "Title", 'bijan' ) );
		ElementorControls::general_style_controls( $this, [ // feature_row_
			'prefix'		=> 'feature_row_',
			'base_selector'	=> '.product-featured-attribute-row',
			
			'section'	=> [
				'name'	=> 'feature_row_styling',
				'label'	=> esc_html__( 'Feature row', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::text_style_controls( $this, '.product-featured-attribute-label', 'attr_title_', esc_html__( "Attribute title", 'bijan' ), '.product-featured-attribute-row .product-featured-attribute-label' );
		ElementorControls::text_style_controls( $this, '.product-featured-attribute-option', 'attr_option_', esc_html__( "Attribute value", 'bijan' ), '.product-featured-attribute-row .product-featured-attribute-option' );

		ElementorControls::general_style_controls( $this, [ // show_all_btn_
			'prefix'		=> 'show_all_btn_',
			'base_selector'	=> '.product-featured-attributes-link',
			
			'section'	=> [
				'name'		=> 'show_all_btn_styling',
				'label'		=> esc_html__( 'View all attributes button', 'bijan' ),
				'condition'	=> [
					'show_all_attributes_btn'	=> 'yes'
				],
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // show_all_btn_text_
			'prefix'		=> 'show_all_btn_text_',
			'base_selector'	=> '.product-featured-attributes-link',
			
			'section'	=> [
				'name'		=> 'show_all_btn_text_styling',
				'label'		=> esc_html__( 'View all attributes button text', 'bijan' ),
				'condition'	=> [
					'show_all_attributes_btn'	=> 'yes'
				],
			],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		bijan_wc_single_feature_attrs( $settings );
	}
}