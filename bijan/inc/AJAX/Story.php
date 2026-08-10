<?php
namespace Bijan\AJAX;

use Bijan\AJAX;
use Bijan\Utils;
use Bijan\Utils\Story as UtilsStory;

class Story extends AJAX {
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	public function __construct() {
		return $this;
	}

	public function content() {
		$this->set_request_data();

		$id = Utils::convert_chars( $this->data['id'], true, 'absint' );

		$this->check_nonce( "story_{$id}_view" );

		$story = UtilsStory::get( $id );
		$html = '';
		ob_start();
		if( !empty( $story['attachment'] ) ) {
			UtilsStory::add_view( $id );
			$attachment_file = get_attached_file( $story['attachment'] );
			$is_img = explode( "/", wp_check_filetype( $attachment_file )['type'] )[0] === 'image';
			?>
			<div id="story-popup-attachment" style="background-image: url(<?php echo wp_get_attachment_image_url( $is_img ? $story['attachment'] : $story['small_img'], 'full' ) ?>)">
				<div id="story-popup-attachment-inner">
					<?php
					if( $is_img ) {
						echo wp_get_attachment_image( $story['attachment'], 'full', false, [
							'class' => 'story-popup-attachment-item',
						] );
					} else {
						?>
						<video src="<?php echo wp_get_attachment_url( $story['attachment'] ) ?>" class="story-popup-attachment-item" autoplay></video>
					<?php } ?>
				</div>
			</div>

			<div id="story-popup-back-btn">
				<?php if( is_rtl() ) { ?>
					<i class="bijan-icon-arrow-right"></i>
				<?php } else { ?>
					<i class="bijan-icon-arrow-left"></i>
				<?php } ?>
			</div>

			<div id="story-popup-details">
				<div id="story-popup-title" class="line-clamp line-clamp-1"><?php echo esc_html( $story['title'] ) ?></div>
				<?php if( !$is_img ) { ?>
					<div id="story-popup-video-progress-wrap">
						<div id="story-popup-video-progress">
							<div id="story-popup-video-progress-fill"></div>
							<div id="story-popup-video-progress-dot"></div>
						</div>
						<div id="story-popup-video-progress-time">00:00</div>
					</div>
				<?php } ?>

				<?php if( !empty( $story['post'] ) || is_user_logged_in() ) { ?>
					<div id="story-popup-bottom">
						<?php
						if( !empty( $story['post'] ) ) {
							$post = get_post( $story['post'] );
							$img = '';
							$title = '';
							if( $post->post_type === 'product' ) {
								$product = wc_get_product( $post );
								$img = $product->get_image( [56, 56] );
								$title = $product->get_name();
							} else {
								$img = get_the_post_thumbnail( $post, [56, 56] );
								$title = get_the_title( $post );
							}
							?>
							<a href="<?php echo get_permalink( $story['post'] ) ?>" id="story-popup-post">
								<div id="story-popup-post-img"><?php echo $img ?></div>
								<div id="story-popup-post-title" class="line-clamp line-clamp-1"><?php echo esc_html( $title ) ?></div>
							</a>
						<?php } ?>
						<?php get_template_part( "templates/components/story-like", null, [
							'story'	=> $story
						] ) ?>
					</div>
				<?php } ?>
			</div>
			<?php
		}
		$html = ob_get_clean();

		$this->result( 'success', [
			'html'		=> $html,
			'nonce'		=> wp_create_nonce( "bijan_toggle_like_story-{$id}" ),
			'nonce2'	=> wp_create_nonce( "bijan_story_like_html-{$id}" ),
		] );
	}

	public function toggle_like() {
		$this->set_request_data();

		$id = Utils::convert_chars( $this->data['id'], true, 'absint' );

		$this->check_nonce( "bijan_toggle_like_story-{$id}" );

		$likes = UtilsStory::toggle_like( $id );
		$this->result( 'success', count( $likes ) );
	}

	public function get_likes_html() {
		$this->set_request_data();
		
		$id = Utils::convert_chars( $this->data['id'], true, 'absint' );
		$this->check_nonce( "bijan_story_like_html-{$id}" );

		get_template_part( "templates/components/story-like", null, [
			'story'	=> UtilsStory::get( $id ),
		] );
		die;
	}
}