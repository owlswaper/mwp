<?php
namespace Bijan\Backend;

class Messages {
	public static function server_messages() {
		wp_enqueue_style( 'bijan-notices', BIJAN_URI . "assets/css/backend/notices.min.css", [], BIJAN_VERSION );
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-notices', BIJAN_URI . "assets/js/notices.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-notices', BIJAN_URI . "assets/js/notices.min.js", ['jquery'], BIJAN_VERSION, true );
		}
		wp_localize_script( 'bijan-notices', 'bijan_notices', [
			'ajaxUrl'	=> admin_url( 'admin-ajax.php' ),
			'nonce'		=> wp_create_nonce( 'bijan_dismiss_notice' ),
		] );
		?>
		<div id="bijan-notices" class="notice" style="display:none"></div>
		<?php
	}
}
add_action( 'admin_notices', [Messages::class, 'server_messages'] );