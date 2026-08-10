<?php
namespace Bijan;

use Bijan\Utils\Elementor;
use MJ\Whitebox\ElementorControls as WhiteboxElementorControls;

if( !defined( 'ABSPATH' ) ) exit;

if( isset( $_GET['elementor_updater'] ) ) return;

class ElementorControls extends WhiteboxElementorControls {
	public static function market_button_controls( $object, $args = [] ) {
		if( !isset( $args['prefix'] ) ) $args['prefix'] = 'market_button_';
		$default_controls = [
			'market'		=> [
				'label'		=> esc_html__( 'Market', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'cafebazar',
				'options'	=> Utils::app_markets(),
			],
			'custom_icon'	=> [
				'label'		=> esc_html__( 'Custom icon', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::MEDIA,
				'default'	=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition'	=> [
					"{$args['prefix']}market"	=> 'custom'
				]
			],
			'top_text'		=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Top text', 'bijan' ),
				'label_block'	=> true,
				'default'		=> __( 'Download from', 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'text'			=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Text', 'bijan' ),
				'label_block'	=> true,
				'default'		=> __( 'Cafebazar', 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'link'			=> [
				'label'		=> esc_html__( 'Link', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			],
			'size'			=> [
				'label'		=> esc_html__( 'Size', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'full',
				'options'	=> [
					'full'	=> __( 'Full width', 'bijan' ),
					'auto'	=> __( 'Fit content width', 'bijan' ),
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		];

		self::_add_controls( $object, $default_controls, $args['prefix'], $args );
	}

	public static function product_discount_progress_style_controls( $object, $selector, $prefix, $label, $hover_selector = '' ) {
		$selector = "{{WRAPPER}} {$selector}";
		$hover_selector = !$hover_selector ? "{$selector}:hover" : $hover_selector;

		$object->start_controls_section(
			"style_{$prefix}section",
			[
				'label'	=> $label,
				'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$object->start_controls_tabs( "tabs_{$prefix}_style" );

		$object->start_controls_tab( // Normal
			"tab_{$prefix}_normal",
			[
				'label'	=> esc_html__( 'Normal', 'bijan' ),
			]
		);

		self::padding( $object, "{$prefix}padding", $selector );
		self::margin( $object, "{$prefix}margin", $selector );
		self::background( $object, "{$prefix}background", $selector );
		self::border( $object, "{$prefix}border", $selector );
		self::border_radius( $object, "{$prefix}border_radius", $selector );
		self::box_shadow( $object, "{$prefix}box_shadow", $selector );

		$object->end_controls_tab();

		$object->start_controls_tab( // Hover
			"tab_{$prefix}_hover",
			[
				'label' => esc_html__( 'Hover', 'bijan' ),
			]
		);

		self::padding( $object, "{$prefix}padding_hover", $hover_selector );
		self::margin( $object, "{$prefix}margin_hover", $hover_selector );
		self::background( $object, "{$prefix}background_hover", $hover_selector );
		self::border( $object, "{$prefix}border_hover", $hover_selector );
		self::border_radius( $object, "{$prefix}border_radius_hover", $hover_selector );
		self::box_shadow( $object, "{$prefix}box_shadow_hover", $hover_selector );

		$object->end_controls_tab();
		$object->end_controls_tabs();

		$object->end_controls_section();
	}
}