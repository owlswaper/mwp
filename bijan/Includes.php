<?php
namespace Bijan;

use Bijan\Utils\Options;
use Bijan\Utils\SMS;

class Includes {
	public static function main( $is_admin ) {
		include( BIJAN_DIR . "inc/Casts/Mobile.php" );

		include( BIJAN_DIR . "inc/Updates/Update.php" );

		$options = Options::get_options( [
			'notifications'		=> true,
			'sms'				=> true,
			'wc-price-history'	=> true,
			'wc-compare'		=> true,
		] );

		include( BIJAN_DIR . "inc/Utils/Sanitizers.php" );
		include( BIJAN_DIR . "inc/Utils/Page.php" );
		include( BIJAN_DIR . "inc/Utils/Archive.php" );
		include( BIJAN_DIR . "inc/Utils/Product.php" );
		include( BIJAN_DIR . "inc/Utils/Story.php" );
		include( BIJAN_DIR . "inc/Utils/UI.php" );
		include( BIJAN_DIR . "inc/Utils/Elementor.php" );
		include( BIJAN_DIR . "inc/Utils/User.php" );
		include( BIJAN_DIR . "inc/Utils/WC.php" );
		include( BIJAN_DIR . "inc/Utils/Wishlist.php" );
		include( BIJAN_DIR . "inc/Utils/Notifications.php" );
		if( $options['sms'] ) {
			include( BIJAN_DIR . "inc/Utils/SMS.php" );
		}
		if( $options['wc-price-history'] ) {
			include( BIJAN_DIR . "inc/Utils/WC/PriceHistory.php" );
		}
		if( $options['wc-compare'] ) {
			include( BIJAN_DIR . "inc/Utils/WC/Compare.php" );
		}
		if( $is_admin ) {
			include( BIJAN_DIR . "inc/Utils/AdminUI.php" );
		}

		if ( !isset( $redux_demo ) && file_exists( BIJAN_DIR . '/Redux/Options.php' ) ) {
			include_once( BIJAN_DIR . 'Redux/Options.php' );
		}

		if( wp_doing_ajax() ) {
			include( BIJAN_DIR . "inc/AJAX.php" );
		} else {
			include( BIJAN_DIR . "inc/PublicScripts.php" );
		}

		self::DB();

		if( $is_admin ) {
			include( BIJAN_DIR . "Redux/Save.php" );

			include( BIJAN_DIR . "inc/Backend/DashboardFont.php" );

			include( BIJAN_DIR . "inc/Backend/Messages.php" );
		}

		include( BIJAN_DIR . "inc/MenuItem.php" );

		include( BIJAN_DIR . "inc/PostTypes/Story.php" );
		if( Utils::to_bool( $options['notifications'] ) ) {
			include( BIJAN_DIR . "inc/PostTypes/Notification.php" );
		}

		if( Utils::to_bool( $options['sms'] ) ) {
			include( BIJAN_DIR . "inc/SMS/Gateway.php" );

			// Include gateway files automatically
			$gateways = SMS::gateways();
			foreach( $gateways as $gateway_name => $gateway ) {
				$filename = Utils::convert_to_pascal_case( $gateway_name );
				include( BIJAN_DIR . "inc/SMS/{$filename}.php" );
			}
			
			include( BIJAN_DIR . "inc/SMS/SMS.php" );
		}

		include( BIJAN_DIR . "inc/ElementorControls.php" );
		include( BIJAN_DIR . "inc/ElementorControls/Button.php" );
		include( BIJAN_DIR . "inc/ElementorControls/Slider.php" );
		include( BIJAN_DIR . "inc/ElementorControls/SectionTitle.php" );

		if( $is_admin ) {
			include( BIJAN_DIR . "inc/Changelogs/Changelog.php" );
		}
	}

	public static function DB() {
		$options = Options::get_options( [
			'wishlist'			=> true,
			'notifications'		=> true,
			'sms'				=> true,
			'wc-price-history'	=> true,
		] );
		if( Utils::to_bool( $options['wishlist'] ) ) {
			include( BIJAN_DIR . "inc/Models/Wishlist.php" );
		}
		if( Utils::to_bool( $options['notifications'] ) ) {
			include( BIJAN_DIR . "inc/Models/NotificationsUserRel.php" );
		}
		if( Utils::to_bool( $options['sms'] ) ) {
			include( BIJAN_DIR . "inc/Models/OTP.php" );
		}
		if( Utils::to_bool( $options['wc-price-history'] ) ) {
			include( BIJAN_DIR . "inc/Models/PriceHistory.php" );
		}
	}

	public static function wc( $is_admin ) {
		if( !Utils::is_wc_active() ) return;
		
		include( BIJAN_DIR . "woocommerce/WC.php" );

		if( $is_admin ) {
			$options = Options::get_options( [
				'wc-price-history'	=> true,
			] );
			include( BIJAN_DIR . "inc/Backend/Metaboxes/Product/Inventory.php" );
			include( BIJAN_DIR . "inc/Backend/Metaboxes/Product/Attribute.php" );
			include( BIJAN_DIR . "inc/Backend/Metaboxes/Product/Notes.php" );
			include( BIJAN_DIR . "inc/Backend/Metaboxes/Product/Icons.php" );
			// include( BIJAN_DIR . "inc/Backend/Metaboxes/Product/Video.php" );

			if( Utils::to_bool( $options['wc-price-history'] ) ) {
				include( BIJAN_DIR . "inc/Backend/Metaboxes/Product/PriceHistory.php" );
			}

			include( BIJAN_DIR . "inc/Backend/WCAttributeFields.php" );
		}
	}

	public static function integrations( $is_admin ) {
		include( BIJAN_DIR . "inc/Integrations/wp-rocket.php" );
	}

	public static function backend_pages() {
		$options = Options::get_options( [
			'sms'	=> true,
		] );
		if( Utils::to_bool( $options['sms'] ) ) {
			include( BIJAN_DIR . "inc/Backend/Pages/SMS.php" );
		}
	}
}
$is_admin = is_admin();
Includes::main( $is_admin );
Includes::wc( $is_admin );
Includes::integrations( $is_admin );
if( $is_admin ) {
	Includes::backend_pages();
}