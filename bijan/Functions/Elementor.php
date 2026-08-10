<?php

use Bijan\Utils;

if( !function_exists( "bijan_elementor_widget_categories" ) ) {
	function bijan_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'bijan',
			[
				'title'	=> esc_html__( 'Bijan', 'bijan' ),
				'icon'	=> 'fa fa-plug',
			]
		);

		$elements_manager->add_category(
			'bijan_single_product',
			[
				'title'	=> esc_html__( 'Bijan - Single Product', 'bijan' ),
				'icon'	=> 'fa fa-plug',
			]
		);
	}
}
add_action( 'elementor/elements/categories_registered', 'bijan_elementor_widget_categories' );

if( !function_exists( "bijan_register_elementor_widgets" ) ) {
	function bijan_register_elementor_widgets( $widgets_manager ) {
		$widgets = [
			'Button',
			'AJAXSearch',
			'AccountBtn',
			'HeaderMenu',
			'FooterMenu',
			'SectionTitle',
			'SectionTitle2',
			'SectionTitle3',
			'Ribbon',
			'BlogGrid',
			'Archive',
			'MarketButton',
			'ProIcon',
			'ProIcon2',
			'Story',
			'InstantDiscount'	=> ['woocommerce'],
			'Slider',
			'AraxSlider',
			'ThumbnailSlider',
			'CategoriesSlider'	=> ['woocommerce'],
			'SpecialOffer',
			'CTA1',
			'CTA2',
			'CTA3',
			'Products'				=> ['woocommerce'],
			'Products2'				=> ['woocommerce'],
			'Products3'				=> ['woocommerce'],
			'Products4'				=> ['woocommerce'],
			'SpecialProducts'		=> ['woocommerce'],
			'AraxSpecialProducts'	=> ['woocommerce'],
			'Testimonials',
			'Brands',
			'Video',
			'Team',
			'CartButton'		=> ['woocommerce'],
			'AraxCircleImage',

			// Single product
			'FeaturedAttributes'	=> ['woocommerce'],
			'VariationSelector'		=> ['woocommerce'],
		];
		include_once( BIJAN_DIR . "inc/Libs/vendor/autoload.php" );
		include_once( BIJAN_DIR . 'inc/ElementorControls.php' );
		foreach( $widgets as $index => $widget ) {
			if( !Utils::should_include_module( $index, $widget ) ) continue;
			$widget = Utils::get_module_name( $index, $widget );
			$class = "\Bijan\Elementor\\" . $widget;
			if( file_exists( BIJAN_DIR . "inc/Elementor/{$widget}.php" ) && !class_exists( $class ) ) {
				include( BIJAN_DIR . "inc/Elementor/{$widget}.php" );
				$widgets_manager->register( new $class() );
			}
		}
	}
}
add_action( 'elementor/widgets/register', 'bijan_register_elementor_widgets' );

if( !function_exists( "bijan_register_elementor_fonts_group" ) ) {
	function bijan_register_elementor_fonts_group( $font_groups ) {
		$font_groups['bijan'] = __( 'Theme fonts', 'bijan' );
		return $font_groups;
	}
}
add_filter( 'elementor/fonts/groups', 'bijan_register_elementor_fonts_group' );

if( !function_exists( "bijan_register_elementor_additional_fonts" ) ) {
	function bijan_register_elementor_additional_fonts( $additional_fonts ) {
		if( !class_exists( "Bijan\Utils" ) ) {
			include_once( BIJAN_DIR . "inc/Utils.php" );
		}
		$active_fonts = Utils::get_active_fonts();
		foreach( $active_fonts as $font ) {
			$additional_fonts[$font] = 'bijan';
		}
		return $additional_fonts;
	}
}
// add_filter( 'elementor/fonts/additional_fonts', 'bijan_register_elementor_additional_fonts' );

if( !function_exists( "bijan_gradient_elements_modify" ) ) {
	function bijan_gradient_elements_modify( $element ) {
		// widget_name	=> controls
		$gradient_elements = [
			'bijan_story'		=> ['item_border_background_background', 'item_border_background_hover_background'],
			'bijan_arax_slider'	=> ['main_slider_wrap_background_background', 'main_slider_wrap_background_hover_background'],
		];

		if( !empty( $element->get_data()['widgetType'] ) && isset( $gradient_elements[$element->get_data()['widgetType']] ) && !empty( $element->get_data()['settings'] ) ) {
			$controls = $gradient_elements[$element->get_data()['widgetType']];
			if( !empty( array_intersect( array_keys( $element->get_data()['settings'] ), $controls ) ) ) {
				$element->add_render_attribute(
					'_wrapper',
					[
						'class' => 'has-bg',
					]
				);
			}
		}
	}
}
add_action(	'elementor/frontend/before_render', 'bijan_gradient_elements_modify' );