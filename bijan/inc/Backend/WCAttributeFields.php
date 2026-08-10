<?php

use Bijan\AdminScripts;
use Bijan\PublicScripts;
use Bijan\Utils;
use Bijan\Utils\AdminUI;
use Bijan\Utils\WC;

class WCAttributeFields {
	public static function add_fields() {
		?>
		<div class="form-field">
			<label for="bijan_attribute_display_type"><?php esc_html_e( 'Attribute display type', 'bijan' ); ?></label>
			<select name="bijan_attribute_display_type" id="bijan_attribute_display_type">
				<?php foreach( WC::attr_display_types() as $key => $label ) { ?>
					<option value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $label ) ?></option>
				<?php } ?>
			</select>
			<p class="description"><?php esc_html_e( 'This option specifies how to display this feature in filters', 'bijan' ) ?></p>
		</div>
		<?php
	}

	public static function edit_fields() {
		$id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;

		$attr_settings = WC::get_attribute_settings( $id );
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="bijan_attribute_display_type"><?php esc_html_e( 'Attribute display type', 'bijan' ); ?></label>
			</th>

			<td>
				<select name="bijan_attribute_display_type" id="bijan_attribute_display_type">
					<?php foreach( WC::attr_display_types() as $key => $label ) { ?>
						<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $attr_settings['display_type'] ) ?>><?php echo esc_html( $label ) ?></option>
					<?php } ?>
				</select>
				<p class="description"><?php esc_html_e( 'This option specifies how to display this feature in filters', 'bijan' ) ?></p>
			</td>
		</tr>
		<?php
	}

	public static function save( $id ) {
		WC::update_attribute_settings( $id, [
			'display_type'	=> !empty( $_POST['bijan_attribute_display_type'] ) ? Utils::convert_chars( $_POST['bijan_attribute_display_type'] ) : 'select',
		] );
	}

	public static function tax_add_fields( $taxonomy ) {
		$attr_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
		$options = WC::get_attribute_settings( $attr_id );
		if( !in_array( $options['display_type'], array_keys( WC::attr_display_types() ) ) ) return;
		if( $options['display_type'] == 'color' ) {
			?>
			<div class="form-field">
				<label><?php esc_html_e( 'Color 1', 'bijan' ) ?></label>
				<div class="ltr">
					<input type="text" class="bijan-color-picker" name="bijan_color_1" id="bijan_color_1">
				</div>
			</div>

			<div class="form-field">
				<label><?php esc_html_e( 'Color 2', 'bijan' ) ?></label>
				<div class="ltr">
					<input type="text" class="bijan-color-picker" name="bijan_color_2" id="bijan_color_2">
				</div>
			</div>

			<div class="form-field">
				<label for="bijan_color_direction"><?php esc_html_e( 'Direction', 'bijan' ) ?></label>
				<select name="bijan_color_direction" id="bijan_color_direction" class="widefat">
					<option value="vertical"><?php esc_html_e( 'Vertical', 'bijan' ) ?></option>
					<option value="horizontal"><?php esc_html_e( 'Horizontal', 'bijan' ) ?></option>
				</select>
			</div>
			<?php
		} else if( $options['display_type'] == 'image' ) {
			?>
			<div class="form-field">
				<?php
				AdminUI::attachment( [
					'name'	=> 'bijan_image',
					'type'	=> 'image'
				] );
				?>
			</div>
			<?php
		} else if( $options['display_type'] == 'icon' ) {
			?>
			<div class="form-field">
				<label for="bijan_icon"><?php esc_html_e( 'Select icon', 'bijan' ) ?></label>
				<?php
				AdminUI::icon_picker( [
					'name'		=> "bijan_icon",
					'id'		=> "bijan_icon",
					'modal_id'	=> 'bijan-icon-picker-modal',
				] );
				AdminUI::modal( [
					'id'				=> "bijan-icon-picker-modal",
					'title'				=> esc_html__( "Select your icon", 'bijan' ),
					'classes'			=> ['icon-picker-modal'],
					'submit_btn_text'	=> esc_html__( "Select icon", 'bijan' ),
				] );
				?>
			</div>
			<?php
		} else if( $options['display_type'] == 'gradient' ) {
			?>
			<div class="form-field">
				<label><?php esc_html_e( 'Color 1', 'bijan' ) ?></label>
				<div class="ltr">
					<input type="text" class="bijan-color-picker" name="bijan_gradient_color_1" id="bijan_gradient_color_1">
				</div>
			</div>

			<div class="form-field">
				<label><?php esc_html_e( 'Color 2', 'bijan' ) ?></label>
				<div class="ltr">
					<input type="text" class="bijan-color-picker" name="bijan_gradient_color_2" id="bijan_gradient_color_2">
				</div>
			</div>

			<div class="form-field">
				<label><?php esc_html_e( 'Angle', 'bijan' ) ?></label>
				<input type="number" min="-360" max="359" name="bijan_gradient_angle" id="bijan_gradient_angle">
				<p class="description"><?php esc_html_e( 'Degree', 'bijan' ) ?></p>
			</div>
			<?php
		}
	}

	public static function tax_edit_fields( $term, $taxonomy ) {
		$attr_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
		$options = WC::get_attribute_settings( $attr_id );
		if( !in_array( $options['display_type'], array_keys( WC::attr_display_types() ) ) ) return;

		if( $options['display_type'] == 'color' ) {
			$color = WC::get_term_color( $term->term_id );
			?>
			<tr class="form-field">
				<th>
					<label><?php esc_html_e( 'Color 1', 'bijan' ) ?></label>
				</th>
				<td>
					<div class="ltr">
						<input type="text" class="bijan-color-picker" name="bijan_color_1" id="bijan_color_1" value="<?php echo esc_attr( $color['color_1'] ) ?>">
					</div>
				</td>
			</tr>

			<tr class="form-field">
				<th>
					<label><?php esc_html_e( 'Color 2', 'bijan' ) ?></label>
				</th>
				<td>
					<div class="ltr">
						<input type="text" class="bijan-color-picker" name="bijan_color_2" id="bijan_color_2" value="<?php echo esc_attr( $color['color_2'] ) ?>">
					</div>
				</td>
			</tr>

			<tr class="form-field">
				<th>
					<label><?php esc_html_e( 'Direction', 'bijan' ) ?></label>
				</th>
				<td>
					<select name="bijan_color_direction" id="bijan_color_direction" class="widefat">
						<option value="vertical" <?php selected( 'vertical', $color['direction'] ) ?>><?php esc_html_e( 'Vertical', 'bijan' ) ?></option>
						<option value="horizontal" <?php selected( 'horizontal', $color['direction'] ) ?>><?php esc_html_e( 'Horizontal', 'bijan' ) ?></option>
					</select>
				</td>
			</tr>
			<?php
		} else if( $options['display_type'] == 'image' ) {
			$image = WC::get_term_img( $term->term_id );
			?>
			<tr class="form-field">
				<th>
					<label for="bijan_image"><?php esc_html_e( 'Image', 'bijan' ) ?></label>
				</th>
				<td>
					<?php
					AdminUI::attachment( [
						'name'	=> 'bijan_image',
						'file'	=> $image,
						'type'	=> 'image',
					] );
					?>
				</td>
			</tr>
			<?php
		} else if( $options['display_type'] == 'icon' ) {
			$icon = WC::get_term_icon( $term->term_id );
			?>
			<tr class="form-field">
				<th>
					<label for="bijan_icon"><?php esc_html_e( 'Icon', 'bijan' ) ?></label>
				</th>
				<td>
					<?php
					AdminUI::icon_picker( [
						'name'		=> "bijan_icon",
						'id'		=> "bijan_icon",
						'modal_id'	=> 'bijan-icon-picker-modal',
						'icon'		=> $icon,
					] );
					AdminUI::modal( [
						'id'				=> "bijan-icon-picker-modal",
						'title'				=> esc_html__( "Select your icon", 'bijan' ),
						'classes'			=> ['icon-picker-modal'],
						'submit_btn_text'	=> esc_html__( "Select icon", 'bijan' ),
					] );
					?>
				</td>
			</tr>
			<?php
		} else if( $options['display_type'] == 'gradient' ) {
			$gradient = WC::get_term_gradient( $term->term_id );
			?>
			<tr class="form-field">
				<th>
					<label><?php esc_html_e( 'Color 1', 'bijan' ) ?></label>
				</th>
				<td>
					<div class="ltr">
						<input type="text" class="bijan-color-picker" name="bijan_gradient_color_1" id="bijan_gradient_color_1" value="<?php echo esc_attr( $gradient['color_1'] ) ?>">
					</div>
				</td>
			</tr>

			<tr class="form-field">
				<th>
					<label><?php esc_html_e( 'Color 2', 'bijan' ) ?></label>
				</th>
				<td>
					<div class="ltr">
						<input type="text" class="bijan-color-picker" name="bijan_gradient_color_2" id="bijan_gradient_color_2" value="<?php echo esc_attr( $gradient['color_2'] ) ?>">
					</div>
				</td>
			</tr>

			<tr class="form-field">
				<th>
					<label><?php esc_html_e( 'Angle', 'bijan' ) ?></label>
				</th>
				<td>
					<div class="ltr">
						<input type="number" min="-360" max="359" name="bijan_gradient_angle" id="bijan_gradient_angle" value="<?php echo esc_attr( $gradient['angle'] ) ?>">
						<p class="description"><?php esc_html_e( 'Degree', 'bijan' ) ?></p>
					</div>
				</td>
			</tr>
			<?php
		}
	}

	public static function tax_save( $term_id ) {
		if( !empty( $_POST["bijan_color_1"] ) ) {
			WC::update_term_color( $term_id, [
				'color_1'	=> Utils::convert_chars( $_POST["bijan_color_1"] ),
				'color_2'	=> Utils::convert_chars( $_POST["bijan_color_2"] ),
				'direction'	=> Utils::convert_chars( $_POST["bijan_color_direction"] ),
			] );
		}
		if( !empty( $_POST["bijan_image"] ) ) {
			WC::update_term_img( $term_id, Utils::convert_chars( $_POST["bijan_image"], true, 'absint' ) );
		}
		if( !empty( $_POST["bijan_icon"] ) ) {
			WC::update_term_icon( $term_id, Utils::convert_chars( $_POST["bijan_icon"] ) );
		}
		if( !empty( $_POST["bijan_gradient_color_1"] ) && !empty( $_POST["bijan_gradient_color_2"] ) && isset( $_POST["bijan_gradient_angle"] ) ) {
			WC::update_term_gradient( $term_id, [
				'color_1'	=> Utils::convert_chars( $_POST["bijan_gradient_color_1"] ),
				'color_2'	=> Utils::convert_chars( $_POST["bijan_gradient_color_2"] ),
				'angle'		=> Utils::convert_chars( $_POST["bijan_gradient_angle"] ),
			] );
		}
	}

	public static function taxonomy_enqueue( $hook ) {
		if( $hook != 'edit-tags.php' && $hook != 'term.php' ) return;

		wp_enqueue_media();
		PublicScripts::jscolorpicker();

		AdminScripts::attachment();
		AdminScripts::modal();
		AdminScripts::icon_picker();
	}
}
add_action( 'woocommerce_after_add_attribute_fields', [WCAttributeFields::class, 'add_fields'] );
add_action( 'woocommerce_after_edit_attribute_fields', [WCAttributeFields::class, 'edit_fields'] );
add_action( 'woocommerce_attribute_added', [WCAttributeFields::class, 'save'] );
add_action( 'woocommerce_attribute_updated', [WCAttributeFields::class, 'save'] );

global $pagenow;
if( !empty( $pagenow ) && in_array( $pagenow, ['edit-tags.php', 'term.php', 'admin-ajax.php'] ) ) {
	$attribute_taxonomies = wc_get_attribute_taxonomies();
	add_action( 'admin_enqueue_scripts', [WCAttributeFields::class, 'taxonomy_enqueue'] );
	foreach( $attribute_taxonomies as $tax ) {
		add_action( "pa_{$tax->attribute_name}_add_form_fields", [WCAttributeFields::class, 'tax_add_fields'] );
		add_action( "pa_{$tax->attribute_name}_edit_form_fields", [WCAttributeFields::class, 'tax_edit_fields'], 10, 2 );
		add_action( "created_pa_{$tax->attribute_name}", [WCAttributeFields::class, 'tax_save'] );
		add_action( "edited_pa_{$tax->attribute_name}", [WCAttributeFields::class, 'tax_save'] );
	}
}