<?php
/** Main navigation drawer. Keep the theme's menu locations and AJAX search. */
defined( 'ABSPATH' ) || exit;
?>
<div id="mobile-menu-container" class="mobile-menu-container clz-navigation-drawer" role="dialog" aria-modal="true" aria-labelledby="clz-drawer-title" aria-describedby="clz-drawer-subtitle" aria-hidden="true" tabindex="-1" inert>
	<div class="clz-drawer-heading">
		<div class="clz-drawer-identity"><h2 id="clz-drawer-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2><span class="clz-drawer-eyebrow" id="clz-drawer-subtitle">فهرست فروشگاه</span></div>
		<button type="button" class="clz-drawer-close" aria-label="بستن فهرست"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
	</div>
	<div class="mobile-menu-search-wrap"><?php get_template_part( 'templates/bottom_nav/search' ); ?></div>
	<div class="mobile-menu-scroll">
		<?php if ( has_nav_menu( 'mobile-menu' ) ) : ?>
		<nav aria-label="فهرست اصلی">
			<?php wp_nav_menu( array( 'theme_location' => 'mobile-menu', 'container_class' => 'mobile-menu-wrap', 'fallback_cb' => false ) ); ?>
		</nav>
		<?php endif; ?>
		<?php if ( has_nav_menu( 'mobile-second-menu' ) ) : ?>
		<nav class="clz-drawer-secondary" aria-label="لینک‌های بیشتر">
			<h3 class="clz-drawer-section-title">دسترسی سریع</h3>
			<?php wp_nav_menu( array( 'theme_location' => 'mobile-second-menu', 'container_class' => 'mobile-menu-wrap', 'fallback_cb' => false ) ); ?>
		</nav>
		<?php endif; ?>
	</div>
</div>
