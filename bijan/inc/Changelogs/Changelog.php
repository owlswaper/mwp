<?php
namespace Bijan\Changelog;

class Changelog {
	public static function add_menu() {
		add_submenu_page(
			'bijan',						// $parent_slug:string,
			__( 'Changelogs', 'bijan' ),	// $page_title:string,
			__( 'Changelogs', 'bijan' ),	// $menu_title:string,
			'manage_options',				// $capability:string,
			'bijan-changelogs',				// $menu_slug:string,
			[__CLASS__, 'view'],			// $callback:callable,
			999								// $position:integer|float|null
		);
	}

	public static function enqueue() {
		if( empty( $_GET['page'] ) || $_GET['page'] != 'bijan-changelogs' ) return;

		wp_enqueue_style( "bijan-changelog", BIJAN_URI . "assets/css/backend/pages/changelog.min.css", [], BIJAN_VERSION );
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-changelog', BIJAN_URI . "assets/js/backend/changelog.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-changelog', BIJAN_URI . "assets/js/backend/changelog.min.js", ['jquery'], BIJAN_VERSION, true );
		}
	}

	public static function view() {
		$logo = BIJAN_URI . "assets/img/logo.svg";
		$current_version = BIJAN_VERSION;
		$rtl_page = 'https://www.rtl-theme.com/bijan-wordpress-theme/';

		$changelogs = [];
		foreach( glob( BIJAN_DIR . "inc/Changelogs/*.json" ) as $changelog_file ) {
			$version = str_replace( ['V', '.json'], '', wp_basename( $changelog_file ) );
			$version = str_replace( '_', '.', $version );
			$changelogs[$version] = wp_json_file_decode( $changelog_file, ['associative' => true] );
		}
		$changelogs = array_reverse( $changelogs );
		wp_localize_script( 'bijan-changelog', 'changelogItems', $changelogs );
		
		$active_version = array_key_first( $changelogs );
		?>
		<div class="wrap">
			<h1 class="page-title"><?php echo esc_html( get_admin_page_title() ) ?></h1>
			<hr>
			<div id="changelogs-wrap">
				<div id="changelogs-sidebar">
					<div class="changelogs-box" id="changelogs-sidebar-main">
						<div id="changelogs-sidebar-info">
							<a href="<?php echo $rtl_page ?>" target="_blank"><img src="<?php echo $logo ?>" alt=""></a>
							<div id="changelogs-sidebar-version"><?php printf( esc_html__( "Current version: %s", 'bijan' ), $current_version ) ?></div>
						</div>

						<div id="changelogs-sidebar-versions">
							<?php foreach( $changelogs as $version => $version_data ) { ?>
								<div class="changelogs-sidebar-version<?php echo $version == $active_version ? ' active' : '' ?>" data-version="<?php echo $version ?>">
									<div class="changelogs-sidebar-version-label"><?php echo $version ?></div>
									<div class="changelogs-sidebar-version-time"><?php echo date_i18n( "Y-m-d", $version_data['time'] ) ?></div>
								</div>
							<?php } ?>
						</div>
					</div>

					<div class="changelogs-box" id="changelogs-sidebar-support">
						<div id="changelogs-sidebar-support-stars">
							<?php for( $index = 1; $index <= 5; $index++ ) { ?>
								<svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M449.536 149.082c18.01-55.476 96.557-55.476 114.568 0L634.64 366.17c8.07 24.817 31.2 41.623 57.283 41.623h228.23c58.37 0 82.644 74.632 35.42 108.966L770.89 650.9c-21.08 15.36-29.936 42.526-21.864 67.343L819.56 935.27c18.014 55.537-45.474 101.678-92.7 67.404L542.24 868.472c-21.143-15.3-49.694-15.3-70.837 0l-184.62 134.205c-47.223 34.274-110.71-11.867-92.7-67.404l70.535-217.027c8.07-24.817-.783-51.983-21.866-67.343L58.067 516.76c-47.165-34.335-22.95-108.967 35.417-108.967h228.232c26.082 0 49.212-16.806 57.284-41.623l70.538-217.088z"/></svg>
							<?php } ?>
						</div>
						<a href="https://www.rtl-theme.com/dashboard/#/downloads" target="_blank" class="button button-primary"><svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><path d="M449.536 149.082c18.01-55.476 96.557-55.476 114.568 0L634.64 366.17c8.07 24.817 31.2 41.623 57.283 41.623h228.23c58.37 0 82.644 74.632 35.42 108.966L770.89 650.9c-21.08 15.36-29.936 42.526-21.864 67.343L819.56 935.27c18.014 55.537-45.474 101.678-92.7 67.404L542.24 868.472c-21.143-15.3-49.694-15.3-70.837 0l-184.62 134.205c-47.223 34.274-110.71-11.867-92.7-67.404l70.535-217.027c8.07-24.817-.783-51.983-21.866-67.343L58.067 516.76c-47.165-34.335-22.95-108.967 35.417-108.967h228.232c26.082 0 49.212-16.806 57.284-41.623l70.538-217.088z"/></svg><?php esc_html_e( 'Submit your score', 'bijan' ) ?></a>
					</div>
				</div>

				<div class="changelogs-box" id="changelogs-content">
					<div id="changelogs-version"><?php echo $active_version ?></div>
					<div id="changelogs-time"><?php echo date_i18n( "Y-m-d", $changelogs[$active_version]['time'] ) ?></div>

					<div id="changelogs-items">
						<?php foreach( $changelogs[$active_version]['log'] as $item ) { ?>
							<div class="changelogs-item"><?php echo $item ?></div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function notice() {
		$last_updated_version = get_option( 'bijan_last_updated_version', '' );
		$fresh_install = false;
		if( $last_updated_version !== '' ) {
			$last_showed_changelog = get_option( 'bijan_last_showed_changelog', '1.0.0.0' );
			$should_show = version_compare( $last_updated_version, $last_showed_changelog ) !== 0;
		} else {
			$last_showed_changelog = '1.0.0.0';
			$fresh_install = true;
			$should_show = true;
		}
		if( !$should_show ) return;

		$logo = BIJAN_URI . "assets/img/logo.svg";
		$rtl_page = 'https://www.rtl-theme.com/bijan-wordpress-theme/';

		$versions_files = [];
		if( $fresh_install ) {
			$version_filename = "V" . str_replace( '.', '_', BIJAN_VERSION ) . ".json";
			$changelog_file = BIJAN_DIR . "inc/Changelogs/{$version_filename}";
			if( !file_exists( $changelog_file ) ) return;
			$versions_files[BIJAN_VERSION] = $changelog_file;
		} else {
			foreach( glob( BIJAN_DIR . "inc/Changelogs/*.json" ) as $json_file ) {
				$filename = pathinfo( $json_file, PATHINFO_FILENAME );
				$version = strtr( $filename, [
					'_'	=> '.',
					'V'	=> '',
				] );
				if( version_compare( $version, $last_showed_changelog, '>' ) && version_compare( $version, BIJAN_VERSION, '<=' ) ) {
					$versions_files[$version] = BIJAN_DIR . "inc/Changelogs/{$filename}.json";
				}
			}
		}

		foreach( $versions_files as $version => $changelog_file ) {
			$changelog = wp_json_file_decode( $changelog_file, ['associative' => true] )['log'];
			?>
			<div class="bijan-update-notice notice notice-success is-dismissible">
				<p><strong><?php printf( esc_html__( 'Bijan has been successfully updated. View the changelog for version %s:', 'bijan' ), $version ) ?></strong></p>
				<div class="bijan-update-notice-content">
					<ul>
						<?php foreach( $changelog as $item ) { ?>
							<li><?php echo $item ?></li>
						<?php } ?>
					</ul>

					<a href="<?php echo $rtl_page ?>" target="_blank"><img src="<?php echo $logo ?>" alt=""></a>
				</div>
				<p>
					<a href="<?php echo admin_url( 'admin.php?page=bijan-changelogs' ) ?>" class="button" target="_blank" title="<?php esc_attr_e( 'Show more changelogs', 'bijan' ) ?>"><?php esc_html_e( 'Show more changelogs', 'bijan' ) ?></a>
				</p>
			</div>
			<?php
		}
		update_option( 'bijan_last_showed_changelog', BIJAN_VERSION, false );
	}
}
add_action( 'admin_menu', [Changelog::class, 'add_menu'], 99 );
add_action( 'admin_enqueue_scripts', [Changelog::class, 'enqueue'] );
add_action( 'admin_notices', [Changelog::class, 'notice'] );