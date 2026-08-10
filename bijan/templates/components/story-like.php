<?php

use Bijan\Utils;
use Bijan\Utils\Story;

if( is_user_logged_in() ) {
	extract( $args );
	$liked = Story::is_user_liked( $story['post'], 0, $story['likes'] );
	?>
	<div id="story-popup-like">
		<i class="bijan-icon-heart-bold story-popup-like story-popup-liked-icon"<?php Utils::hide( true, $liked ) ?>></i>
		<i class="bijan-icon-heart story-popup-like story-popup-like-icon"<?php Utils::hide( true, !$liked ) ?>></i>
		<span id="story-popup-like-count"><?php echo absint( $story['likes_count'] ) ?></span>
	</div>
	<?php
}