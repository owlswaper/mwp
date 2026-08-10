<?php
namespace Bijan;

class AdminScripts {
	public static function main() {
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-backend', BIJAN_URI . "assets/js/backend/backend.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-backend', BIJAN_URI . "assets/js/backend/backend.min.js", ['jquery'], BIJAN_VERSION, true );
		}
	}

	public static function metabox() {
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-metaboxes', BIJAN_URI . "assets/js/backend/metaboxes.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-metaboxes', BIJAN_URI . "assets/js/backend/metaboxes.min.js", ['jquery'], BIJAN_VERSION, true );
		}
		wp_localize_script( 'bijan-metaboxes', 'bijanMetabox', [
			'ajaxUrl'	=> admin_url( 'admin-ajax.php' ),
			'nonces'	=> [
				'postFinder'	=> wp_create_nonce( "bijan-metabox-post-finder" ),
				'userFinder'	=> wp_create_nonce( "bijan-metabox-user-finder" ),
				'iconPicker'	=> wp_create_nonce( "bijan-metabox-icon-picker" ),
			],
		] );
	}

	public static function tabs() {
		wp_enqueue_style( 'bijan-tabs', BIJAN_URI . "assets/css/backend/components/tabs.min.css", [], BIJAN_VERSION );
	}

	public static function switch() {
		wp_enqueue_style( 'bijan-switch', BIJAN_URI . "assets/css/backend/components/switch.min.css", [], BIJAN_VERSION );
	}

	public static function switch_select() {
		wp_enqueue_style( 'bijan-switch-select', BIJAN_URI . "assets/css/backend/components/switch-select.min.css", [], BIJAN_VERSION );
	}

	public static function alert() {
		wp_enqueue_style( 'bijan-alert', BIJAN_URI . "assets/css/backend/components/alert.min.css", [], BIJAN_VERSION );
	}

	public static function attachment() {
		wp_enqueue_style( 'bijan-attachment', BIJAN_URI . "assets/css/backend/components/attachment.min.css", [], BIJAN_VERSION );
	}

	public static function modal() {
		wp_enqueue_style( "bijan-modal", BIJAN_URI . "assets/css/backend/components/modal.min.css", [], BIJAN_VERSION );
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-modal', BIJAN_URI . "assets/js/backend/components/modal.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-modal', BIJAN_URI . "assets/js/backend/components/modal.min.js", ['jquery'], BIJAN_VERSION, true );
		}
	}

	public static function icon_picker() {
		wp_enqueue_style( 'bijan-font-awesome', BIJAN_URI . "assets/css/fa.min.css", [], BIJAN_VERSION );
		wp_enqueue_style( 'bijan-icons', BIJAN_URI . "assets/css/iconly.min.css", [], BIJAN_VERSION );
		wp_enqueue_style( "bijan-icon-picker", BIJAN_URI . "assets/css/backend/components/icon-picker.min.css", [], BIJAN_VERSION );
		if( BIJAN_DEV ) {
			wp_enqueue_script( 'bijan-icon-picker', BIJAN_URI . "assets/js/backend/components/icon-picker.js", ['jquery'], BIJAN_VERSION, true );
		} else {
			wp_enqueue_script( 'bijan-icon-picker', BIJAN_URI . "assets/js/backend/components/icon-picker.min.js", ['jquery'], BIJAN_VERSION, true );
		}
		wp_localize_script( 'bijan-icon-picker', 'bijanIconPicker', [
			'ajaxUrl'	=> admin_url( 'admin-ajax.php' ),
			'nonce'	=> wp_create_nonce( "bijan-icon-picker" ),
		] );
	}
}
AdminScripts::main();