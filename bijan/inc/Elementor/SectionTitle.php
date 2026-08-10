<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls\SectionTitle as ElementorControlsSectionTitle;

class SectionTitle extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_section_title';
	}

	public function get_title() {
		return esc_html__( 'Section Title (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['title', 'section', 'عنوان', 'بخش'];
	}

	protected function register_controls() {
		ElementorControlsSectionTitle::settings( $this, ['prefix' => ''] );

		ElementorControlsSectionTitle::styles( $this );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/section_title", null, $settings );
	}
}