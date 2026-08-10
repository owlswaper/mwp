<?php
namespace Bijan\Elementor;

use Bijan\ElementorControls\SectionTitle as ElementorControlsSectionTitle;

class SectionTitle3 extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_section_title_3';
	}

	public function get_title() {
		return esc_html__( 'Section Title 3 (Bijan)', 'bijan' );
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
		ElementorControlsSectionTitle::section_title_3_settings( $this, ['prefix' => ''] );
	}

	protected function render() {
		get_template_part( "templates/components/section_title-3", null, $this->get_settings_for_display() );
	}
}