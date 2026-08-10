<?php
namespace Bijan\Elementor;

use MJ\Whitebox\Utils as WhiteboxUtils;
use Bijan\ElementorControls;

class CartButton extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_cart_button';
	}

	public function get_title() {
		return esc_html__( 'Mini cart (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-cart';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['cart', 'woocommerce', 'wc', 'mini cart', 'سبد', 'سبد خرید', 'ووکامرس'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // cart-icon
			'cart-icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'bijan' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'bijan-icon-shopping-cart',
					'library'	=> 'bijan-icon'
				],
			]
		);

		$this->add_control( // show-mini-cart
			'show-mini-cart',
			[
				'label'			=> esc_html__( 'Show mini cart', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'bijan' ),
				'label_off'		=> esc_html__( 'Hide', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // empty-mini-cart-text
			'empty-mini-cart-text',
			[
				'label'			=> esc_html__( 'Empty cart text', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'The cart is empty.', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // cart_button_
			'prefix'		=> 'cart_button_',
			'base_selector'	=> '.header-cart-wrap',
			'selector'		=> '.header-action-btn',

			'section'	=> [
				'name'	=> 'cart_button_section',
				'label'	=> esc_html__( 'Button style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // cart_button_icon_
			'prefix'		=> 'cart_button_icon_',
			'base_selector'	=> '.header-cart-wrap',
			'selector'		=> '.header-cart-icon',

			'section'	=> [
				'name'	=> 'cart_button_icon_section',
				'label'	=> esc_html__( 'Icon style', 'bijan' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::text_style_controls( $this, '.header-cart-empty', 'cart_button_empty_text', __( "Cart empty text", 'bijan' ), ".header-cart-wrap .header-cart-empty" );
		ElementorControls::text_style_controls( $this, '.header-cart-count-wrap .cart-count', 'cart_button_count_number', __( "Cart count number", 'bijan' ), ".header-cart-wrap .header-cart-count-wrap .cart-count" );
		ElementorControls::text_style_controls( $this, '.header-cart-count-label', 'cart_button_count_label', __( "Cart count label", 'bijan' ), ".header-cart-wrap .header-cart-count-label" );
		ElementorControls::text_style_controls( $this, '.header-cart-total', 'cart_button_total', __( "Cart amount total", 'bijan' ), ".header-cart-wrap .header-cart-total" );
		ElementorControls::text_style_controls( $this, '.header-cart-total .woocommerce-Price-currencySymbol', 'cart_button_amount_currency', __( "Cart amount currency", 'bijan' ), ".header-cart-wrap .header-cart-total .woocommerce-Price-currencySymbol" );
		ElementorControls::general_style_controls( $this, [ // cart_button_item_image_
			'prefix'		=> 'cart_button_item_image_',
			'base_selector'	=> '.header-cart-wrap',
			'selector'		=> '.mini-cart-product-top img',

			'section'	=> [
				'name'		=> 'cart_button_item_image',
				'label'		=> esc_html__( 'Item image', 'bijan' ),
				'condition'	=> [
					'show-mini-cart'	=> 'yes'
				],
			],

			'mode'	=> 'image',
		] );
		ElementorControls::general_style_controls( $this, [ // cart_button_item_title_
			'prefix'		=> 'cart_button_item_title_',
			'base_selector'	=> '.header-cart-wrap',
			'selector'		=> '.mini-cart-product-title',

			'section'	=> [
				'name'		=> 'cart_button_item_title',
				'label'		=> esc_html__( 'Item title', 'bijan' ),
				'condition'	=> [
					'show-mini-cart'	=> 'yes'
				],
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // cart_button_item_remove_
			'prefix'		=> 'cart_button_item_remove_',
			'base_selector'	=> '',
			'selector'		=> '.remove_from_cart_button',

			'section'	=> [
				'name'		=> 'cart_button_item_remove',
				'label'		=> esc_html__( 'Item remove button', 'bijan' ),
				'condition'	=> [
					'show-mini-cart'	=> 'yes'
				],
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // cart_button_checkout_
			'prefix'		=> 'cart_button_checkout_',
			'base_selector'	=> '',
			'selector'		=> '.woocommerce-mini-cart__buttons .button',

			'section'	=> [
				'name'		=> 'cart_button_checkout',
				'label'		=> esc_html__( 'Checkout button', 'bijan' ),
				'condition'	=> [
					'show-mini-cart'	=> 'yes'
				],
			],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$settings['cart-icon'] = WhiteboxUtils::get_icon( $settings['cart-icon'], 'header-action-icon header-cart-icon' );

		get_template_part( "templates/header/action", 'mini_cart', $settings );
	}
}