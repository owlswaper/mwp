<?php
namespace Bijan;

use Bijan\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( "\Bijan\PublicScripts" ) ) {
	class PublicScripts {
		public static function swiper() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_style( 'bijan-swiper', BIJAN_URI . "assets/libs/swiper/swiper-bundle.min.css", [], BIJAN_VERSION );
				wp_enqueue_script( 'bijan-swiper', BIJAN_URI . "assets/libs/swiper/swiper-bundle.min.js", [], BIJAN_VERSION, true );
				$enqueued = true;
			}
		}

		public static function slider() {
			static $enqueued = false;
			if( !$enqueued ) {
				self::swiper();
				if( BIJAN_DEV ) {
					wp_enqueue_script( 'bijan-slider', BIJAN_URI . "assets/js/slider.js", ['jquery', 'bijan-swiper'], BIJAN_VERSION, true );
				} else {
					wp_enqueue_script( 'bijan-slider', BIJAN_URI . "assets/js/slider.min.js", ['jquery', 'bijan-swiper'], BIJAN_VERSION, true );
				}
				$enqueued = true;
			}
		}

		public static function select2() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_style( 'bijan-select2', BIJAN_URI . "assets/libs/select2/select2.min.css", [], BIJAN_VERSION );
				wp_enqueue_script( 'bijan-select2', BIJAN_URI . "assets/libs/select2/select2.min.js", [], BIJAN_VERSION, true );
				$enqueued = true;
			}
		}

		public static function masonry() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_script( 'bijan-masonry', BIJAN_URI . "assets/libs/masonry.pkgd.min.js", [], BIJAN_VERSION, true );
				$enqueued = true;
			}
		}

		public static function pdp() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_script( 'bijan-pd', BIJAN_URI . "assets/libs/pdp/persian-date.min.js", [], BIJAN_VERSION, true );
				wp_enqueue_script( 'bijan-pdp', BIJAN_URI . "assets/libs/pdp/persian-datepicker.min.js", ['jquery'], BIJAN_VERSION, true );
				wp_enqueue_style( 'bijan-pdp', BIJAN_URI . "assets/libs/pdp/persian-datepicker.min.css", [], BIJAN_VERSION );
				$enqueued = true;
			}
		}

		public static function chartjs() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_script( 'bijan-chartjs', BIJAN_URI . "assets/libs/chart.min.js", [], BIJAN_VERSION, true );
				$enqueued = true;
			}
		}

		public static function jscookie() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_script( 'bijan-jscookie', BIJAN_URI . "assets/libs/js.cookie.min.js", [], BIJAN_VERSION, true );

				wp_localize_script( 'bijan-jscookie', 'bijanCookieVars', [
					'path'		=> COOKIEPATH,
					'domain'	=> COOKIE_DOMAIN,
				] );

				$enqueued = true;
			}
		}

		public static function lightgallery() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_style( 'bijan-lightgallery', BIJAN_URI . "assets/libs/lightgallery/css/lightgallery-bundle.min.css", [], BIJAN_VERSION );
				wp_enqueue_script( 'bijan-lightgallery', BIJAN_URI . "assets/libs/lightgallery/lightgallery.umd.min.js", [], BIJAN_VERSION, true );
				wp_enqueue_script( 'bijan-lightgallery-video', BIJAN_URI . "assets/libs/lightgallery/plugins/video/lg-video.min.js", [], BIJAN_VERSION, true );
				
				if( BIJAN_DEV ) {
					wp_enqueue_style( 'bijan-lightgallery-zoom', BIJAN_URI . "assets/libs/lightgallery/css/lg-zoom.css", [], BIJAN_VERSION );
				} else {
					wp_enqueue_style( 'bijan-lightgallery-zoom', BIJAN_URI . "assets/libs/lightgallery/css/lg-zoom.min.css", [], BIJAN_VERSION );
				}
				wp_enqueue_script( 'bijan-lightgallery-zoom', BIJAN_URI . "assets/libs/lightgallery/plugins/zoom/lg-zoom.min.js", [], BIJAN_VERSION, true );

				$options = Options::get_options( [
					'wc-lightbox-thumbnail'		=> true,
					'wc-lightbox-fullscreen'	=> true,
					'wc-lightbox-rotate'		=> true,
				] );
				if( $options['wc-lightbox-thumbnail'] ) {
					if( BIJAN_DEV ) {
						wp_enqueue_style( 'bijan-lightgallery-thumbnail', BIJAN_URI . "assets/libs/lightgallery/css/lg-thumbnail.css", [], BIJAN_VERSION );
					} else {
						wp_enqueue_style( 'bijan-lightgallery-thumbnail', BIJAN_URI . "assets/libs/lightgallery/css/lg-thumbnail.min.css", [], BIJAN_VERSION );
					}
					wp_enqueue_script( 'bijan-lightgallery-thumbnail', BIJAN_URI . "assets/libs/lightgallery/plugins/thumbnail/lg-thumbnail.min.js", [], BIJAN_VERSION, true );
				}
				if( $options['wc-lightbox-fullscreen'] ) {
					if( BIJAN_DEV ) {
						wp_enqueue_style( 'bijan-lightgallery-fullscreen', BIJAN_URI . "assets/libs/lightgallery/css/lg-fullscreen.css", [], BIJAN_VERSION );
					} else {
						wp_enqueue_style( 'bijan-lightgallery-fullscreen', BIJAN_URI . "assets/libs/lightgallery/css/lg-fullscreen.min.css", [], BIJAN_VERSION );
					}
					wp_enqueue_script( 'bijan-lightgallery-fullscreen', BIJAN_URI . "assets/libs/lightgallery/plugins/fullscreen/lg-fullscreen.min.js", [], BIJAN_VERSION, true );
				}
				if( $options['wc-lightbox-rotate'] ) {
					if( BIJAN_DEV ) {
						wp_enqueue_style( 'bijan-lightgallery-rotate', BIJAN_URI . "assets/libs/lightgallery/css/lg-rotate.css", [], BIJAN_VERSION );
					} else {
						wp_enqueue_style( 'bijan-lightgallery-rotate', BIJAN_URI . "assets/libs/lightgallery/css/lg-rotate.css", [], BIJAN_VERSION );
					}
					wp_enqueue_script( 'bijan-lightgallery-rotate', BIJAN_URI . "assets/libs/lightgallery/plugins/rotate/lg-rotate.min.js", [], BIJAN_VERSION, true );
				}

				$enqueued = true;
			}
		}

		public static function jscolorpicker() {
			static $enqueued = false;
			if( !$enqueued ) {
				wp_enqueue_style( 'bijan-jscolorpicker', BIJAN_URI . "assets/libs/jscolorpicker/colorpicker.min.css", [], BIJAN_VERSION );
				wp_enqueue_script( 'bijan-jscolorpicker', BIJAN_URI . "assets/libs/jscolorpicker/colorpicker.iife.min.js", [], BIJAN_VERSION, true );

				$enqueued = true;
			}
		}
	}
}