<?php
namespace Bijan\Elementor;

use Bijan\Utils;
use Bijan\ElementorControls;

class AccountBtn extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_account_btn';
	}

	public function get_title() {
		return esc_html__( 'Account button (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-lock-user';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['account', 'user', 'login', 'signup', 'کاربر', 'ورود', 'عضویت'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // icon
			"icon",
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'bijan' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'		=> [
					'value'		=> 'bijan-icon-user',
					'library'	=> 'bijan-icon'
				],
			]
		);

		$this->add_control( // link
			'link',
			[
				'label'		=> esc_html__( 'Link', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'dynamic'	=> [
					'active'	=> true,
				],
				'default'	=> [
					'url'	=> Utils::is_wc_active() ? home_url( 'my-account' ) : home_url(),
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // icon_
			'prefix'		=> 'icon_',
			'base_selector'	=> '.account-btn-wrap',
			'selector'		=> '.account-btn-link',
			
			'section'	=> [
				'name'	=> 'icon_section',
				'label'	=> esc_html__( 'Icon style', 'bijan' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // popover_
			'prefix'		=> 'popover_',
			'base_selector'	=> '.bijan-popover',
			
			'section'	=> [
				'name'	=> 'popover_section',
				'label'	=> esc_html__( 'Popover style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // name_
			'prefix'		=> 'name_',
			'base_selector'	=> '.account-name-link',
			
			'section'	=> [
				'name'	=> 'name_section',
				'label'	=> esc_html__( 'User name style', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // item_
			'prefix'		=> 'item_',
			'base_selector'	=> '.account-item',
			
			'section'	=> [
				'name'	=> 'item_section',
				'label'	=> esc_html__( 'Menu item style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // item_icon_
			'prefix'		=> 'item_icon_',
			'base_selector'	=> '.account-item',
			'selector'		=> '.account-item-icon',
			
			'section'	=> [
				'name'	=> 'item_icon_section',
				'label'	=> esc_html__( 'Menu item icon style', 'bijan' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // item_text_
			'prefix'		=> 'item_text_',
			'base_selector'	=> '.account-item',
			'selector'		=> '.account-item-icon',
			
			'section'	=> [
				'name'	=> 'item_text_section',
				'label'	=> esc_html__( 'Menu item text style', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/account-btn", null, $settings );
	}
}