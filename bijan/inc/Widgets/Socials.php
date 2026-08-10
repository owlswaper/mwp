<?php
namespace Bijan\Widgets;

use Bijan\Utils;
use Bijan\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

class Socials extends \WP_Widget {
	private $defaults = [
		'title'	=> '',
		'items'	=> [],
	];

	public function __construct() {
		parent::__construct(
			'bijan_socials', // Base ID
			esc_html__( 'Bijan - Socials', 'bijan' ), // Name
			[
				'description'	=> __( 'Show social pages', 'bijan' )
			]
		);
	}

	public function form( $instance ) {
		$values = Utils::check_default( $instance, $this->defaults );
		$options = Options::get_options( [
			'socials'	=> [
				'social_name'	=> [
					esc_html__( "Instagram", 'bijan' ),
					esc_html__( "LinkedIn", 'bijan' ),
					esc_html__( "Telegram", 'bijan' ),
				],
				'social_icon'	=> [
					'bijan-icon-instagram',
					'bijan-icon-linkedin-bold',
					'bijan-icon-telegram-bold',
				],
				'social_link'	=> [
					'#',
					'#',
					'#',
				]
			]
		] );

		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'bijan' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $values['title'] ); ?>" >
		</p>

		<?php if( !empty( $options['socials'] ) ) { ?>
			<div><?php esc_html_e( 'Select your socials to show in this widget', 'bijan' ) ?></div>

			<?php foreach( $options['socials']['social_name'] as $social_name ) {
				?>
				<p>
					<label>
						<input type="checkbox" name="<?php echo $this->get_field_name( 'items' ) ?>[]" value="<?php echo esc_attr( $social_name ) ?>" <?php checked( in_array( $social_name, $values['items'] ) ) ?>">
						<?php echo esc_html( $social_name ) ?>
					</label>
				</p>
				<?php
			}
		} else {
			?>
			<p><strong><?php printf( __( 'Add some social accounts from <a href="%s">settings</a>', 'bijan' ), admin_url( "admin.php?page=bijan&tab=34" ) ) ?></strong></p>
			<?php
		}
		?>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];
		$instance['items'] = !empty( $new_instance['items'] ) ? $new_instance['items'] : $instance['items'];

		return $instance;
	}

	public function widget( $args, $instance ) {
		$options = Options::get_options( [
			'socials'	=> [
				'social_name'	=> [
					esc_html__( "Instagram", 'bijan' ),
					esc_html__( "LinkedIn", 'bijan' ),
					esc_html__( "Telegram", 'bijan' ),
				],
				'social_icon'	=> [
					'bijan-icon-instagram',
					'bijan-icon-linkedin-bold',
					'bijan-icon-telegram-bold',
				],
				'social_link'	=> [
					'#',
					'#',
					'#',
				]
			],
		]);
		if( empty( $options['socials'] ) || empty( $options['socials']['social_icon'] ) ) return;
		$socials = $options['socials'];

		$instance = Utils::check_default( $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $instance['title'] );
 
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
 
		$items = $instance['items'];
		?>
		<div class="social-items">
			<?php
			foreach( $items as $social_name ) {
				$index = array_search( $social_name, $socials['social_name'] );
				if( $index === false ) continue;
				$link = $socials['social_link'][$index];
				$icon = $socials['social_icon'][$index];
				?>
				<a href="<?php echo esc_url( $link ) ?>" target="_blank" rel="noopener noreferrer" class="social-item">
					<i class="<?php echo esc_attr( $icon ) ?> social-icon"></i>
					<span class="social-name"><?php echo esc_html( $social_name ) ?></span>
				</a>
			<?php } ?>
		</div>
		<?php
		
		echo $args['after_widget'];
	}
}