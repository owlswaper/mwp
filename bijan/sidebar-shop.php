<?php

use Bijan\Utils\Options;

if ( ! defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'wc-filters-mobile-opener-icon' => is_rtl() ? 'bijan-icon-double-arrow-left' : 'bijan-icon-double-arrow-right',
	'wc-filters-mobile-close-icon'  => is_rtl() ? 'bijan-icon-double-arrow-right' : 'bijan-icon-double-arrow-left',
] );
?>

<style>
@media (max-width:768px){

	/* حذف دکمه شناور */
	.sidebar-mobile-expand-btn{
		display:none!important;
	}

	/* نمایش سایدبار به صورت عادی */
	#sidebar.sidebar-shop{
		position:relative!important;
		inset:auto!important;
		top:auto!important;
		right:auto!important;
		bottom:auto!important;
		left:auto!important;
		transform:none!important;
		width:100%!important;
		max-width:100%!important;
		height:auto!important;
		max-height:none!important;
		overflow:visible!important;
		pointer-events:auto!important;
		margin:0 0 20px!important;
		padding:0!important;
		border-radius:16px!important;
		box-sizing:border-box!important;
		box-shadow:none!important;
		z-index:auto!important;
	}

	#sidebar.sidebar-shop::before,
	#sidebar.sidebar-shop::after{
		display:none!important;
		content:none!important;
	}

	#sidebar.sidebar-shop .widget-area{
		pointer-events:auto!important;
	}

	.content-area.archive{
		display:flex!important;
		flex-direction:column!important;
	}

	#sidebar.sidebar-shop{
		order:1!important;
	}

	#posts{
		order:2!important;
	}
}
</style>

<aside id="sidebar"
	class="sidebar sidebar-shop col-md-3 col-sm-12"
	aria-label="<?php esc_attr_e( 'Shop Sidebar', 'bijan' ); ?>">

	<section id="widget-area"
		class="widget-area"
		role="complementary"
		aria-label="<?php esc_attr_e( 'Shop Widgets', 'bijan' ); ?>">

		<?php dynamic_sidebar( 'sidebar-shop' ); ?>

	</section>

</aside>