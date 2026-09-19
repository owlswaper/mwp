<?php
/** Shared editorial author for the blog. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cloz_blog_author() {
	static $cached = false;
	if ( false !== $cached ) {
		return $cached;
	}
	$settings = get_option( 'cloz_blog_author', [] );
	if ( ! is_array( $settings ) ) {
		return $cached = null;
	}
	$defaults = [ 'user_id' => 0, 'name' => '', 'image_id' => 0, 'bio' => '', 'summary' => '', 'x' => '', 'instagram' => '', 'facebook' => '', 'updated_at' => '' ];
	$settings = wp_parse_args( $settings, $defaults );
	$settings['user_id'] = absint( $settings['user_id'] );
	if ( ! $settings['user_id'] || ! get_userdata( $settings['user_id'] ) || '' === trim( $settings['name'] ) ) {
		return $cached = null;
	}
	$settings['url']   = get_author_posts_url( $settings['user_id'] );
	$settings['image'] = $settings['image_id'] ? wp_get_attachment_image_url( absint( $settings['image_id'] ), 'thumbnail' ) : '';
	return $cached = $settings;
}

function cloz_is_blog_author_page() {
	if ( ! is_author() ) {
		return false;
	}
	$author = cloz_blog_author();
	return $author && is_author( $author['user_id'] );
}

function cloz_blog_author_page_title( $author ) {
	$site_name = trim( get_bloginfo( 'name' ) );
	return 'درباره ' . $author['name'] . ( $site_name ? ' | ' . $site_name : '' );
}

function cloz_blog_author_description( $author ) {
	$source = $author['summary'] ?: $author['bio'];
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $source ) ) );
	return $text ? wp_trim_words( $text, 30, '…' ) : '';
}

function cloz_blog_author_socials( $author ) {
	$labels = [ 'x' => 'ایکس', 'instagram' => 'اینستاگرام', 'facebook' => 'فیسبوک' ];
	$icons = [
		'x' => '<path d="M18.9 2H22l-6.8 7.8L23.2 22h-6.3L12 14.6 5.5 22H2.3l7.3-8.5L1.8 2h6.5l4.5 6.9L18.9 2Zm-1.1 18h1.7L7.3 3.9H5.5L17.8 20Z"/>',
		'instagram' => '<rect x="2" y="2" width="20" height="20" rx="6" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="6" r="1.2"/>',
		'facebook' => '<path d="M14.5 22v-9h3l.5-4h-3.5V6.5c0-1.2.4-2 2-2H18V1.1A23 23 0 0 0 15.2 1C12.4 1 10.5 2.7 10.5 5.8V9H7.4v4h3.1v9h4Z"/>',
	];
	$links = '';
	foreach ( $labels as $key => $label ) {
		if ( ! empty( $author[ $key ] ) ) {
			$links .= '<a href="' . esc_url( $author[ $key ] ) . '" target="_blank" rel="noopener noreferrer me" aria-label="' . esc_attr( $label ) . '"><svg viewBox="0 0 24 24" width="21" height="21" fill="currentColor" aria-hidden="true">' . $icons[ $key ] . '</svg></a>';
		}
	}
	return $links ? '<div class="cloz-author-socials"><strong>صفحات اجتماعی:</strong><div class="cloz-author-social-links">' . $links . '</div></div>' : '';
}

function cloz_blog_author_card() {
	$author = cloz_blog_author();
	if ( ! $author ) {
		return '';
	}
	$image = $author['image'] ? '<a class="cloz-author-avatar" href="' . esc_url( $author['url'] ) . '" rel="author">' . wp_get_attachment_image( absint( $author['image_id'] ), 'thumbnail', false, [ 'alt' => $author['name'], 'loading' => 'lazy', 'decoding' => 'async' ] ) . '</a>' : '';
	$summary = trim( $author['summary'] ) ? '<p>' . nl2br( esc_html( $author['summary'] ) ) . '</p>' : '';
	return '<aside class="cloz-author-card" aria-label="درباره نویسنده"><div class="cloz-author-card-main">' . $image . '<div><h2><a href="' . esc_url( $author['url'] ) . '" rel="author">' . esc_html( $author['name'] ) . '</a></h2>' . $summary . '</div></div>' . cloz_blog_author_socials( $author ) . '</aside>';
}

add_filter( 'the_author', function ( $name ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() ) {
		return $name;
	}
	$author = cloz_blog_author();
	return $author ? $author['name'] : $name;
} );

add_filter( 'rank_math/frontend/title', function ( $title ) {
	return cloz_is_blog_author_page() ? cloz_blog_author_page_title( cloz_blog_author() ) : $title;
} );

add_filter( 'rank_math/frontend/description', function ( $description ) {
	if ( ! cloz_is_blog_author_page() ) {
		return $description;
	}
	$profile_description = cloz_blog_author_description( cloz_blog_author() );
	return $profile_description ?: $description;
} );

add_filter( 'document_title_parts', function ( $parts ) {
	if ( cloz_is_blog_author_page() ) {
		$parts['title'] = 'درباره ' . cloz_blog_author()['name'];
	}
	return $parts;
} );

add_filter( 'get_the_archive_title', function ( $title ) {
	return cloz_is_blog_author_page() ? 'درباره ' . cloz_blog_author()['name'] : $title;
} );

add_filter( 'author_link', function ( $link, $author_id ) {
	if ( ! is_singular( 'post' ) ) {
		return $link;
	}
	$settings = get_option( 'cloz_blog_author', [] );
	$selected_id = is_array( $settings ) && ! empty( $settings['name'] ) ? absint( $settings['user_id'] ?? 0 ) : 0;
	return $selected_id && (int) $author_id !== $selected_id && get_userdata( $selected_id ) ? get_author_posts_url( $selected_id ) : $link;
}, 10, 2 );

add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular( 'post' ) || is_author() ) {
		wp_enqueue_style( 'cloz-blog-author', get_stylesheet_directory_uri() . '/assets/blog-author.css', [], filemtime( get_stylesheet_directory() . '/assets/blog-author.css' ) );
	}
} );

// Keep the configured author archive available even when its WordPress user did not publish the posts.
add_filter( 'pre_handle_404', function ( $preempt, $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_author() ) {
		return $preempt;
	}
	$author = cloz_blog_author();
	if ( $author && (int) $query->get_queried_object_id() === (int) $author['user_id'] ) {
		return true;
	}
	return $preempt;
}, 10, 2 );

function cloz_blog_author_schema_person( $author ) {
	$person = [
		'@type'       => 'Person',
		'@id'         => untrailingslashit( $author['url'] ) . '/#person',
		'name'        => $author['name'],
		'url'         => $author['url'],
	];
	$description = trim( wp_strip_all_tags( $author['bio'] ?: $author['summary'] ) );
	if ( $description ) {
		$person['description'] = $description;
	}
	if ( $author['image_id'] ) {
		$image = wp_get_attachment_image_url( absint( $author['image_id'] ), 'full' );
		if ( $image ) {
			$person['image'] = [ '@type' => 'ImageObject', 'url' => $image ];
		}
	}
	$socials = array_values( array_filter( [ $author['x'], $author['instagram'], $author['facebook'] ] ) );
	if ( $socials ) {
		$person['sameAs'] = $socials;
	}
	return $person;
}

// Rank Math stores the Person under the misleading "ProfilePage" key, while
// its WebPage node has the actual ProfilePage type on author archives.
add_filter( 'rank_math/json_ld', function ( $data ) {
	if ( ! is_array( $data ) || ( ! is_singular( 'post' ) && ! is_author() ) ) {
		return $data;
	}
	$author = cloz_blog_author();
	if ( ! $author || ( is_author() && ! is_author( $author['user_id'] ) ) ) {
		return $data;
	}
	$person = cloz_blog_author_schema_person( $author );
	if ( isset( $data['ProfilePage'] ) && is_array( $data['ProfilePage'] ) && in_array( 'Person', (array) ( $data['ProfilePage']['@type'] ?? [] ), true ) ) {
		// Keep Rank Math's ID so references from custom schemas cannot be orphaned.
		if ( ! empty( $data['ProfilePage']['@id'] ) ) {
			$person['@id'] = $data['ProfilePage']['@id'];
		}
		// Preserve Rank Math's other Person properties and replace only profile fields.
		$data['ProfilePage'] = array_merge( $data['ProfilePage'], $person );
		if ( empty( $person['description'] ) ) {
			unset( $data['ProfilePage']['description'] );
		}
		if ( empty( $person['image'] ) ) {
			unset( $data['ProfilePage']['image'] );
		}
		if ( empty( $person['sameAs'] ) ) {
			unset( $data['ProfilePage']['sameAs'] );
		}
	} else {
		$data['cloz_author_person'] = $person;
	}
	$person_ref = [ '@id' => $person['@id'], '@type' => 'Person', 'name' => $person['name'], 'url' => $person['url'] ];
	$is_profile = is_author( $author['user_id'] );
	foreach ( $data as $key => $node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		$types = isset( $node['@type'] ) ? (array) $node['@type'] : [];
		if ( is_singular( 'post' ) && in_array( 'BlogPosting', $types, true ) ) {
			$data[ $key ]['author'] = $person_ref;
		}
		if ( $is_profile && in_array( 'ProfilePage', $types, true ) ) {
			$data[ $key ]['mainEntity'] = $person_ref;
			$data[ $key ]['name'] = cloz_blog_author_page_title( $author );
			$description = cloz_blog_author_description( $author );
			if ( $description ) {
				$data[ $key ]['description'] = $description;
			}
			if ( ! empty( $author['updated_at'] ) ) {
				$data[ $key ]['dateModified'] = $author['updated_at'];
			}
		}
	}
	if ( $is_profile ) {
		$has_profile = false;
		foreach ( $data as $node ) {
			if ( is_array( $node ) && in_array( 'ProfilePage', (array) ( $node['@type'] ?? [] ), true ) ) {
				$has_profile = true;
				break;
			}
		}
		if ( ! $has_profile ) {
			$data['cloz_author_profile'] = [ '@type' => 'ProfilePage', '@id' => untrailingslashit( $author['url'] ) . '/#profile', 'url' => $author['url'], 'name' => cloz_blog_author_page_title( $author ), 'mainEntity' => $person_ref ];
			$description = cloz_blog_author_description( $author );
			if ( $description ) {
				$data['cloz_author_profile']['description'] = $description;
			}
			if ( ! empty( $author['updated_at'] ) ) {
				$data['cloz_author_profile']['dateModified'] = $author['updated_at'];
			}
		}
	}
	return $data;
}, 99 );

add_action( 'admin_menu', function () {
	add_posts_page( 'نویسنده وبلاگ', 'نویسنده وبلاگ', 'manage_options', 'cloz-blog-author', 'cloz_blog_author_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'cloz_blog_author_group', 'cloz_blog_author', [ 'type' => 'array', 'sanitize_callback' => 'cloz_blog_author_sanitize', 'default' => [] ] );
} );

function cloz_blog_author_sanitize( $input ) {
	$input = is_array( $input ) ? $input : [];
	$user_id = absint( $input['user_id'] ?? 0 );
	$image_id = absint( $input['image_id'] ?? 0 );
	$output = [
		'user_id'    => $user_id && get_userdata( $user_id ) ? $user_id : 0,
		'name'       => sanitize_text_field( $input['name'] ?? '' ),
		'image_id'   => $image_id && 'attachment' === get_post_type( $image_id ) ? $image_id : 0,
		'bio'        => wp_kses_post( $input['bio'] ?? '' ),
		'summary'    => sanitize_textarea_field( $input['summary'] ?? '' ),
		'updated_at' => gmdate( 'c' ),
	];
	foreach ( [ 'x', 'instagram', 'facebook' ] as $network ) {
		$url = esc_url_raw( $input[ $network ] ?? '', [ 'https' ] );
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$allowed = [ 'x' => [ 'x.com', 'twitter.com', 'www.x.com', 'www.twitter.com' ], 'instagram' => [ 'instagram.com', 'www.instagram.com' ], 'facebook' => [ 'facebook.com', 'www.facebook.com', 'fb.com', 'www.fb.com' ] ];
		$output[ $network ] = $host && in_array( strtolower( $host ), $allowed[ $network ], true ) ? $url : '';
	}
	return $output;
}

function cloz_blog_author_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$value = wp_parse_args( get_option( 'cloz_blog_author', [] ), [ 'user_id' => 0, 'name' => '', 'image_id' => 0, 'bio' => '', 'summary' => '', 'x' => '', 'instagram' => '', 'facebook' => '' ] );
	$users = get_users( [ 'fields' => [ 'ID', 'display_name', 'user_login' ], 'orderby' => 'display_name' ] );
	?>
	<div class="wrap"><h1>نویسنده وبلاگ</h1><p>این پروفایل در همه نوشته‌های وبلاگ نمایش داده می‌شود. شناسه، حساب وردپرس نویسنده و نشانی صفحه اختصاصی او را تعیین می‌کند.</p>
	<?php if ( class_exists( '\\RankMath\\Helper' ) && \RankMath\Helper::get_settings( 'titles.disable_author_archives' ) ) : ?><div class="notice notice-warning inline"><p>در Rank Math، آرشیو نویسندگان غیرفعال است و پیوند صفحه نویسنده به خانه هدایت می‌شود. از «Rank Math SEO ← عنوان‌ها و متا ← نویسندگان» آن را فعال کنید.</p></div><?php endif; ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'cloz_blog_author_group' ); ?>
		<table class="form-table" role="presentation"><tbody>
		<tr><th scope="row"><label for="cloz-author-id">شناسه نویسنده</label></th><td><select id="cloz-author-id" name="cloz_blog_author[user_id]"><option value="0">انتخاب کنید</option><?php foreach ( $users as $user ) : ?><option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $value['user_id'], $user->ID ); ?>><?php echo esc_html( $user->display_name . ' (' . $user->user_login . '، ID: ' . $user->ID . ')' ); ?></option><?php endforeach; ?></select><p class="description">نشانی صفحه نویسنده از نام کاربری این حساب ساخته می‌شود.</p></td></tr>
		<tr><th scope="row"><label for="cloz-author-name">نام نمایشی</label></th><td><input id="cloz-author-name" class="regular-text" name="cloz_blog_author[name]" value="<?php echo esc_attr( $value['name'] ); ?>" required></td></tr>
		<tr><th scope="row">تصویر پروفایل</th><td><input type="hidden" id="cloz-author-image-id" name="cloz_blog_author[image_id]" value="<?php echo esc_attr( $value['image_id'] ); ?>"><div id="cloz-author-preview"><?php if ( $value['image_id'] ) { echo wp_get_attachment_image( absint( $value['image_id'] ), 'thumbnail' ); } ?></div><button type="button" class="button" id="cloz-author-image-select">انتخاب تصویر</button> <button type="button" class="button" id="cloz-author-image-remove">حذف تصویر</button></td></tr>
		<tr><th scope="row"><label for="cloz-author-bio">بیوگرافی کامل</label></th><td><?php wp_editor( $value['bio'], 'cloz-author-bio', [ 'textarea_name' => 'cloz_blog_author[bio]', 'textarea_rows' => 12, 'media_buttons' => false, 'teeny' => false ] ); ?><p class="description">از نوار ابزار برای افزودن لینک و قالب‌بندی متن استفاده کنید.</p></td></tr>
		<tr><th scope="row"><label for="cloz-author-summary">توضیح کوتاه</label></th><td><textarea id="cloz-author-summary" class="large-text" rows="4" name="cloz_blog_author[summary]"><?php echo esc_textarea( $value['summary'] ); ?></textarea><p class="description">در باکس مقاله، معرفی صفحه نویسنده و توضیح متای همان صفحه استفاده می‌شود.</p></td></tr>
		<?php foreach ( [ 'x' => 'ایکس / توییتر', 'instagram' => 'اینستاگرام', 'facebook' => 'فیسبوک' ] as $key => $label ) : ?><tr><th scope="row"><label for="cloz-author-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th><td><input type="url" id="cloz-author-<?php echo esc_attr( $key ); ?>" class="regular-text" dir="ltr" name="cloz_blog_author[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value[ $key ] ); ?>" placeholder="https://"></td></tr><?php endforeach; ?>
		</tbody></table><?php submit_button( 'ذخیره نویسنده' ); ?>
	</form></div>
	<?php
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'posts_page_cloz-blog-author' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'cloz-blog-author-admin', get_stylesheet_directory_uri() . '/assets/blog-author-admin.js', [ 'jquery' ], filemtime( get_stylesheet_directory() . '/assets/blog-author-admin.js' ), true );
} );
