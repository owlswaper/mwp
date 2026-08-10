<?php

use Bijan\Utils;
use Bijan\Utils\Options;

if( !function_exists( "bijan_setup" ) ) {
	function bijan_setup() {
		$supports = ['title-tag', 'post-thumbnails', 'automatic-feed-links', 'menus', 'widgets', 'woocommerce'];
		foreach( $supports as $support ) {
			add_theme_support( $support );
		}
	}
}
add_action( 'after_setup_theme', 'bijan_setup' );

if( !function_exists( "bijan_init" ) ) {
	function bijan_init() {
		load_theme_textdomain( 'bijan',  BIJAN_DIR . 'languages' );
		load_textdomain( 'mj-whitebox',  BIJAN_DIR . 'inc/Libs/vendor/mjkhajeh/whitebox/languages/mj-whitebox-' . get_locale() . '.mo' );

		// Nav menus
		register_nav_menus( [
			'main-menu'				=> esc_html__( 'Header (Main menu)', 'bijan' ),
			'header-second-menu'	=> esc_html__( 'Header - Second menu', 'bijan' ),
			'mobile-menu'			=> esc_html__( 'Mobile menu (Main menu)', 'bijan' ),
			'mobile-second-menu'	=> esc_html__( 'Mobile - Second menu', 'bijan' ),
			'footer-menu'			=> esc_html__( 'Footer', 'bijan' ),
			'footer-contact-menu'	=> esc_html__( 'Footer (Contacts)', 'bijan' ),
			'footer-menu-3'			=> esc_html__( 'Footer Menu 3', 'bijan' ),
			'account-menu'			=> esc_html__( 'Account menu (Logged in users)', 'bijan' ),
		] );

		include_once( BIJAN_DIR . "inc/Libs/vendor/autoload.php" );

		include( BIJAN_DIR . "inc/Utils.php" );
		include( BIJAN_DIR . "inc/Utils/Options.php" );

		include_once( BIJAN_DIR . "inc/TGM/tgm.php" );

		include( BIJAN_DIR . "Includes.php" );
	}
}
add_action( 'init', 'bijan_init', 0 );

if( !function_exists( 'bijan_register_sidebars' ) ) {
	function bijan_register_sidebars() {
		register_sidebar( [
			'id'			=> 'general',
			'name'			=> __( 'General sidebar', 'bijan' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'page',
			'name'			=> __( 'Page sidebar', 'bijan' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'blog',
			'name'			=> __( 'Blog sidebar', 'bijan' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'single',
			'name'			=> __( 'Single sidebar', 'bijan' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'sidebar-shop',
			'name'			=> __( 'Shop', 'woocommerce' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );
	}
}
add_action( 'widgets_init', 'bijan_register_sidebars' );

if( !function_exists( 'bijan_register_widgets' ) ) {
	/**
	 * Registers theme widgets.
	 *
	 * @since 1.0.0.0
	 */
	function bijan_register_widgets() {
		$widgets = ['Socials'];
		foreach( $widgets as $widget ) {
			if( file_exists( BIJAN_DIR . "inc/Widgets/{$widget}.php" ) ) {
				include( BIJAN_DIR . "inc/Widgets/{$widget}.php" );
				register_widget( "\Bijan\Widgets\\$widget" );
			}
		}
	}
}
add_action( 'widgets_init', 'bijan_register_widgets' );

if( !function_exists( "bijan_admin_enqueue" ) ) {
	function bijan_admin_enqueue() {
		$screen = get_current_screen();
		if( $screen->base === 'toplevel_page_bijan' ) {
			if( is_rtl() ) {
				wp_enqueue_style( 'bijan-options', BIJAN_URI . "assets/css/backend/options.rtl.min.css", [], BIJAN_VERSION );
			}
		}

		include( BIJAN_DIR . "inc/AdminScripts.php" );
	}
}
add_action( 'admin_enqueue_scripts', "bijan_admin_enqueue", 9 );

if( !function_exists( "bijan_enqueue" ) ) {
	function bijan_enqueue() {
		if( !defined( 'BIJAN_CHILD_DIR' ) ) {
			include( BIJAN_DIR . "inc/Scripts.php" );
		}
	}
}
add_action( 'wp_enqueue_scripts', "bijan_enqueue" );

if( !function_exists( "bijan_load_metaboxes" ) ) {
	function bijan_load_metaboxes() {
		$metaboxes = [
			'Page'		=> [
				'Settings'
			],
			'Story'		=> [
				'Settings'
			],
			'Notification'	=> [
				'Users'
			],
		];
		foreach( $metaboxes as $post_type => $files ) {
			foreach( $files as $filename ) {
				if( file_exists( BIJAN_DIR . "inc/Backend/Metaboxes/{$post_type}/{$filename}.php" ) ) {
					include( BIJAN_DIR . "inc/Backend/Metaboxes/{$post_type}/{$filename}.php" );
				}
			}
		}
	}
}
add_action( 'admin_init', 'bijan_load_metaboxes' );

if( !function_exists( 'bijan_register_widgets' ) ) {
	function bijan_register_widgets() {
		$widgets = ['RecentPosts'];
		foreach( $widgets as $index => $widget ) {
			if( !Utils::should_include_module( $index, $widget ) ) continue;
			if( file_exists( BIJAN_DIR . "inc/Widgets/{$widget}.php" ) ) {
				include( BIJAN_DIR . "inc/Widgets/{$widget}.php" );
				register_widget( "\Bijan\Widgets\\$widget" );
			}
		}
	}
}
add_action( 'widgets_init', 'bijan_register_widgets' );

if( !function_exists( 'bijan_custom_head_code' ) ) {
	function bijan_custom_head_code() {
		if( empty( $GLOBALS['bijan'] ) ) return;
		
		$options = $GLOBALS['bijan'];
		echo isset( $options['header_custom_code'] ) ? $options['header_custom_code'] : '';
	}
}
add_action( 'wp_head', 'bijan_custom_head_code' );

if( !function_exists( 'bijan_custom_footer_code' ) ) {
	function bijan_custom_footer_code() {
		if( empty( $GLOBALS['bijan'] ) ) return;

		$options = $GLOBALS['bijan'];
		echo isset( $options['footer_custom_code'] ) ? $options['footer_custom_code'] : '';
	}
}
add_action( 'wp_footer', 'bijan_custom_footer_code' );

if( !function_exists( 'bijan_exclude_search_post_types' ) ) {
	function bijan_exclude_search_post_types( $query ) {
		if( is_admin() && !wp_doing_ajax() ) return;
		if( empty( $GLOBALS['bijan'] ) ) return;

		$options = $GLOBALS['bijan'];
		if( ( $query->is_main_query() && $query->is_search() ) && !empty( $options['exclude_post_types'] ) ) {
			$post_types = get_post_types( ['exclude_from_search' => false] );
			$query->set( 'post_type', array_diff( array_keys( $post_types ), $options['exclude_post_types'] ) );
		}
	}
}
add_action( 'pre_get_posts', 'bijan_exclude_search_post_types' );

if( !function_exists( "bijan_footer_assets" ) ) {
	function bijan_footer_assets() {
		?>
		<div id="bijan-megamenu-overlay"></div>
		<div id="bijan-overlay"></div>
		<div id="mobile-menu-overlay" class="hide-desktop"></div>
		<div id="story-popup">
			<div id="story-popup-progress"></div>
			<div id="story-popup-content"></div>
		</div>
		<?php
		echo file_get_contents( BIJAN_DIR . "assets/img/instant-discount.svg" );
	}
}
add_action( 'wp_footer', 'bijan_footer_assets', 1 );

if( !function_exists( "bijan_bottom_nav" ) ) {
	function bijan_bottom_nav() {
		$home_url = home_url();
		$options = Options::get_options( [
			'show_bottom_nav'	=> true,

			'bottom_nav_show_item_1'	=> true,
			'bottom_nav_1_icon'			=> 'bijan-icon-home',
			'bottom_nav_1_text'			=> __( 'Home', 'bijan' ),
			'bottom_nav_1_url'			=> $home_url,
			'bottom_nav_1_special'		=> '',

			'bottom_nav_show_item_2'	=> true,
			'bottom_nav_2_icon'			=> 'bijan-icon-shopping-bag',
			'bottom_nav_2_text'			=> __( 'Cart', 'bijan' ),
			'bottom_nav_2_url'			=> Utils::is_wc_active() ? wc_get_cart_url() : '#',
			'bottom_nav_2_special'		=> 'cart',

			'bottom_nav_show_item_3'	=> true,
			'bottom_nav_3_icon'			=> 'bijan-icon-grid',
			'bottom_nav_3_text'			=> __( 'Categories', 'bijan' ),
			'bottom_nav_3_url'			=> "#",
			'bottom_nav_3_special'		=> 'categories',

			'bottom_nav_show_item_4'	=> true,
			'bottom_nav_4_icon'			=> 'bijan-icon-heart',
			'bottom_nav_4_text'			=> __( 'Wishlist', 'bijan' ),
			'bottom_nav_4_url'			=> Utils::is_wc_active() ? wc_get_account_endpoint_url( 'wishlist' ) : '#',
			'bottom_nav_4_special'		=> '',

			'bottom_nav_show_item_5'	=> true,
			'bottom_nav_5_icon'			=> 'bijan-icon-user',
			'bottom_nav_5_text'			=> __( 'Profile', 'bijan' ),
			'bottom_nav_5_url'			=> Utils::is_wc_active() ? wc_get_page_permalink( 'myaccount' ) : '#',
			'bottom_nav_5_special'		=> 'account',
		] );

		if( !Utils::to_bool( $options['show_bottom_nav'] ) ) return;

		$has_cart = false;
		?>
		<div id="bottom-nav" class="hide-desktop">
			<div id="bottom-nav-items">
				<?php
				for( $index = 1; $index <= 5; $index++ ) {
					if( !Utils::to_bool( $options['bottom_nav_show_item_' . $index] ) ) continue;

					if( $options['bottom_nav_' . $index . '_special'] === 'cart' ) $has_cart = true;

					get_template_part( "templates/bottom_nav/item", null, [
						'icon'		=> $options['bottom_nav_' . $index . '_icon'],
						'text'		=> $options['bottom_nav_' . $index . '_text'],
						'url'		=> $options['bottom_nav_' . $index . '_url'],
						'special'	=> $options['bottom_nav_' . $index . '_special'],
					] );
				}
				?>
			</div>

			<?php if( Utils::is_wc_active() && $has_cart ) { ?>
				<div class="bottom-nav-cart-wrap">
					<?php woocommerce_mini_cart() ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}
add_action( 'wp_footer', 'bijan_bottom_nav' );

if( !function_exists( "bijan_mobile_menu" ) ) {
	function bijan_mobile_menu() {
		get_template_part( "templates/responsive/mobile_menu" );
	}
}
add_action( 'wp_footer', 'bijan_mobile_menu' );

if( !function_exists( "bijan_mobile_account_menu" ) ) {
	function bijan_mobile_account_menu() {
		get_template_part( "templates/responsive/mobile_account_menu" );
	}
}
add_action( 'wp_footer', 'bijan_mobile_account_menu' );

if( !function_exists( "bijan_redux_prevent_icons_request" ) ) {
	function bijan_redux_prevent_icons_request( $response, $parsed_args, $url ) {
		if( !is_admin() ) {
			return $url === BIJAN_URI . "assets/css/iconly.min.css";
		}
		return $response;
	}
}
add_filter( 'pre_http_request', 'bijan_redux_prevent_icons_request', 10, 3 );

if( !function_exists( "bijan_body_classes" ) ) {
	function bijan_body_classes( $classes ) {
		$options = Options::get_options( [
			'show_bottom_nav'		=> true,
			'auto_hide_bottom_nav'	=> true,
			'show-header-banner'	=> false,
		] );
		if( $options['show_bottom_nav'] ) {
			$classes[] = 'active_bottom_nav';
			if( $options['auto_hide_bottom_nav'] ) {
				$classes[] = 'auto-hide-bottom_nav';
			}
		}
		if( $options['show-header-banner'] ) {
			$classes[] = 'active_header_banner';
		}
		return $classes;
	}
}
add_filter( 'body_class', 'bijan_body_classes' );

if( !function_exists( "bijan_header_banner" ) ) {
	function bijan_header_banner() {
		$options = Options::get_options( [
			'show-header-banner'	=> false,
			'header-banner-title'	=> '',
			'header-banner-img'		=> [
				'url'	=> '',
			],
			'header-banner-height'	=> 80,

			'show-header-banner-tablet'	=> true,
			'header-banner-img-tablet'	=> [
				'url'	=> '',
			],
			'header-banner-tablet-height'	=> 80,

			'show-header-banner-mobile'	=> true,
			'header-banner-img-mobile'	=> [
				'url'	=> '',
			],
			'header-banner-mobile-height'	=> 80,

			'header-banner-link'			=> '',
			'header-banner-link-new_tab'	=> false,
		] );
		if( !$options['show-header-banner'] &&
			( empty( $options['header-banner-img'] ) || empty( $options['header-banner-img']['url'] ) ) &&
			( empty( $options['header-banner-img-tablet'] ) || empty( $options['header-banner-img-tablet']['url'] ) ) &&
			( empty( $options['header-banner-img-mobile'] ) || empty( $options['header-banner-img-mobile']['url'] ) )
		) return;

		$classes = ['header-banner'];
		if( !$options['show-header-banner-tablet'] ) {
			$classes[] = 'header-banner-hide-tablet';
		}
		if( !$options['show-header-banner-mobile'] ) {
			$classes[] = 'header-banner-hide-mobile';
		}

		$tablet_banner = empty( $options['header-banner-img-tablet'] ) || empty( $options['header-banner-img-tablet']['url'] ) ? $options['header-banner-img']['url'] : $options['header-banner-img-tablet']['url'];
		$mobile_banner = empty( $options['header-banner-img-mobile'] ) || empty( $options['header-banner-img-mobile']['url'] ) ? $options['header-banner-img']['url'] : $options['header-banner-img-mobile']['url'];

		$html_attrs = [
			'classes'	=> $classes,
			'id'		=> 'header-banner',
			'style'		=> [
				'height'			=> $options['header-banner-height'] . "px",
				'--height'			=> $options['header-banner-height'] . "px",
				'--height-tablet'	=> $options['header-banner-tablet-height'] . "px",
				'--height-mobile'	=> $options['header-banner-mobile-height'] . "px",
				'--banner'			=> "url({$options['header-banner-img']['url']})",
				'--tablet-banner'	=> "url({$tablet_banner})",
				'--mobile-banner'	=> "url({$mobile_banner})",
			],
			'title'	=> $options['header-banner-title'],
		];

		$tag = 'div';
		if( $options['header-banner-link'] ) {
			$tag = 'a';
			$a_html_attrs = [
				'href'	=> $options['header-banner-link'],
			];
			if( $options['header-banner-link-new_tab'] ) {
				$a_html_attrs['target'] = '_blank';
				$a_html_attrs['rel'] = 'noopener';
			}
			$html_attrs = array_merge( $a_html_attrs, $html_attrs );
		}
		?>
		<<?php echo "{$tag} " . Utils::get_html_attributes( $html_attrs ) ?>></<?php echo $tag ?>>
		<?php
	}
}
add_action( 'wp_body_open', 'bijan_header_banner', 1 );

add_filter( 'wp_theme_json_get_style_nodes', function($nodes) {
	if (!is_array($nodes)) {
		return $nodes;
	}

	$nodes = array_filter($nodes, function ($node) {
		if (
			!empty($node['selector']) &&
			$node['selector'] == 'a:where(:not(.wp-element-button))'
		) {
			return false;
		}

		return true;
	});

	return $nodes;
});