<?php

use Bijan\Utils;
use Bijan\Utils\UI;

if( !function_exists( 'bijan_comment_fields' ) ) {
	function bijan_comment_fields( $fields ) {
		$fields = Utils::unset( $fields, ['url', 'cookies'] );
		return $fields;
	}
}
add_filter( 'comment_form_fields', 'bijan_comment_fields' );

if( !function_exists( "bijan_comment_reply_link_args" ) ) {
	function bijan_comment_reply_link_args( $args ) {
		$args['reply_text'] = '<i class="bijan-icon-undo"></i>';
		return $args;
	}
}
add_filter( 'comment_reply_link_args', 'bijan_comment_reply_link_args' );

if( !function_exists( 'bijan_comment_stars_form' ) ) {
	function bijan_comment_stars_form() {
		if( is_singular( 'product' ) ) return;
		?>
		<div class="bijan_comment_stars-wrap">
			<div class="bijan_comment_star-title"><?php esc_html_e( 'Your score:', 'bijan' ) ?></div>
			<?php UI::stars( 0, 5, true ) ?>
		</div>
		<?php
	}
}
add_action( 'comment_form', 'bijan_comment_stars_form' );

if( !function_exists( 'bijan_save_comment_stars' ) ) {
	function bijan_save_comment_stars( $comment_id, $comment_approved ) {
		$star = 0;
		if( !empty( $_POST["bijan_star"] ) ) {
			$star = Utils::convert_chars( $_POST["bijan_star"], true, 'absint' );
			$star = $star < 0 ? 0 : $star;
			$star = $star > 5 ? 5 : $star;
		}
		update_comment_meta( $comment_id, '_bijan_star', $star );
	}
}
add_action( 'comment_post', 'bijan_save_comment_stars', 10, 2 );

if( !function_exists( 'bijan_comment_columns' ) ) {
	function bijan_comment_columns( $columns ) {
		$columns['stars'] = __( 'Stars', 'bijan' );
		return $columns;
	}
}
add_filter( 'manage_edit-comments_columns', 'bijan_comment_columns' );

if( !function_exists( 'bijan_comment_star_column' ) ) {
	function bijan_comment_star_column( $column, $comment_id ) {
		if( $column != 'stars' ) return;

		$stars = get_comment_meta( $comment_id, '_bijan_star', true );
		$stars = Utils::convert_chars( $stars, true, 'absint' );
		$stars = $stars < 0 ? 0 : $stars;
		$stars = $stars > 5 ? 5 : $stars;
		if( $stars === 0 ) {
			echo "-----";
		} else {
			echo '<div style="display:flex;gap:4px">';
			for( $index = 0; $index <= $stars; $index++ ) {
				echo '<div style="width: 20px;height:20px;">' . file_get_contents( BIJAN_DIR . "assets/icons/star.svg" ) . "</div>";
			}
			echo "</div>";
		}
	}
}
add_action( 'manage_comments_custom_column', 'bijan_comment_star_column', 10, 2 );

if( !function_exists( 'bijan_comment_stars' ) ) {
	function bijan_comment_stars( $args, $comment ) {
		$stars = absint(get_comment_meta( $comment->comment_ID, '_bijan_star', true ));
		
		if( $stars ) {
			$args['after'] = '<div class="bijan_comment-star"><span class="bijan_comment-star-count">' . $stars . '</span><i class="bijan-icon-star"></i></div>';
		}

		return $args;
	}
}
add_filter( 'comment_reply_link_args', 'bijan_comment_stars', 10, 2 );

if( !function_exists( "bijan_comment_form_top" ) ) {
	function bijan_comment_form_top() {
		get_template_part( "templates/components/section_title", null, [
			'icon'	=> 'bijan-icon-edit',
			'tag'	=> 'h4',
			'title'	=> esc_html__( "Your comment", 'bijan' ),
		] );
	}
}
add_action( 'comment_form_top', 'bijan_comment_form_top' );