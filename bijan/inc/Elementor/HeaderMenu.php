<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls;

class HeaderMenu extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_header_menu';
	}

	public function get_title() {
		return esc_html__( 'Header menu (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['menu', 'nav', 'mega menu', 'megamenu', 'منو', 'مگا منو'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$menus = wp_get_nav_menus();

		$options = [];

		foreach( $menus as $menu ) {
			$options[$menu->slug] = $menu->name;
		}

		$this->add_control( // menu
			"menu",
			[
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Menu', 'bijan' ),
				'options'		=> $options,
				'default'		=> array_keys( $options )[0] ?? '',
				'description' => sprintf(
					/* translators: 1: Link opening tag, 2: Link closing tag. */
					esc_html__( 'Go to the %1$sMenus screen%2$s to manage your menus.', 'elementor-pro' ),
					sprintf( '<a href="%s" target="_blank">', admin_url( 'nav-menus.php' ) ),
					'</a>'
				),
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();

		ElementorControls::general_style_controls( $this, [ // item_icon_
			'prefix'		=> 'item_icon_',
			'base_selector'	=> '.menu-item',
			'selector'		=> '> a > i',
			
			'section'	=> [
				'name'	=> 'item_icon_section',
				'label'	=> esc_html__( 'Item icon style', 'bijan' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // item_text_
			'prefix'		=> 'item_text_',
			'base_selector'	=> '.menu-item > a',
			
			'section'	=> [
				'name'	=> 'item_text_section',
				'label'	=> esc_html__( 'Item text style', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
		ElementorControls::general_style_controls( $this, [ // submenu_
			'prefix'			=> 'submenu_',
			'base_selector'		=> '.sub-menu',
			'hover_selector'	=> false,
			
			'section'	=> [
				'name'	=> 'submenu_section',
				'label'	=> esc_html__( 'Submenu style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [ // submenu_icon_
			'prefix'		=> 'submenu_icon_',
			'base_selector'	=> '.sub-menu .menu-item',
			'selector'		=> 'a i',
			
			'section'	=> [
				'name'	=> 'submenu_icon_section',
				'label'	=> esc_html__( 'Submenu item icon style', 'bijan' ),
			],

			'mode'	=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [ // submenu_text_
			'prefix'		=> 'submenu_text_',
			'base_selector'	=> '.sub-menu .menu-item a',
			
			'section'	=> [
				'name'	=> 'submenu_text_section',
				'label'	=> esc_html__( 'Submenu item text style', 'bijan' ),
			],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		?>
		<nav class="menu-wrap">
			<?php
			wp_nav_menu( [
				'menu'				=> $settings['menu'],
				'container_class'	=> $settings['menu']
			] );
			?>
		</nav>
		<?php
	}
}