<?php
namespace Bijan;

use Bijan\Utils\AdminUI;
use Bijan\Utils\Options;
use Bijan\Utils\UI;

// Source: https://www.wpexplorer.com/adding-custom-attributes-to-wordpress-menus/
class MenuItem {
	private static $logged_in = false;
	private static $display_caches = [];
	private static $removed_items = [];

	public static function enqueue() {
		$screen = get_current_screen();
		if( $screen->base != 'nav-menus' ) return;

		AdminScripts::modal();
		AdminScripts::icon_picker();
	}

	private static function _get_display( $item_id ) {
		if( !isset( self::$display_caches[$item_id] ) ) {
			self::$display_caches[$item_id] = get_post_meta( $item_id, '_bijan_display', true ) ?: 'all';
		}
		return self::$display_caches[$item_id];
	}
	public static function fields( $item_id, $item, $depth ) {
		$options = Options::get_options( [
			'active-megamenu'	=> true,
		] );

		$icon = get_post_meta( $item_id, '_bijan_icon', true );

		$megamenu_item_id = $depth === 0 ? $item_id : $item->menu_item_parent;
		$is_megamenu_activated = Utils::to_bool( get_post_meta( $megamenu_item_id, '_bijan_megamenu', true ) );

		$selected_column = 'auto';
		if( $depth === 1 ) {
			$megamenu_columns = Utils::megamenu_columns();
			$selected_column = get_post_meta( $item_id, '_bijan_megamenu_col', true );
			if( empty( $selected_column ) || !in_array( $selected_column, array_keys( $megamenu_columns ) ) ) {
				$selected_column = 'auto';
			}
		}

		$display = self::_get_display( $item_id );
		?>
		<p class="field-display_meta description-wide">
			<label for="edit-menu-item-attr-display-<?php echo $item_id; ?>">
				<?php esc_html_e( 'Display condition', 'bijan' ); ?><br />
				<select name="bijan[display][<?php echo $item_id; ?>]" id="edit-menu-item-attr-display-<?php echo $item_id; ?>">
					<option value="all" <?php selected( $display, 'all' ) ?>><?php esc_html_e( 'All users', 'bijan' ) ?></option>
					<option value="guests" <?php selected( $display, 'guests' ) ?>><?php esc_html_e( 'Guests only', 'bijan' ) ?></option>
					<option value="users" <?php selected( $display, 'users' ) ?>><?php esc_html_e( 'Logged in users only', 'bijan' ) ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Show this item for specific users', 'bijan' ) ?></p>
			</label>
		</p>

		<div class="field-icon_menu_meta description-wide">
			<label><?php esc_html_e( 'Icon', 'bijan' ); ?><br /></label>
			<?php
			AdminUI::icon_picker( [
				'id'		=> "edit-menu-item-attr-icon-{$item_id}",
				'name'		=> "bijan[icon][{$item_id}]",
				'icon'		=> $icon,
				'modal_id'	=> 'bijan-icon-picker-modal',
			] );
			?>
			<p class="description"><?php esc_html_e( 'Enter the class of the icon you want to show before the item name', 'bijan' ) ?></p>
		</div>

		<p class="field-featured_menu_meta">
			<label>
				<input type="checkbox" name="bijan[featured][<?php echo $item_id; ?>]" class="edit-menu-item-attr-featured" value="true" id="edit-menu-item-attr-featured-<?php echo $item_id; ?>" <?php checked( Utils::to_bool( get_post_meta( $item_id, '_bijan_featured', true ) ), true ) ?>>
				<?php esc_html_e( 'Featured item', 'bijan' ); ?><br />
			</label>
		</p>

		<?php if( Utils::to_bool( $options['active-megamenu'] ) ) { ?>
			<?php if( $depth === 0 ) { ?>
				<p class="field-active_megamenu_meta">
					<label>
						<input type="checkbox" name="bijan[megamenu][<?php echo $item_id ?>]" class="bijan-active-megamenu" value="true" <?php checked( $is_megamenu_activated, true ) ?>>
						<?php esc_html_e( 'Activate mega menu', 'bijan' ) ?>
					</label>
				</p>
				<?php
			}
		}
	}

	public static function save( $menu_id, $menu_item_db_id ) {
		if( empty( $_POST ) || empty( $_POST['bijan'] ) ) return;

		$display = 'all';
		if( !empty( $_POST['bijan']['display'] ) && !empty( $_POST['bijan']['display'][$menu_item_db_id] ) ) {
			$display = Utils::convert_chars( $_POST['bijan']['display'][$menu_item_db_id] );
			$display = Utils::ensure_values_in_array( $display, ['all', 'guests', 'users'], 'all' );
			update_post_meta( $menu_item_db_id, '_bijan_display', $display );
		} else {
			delete_post_meta( $menu_item_db_id, '_bijan_display' );
		}

		$icon = '';
		if( !empty( $_POST['bijan']['icon'] ) && !empty( $_POST['bijan']['icon'][$menu_item_db_id] ) ) {
			$icon = Utils::convert_chars( $_POST['bijan']['icon'][$menu_item_db_id] );
			update_post_meta( $menu_item_db_id, '_bijan_icon', $icon );
		} else {
			delete_post_meta( $menu_item_db_id, '_bijan_icon' );
		}

		// Save featured
		$featured = !empty( $_POST["bijan"]['featured'] ) && !empty( $_POST["bijan"]['featured'][$menu_item_db_id] ) && Utils::to_bool( $_POST["bijan"]['featured'][$menu_item_db_id] );
		if( $featured ) {
			update_post_meta( $menu_item_db_id, '_bijan_featured', true );
		} else {
			delete_post_meta( $menu_item_db_id, '_bijan_featured' );
		}

		// Save megamenu activated
		$megamenu = !empty( $_POST["bijan"]['megamenu'] ) && !empty( $_POST["bijan"]['megamenu'][$menu_item_db_id] ) && Utils::to_bool( $_POST["bijan"]['megamenu'][$menu_item_db_id] );
		if( $megamenu ) {
			update_post_meta( $menu_item_db_id, '_bijan_megamenu', true );
		} else {
			delete_post_meta( $menu_item_db_id, '_bijan_megamenu' );
		}
	}

	public static function show_icon( $title, $item ) {
		if( is_a( $item, 'WP_Post' ) && isset( $item->ID ) ) {
			$title = UI::get_menu_icon( $item->ID ) . '<span class="menu-item-text">' . $title . '</span>';
		}
		return $title;
	}

	public static function item_classes( $classes, $menu_item, $args, $depth ) {
		$options = Options::get_options( [
			'active-megamenu'	=> true,
		] );

		if( Utils::to_bool( get_post_meta( $menu_item->ID, '_bijan_featured', true ) ) ) {
			$classes[] = 'bijan-featured-item';
		}

		if( Utils::to_bool( $options['active-megamenu'] ) ) {
			if( $depth === 0 ) {
				$megamenu_item_id = $depth === 0 ? $menu_item->ID : $menu_item->menu_item_parent;
				if( Utils::to_bool( get_post_meta( $megamenu_item_id, '_bijan_megamenu', true ) ) ) {
					$classes[] = 'bijan-megamenu-container';
				}
			} else if( $depth === 1 ) {
				if( Utils::to_bool( get_post_meta( $menu_item->menu_item_parent, '_bijan_megamenu', true ) ) ) {
					$classes[] = 'megamenu-wrap';
					$column = Utils::convert_chars( get_post_meta( $menu_item->ID, '_bijan_megamenu_col', true ) );
					if( $column !== '' ) {
						$classes[] = "megamenu-col-{$column}";
					}
				}
			}
		}

		return $classes;
	}

	public static function set_display_items( $items ) {
		self::$logged_in = is_user_logged_in();
		foreach( $items as $key => $item ) {
			if( in_array( $item->menu_item_parent, self::$removed_items ) ) {
				self::$removed_items[] = $item->ID;
				unset( $items[$key] );
				continue;
			}
			
			$display = self::_get_display( $item->ID );
			if( $display == 'users' ) {
				if( !self::$logged_in ) {
					self::$removed_items[] = $item->ID;
					unset( $items[$key] );
					continue;
				}
			} else if( $display == 'guests' ) {
				if( self::$logged_in ) {
					self::$removed_items[] = $item->ID;
					unset( $items[$key] );
					continue;
				}
			}
		}

		return $items;
	}

	public static function icon_picker_modal() {
		$screen = get_current_screen();
		if( $screen->base != 'nav-menus' ) return;

		AdminUI::modal( [
			'id'				=> "bijan-icon-picker-modal",
			'title'				=> esc_html__( "Select your icon", 'bijan' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'bijan' ),
		] );
	}
}
add_action( 'admin_enqueue_scripts', [MenuItem::class, 'enqueue'] );
add_action( 'wp_nav_menu_item_custom_fields', [MenuItem::class, 'fields'], 10, 3 );
add_action( 'wp_update_nav_menu_item', [MenuItem::class, 'save'], 10, 2 );
add_filter( 'nav_menu_item_title', [MenuItem::class, 'show_icon'], 10, 2 );
add_filter( 'nav_menu_css_class', [MenuItem::class, 'item_classes'], 10, 4 );
add_filter( 'wp_nav_menu_objects', [MenuItem::class, 'set_display_items'], 1 );
add_action( 'admin_footer', [MenuItem::class, 'icon_picker_modal'] );