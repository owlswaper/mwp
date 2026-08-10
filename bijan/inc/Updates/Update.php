<?php
namespace Bijan\Updates;

use Bijan\Utils;

class Update {
	private static $TYPE = 'theme'; // theme | plugin
	private static $NAME = 'bijan'; // Name at the sheyda server
	private static $VERSION = BIJAN_VERSION; // Current version of the project
	private static $LAST_UPDATE_OPTION = 'bijan_last_updated_version'; // Option name to store last updated version
	private static $DIR = BIJAN_DIR; // DIR path for list of updates file
	private static $UPDATE_DIR = BIJAN_DIR . "Updates"; // DIR path for list of updates file
	private static $UPDATE_CLASS = "Bijan\Updates\V"; // Prefixed class to execute the updates file
	private static $UPDATE_NOTICE_OPTION = 'bijan_update_notice'; // Option name for show update notice of the product
	private static $IS_LOCAL = BIJAN_IS_LOCAL;
	
	// Only for plugin
	private static $PLUGIN_FILE = '';

	// Settings
	private static $request_args = [
		'timeout'		=> 15,
		'httpversion'	=> '1.1',
		'sslverify'		=> false,
	];

	private static function texts() {
		return [
			'update_notice'	=> esc_html__( "A new version of the %s is available.", 'bijan' ),
			'see_update'	=> esc_html__( "See updates", 'bijan' ),
		];
	}

	public static function execute() {
		$last_updated_version = get_option( self::$LAST_UPDATE_OPTION, '1.0.0.0' );
		if( version_compare( self::$VERSION, $last_updated_version, '>' ) ) {
			if( !function_exists( 'WP_Filesystem' ) || !class_exists( 'WP_Filesystem_Direct' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				require_once( ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php' );
				require_once( ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php' );
			}
			$wp_filesystem = new \WP_Filesystem_Direct( false );
			$versions = $wp_filesystem->dirlist( self::$UPDATE_DIR );
			$versions = Utils::unset( $versions, ['Update.php'] );
			if( !empty( $versions ) ) {
				$versions = array_keys( $versions );
				$versions = array_map( function( $filename ) {
					$version = str_replace( ['V', '.php'], '', $filename );
					$version = str_replace( '_', '.', $version );
					return $version;
				}, $versions );
				$versions = array_filter( $versions, function( $version ) use( $last_updated_version ) {
					return version_compare( $last_updated_version, $version, '<' );
				} );

				foreach( $versions as $version ) {
					$version_filename = str_replace( '.', '_', $version );
					$filename = self::$UPDATE_DIR . "/V{$version_filename}.php";
					require( $filename );
					$class = self::$UPDATE_CLASS . $version_filename;
					$class::update();
				}
			}

			update_option( self::$LAST_UPDATE_OPTION, self::$VERSION, true );
		}
	}

	public static function enqueue( $hook ) {
		if( $hook != 'update-core.php' ) return;
		?>
		<style>
			#TB_window.plugin-details-modal:has(iframe[src*="sheyda"]) {
				border-radius: 32px;
				overflow: hidden;
			}
		</style>
		<?php
	}

	private static function get_update_response() {
		$endpoint = '/sheyda/v2/product/check_version?product=' . self::$NAME . '&version=' . self::$VERSION;
		$hosts = [
			'https://sheydateam.ir/api',
			'https://sheydateam.com/api',
		];

		if( self::$IS_LOCAL ) {
			$hosts = ['http://localhost/sheyda/wp-json'];
		}

		$update_response = [
			'need_update'	=> false,
			'file'			=> '',
			'version'		=> '',
			'rtl_url'		=> '',
			'icon'			=> '',
		];
		
		foreach( $hosts as $host ) {
			$url = $host . $endpoint;
			$request = wp_remote_get( $url, self::$request_args );
			
			if ( is_wp_error( $request ) ) {
				continue;
			}
			
			$response_code = wp_remote_retrieve_response_code( $request );
			if ( $response_code !== 200 ) {
				continue;
			}
			
			$response = json_decode( wp_remote_retrieve_body( $request ), true );
			
			if ( empty( $response ) ) {
				continue;
			}

			if( empty( $response['need_update'] ) || !Utils::to_bool( $response['need_update'] ) || empty( $response['file'] ) ) {
				break;
			}

			// Need update
			$update_response = $response;
			break;
		}
		return $update_response;
	}

	public static function theme_update_checker() {
		if( self::$TYPE != 'theme' ) return;

		if( !current_user_can( 'update_themes' ) ) {
			return;
		}

		$update_response = self::get_update_response();

		if( $update_response['need_update'] && $update_response['file'] ) {
			$update_themes = get_site_transient( 'update_themes' );
			$theme = wp_get_theme( get_template() );
			$update_themes->response[$theme->get_stylesheet()] = (array)[
				'id'				=> $update_response['file'],
				'theme'				=> $theme->get( 'Name' ),
				'new_version'		=> $update_response['version'] ?? self::$VERSION,
				'url'				=> $update_response['rtl_url'] ?? '',
				'package'			=> $update_response['file'],
				'theme_data'		=> $theme,
				'theme_stylesheet'	=> $theme->get_stylesheet(),
			];
			remove_action( 'set_site_transient_update_themes', [__CLASS__, 'theme_update_checker'] );
			set_site_transient( 'update_themes', $update_themes );

			update_option( self::$UPDATE_NOTICE_OPTION, $update_response, false );
		} else {
			// No update needed - clear stale data
			delete_option( self::$UPDATE_NOTICE_OPTION );
		}
	}

	public static function plugin_update_checker() {
		if( self::$TYPE != 'plugin' ) return;

		if( !current_user_can( 'update_plugins' ) ) {
			return;
		}

		$update_response = self::get_update_response();

		if( $update_response['need_update'] && $update_response['file'] ) {
			$update_plugins = get_site_transient( 'update_plugins' );
			
			$plugins = get_plugins();
			$plugin_filepath = basename( self::$DIR ) . "/" . self::$PLUGIN_FILE;
			$plugin = $plugins[$plugin_filepath];
			
			$plugin_update_info = new \stdClass();
			$plugin_update_info->id = $update_response['file'] ?? '';
			$plugin_update_info->slug = self::$IS_LOCAL 
				? "http://localhost/sheyda/?changelog=" . self::$NAME 
				: "https://sheydateam.ir/?changelog=" . self::$NAME;
			$plugin_update_info->plugin = $plugin_filepath;
			$plugin_update_info->new_version = $update_response['version'] ?? self::$VERSION;
			$plugin_update_info->url = $update_response['rtl_url'] ?? '';
			$plugin_update_info->package = $update_response['file'] ?? '';
			$plugin_update_info->plugin_data = $plugin;
			$plugin_update_info->plugin_file = $plugin_filepath;
			$plugin_update_info->icons = [
				'default' => $update_response['icon'] ?? '',
			];

			$update_plugins->response[$plugin_filepath] = $plugin_update_info;

			remove_action( 'set_site_transient_update_plugins', [__CLASS__, 'plugin_update_checker'] );
			set_site_transient( 'update_plugins', $update_plugins );

			update_option( self::$UPDATE_NOTICE_OPTION, $update_response, false );
		} else {
			// No update needed - clear stale data
			delete_option( self::$UPDATE_NOTICE_OPTION );
		}
	}

	public static function update_notice() {
		if ( ( self::$TYPE == 'plugin' && !current_user_can( 'update_plugins' ) ) || ( self::$TYPE == 'theme' && !current_user_can( 'update_themes' ) ) ) {
			return;
		}

		$screen = get_current_screen();
		if( $screen->id == 'update-core' ) return;
		
		$update_response = get_option( self::$UPDATE_NOTICE_OPTION, [] );
		if( empty( $update_response ) ) return;

		if( self::$TYPE == 'theme' ) {
			$theme = wp_get_theme( get_template() );
			$text_domain = $theme->get( 'TextDomain' );
			$name = __( $theme->get( 'Name' ), $text_domain );
		} else {
			$plugins = get_plugins();
			$plugin_filepath = basename( self::$DIR ) . "/" . self::$PLUGIN_FILE;
			$plugin = $plugins[$plugin_filepath];
			$text_domain = $plugin['TextDomain'];
			$name = __( $plugin['Name'], $text_domain );
		}

		$texts = self::texts();

		$message = sprintf( $texts['update_notice'], $name );
		$message .= '<br><a href="' . esc_url( admin_url( 'update-core.php' ) ) . '#update-plugins-table" target="_blank" class="button button-primary">' . $texts['see_update'] . '</a>';

		wp_admin_notice( $message, [
			'type'			=> 'warning',
			'dismissible'	=> false,
			'id'			=> self::$UPDATE_NOTICE_OPTION,
		] );
	}

	public static function plugins_api( $result, $action, $args ) {
		if( $action == 'plugin_information' ) {
			if( !empty( $args->slug ) ) {
				$parse_url = wp_parse_url( $args->slug );
				if( $parse_url ) {
					if( !empty( $parse_url['host'] ) ) {
						$host = strtolower( Utils::convert_chars( $parse_url['host'] ) );
						if ( $host === 'sheydateam.ir' || $host === 'localhost' ) {
							$request = wp_remote_get( $args->slug, self::$request_args );
							$html = '';
							if ( ! empty( $request ) && ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) === 200 ) {
								$html = wp_remote_retrieve_body( $request );
							}
							if ( ! empty( $html ) ) {
								echo $html;
								die;
							}
						}
					}
				}
			}
		}

		return $result ?? null;
	}

	public static function after_update( $upgrader, $options ) {
		if ( ! isset( $options['type'], $options['action'] ) || $options['action'] !== 'update' ) return;

		$update_response = get_option( self::$UPDATE_NOTICE_OPTION, [] );
		if ( empty( $update_response['version'] ) ) return;

		if ( self::$TYPE === 'theme' && $options['type'] === 'theme' ) {
			$themes = (array) ( $options['themes'] ?? [] );
			$parent_theme_slug = get_template();
			if ( ! in_array( $parent_theme_slug, $themes, true ) ) return;

			$parent_theme = wp_get_theme( $parent_theme_slug );
			$installed_version = $parent_theme->get( 'Version' );

			if ( version_compare( $installed_version, $update_response['version'], '>=' ) ) {
				delete_option( self::$UPDATE_NOTICE_OPTION );
			}

		} elseif ( self::$TYPE === 'plugin' && $options['type'] === 'plugin' ) {
			$plugins = (array) ( $options['plugins'] ?? [] );
			$plugin_filepath = basename( self::$DIR ) . '/' . self::$PLUGIN_FILE;
			if ( ! in_array( $plugin_filepath, $plugins, true ) ) return;

			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_filepath, false, false );
			$installed_version = $plugin_data['Version'] ?? '';

			if ( ! empty( $installed_version ) && version_compare( $installed_version, $update_response['version'], '>=' ) ) {
				delete_option( self::$UPDATE_NOTICE_OPTION );
			}
		}
	}

	public static function after_theme_switch( $new_name, $new_theme ) {
		if ( self::$TYPE !== 'theme' ) return;

		$parent_theme = wp_get_theme( get_template() );
		$matches = strtolower( $new_theme->get_stylesheet() ) === strtolower( self::$NAME )
				|| strtolower( $new_theme->get_template() )  === strtolower( self::$NAME );

		if ( ! $matches ) return;

		$installed_version = $parent_theme->get( 'Version' );

		$update_response = get_option( self::$UPDATE_NOTICE_OPTION, [] );
		if ( empty( $update_response['version'] ) ) return;

		if ( version_compare( $installed_version, $update_response['version'], '>=' ) ) {
			delete_option( self::$UPDATE_NOTICE_OPTION );
		}
	}
}
Update::execute();
add_action( 'admin_enqueue_scripts', [Update::class, 'enqueue'] );
add_action( 'set_site_transient_update_themes', [Update::class, 'theme_update_checker'] );
add_action( 'set_site_transient_update_plugins', [Update::class, 'plugin_update_checker'] );
add_action( 'admin_notices', [Update::class, 'update_notice'] );
add_filter( 'plugins_api', [Update::class, 'plugins_api'], 10, 3 );
add_action( 'upgrader_process_complete', [Update::class, 'after_update'], 10, 2 );
add_action( 'switch_theme', [Update::class, 'after_theme_switch'], 10, 2 );
