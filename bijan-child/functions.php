<?php
/**************************************************************************
***************************************************************************/
/* DON'T REMOVE THIS LINE 👇🏻 */
include( trailingslashit( get_stylesheet_directory() ) . 'ChildInit.php' );
/* DON'T REMOVE THIS LINE 👆🏻 */
/**************************************************************************
***************************************************************************/

// Product reviews and Q&A (kept in the child theme so parent updates are safe).
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/product-community.php';

// Stable 12-hour product shuffle for product category archives.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/category-product-shuffle.php';

// Unified, performance-focused product-category template and assets.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/product-category-performance.php';

// Streamlined, Iran-only classic WooCommerce checkout.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/checkout-customizations.php';

// Mobile header/menu behavior and the quick support action.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/mobile-header.php';

// Purpose-built, action-first contact page.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/contact-page.php';

// Clearer product attributes plus the fixed shipping and returns guide.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/product-tabs.php';

// Secure customer order tracking, fulfilment workflow and status SMS.
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/order-tracking.php';
