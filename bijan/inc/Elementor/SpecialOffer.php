<?php
namespace Bijan\Elementor;

use Bijan\Utils\Elementor;
use Bijan\ElementorControls as ElementorControls;
use Bijan\ElementorControls\Button as ElementorControlsButton;

use MJ\Whitebox\Utils\Date as WhiteboxDate;

class SpecialOffer extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_special_offer';
	}

	public function get_title() {
		return esc_html__( 'Special Offer (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-menu-toggle';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['special', 'offer', 'time', 'discount', 'product', 'ویژه', 'تخفیف', 'فروشگاه', 'محصول', 'خرید'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title
			'title',
			[
				'label'			=> esc_html__( 'Title', 'bijan' ),
				'label_block'	=> true,
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Special offer', 'bijan' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // end_time
			'end_time',
			[
				'label'				=> esc_html__( 'End time', 'bijan' ),
				'type'				=> \Elementor\Controls_Manager::DATE_TIME,
				'picker_options'	=> [
					'minDate'	=> WhiteboxDate::maybe_j2g( date_i18n( "Y-m-d" ) )
				],
				'ai'				=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'			=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // show_percentage
			'show_percentage',
			[
				'label'			=> esc_html__( 'Show percentage symbol', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'bijan' ),
				'label_off'		=> esc_html__( 'Hide', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control( // show_button
			'show_button',
			[
				'label'			=> esc_html__( 'Show button', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'bijan' ),
				'label_off'		=> esc_html__( 'Hide', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControlsButton::settings( $this, [
			'section'	=> [
				'condition'	=> [
					'show_button'	=> 'yes'
				]
			],
			'excludes'	=> [
				'type',
				'align'
			],
			'controls'	=> [
				'text'	=> [
					'default'	=> esc_html__( 'View all', 'bijan' )
				],
				'style'	=> [
					'default'	=> 'rounded'
				],
			],
		] );

		ElementorControls::text_style_controls( $this, '.special-offer-title', 'title_', esc_html__( "Title", 'bijan' ), "{{WRAPPER}} .special-offer:hover .special-offer-title" );
		ElementorControls::general_style_controls( $this, [ // timer_wrap
			'prefix'		=> 'timer_wrap_',
			'base_selector'	=> '.special-offer',
			'selector'		=> '.special-offer-timer',
			
			'section'	=> [
				'name'	=> 'timer_wrap_section',
				'label'	=> esc_html__( 'Timer', 'bijan' ),
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // timer_number
			'prefix'		=> 'timer_number_',
			'base_selector'	=> '.special-offer',
			'selector'		=> '.special-offer-timer-number',
			
			'section'	=> [
				'name'	=> 'timer_number_section',
				'label'	=> esc_html__( 'Timer number', 'bijan' ),
			],

			'mode'	=> 'text'
		] );
		ElementorControls::general_style_controls( $this, [ // timer_separator
			'prefix'		=> 'timer_separator_',
			'base_selector'	=> '.special-offer',
			'selector'		=> '.special-offer-timer-separator',
			
			'section'	=> [
				'name'	=> 'timer_separator_section',
				'label'	=> esc_html__( 'Timer separator', 'bijan' ),
			],

			'mode'	=> 'svg',
		] );

		ElementorControls::general_style_controls( $this, [ // percentage
			'prefix'		=> 'percentage_',
			'base_selector'	=> '.special-offer',
			'selector'		=> '.special-offer-percentage',
			
			'section'	=> [
				'name'		=> 'percentage_section',
				'label'		=> esc_html__( 'Percentage', 'bijan' ),
				'condition'	=> [
					'show_percentage'	=> 'yes'
				],
			],

			'mode'	=> 'svg',
		] );

		ElementorControls::general_style_controls( $this, [ // button
			'prefix'		=> 'button_',
			'base_selector'	=> '.special-offer',
			'selector'		=> '.button',
			'hover_type'	=> 'normal',
			
			'section'	=> [
				'name'		=> 'button_section',
				'label'		=> esc_html__( 'Button', 'bijan' ),
				'condition'	=> [
					'show_button'	=> 'yes'
				],
			],

			'mode'	=> 'wrapper',
		] );
		ElementorControls::general_style_controls( $this, [ // button_text
			'prefix'			=> 'button_text_',
			'base_selector'		=> '.special-offer',
			'selector'			=> '.button-text',
			'hover_selector'	=> '.special-offer .button:hover .button-text',
			
			'section'	=> [
				'name'		=> 'button_text_section',
				'label'		=> esc_html__( 'Button text', 'bijan' ),
				'condition'	=> [
					'show_button'	=> 'yes'
				],
			],

			'mode'	=> 'text',
		] );
	}

	protected function render() {
		get_template_part( "templates/components/special_offer", null, $this->get_settings_for_display() );
	}
}