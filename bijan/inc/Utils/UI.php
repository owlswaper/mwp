<?php
namespace Bijan\Utils;

use Bijan\Utils;

class UI extends Utils {
	/**
	 * Create stars
	 *
	 * @param integer $active
	 * @param integer $count
	 * @param boolean $radio
	 * @param string $radio_name
	 * @param boolean $echo
	 * @return string
	 */
	public static function stars( int $active = 0, int $count = 5, bool $radio = false, string $radio_name = 'bijan_star', bool $echo = true ) : string {
		$html = '<div class="bijan_stars' . ($radio ? " bijan_stars-has-radio" : '') . '">';
		for( $index = 1; $index <= $count; $index++ ) {
			if( $radio ) {
				$html .= '<input type="radio" name="' . $radio_name . '" class="bijan_star-input" value="' . $index . '">';
			}
			$html .= '<div class="bijan_star' . ( $active !== 0 && $index <= $active ? " bijan_star-active" : "") . '">' . file_get_contents( BIJAN_DIR . "assets/icons/star.svg" ) . "</div>";
		}
		$html .= "</div>";

		if( $echo ) echo $html;

		return $html;
	}

	/**
	 * Create custom dropdown
	 *
	 * @param array $args [
	 * 		id		=> string Custom id
	 * 		classes	=> array Additional classes
	 * 		options	=> array List of options by key for value of the option and value for label
	 * 		current	=> string Selected option
	 * 		empty	=> string Label of the empty option
	 * ]
	 * @param bool $echo Echo the dropdown [Default: true]
	 * @return string
	 */
	public static function dropdown( array $args, bool $echo = true ) : string {
		$args = parent::check_default( $args, [
			'id'			=> '',
			'classes'		=> [],
			'options'		=> [],
			'current'		=> '',
			'empty'			=> '',
			'attrs'			=> [],
			'placeholder'	=> '',
		] );
		$wrap_attrs = [
			'class'	=> array_merge( ['dropdown'], $args['classes'] ),
		];
		if( !empty( $args['id'] ) ) {
			$wrap_attrs['id'] = $args['id'];
		}
		if( !empty( $args['attrs'] ) ) {
			$wrap_attrs = array_merge( $wrap_attrs, $args['attrs'] );
		}

		$current = !empty( $args['current'] ) && !empty( $args['options'][$args['current']] ) ? $args['options'][$args['current']] : $args['empty'];
		if( !$current && $args['placeholder'] ) {
			$current = $args['placeholder'];
		}

		$html = "<div " . parent::get_html_attributes( $wrap_attrs ) . ">";
			$html .= '<div class="dropdown-current-wrap">';
				$html .= '<div class="dropdown-current">' . esc_html( $current ) . '</div>';
				$html .= '<i class="bijan-icon-bottom dropdown-current-icon"></i>';
			$html .= '</div>';

			$html .= '<ul class="dropdown-items">';
				foreach( $args['options'] as $value => $label ) {
					$html .= '<li class="dropdown-item" data-value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</li>';
				}
			$html .= '</ul>';
		$html .= '</div>';
		
		if( $echo ) echo $html;

		return $html;
	}

	/**
	 * Show loading
	 *
	 * @param boolean $svg return svg file content
	 * @param boolean $echo
	 * @return string
	 */
	public static function loading( bool $svg = false, bool $echo = true ) : string {
		$file = "assets/img/loading.svg";
		if( $svg ) {
			$html = file_get_contents( BIJAN_DIR . $file );
		} else {
			$html = '<img src="' . BIJAN_URI . $file . '" alt="">';
		}
		if( $echo ) echo $html;

		return $html;
	}

	public static function curve( string $file, string $hover = '' ) {
		$classes = ["shape-curve"];
		$fill = '<div class="shape-curve-fill"></div>';
		$fill1 = '<div class="shape-curve-fill-1"></div>';
		$file = file_get_contents( BIJAN_DIR . "assets/img/curves/{$file}.svg" );
		$fill2 = '<div class="shape-curve-fill-2"></div>';
		if( $hover ) {
			$hover = '<div class="shape-curve-hover">' . file_get_contents( BIJAN_DIR . "assets/img/curves/{$hover}.svg" ) . '</div>';
		}
		$html = '<div class="' . parent::prepare_html_classes( $classes ) . '">' . $fill . $fill1 . $file . $fill2 . $hover . '</div>';
		echo $html;

		return $html;
	}

	public static function get_menu_icon( $item_id, $return = 'html' ) {
		$icon = get_post_meta( $item_id, '_bijan_icon', true );
		$filename = Utils::convert_chars( $icon, true, 'strtolower' );
		if( file_exists( BIJAN_DIR . "assets/icons/{$filename}.svg" ) ) {
			if( strpos( $icon, "bijan-icon-" ) !== 0 ) {
				$icon = "bijan-icon-{$icon}";
			}
		}
		if( $return != 'html' ) return $icon;

		// Get HTML
		$html = '';
		$icon_packs = Utils::get_icon_packs();
		foreach( $icon_packs as $pack ) {
			foreach( $pack['icons'] as $icon_name_or_url ) {
				if( $pack['mode'] == 'font-icon' ) {
					if( $icon != "{$pack['prefix']}{$icon_name_or_url}" ) continue;
				} else {
					if( !substr( $icon_name_or_url, -strlen( "{$icon}.svg" ) ) != $icon ) continue;
					$html = '<img src="' . $icon . '" alt="" class="menu-item-icon">';
				}
			}
		}
		if( !$html && $icon ) {
			$html .= '<i class="menu-item-icon ' . $icon . '" aria-hidden="true"></i>';
		}
		return $html;
	}

	public static function product_wishlist( $product_id, array $args = [] ) {
		$icon = 'heart';
		if( is_user_logged_in() ) {
			if( Wishlist::is_in_wishlist( $product_id, get_current_user_id() ) ) {
				$icon = 'heart-bold';
			}
		}

		$args = parent::check_default( $args, [
			'additional_classes'	=> [],
			'label'					=> '',
			'added_text'			=> esc_html__( "Added to wishlist.", 'bijan' ),
			'removed_text'			=> esc_html__( "Removed from wishlist.", 'bijan' ),
		] );
		$classes = array_merge( ['wishlist-button'], $args['additional_classes'] );
		?>
		<div class="<?php echo parent::prepare_html_classes( $classes ) ?>" data-product-id="<?php echo esc_attr( absint( $product_id ) ) ?>" data-nonce="<?php echo wp_create_nonce( "wishlist-toggle-{$product_id}" ) ?>">
			<i class="bijan-icon-<?php echo $icon ?>"></i>
			<?php
			if( $args['label'] ) {
				echo '<span class="wishlist-label">' . $args['label'] . '</span>';
			}
			?>

			<div class="wishlist-popover wishlist-popover-added">
				<?php echo $args['added_text'] ?>
			</div>

			<div class="wishlist-popover wishlist-popover-removed">
				<?php echo $args['removed_text'] ?>
			</div>

			<img src="<?php echo BIJAN_URI ?>assets/img/wishlist-loading.svg" alt="" class="wishlist-loading">
		</div>
		<?php
	}

	public static function title( string $text, string $tag = 'div', string $style = 'style-1', bool $echo = true ) {
		$classes = ['bijan-title'];
		if( !empty( $style ) ) {
			$classes[] = $style;
		}
		$file = "assets/img/curves/title.svg";
		if( $style == 'style-2' ) {
			$file = "assets/img/curves/title-2.svg";
		}
		$html = '<' . $tag . ' class="' .  parent::prepare_html_classes( $classes ) . '">' . esc_html( $text ) . file_get_contents( BIJAN_DIR . $file ) .'</' . $tag . '>';
		if( $echo ) echo $html;
		return $html;
	}

	public static function filter_radio( string $text, string $query_param, string $query_param_value, array $args = [] ) {
		$is_active = isset( $_GET[$query_param] );
		$url = $is_active ? remove_query_arg( $query_param ) : add_query_arg( $query_param, $query_param_value );

		$args = parent::check_default( $args, [
			'radio-align'	=> 'end', // end | start
		] );

		$classes = ['bijan_filter', 'bijan_filter_additional_option'];
		if( $is_active ) {
			$classes[] = 'active';
		}
		if( $args['radio-align'] == 'end' ) {
			$classes[] = 'radio-end';
		} else {
			$classes[] = 'radio-start';
		}
		?>
		<a href="<?php echo esc_url( $url ) ?>" class="<?php echo parent::prepare_html_classes( $classes ) ?>"><?php echo $text ?></a>
		<?php
	}
}