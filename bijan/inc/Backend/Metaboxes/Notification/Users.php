<?php
namespace Bijan\Metaboxes\Backend\Notification;

use Bijan\AdminScripts;
use Bijan\Model\NotificationsUserRel;
use Bijan\PublicScripts;
use Bijan\Utils;
use Bijan\Utils\Notifications;

if( !defined( 'ABSPATH' ) ) exit;

class Settings {
	PRIVATE STATIC $PREFIX = "bijan_notification_";
	PRIVATE STATIC $POST_TYPES = ['notification'];

	public static function enqueue( $hook ) {
		if( !in_array( $hook, ['post-new.php', 'post.php'] ) || !in_array( get_post_type(), self::$POST_TYPES ) ) return;
		
		PublicScripts::select2();
		AdminScripts::metabox();

		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-notification-metabox', BIJAN_URI . "assets/js/backend/notification-metabox.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-notification-metabox', BIJAN_URI . "assets/js/backend/notification-metabox.min.js", ['jquery'], BIJAN_VERSION, true );
		}
	}

	public static function add() {
		add_meta_box(
			self::$PREFIX,			// id
			__( 'Users', 'bijan' ),	// title
			[__CLASS__, 'view'],	// callback
			self::$POST_TYPES		// screens
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "save_notification", self::$PREFIX . "nonce" );

		$notification = Notifications::get( $post );
		?>
		<div class="bijan_metabox">
			<table class="form-table">
				<tr>
					<th>
						<label for="<?php echo self::$PREFIX ?>all_users"><?php esc_html_e( 'All users', 'bijan' ) ?></label>
					</th>

					<td>
						<label>
							<input type="checkbox" name="<?php echo self::$PREFIX ?>all_users" id="<?php echo self::$PREFIX ?>all_users" value="true" <?php checked( $notification['all_users'], true ) ?>>
							<?php esc_html_e( 'Show this notification to all users', 'bijan' ) ?>
						</label>
					</td>
				</tr>

				<tr id="<?php echo self::$PREFIX ?>select_user"<?php echo $notification['all_users'] ? ' style="display:none"' : '' ?>>
					<th>
						<label for="<?php echo self::$PREFIX ?>users"><?php esc_html_e( 'Users', 'bijan' ) ?></label>
					</th>

					<td>
						<select name="<?php echo self::$PREFIX ?>users[]" id="<?php echo self::$PREFIX ?>users" class="bijan_metabox_user_finder" multiple>
							<?php foreach( $notification['users'] as $user ) { ?>
								<option value="<?php echo esc_attr( $user->ID ) ?>" selected><?php echo esc_html( $user->display_name ) ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( empty( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save_notification" ) ) return;

		$old_data = Notifications::get( $post, false );

		$settings = [];
		$settings['all_users'] = isset( $_POST[self::$PREFIX . "all_users"] );
		Notifications::save_options( $settings, $post_id );

		if( $settings['all_users'] ) { // Now it's all users. Then delete all user rel
			$notifs = NotificationsUserRel::query()->where( 'notif_id', $post_id )->get();
			foreach( $notifs as $notif ) {
				$notif->delete();
			}
		} else {
			if( !empty( $_POST[self::$PREFIX . "users"] ) ) {
				foreach( $_POST[self::$PREFIX . "users"] as $user_id ) {
					if( !in_array( $user_id, $old_data['users'] ) ) { // New
						$notif_rel = new NotificationsUserRel;
						$notif_rel->notif_id = $post_id;
						$notif_rel->user_id = $user_id;
						$notif_rel->save();
					} else {
						// unset
						$key = array_search( $user_id, $old_data['users'] );
						unset( $old_data['users'][$key] );
					}
				}
			}
			if( !empty( $old_data['users'] ) ) {
				$notifs = NotificationsUserRel::query()->whereIn( 'user_id', $old_data['users'] )->where( 'notif_id', $post_id )->get();
				foreach( $notifs as $notif ) {
					$notif->delete();
				}
			}
		}
	}
}
add_action( 'admin_enqueue_scripts', [Settings::class, 'enqueue'] );
add_action( 'add_meta_boxes', [Settings::class, 'add'] );
add_action( 'save_post', [Settings::class, 'save'], 10, 2 );