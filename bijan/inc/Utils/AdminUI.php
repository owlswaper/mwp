<?php
namespace Bijan\Utils;

use Bijan\Utils;

class AdminUI extends Utils {
	public static function modal( array $args ) {
		$args = parent::check_default( $args, [
			'id'				=> '',
			'title'				=> '',
			'submit_btn_text'	=> esc_html__( 'Apply', 'bijan' ),
			'body'				=> '',
			'classes'			=> [],
		] );

		$classes = array_merge( ['bijan-modal'], $args['classes'] );
		?>
		<div class="<?php echo parent::prepare_html_classes( $classes ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>">
			<div class="bijan-modal-head">
				<span class="bijan-modal-title"><?php echo esc_html( $args['title'] ) ?></span>
				<div class="bijan-modal-close"><i class="dashicons dashicons-no-alt"></i></div>
			</div>

			<div class="bijan-modal-content"><?php echo $args['body'] ?></div>

			<div class="bijan-modal-footer">
				<button class="button button-primary bijan-modal-close bijan-modal-submit-btn"><?php echo esc_html( $args['submit_btn_text'] ) ?></button>
			</div>
		</div>
		<div class="bijan-modal-overlay"></div>
		<?php
	}

	public static function icon_picker( array $args ) {
		$args = parent::check_default( $args, [
			'id'		=> '',
			'name'		=> '',
			'icon'		=> '',
			'modal_id'	=> '',
		] );
		if( !$args['id'] ) {
			$args['id'] = $args['name'];
		}
		?>
		<div class="icon-picker-form" data-modal="<?php echo esc_attr( $args['modal_id'] ) ?>">
			<i class="<?php echo esc_attr( $args['icon'] ) ?> icon-picker-select icon-picker-select-icon"></i>
			<input type="text" name="<?php echo esc_attr( $args['name'] ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>" class="ltr icon-picker-field" value="<?php echo esc_attr( $args['icon'] ) ?>">
			<div class="button icon-picker-select"><?php esc_html_e( 'Select', 'bijan' ) ?></div>
		</div>
		<?php
	}

	public static function switch( $args ) {
		$args = parent::check_default( $args, [
			'name'			=> '',
			'id'			=> '',
			'value'			=> '',
			'active'		=> true,
			'label'			=> '',
			'input_classes'	=> [],
			'disabled'		=> false,
		] );

		$wrap_attrs = [
			'class'	=> ['bijan-switch-wrap'],
		];
		if( !empty( $args['id'] ) ) {
			$wrap_attrs['id'] = "{$args['id']}-wrap";
		}
		if( $args['disabled'] ) {
			$wrap_attrs['class'][] = 'disabled';
		}

		$input_attrs = [
			'type'	=> 'checkbox',
			'name'	=> $args['name'],
			'class'	=> array_merge( ['bijan-switch'], $args['input_classes'] ),
			'value'	=> $args['value'],
		];
		if( !empty( $args['id'] ) ) {
			$input_attrs['id'] = $args['id'];
		}
		if( $args['active'] ) {
			$input_attrs['checked'] = 'checked';
		}
		if( $args['disabled'] ) {
			$input_attrs['disabled'] = 'disabled';
		}
		?>
		<label <?php echo parent::get_html_attributes( $wrap_attrs ) ?>>
			<input <?php echo parent::get_html_attributes( $input_attrs ) ?>>
			<div class="bijan-switch-slider"></div>
			<div class="bijan-switch-label"><?php echo esc_html( $args['label'] ) ?></div>
		</label>
		<?php
	}

	public static function switch_select( array $args ) {
		$args = parent::check_default( $args, [
			'name'		=> '',
			'id'		=> '',
			'active'	=> '', // option key
			'options'	=> [],
			'classes'	=> [],
		] );

		$wrap_attrs = [
			'class'	=> array_merge( ['bijan-switch-select-wrap'], $args['classes'] ),
		];
		if( $args['id'] ) {
			$wrap_attrs['id'] = $args['id'];
		}

		$input_attrs = [
			'type'	=> 'radio',
			'name'	=> $args['name'],
			'class'	=> ['bijan-switch-select-input'],
		];
		?>
		<div <?php echo parent::get_html_attributes( $wrap_attrs ) ?>>
			<?php
			foreach( $args['options'] as $value => $label ) {
				$option_attrs = array_merge( $input_attrs, ['value' => $value] );
				if( $value == $args['active'] ) {
					$option_attrs['checked'] = 'checked';
				}
				?>
				<label class="bijan-switch-select-label">
					<input <?php echo parent::get_html_attributes( $option_attrs ) ?>>
					<span class="bijan-switch-select-text"><?php echo esc_html( $label ) ?></span>
				</label>
			<?php } ?>
		</div>
		<?php
	}

	public static function attachment( $args ) {
		$args = parent::check_default( $args, [
			'name'	=> '',
			'file'	=> 0,
			'icon'	=> 'dashicons dashicons-media-default',
			'type'	=> '',
		] );

		$file = get_attached_file( $args['file'] );
		$is_image = !empty( $file ) ? file_is_valid_image( $file ) : false;
		?>
		<div class="bijan-attachment-wrap" data-type="<?php echo esc_html( $args['type'] ) ?>">
			<input type="hidden" name="<?php echo esc_attr( $args['name'] ) ?>" class="bijan-attachment-input" value="<?php echo esc_attr( $args['file'] ) ?>">
			<div class="bijan-attachment-icon">
				<?php
				if( $is_image ) {
					echo wp_get_attachment_image( $args['file'], [80, 80] );
				} else {
					?>
					<i class="<?php echo $args['icon'] ?>"></i>
				<?php } ?>
			</div>
			<div class="bijan-attachment-details">
				<strong class="bijan-attachment-name"><?php echo esc_html( !empty( $file ) ? wp_basename( $file ) : __( 'Select file', 'bijan' ) ) ?></strong>
				<div class="bijan-attachment-size"<?php parent::hide( true, !empty( $file ) ) ?>><?php echo esc_html( !empty( $file ) ? size_format( filesize( $file ) ) : '' ) ?></div>
			</div>
		</div>
		<?php
	}

	public static function alert( array $args ) {
		$args = parent::check_default( $args, [
			'text'		=> '',
			'type'		=> 'notice',
			'icon'		=> '',
			'classes'	=> [],
		] );
		
		$classes = [
			'bijan-alert',
			"bijan-alert-{$args['type']}",
		];
		$classes = array_merge( $classes, $args['classes'] );
		?>
		<div class="<?php echo parent::prepare_html_classes( $classes ) ?>">
			<?php if( !empty( $args['icon'] ) ) { ?>
				<i class="bijan-alert-icon <?php echo esc_attr( $args['icon'] ) ?>"></i>
			<?php } ?>
			<span class="bijan-alert-text"><?php echo $args['text'] ?></span>
		</div>
		<?php
	}
}