<?php
namespace Bijan\Elementor;

class Video extends \Elementor\Widget_Base {
	public function get_name() {
		return 'bijan_video';
	}

	public function get_title() {
		return esc_html__( 'Video (Bijan)', 'bijan' );
	}

	public function get_icon() {
		return 'eicon-video-camera';
	}

	public function get_categories() {
		return ['bijan', 'basic'];
	}

	public function get_keywords() {
		return ['video', 'film', 'movie', 'ویدیو', 'فیلم'];
	}

	private function video_controls() {
		$this->start_controls_section( // content_section
			'video_section',
			[
				'label'	=> esc_html__( 'Video', 'bijan' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // video_source
			'video_source',
			[
				'label'		=> esc_html__( 'Video source', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'file',
				'options'	=> [
					'file'		=> esc_html__( 'File', 'bijan' ),
					'aparat'	=> esc_html__( 'Aparat', 'bijan' ),
					'youtube' 	=> esc_html__( 'Youtube', 'bijan' ),
				],
			]
		);

		$this->add_control( // video_file
			'video_file',
			[
				'label'			=> esc_html__( 'File', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::MEDIA,
				'media_types'	=> ['video'],
				'condition'		=> [
					'video_source'	=> 'file'
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // video_embed_id
			'video_embed_id',
			[
				'label'			=> esc_html__( 'Video ID', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label_block'	=> true,
				'condition'		=> [
					'video_source!'	=> 'file'
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // controls
			"controls",
			[
				'label'			=> esc_html__( "Show video controls", 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'condition'		=> [
					'video_source'	=> 'file',
				],
			]
		);

		$this->end_controls_section();
	}

	private function cover_controls() {
		$this->start_controls_section( // content_section
			'cover_section',
			[
				'label'		=> esc_html__( 'Cover', 'bijan' ),
				'tab'		=> \Elementor\Controls_Manager::TAB_CONTENT,
				'condition'	=> [
					'video_source'	=> 'file',
				],
			]
		);


		$this->add_control( // cover_file
			'cover_file',
			[
				'label'		=> esc_html__( 'Cover', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::MEDIA,
				'default'	=> [
					'url'	=> \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	private function aparat_controls() {
		$this->start_controls_section( // content_section
			'aparat_section',
			[
				'label'		=> esc_html__( 'Aparat settings', 'bijan' ),
				'tab'		=> \Elementor\Controls_Manager::TAB_CONTENT,
				'condition'	=> [
					'video_source'	=> 'aparat',
				],
			]
		);


		$this->add_control( // aparat_autoplay
			'aparat_autoplay',
			[
				'label'			=> esc_html__( 'Autoplay', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // aparat_show_title
			'aparat_show_title',
			[
				'label'			=> esc_html__( 'Display video titles and player icons', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // aparat_muted
			'aparat_muted',
			[
				'label'			=> esc_html__( 'Initial playback should be silent', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // aparat_end_recomendation
			'aparat_end_recomendation',
			[
				'label'			=> esc_html__( 'Show other videos from your channel at the end of the video', 'bijan' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'bijan' ),
				'label_off'		=> esc_html__( 'No', 'bijan' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // aparat_start_minute
			'aparat_start_minute',
			[
				'label'		=> esc_html__( 'Video start minute', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'default'	=> 0,
				'separator'	=> 'before',
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control( // aparat_start_second
			'aparat_start_second',
			[
				'label'		=> esc_html__( 'Video start seconds', 'bijan' ),
				'type'		=> \Elementor\Controls_Manager::NUMBER,
				'min'		=> 0,
				'max'		=> 59,
				'default'	=> 0,
				'dynamic'	=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->video_controls();
		$this->cover_controls();
		$this->aparat_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		get_template_part( "templates/components/video", null, $settings );
	}
}