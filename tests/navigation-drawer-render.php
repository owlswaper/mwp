<?php
define('ABSPATH', __DIR__);
function esc_html($s) { return htmlspecialchars($s, ENT_QUOTES); }
function get_bloginfo($key) { return 'کلوز'; }
function has_nav_menu($key) { return true; }
function get_template_part($key) { echo '<form class="bijan-search bijan-search-ajax" id="mobile-search"><input class="bijan-search-field" type="search" aria-label="جست‌وجو" placeholder="جست‌وجوی محصولات"><div class="bijan-search-results"></div></form>'; }
function wp_nav_menu($args) {
 echo '<div class="mobile-menu-wrap"><ul class="menu">';
 if ($args['theme_location']==='mobile-menu') {
 echo '<li class="menu-item-has-children current-menu-ancestor"><a href="https://drawer.test/category/">پیرسینگ و زیورآلات</a><ul><li class="menu-item-has-children"><a href="https://drawer.test/ear/">پیرسینگ گوش</a><ul><li><a href="https://drawer.test/ring/">حلقه‌های گوش</a></li></ul></li><li class="current-menu-item"><a href="https://drawer.test/nose/">پیرسینگ بینی</a></li></ul></li>';
 foreach (['محصولات تازه','گوشواره','گردنبند','دستبند','اکسسوری','پیشنهادهای ویژه','راهنمای انتخاب و نگهداری','تمام محصولات'] as $i=>$title) echo '<li><a href="https://drawer.test/item/'.$i.'">'.$title.'</a></li>';
 } else { echo '<li><a href="https://drawer.test/contact/">تماس با ما</a></li><li><a href="https://drawer.test/tracking/">پیگیری سفارش</a></li>'; }
 echo '</ul></div>';
}
include dirname(__DIR__).'/bijan-child/templates/responsive/mobile_menu.php';
