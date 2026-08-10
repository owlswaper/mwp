<?php
/**
 * The template for displaying comments
 *
 * The area of the page that contains both current comments
 * and the comment form.
 */
if( !defined( 'ABSPATH' ) ) exit;
if( post_password_required() || !post_type_supports( get_post_type(), 'comments' ) || !comments_open() ) {
	return;
}
?>
<div id="comments">
	<?php if( have_comments() ) { ?>
		<ol class="comment-list">
			<?php
				wp_list_comments(
					[
						'style'			=> 'ol',
						'avatar_size'	=> 56,
					]
				);
			?>
		</ol><!-- .comment-list -->
	<?php } ?>

	<div id="comments-form-wrap">
		<?php
		comment_form( [
			'title_reply'	=> '',
			'logged_in_as'	=> '',
		] );
		?>
	</div>
</div>