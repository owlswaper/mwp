<?php
namespace Bijan;

use Bijan\Utils\Options;
use Bijan\Utils\SMS;
use MJ\Whitebox\Utils\WC as WhiteboxWC;

if( !class_exists( "Bijan\Scripts" ) ) {
	class Scripts {
		PRIVATE STATIC $IS_RTL = false;

		public static function main() {
			$options = Options::get_options( [
				'active-megamenu'			=> true,
				'show_bottom_nav'			=> true,
				'auth-modal'				=> true,
				'auth-email'				=> true,
				'auth_sms'					=> true,
				'sms'						=> true,
			] );
			$active_fonts = Utils::get_active_fonts();

			// Load active fonts
			foreach( $active_fonts as $font ) {
				wp_enqueue_style( "bijan-font-{$font}", Utils::get_font_stylesheet( $font ), [], BIJAN_VERSION );
			}
			if( self::uses_slider() ) {
				PublicScripts::slider();
			}

			self::$IS_RTL = is_rtl();

			if( BIJAN_DEV ) {
				wp_enqueue_script( 'bijan-utils', BIJAN_URI . "assets/js/utils.js", ['jquery'], BIJAN_VERSION, true );
			} else {
				wp_enqueue_script( 'bijan-utils', BIJAN_URI . "assets/js/utils.min.js", ['jquery'], BIJAN_VERSION, true );
			}

			self::bootstrap();

			// Font Awesome is only used by optional Elementor/custom content. Iconly is
			// part of the public header and remains a core asset.
			if( self::uses_font_awesome() ) {
				wp_enqueue_style( 'bijan-font-awesome', BIJAN_URI . "assets/css/fa.min.css", [], BIJAN_VERSION );
			}
			wp_enqueue_style( 'bijan-icons', BIJAN_URI . "assets/css/iconly.min.css", [], BIJAN_VERSION );
			wp_enqueue_style( 'bijan', BIJAN_URI . "assets/css/style.min.css", [], BIJAN_VERSION );

			if( BIJAN_DEV ) {
				wp_enqueue_script( 'bijan', BIJAN_URI . "assets/js/script.js", ['jquery'], BIJAN_VERSION, true );
			} else {
				wp_enqueue_script( 'bijan', BIJAN_URI . "assets/js/script.min.js", ['jquery'], BIJAN_VERSION, true );
			}
			wp_localize_script( 'bijan', 'bijanVars', [
				"ajaxUrl"	=> admin_url( 'admin-ajax.php' ),
				'rtl'		=> is_rtl(),
				'defaults'	=> [
					'avatar'	=> BIJAN_URI . "assets/img/user.svg"
				],
				'nonces'	=> [
					'ajaxSearch'	=> wp_create_nonce( 'bijan-search-query' ),
				],
			] );

			if( Utils::to_bool( $options['active-megamenu'] ) && self::has_active_megamenu() ) {
				wp_enqueue_style( 'bijan-megamenu', BIJAN_URI . "assets/css/components/megamenu.min.css", [], BIJAN_VERSION );

				PublicScripts::masonry();

				if( BIJAN_DEV ) {
					wp_enqueue_script( 'bijan-megamenu', BIJAN_URI . "assets/js/megamenu.js", ['jquery'], BIJAN_VERSION, true );
				} else {
					wp_enqueue_script( 'bijan-megamenu', BIJAN_URI . "assets/js/megamenu.min.js", ['jquery'], BIJAN_VERSION, true );
				}
			}

			if( is_search() ) {
				wp_enqueue_style( 'bijan-search-page', BIJAN_URI . "assets/css/search-page.min.css", [], BIJAN_VERSION );
			}

			self::not_found();
			self::singles();
			self::wc();

			if( $options['show_bottom_nav'] ) {
				wp_enqueue_style( "bijan-bottom-nav", BIJAN_URI . "assets/css/bottom-nav.min.css", [], BIJAN_VERSION );
			}

			if( !is_user_logged_in() && $options['auth-modal'] && ( $options['auth-email'] || $options['auth_sms'] ) ) {
				$sms_settings = Utils::to_bool( $options['sms'] ) ? SMS::get_settings() : [];
				wp_enqueue_style( "bijan-auth-modal", BIJAN_URI . "assets/css/auth-modal.min.css", [], BIJAN_VERSION );
				if( BIJAN_DEV ) {
					wp_enqueue_script( 'bijan-auth-modal', BIJAN_URI . "assets/js/auth-modal.js", ['jquery'], BIJAN_VERSION, true );
				} else {
					wp_enqueue_script( 'bijan-auth-modal', BIJAN_URI . "assets/js/auth-modal.min.js", ['jquery'], BIJAN_VERSION, true );
				}
				$auth_localize = [
					'showLogin'	=> isset( $_GET['login'] ),
					'authSms'	=> $options['auth_sms'],
				];
				if( !empty( $sms_settings ) ) {
					$auth_localize['smsOneForm'] = !empty( $sms_settings['settings']['auth']['one_form'] );
					if( !empty( $sms_settings['settings']['auth']['one_form'] ) ) {
						$auth_localize['otpLoginTime'] = $sms_settings['settings']['auth']['login']['otp_timer'];
					} else {
						if( !empty( $sms_settings['settings']['auth']['login']['enabled'] ) ) {
							$auth_localize['smsLogin'] = true;
							$auth_localize['otpLoginTime'] = $sms_settings['settings']['auth']['login']['otp_timer'];
						}
						if( !empty( $sms_settings['settings']['auth']['register']['enabled'] ) ) {
							$auth_localize['smsRegister'] = true;
							$auth_localize['otpRegisterTime'] = $sms_settings['settings']['auth']['register']['otp_timer'];
						}
					}
				}
				wp_localize_script( 'bijan-auth-modal', 'bijanLogin', $auth_localize );
			}

			$upload_dir = wp_upload_dir();
			$custom_style_file = trailingslashit( $upload_dir['basedir'] ) . 'bijan.css';
			if ( file_exists( $custom_style_file ) && is_readable( $custom_style_file ) ) {
				$custom_style_version = get_option( 'bijan_custom_style_version', filemtime( $custom_style_file ) );
				wp_enqueue_style( 'bijan-custom', trailingslashit( $upload_dir['baseurl'] ) . 'bijan.css', [], $custom_style_version );
			}
		}

		/**
		 * Elementor stores its layout outside post_content. Inspecting the current
		 * document before wp_head lets us keep page-specific assets out of pages
		 * that do not render the related widgets.
		 */
		private static function page_uses_widgets( array $widgets ) {
			$post_id = get_queried_object_id();
			if( !$post_id ) return false;

			$content = (string) get_post_field( 'post_content', $post_id );
			$content .= (string) get_post_meta( $post_id, '_elementor_data', true );
			foreach( $widgets as $widget ) {
				if( strpos( $content, $widget ) !== false ) return true;
			}

			return false;
		}

		private static function uses_slider() {
			if( Utils::is_wc_active() && ( is_product() || is_shop() || is_product_taxonomy() ) ) return true;

			return self::page_uses_widgets( [
				'bijan_slider',
				'bijan_arax_slider',
				'bijan_thumbnail_slider',
				'bijan_categories_slider',
				'bijan_instant_discount',
				'bijan_story',
				'bijan_testimonials',
				'bijan_team',
				'bijan_proicon_2',
				'bijan_products',
				'bijan_special_products',
				'bijan_arax_special_products',
			] );
		}

		private static function uses_woocommerce_components() {
			if( !Utils::is_wc_active() ) return false;
			if( is_front_page() || is_cart() || is_checkout() || is_account_page() ) return true;
			if( function_exists( 'is_woocommerce' ) && is_woocommerce() ) return true;
			if( function_exists( 'WC' ) && WC()->cart && !WC()->cart->is_empty() ) return true;

			return self::page_uses_widgets( [
				'bijan_products',
				'bijan_special_products',
				'bijan_arax_special_products',
				'bijan_categories_slider',
				'bijan_instant_discount',
				'bijan_cart_button',
			] );
		}

		private static function uses_font_awesome() {
			$post_id = get_queried_object_id();
			if( !$post_id ) return (bool) apply_filters( 'bijan/assets/uses_font_awesome', false );

			$content = (string) get_post_field( 'post_content', $post_id );
			$content .= (string) get_post_meta( $post_id, '_elementor_data', true );
			$uses_font_awesome = (bool) preg_match( '/(?:class=[\'\"][^\'\"]*\bfa(?:s|r|b)?\b|\bfa-[a-z0-9-]+)/i', $content );

			return (bool) apply_filters( 'bijan/assets/uses_font_awesome', $uses_font_awesome );
		}

		private static function has_active_megamenu() {
			static $has_active_megamenu = null;
			if( $has_active_megamenu !== null ) return $has_active_megamenu;

			$locations = get_nav_menu_locations();
			$menu_ids = array_filter( [
				$locations['main-menu'] ?? 0,
				$locations['header-second-menu'] ?? 0,
				$locations['mobile-menu'] ?? 0,
				$locations['mobile-second-menu'] ?? 0,
			] );
			foreach( array_unique( $menu_ids ) as $menu_id ) {
				$items = wp_get_nav_menu_items( $menu_id );
				foreach( $items ?: [] as $item ) {
					if( Utils::to_bool( get_post_meta( $item->ID, '_bijan_megamenu', true ) ) ) {
						return $has_active_megamenu = true;
					}
				}
			}

			return $has_active_megamenu = false;
		}

		private static function bootstrap() {
			wp_enqueue_style( 'bijan-bootstrap', BIJAN_URI . "assets/libs/bootstrap-grid.min.css", [], BIJAN_VERSION );	
			if( self::$IS_RTL ) {
				wp_enqueue_style( 'bijan-bootstrap-rtl', BIJAN_URI . "assets/libs/bootstrap-grid.rtl.min.css", [], BIJAN_VERSION );	
			}
		}

		private static function not_found() {
			if( is_404() ) {
				wp_enqueue_style( 'bijan-404', BIJAN_URI . "assets/css/404.min.css", [], BIJAN_VERSION );
			}
		}

		private static function wc() {
			if( !Utils::is_wc_active() ) return;

			$options = Options::get_options( [
				'show-mini-cart'			=> true,
				'wishlist'					=> true,
				'wc-price-history'			=> true,
				'wc-compare'				=> true,
				'wc-lightbox'				=> true,
				'wc-lightbox-download'		=> true,
				'wc-lightbox-thumbnail'		=> true,
				'wc-lightbox-fullscreen'	=> true,
				'wc-lightbox-rotate'		=> true,
			] );

			$uses_wc_components = self::uses_woocommerce_components();
			// The header can render an empty mini-cart on every page. Its tiny
			// component stylesheet is kept separate from the heavier shop bundle.
			if( Utils::to_bool( $options['show-mini-cart'] ) ) {
				wp_enqueue_style( 'bijan-wc-mini-cart', BIJAN_URI . "assets/css/wc/mini_cart.min.css", [], BIJAN_VERSION );
			}
			if( !$uses_wc_components ) return;

			wp_enqueue_style( 'bijan-wc', BIJAN_URI . "assets/css/wc/wc.min.css", [], BIJAN_VERSION );
			if( BIJAN_DEV ) {
				wp_enqueue_script( 'bijan-wc', BIJAN_URI . "assets/js/wc/wc.js", ['jquery'], BIJAN_VERSION, true );
			} else {
				wp_enqueue_script( 'bijan-wc', BIJAN_URI . "assets/js/wc/wc.min.js", ['jquery'], BIJAN_VERSION, true );
			}

			if( is_shop() || is_product_taxonomy() || is_post_type_archive( 'product' ) ) {
				wp_enqueue_style( 'bijan-wc-archive', BIJAN_URI . "assets/css/wc/archive.min.css", [], BIJAN_VERSION );
			}

			if( is_product() ) {
				PublicScripts::slider();
				if( $options['wc-lightbox'] ) {
					PublicScripts::lightgallery();
				}

				wp_enqueue_style( 'bijan-wc-product', BIJAN_URI . "assets/css/wc/single.min.css", [], BIJAN_VERSION );
				if( is_rtl() ) {
					wp_enqueue_style( 'bijan-wc-product-rtl', BIJAN_URI . "assets/css/wc/single.rtl.min.css", [], BIJAN_VERSION );
				}
				if( BIJAN_DEV ) {
					wp_enqueue_script( 'bijan-wc-single', BIJAN_URI . "assets/js/wc/single.js", ['bijan-slider'], BIJAN_VERSION, true );
				} else {
					wp_enqueue_script( 'bijan-wc-single', BIJAN_URI . "assets/js/wc/single.min.js", ['bijan-slider'], BIJAN_VERSION, true );
				}
				wp_localize_script( 'bijan-wc-single', 'bijanWCSingle', [
					'lightbox'				=> $options['wc-lightbox'],
					'lightboxDownload'		=> $options['wc-lightbox-download'],
					'lightboxThumb'			=> $options['wc-lightbox-thumbnail'],
					'lightboxFullscreen'	=> $options['wc-lightbox-fullscreen'],
					'lightboxRotate'		=> $options['wc-lightbox-rotate'],

					'i18n'	=> [
						'instock'	=> esc_html__( "In stock", 'woocommerce' ),
					],
				] );

				// Price history
				if( Utils::to_bool( $options['wc-price-history'] ) ) {
					wp_enqueue_style( 'bijan-wc-single-price-history', BIJAN_URI . "assets/css/wc/single/price_history.min.css", [], BIJAN_VERSION );
					PublicScripts::chartjs();
					if( BIJAN_DEV ) {
						wp_enqueue_script( 'bijan-wc-single-price-history', BIJAN_URI . "assets/js/wc/single/price_history.js", ['jquery'], BIJAN_VERSION, true );
					} else {
						wp_enqueue_script( 'bijan-wc-single-price-history', BIJAN_URI . "assets/js/wc/single/price_history.min.js", ['jquery'], BIJAN_VERSION, true );
					}
					$active_fonts = Utils::get_active_fonts();
					$price_history_options = Options::get_options( [
						'primary_color_100'	=> '#FF9800',
						'text_main_color'	=> '#9B9CA4',
						'main-typography'	=> 'IRANYekanXFANum',
					] );
					wp_localize_script( 'bijan-wc-single-price-history', 'bijanPriceHistory', [
						'rtl'		=> is_rtl(),
						'colors'	=> [
							'primary'	=> $price_history_options['primary_color_100'],
							'secondary'	=> $price_history_options['text_main_color'],
						],
						'font'		=> in_array( $price_history_options['main-typography'], $active_fonts ) ? $active_fonts[array_search( $price_history_options['main-typography'], $active_fonts )] : $active_fonts[0],
						'tooltip'	=> [
							'up'	=> esc_html_x( "Price increase", 'chart', 'bijan' ),
							'down'	=> esc_html_x( "Price reduction", 'chart', 'bijan' ),
						]
					] );
				}

				// Compare
				if( Utils::to_bool( $options['wc-compare'] ) ) {
					PublicScripts::jscookie();

					wp_enqueue_style( 'bijan-wc-single-compare', BIJAN_URI . "assets/css/wc/single/compare.min.css", [], BIJAN_VERSION );
					if( is_rtl() ) {
						wp_enqueue_style( 'bijan-wc-single-compare-rtl', BIJAN_URI . "assets/css/wc/single/compare.rtl.min.css", [], BIJAN_VERSION );
					}
					if( BIJAN_DEV ) {
						wp_enqueue_script( 'bijan-wc-single-compare', BIJAN_URI . "assets/js/wc/single/compare.js", ['jquery'], BIJAN_VERSION, true );
					} else {
						wp_enqueue_script( 'bijan-wc-single-compare', BIJAN_URI . "assets/js/wc/single/compare.min.js", ['jquery'], BIJAN_VERSION, true );
					}
					wp_localize_script( 'bijan-wc-single-compare', 'bijanCompare', [
						'nonce'	=> wp_create_nonce( "bijan-add-compare" )
					] );
				}
			}

			if( is_cart() ) {
				wp_enqueue_style( 'bijan-wc-cart', BIJAN_URI . "assets/css/wc/cart.min.css", [], BIJAN_VERSION );
			}

			if( is_checkout() ) {
				wp_enqueue_style( 'bijan-wc-checkout', BIJAN_URI . "assets/css/wc/checkout.min.css", [], BIJAN_VERSION );
			}
			if( is_order_received_page() ) {
				wp_enqueue_style( 'bijan-wc-order', BIJAN_URI . "assets/css/wc/order.min.css", [], BIJAN_VERSION );
			}

			if( is_account_page() ) {
				if( BIJAN_DEV ) {
					wp_enqueue_script( 'bijan-wc-myaccount', BIJAN_URI . "assets/js/wc/my-account/my-account.js", ['jquery'], BIJAN_VERSION, true );
				} else {
					wp_enqueue_script( 'bijan-wc-myaccount', BIJAN_URI . "assets/js/wc/my-account/my-account.min.js", ['jquery'], BIJAN_VERSION, true );
				}
				wp_enqueue_style( "bijan-wc-myaccount", BIJAN_URI . "assets/css/wc/my-account/my-account.min.css", [], BIJAN_VERSION );
				if( is_user_logged_in() ) {
					$my_account_page = WhiteboxWC::get_account_endpoint();
					if( !$my_account_page || $my_account_page === 'dashboard' ) {
						wp_enqueue_style( "bijan-wc-myaccount-dashboard", BIJAN_URI . "assets/css/wc/my-account/dashboard.min.css", [], BIJAN_VERSION );
					} else if( $my_account_page === 'orders' ) {
						wp_enqueue_style( "bijan-wc-myaccount-orders", BIJAN_URI . "assets/css/wc/my-account/orders.min.css", [], BIJAN_VERSION );
					} else if( $my_account_page === 'view-order' ) {
						wp_enqueue_style( 'bijan-wc-order', BIJAN_URI . "assets/css/wc/order.min.css", [], BIJAN_VERSION );
						wp_enqueue_style( "bijan-wc-myaccount-downloads", BIJAN_URI . "assets/css/wc/my-account/downloads.min.css", [], BIJAN_VERSION );
					} else if( $my_account_page === 'downloads' ) {
						wp_enqueue_style( "bijan-wc-myaccount-downloads", BIJAN_URI . "assets/css/wc/my-account/downloads.min.css", [], BIJAN_VERSION );
					} else if( $my_account_page === 'edit-address' ) {
						wp_enqueue_style( "bijan-wc-myaccount-addresses", BIJAN_URI . "assets/css/wc/my-account/addresses.min.css", [], BIJAN_VERSION );
					} else if( $my_account_page === 'edit-account' ) {
						wp_enqueue_media();
						wp_enqueue_style( "bijan-wc-myaccount-edit-account", BIJAN_URI . "assets/css/wc/my-account/edit-account.min.css", [], BIJAN_VERSION );

						if( BIJAN_DEV ) {
							wp_enqueue_script( 'bijan-wc-myaccount-edit-account', BIJAN_URI . "assets/js/wc/my-account/edit-account.js", ['jquery'],  BIJAN_VERSION, true );
						} else {
							wp_enqueue_script( 'bijan-wc-myaccount-edit-account', BIJAN_URI . "assets/js/wc/my-account/edit-account.min.js", ['jquery'],  BIJAN_VERSION, true );
						}
					} else if( $my_account_page === 'wishlist' ) {
						wp_enqueue_style( "bijan-wc-myaccount-wishlist", BIJAN_URI . "assets/css/wc/my-account/wishlist.min.css", [], BIJAN_VERSION );
					} else if( $my_account_page === 'coupons' ) {
						wp_enqueue_style( "bijan-wc-myaccount-coupons", BIJAN_URI . "assets/css/wc/my-account/coupons.min.css", [], BIJAN_VERSION );
					} else if( $my_account_page === 'notifications' ) {
						wp_enqueue_style( "bijan-wc-myaccount-notifications", BIJAN_URI . "assets/css/wc/my-account/notifications.min.css", [], BIJAN_VERSION );
						wp_enqueue_script( 'bijan-wc-myaccount-notifications', BIJAN_URI . "assets/js/wc/my-account/notifications.js", ['jquery'], BIJAN_VERSION, true );
						$notification_nonces = [];
						foreach ( \Bijan\Utils\Notifications::get_user_notifications( true ) as $notification ) {
							$notification_nonces[ $notification->ID ] = wp_create_nonce( "bijan_notification_read_{$notification->ID}" );
						}
						wp_localize_script( 'bijan-wc-myaccount-notifications', 'bijanNotifs', [
							'nonces' => $notification_nonces,
							'i18n'	=> [
								'open'	=> __( "View", 'bijan' ),
								'close'	=> __( "Close", 'bijan' ),
							]
						] );
					}
				} else {
					wp_enqueue_style( "bijan-wc-myaccount-login", BIJAN_URI . "assets/css/wc/my-account/login.min.css", [], BIJAN_VERSION );
				}
			}

			if( Utils::to_bool( $options['wishlist'] ) ) {
				wp_enqueue_style( "bijan-wishlist", BIJAN_URI . "assets/css/wc/wishlist.min.css", [], BIJAN_VERSION );
				if( BIJAN_DEV ) {
					wp_enqueue_script( 'bijan-wishlist', BIJAN_URI . "assets/js/wc/wishlist.js", ['jquery'], BIJAN_VERSION, true );
				} else {
					wp_enqueue_script( 'bijan-wishlist', BIJAN_URI . "assets/js/wc/wishlist.min.js", ['jquery'], BIJAN_VERSION, true );
				}
			}
		}

		private static function singles() {
			if( is_singular() ) {
				wp_enqueue_style( 'bijan-single', BIJAN_URI . "assets/css/single.min.css", [], BIJAN_VERSION );

				if( comments_open() && get_option( 'thread_comments' ) ) {
					wp_enqueue_script( 'comment-reply' );
					wp_enqueue_style( 'bijan-comments', BIJAN_URI . "assets/css/comments.min.css", [], BIJAN_VERSION );
				}
			}
		}

		public static function header_banner() {
			$options = Options::get_options( [
				'show-header-banner'	=> false,
				'header-banner-img'		=> [
					'url'	=> '',
				],

				'header-banner-img-tablet'	=> [
					'url'	=> '',
				],

				'header-banner-img-mobile'	=> [
					'url'	=> '',
				],
			] );
			if( !$options['show-header-banner'] &&
				( empty( $options['header-banner-img'] ) || empty( $options['header-banner-img']['url'] ) ) &&
				( empty( $options['header-banner-img-tablet'] ) || empty( $options['header-banner-img-tablet']['url'] ) ) &&
				( empty( $options['header-banner-img-mobile'] ) || empty( $options['header-banner-img-mobile']['url'] ) )
			) return;
			wp_enqueue_style( 'bijan-header-banner', BIJAN_URI . "assets/css/header_banner.min.css", [], BIJAN_VERSION );
		}
	}
	Scripts::main();
	Scripts::header_banner();
}
