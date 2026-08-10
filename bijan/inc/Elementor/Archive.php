<?php
namespace Bijan\Elementor;

use Bijan\Utils\Archive as UtilsArchive;
use Bijan\ElementorControls;

class Archive extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_archive';
	}

	public function get_title() {
		return esc_html__( 'Archive (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-gallery-justified';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['blog', 'post', 'پست', 'نوشته', 'بلاگ', 'وبلاگ'];
	}

	protected function register_controls() {
		ElementorControls::display_settings( $this, [
			'controls'	=> [
				'desktop_cols'	=> [
					'default'	=> 3,
				],
				'tablet_cols'	=> [
					'default'	=> 1,
				],
				'mobile_cols'	=> [
					'default'	=> 1,
				],
			],
		] );
		ElementorControls::query_controls( $this );
		ElementorControls::pagination_controls( $this, [
			'controls'	=> [
				'ppp'				=> [
					'default'	=> 12
				],
				'show_pagination'	=> [
					'default'	=> 'yes'
				]
			],
		] );

		ElementorControls::general_style_controls( $this, [ // post
			'prefix'		=> 'post_',
			'base_selector'	=> 'article',
			
			'section'	=> [
				'name'	=> 'post_section',
				'label'	=> esc_html__( 'Post style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [ // post_img
			'prefix'		=> 'post_img_',
			'base_selector'	=> 'article',
			'selector'		=> '.post-thumbnail img',
			
			'section'	=> [
				'name'	=> 'post_img_section',
				'label'	=> esc_html__( 'Post image style', 'bijan' ),
			],

			'mode'	=> 'image',
		] );

		ElementorControls::general_style_controls( $this, [ // post_texts_wrap
			'prefix'		=> 'post_texts_wrap_',
			'base_selector'	=> 'article',
			'selector'		=> '.post-texts',
			
			'section'	=> [
				'name'	=> 'post_texts_wrap_section',
				'label'	=> esc_html__( 'Texts wrap style', 'bijan' ),
			],

			'mode'	=> 'wrap',
		] );

		ElementorControls::text_style_controls( $this, '.post-title', 'post_title_', esc_html__( 'Post title', 'bijan' ), 'article:hover .post-title' );
		ElementorControls::text_style_controls( $this, '.post-time', 'post_time_', esc_html__( 'Post time', 'bijan' ), 'article:hover .post-time' );
		ElementorControls::text_style_controls( $this, '.post-excerpt', 'post_excerpt_', esc_html__( 'Post excerpt', 'bijan' ), 'article:hover .post-excerpt' );

		ElementorControls::pagination_style_controls( $this );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		UtilsArchive::posts( $settings );
	}
}