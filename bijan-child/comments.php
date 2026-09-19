<?php
/** Place the editorial profile after the article footer, before comments. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_singular( 'post' ) && ! post_password_required() && function_exists( 'cloz_blog_author_card' ) ) {
	echo cloz_blog_author_card();
}

include get_template_directory() . '/comments.php';
