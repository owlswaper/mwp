<?php

use Bijan\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'video_source'		=> 'file',
	'video_file'		=> [
		'id'	=> 0,
		'url'	=> '',
	],
	'video_embed_id'	=> '',
	'controls'			=> true,
	'cover_file'		=> [
		'id'	=> 0,
		'url'	=> ','
	],

	// Aparat settings
	'aparat_autoplay'			=> false,
	'aparat_show_title'			=> false,
	'aparat_muted'				=> false,
	'aparat_end_recomendation'	=> false,
	'aparat_start_minute'		=> 0,
	'aparat_start_second'		=> 0,
] );
$args['aparat_start_minute'] = Utils::absint_pro( $args['aparat_start_minute'], 0 );
$args['aparat_start_second'] = Utils::absint_pro( $args['aparat_start_second'], 0, 59 );

$classes = ['video-container', "video-{$args['video_source']}"];

$cover = !empty( $args['cover_file']['id'] ) ? wp_get_attachment_image( $args['cover_file']['id'], 'full', false, ['loading' => 'false'] ) : '<img src="' . $args['cover_file']['url'] . '" alt="">';
?>
<div class="<?php echo Utils::prepare_html_classes( $classes ) ?>">
	<div class="video-inner">
		<?php if( $args['video_source'] == 'file' ) { ?>
			<div class="video-wrap">
				<?php echo $cover ?>
				<video src="<?php echo $args['video_file']['id'] ? wp_get_attachment_url( $args['video_file']['id'] ) : esc_url( $args['video_file']['url'] ) ?>"<?php echo $args['controls'] ? " controls" : '' ?>></video>
				<?php
				get_template_part( "templates/components/button", null, [
					'icon'		=> 'bijan-icon-play',
					'classes'	=> ['bijan-video-play']
				] )
				?>
			</div>
			<?php
		} else if( $args['video_source'] == 'aparat' ) {
			$aparat_url = "https://www.aparat.com/video/video/embed/videohash/{$args['video_embed_id']}/vt/frame";
			if( $args['aparat_show_title'] ) {
				$aparat_url = add_query_arg( 'titleShow', 'true', $aparat_url );
			}
			if( $args['aparat_muted'] ) {
				$aparat_url = add_query_arg( 'muted', 'true', $aparat_url );
			}
			if( $args['aparat_autoplay'] ) {
				$aparat_url = add_query_arg( 'autoplay', 'true', $aparat_url );
			}
			if( $args['aparat_end_recomendation'] ) {
				$aparat_url = add_query_arg( 'recom', 'self', $aparat_url );
			}
			if( !empty( $args['aparat_start_minute'] ) || !empty( $args['aparat_start_second'] ) ) {
				$start_time = $args['aparat_start_minute']*MINUTE_IN_SECONDS + $args['aparat_start_second'];
				$aparat_url = add_query_arg( 'startTime', $start_time, $aparat_url );
			}

			$iframe_attrs = [
				'src'					=> $aparat_url,
				'allowFullScreen'		=> 'true',
				'webkitallowfullscreen'	=> 'true',
				'mozallowfullscreen'	=> 'true',
			];
			if( $args['aparat_autoplay'] ) {
				$iframe_attrs['allow'] = 'autoplay';
			}
			?>
			<div class="bijan_iframe-aparat_embed_frame"><span></span><iframe <?php echo Utils::get_html_attributes( $iframe_attrs ) ?>></iframe></div>
		<?php } else if( $args['video_source'] == 'youtube' ) { ?>
			<iframe width="100%" src="https://www.youtube.com/embed/<?php echo $args['video_embed_id'] ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
		<?php } ?>
	</div>
</div>