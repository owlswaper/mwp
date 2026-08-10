<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class Posts extends Utils {
	/**
	 * Get a post object by various means.
	 *
	 * @param mixed $post The post identifier (ID, object, or array).
	 * @return WP_Post|null The post object or null if not found.
	 */
	public static function get_post( $post = null ) {
		if( is_object( $post ) || is_numeric( $post ) ) {
			return get_post( $post );
		} else if( is_array( $post ) ) {
			if( !empty( $post['ID'] ) ) {
				return get_post( $post['ID'] );
			} else if( !empty( $post['id'] ) ) {
				return get_post( $post['id'] );
			}
		}
		global $post;
		return $post;
	}

	/**
	 * Get the ID of a post by various means.
	 *
	 * @param mixed $post The post identifier (ID, object, or array).
	 * @return int The post ID or 0 if not found.
	 */
	public static function get_post_id( $post = null ) {
		if( $post === 0 ) {
			return 0;
		}
		
		if( is_a( $post, 'WP_Post' ) ) {
			return $post->ID;
		}

		if( is_numeric( $post ) && absint( $post ) !== 0 ) {
			return absint( $post );
		}

		if( absint( $post ) === 0 && !is_singular() ) return 0;

		return get_the_ID();
	}

	/**
	 * Retrieve post options with defaults applied.
	 *
	 * Merges the provided options with defaults and fetches the values
	 * from post meta. Boolean defaults are properly converted.
	 *
	 * @param array[string] $defaults Default option values.
	 * @param int $post_id Optional. Post ID to retrieve options for. Defaults to 0.
	 * @param array $options Optional. Specific options to retrieve. Defaults to all.
	 *
	 * @return array[string] Array of option keys and their resolved values.
	 */
	public static function get_post_options( $defaults, $post_id = 0, array $options = [] ) {
		$post_id = self::get_post_id( $post_id );

		if( !$options ) {
			$options = $defaults;
		} else {
			foreach( $options as $index => $option ) {
				if( isset( $defaults[$option] ) ) {
					$options[$option] = $defaults[$option];
				}
				unset( $options[$index] );
			}
		}
		foreach( $options as $key => $default ) {
			$value = get_post_meta( $post_id, "_{$key}", true );
			if( is_bool( $default ) ) {
				if( $value === '' ) {
					$value = $default;
				}
				$value = self::to_bool( $value );
			}
			if( $value === '' ) {
				if( !metadata_exists( 'post', $post_id, $key ) ) {
					$value = $defaults[$key];
				}
			}
			$options[$key] = $value;
		}
		return self::check_default( $options, $defaults );
	}

	/**
	 * Save post options to post meta.
	 *
	 * Updates each option in $options using post meta keys prefixed with "_".
	 * Boolean values are converted to proper string representation. Scalar
	 * values are sanitized before saving.
	 *
	 * @param array[string] $options Option values to save.
	 * @param array $defaults Default option values.
	 * @param int $post_id Optional. Post ID to save options for. Defaults to 0.
	 *
	 * @return void
	 */
	public static function save_post_options( array $options, $defaults, $post_id = 0 ) {
		$post_id = self::get_post_id( $post_id );

		foreach( $defaults as $option_key => $value ) {
			if( isset( $options[$option_key] ) ) {
				$value = $options[$option_key];
				if( is_scalar( $value ) ) {
					if( is_bool( $value ) ) {
						$value = self::to_bool( $value );
						if( $value === false ) {
							$value = "false";
						}
					}
				}
				update_post_meta( $post_id, "_{$option_key}", $value );
			}
		}
	}
}