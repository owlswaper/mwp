<?php
namespace Bijan\Elementor;

class VariationSelector extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_variation_selector';
	}

	public function get_title() {
		return esc_html__( 'Variation Selector (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-kit-details';
	}

	public function get_categories() {
		return ['bijan_single_product'];
	}

	public function get_keywords() {
		return ['product', 'woocommerce', 'single', 'variation', 'attribute', 'محصول', 'ویژگی', 'ووکامرس', "متغییر", "ویژگی ها"];
	}

	protected function register_controls() {
		
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		bijan_wc_single_variation_options();
	}
}