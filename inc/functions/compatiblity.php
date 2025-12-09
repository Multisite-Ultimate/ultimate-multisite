<?php
/**
 * Backport of WordPress 6.7.0 current_user_can_for_site() function.
 *
 * @package WP_Ultimo
 */

defined('ABSPATH') || exit;

if ( ! function_exists('current_user_can_for_site')) {

	/**
	 * Returns whether the current user has the specified capability for a given site.
	 *
	 * This function also accepts an ID of an object to check against if the capability is a meta capability. Meta
	 * capabilities such as `edit_post` and `edit_user` are capabilities used by the `map_meta_cap()` function to
	 * map to primitive capabilities that a user or role has, such as `edit_posts` and `edit_others_posts`.
	 *
	 * This function replaces the current_user_can_for_blog() function.
	 *
	 * Example usage:
	 *
	 *     current_user_can_for_site( $site_id, 'edit_posts' );
	 *     current_user_can_for_site( $site_id, 'edit_post', $post->ID );
	 *     current_user_can_for_site( $site_id, 'edit_post_meta', $post->ID, $meta_key );
	 *
	 * @since 6.7.0
	 *
	 * @param int    $site_id    Site ID.
	 * @param string $capability Capability name.
	 * @param mixed  ...$args    Optional further parameters, typically starting with an object ID.
	 * @return bool Whether the user has the given capability.
	 */
	function current_user_can_for_site($site_id, $capability, ...$args) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		return current_user_can_for_blog($site_id, $capability, ...$args);
	}
}
