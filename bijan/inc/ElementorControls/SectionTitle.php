<?php
namespace Bijan\ElementorControls;

use Bijan\Utils;
use MJ\Whitebox\ElementorControls\SectionTitle as WhiteboxSectionTitle;

class SectionTitle extends WhiteboxSectionTitle {
	public static function section_title_3_settings( $object, $args = [] ) {
		$args = Utils::check_default( $args, [
			'section'	=> [
				'name'	=> 'section_title_section',
				'label'	=> esc_html__( 'Section title', 'bijan' ),
			],
			'excludes'	=> [],
			'controls'	=> [], // Additional controls or other settings for current controls
		] );
		$section_args = [
			'label'	=> $args['section']['label'],
			'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
		];
		if( !empty( $args['section']['condition'] ) ) {
			$section_args['condition'] = $args['section']['condition'];
		}
		if( !empty( $args['section']['conditions'] ) ) {
			$section_args['conditions'] = $args['section']['conditions'];
		}

		$object->start_controls_section( // content_section
			$args['section']['name'],
			$section_args
		);

		self::section_title_3_controls( $object, $args );

		$object->end_controls_section();
	}

	public static function section_title_3_controls( $object, $args = [] ) {
		if( !isset( $args['prefix'] ) ) $args['prefix'] = 'section_title_';

		parent::_add_controls( $object, self::section_title_3_default_controls(), $args['prefix'], $args );
	}

	public static function section_title_3_default_controls() {
		return [
			'tag'	=> [
				'type'			=> \Elementor\Controls_Manager::SELECT,
				'label'			=> esc_html__( 'Tag', 'bijan' ),
				'label_block'	=> true,
				'default'		=> 'h2',
				'options'		=> Utils::custom_tags()
			],
			'top_text'	=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( "Top text", 'bijan' ),
				'label_block'	=> true,
				'default'		=> esc_html__( "Lorem ipsum", 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'title'	=> [
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( "Title", 'bijan' ),
				'label_block'	=> true,
				'default'		=> esc_html__( "Lorem {ipsum}", 'bijan' ),
				'description'	=> esc_html__( 'HTML tags allowed', 'bijan' ) . '<br>' . esc_html__( "To color a portion of text, enclose the text in { and }. Example: {percentage}", 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			],
			'link'	=> [
				'label'		=> esc_html__( 'Link', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::URL,
				'separator'	=> 'after',
				'default'	=> [
					'url'	=> '#'
				],
				'dynamic'	=> [
					'active'	=> true,
				],
			],
		];
	}
}