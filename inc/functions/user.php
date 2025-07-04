<?php
/**
 * User Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns a list of valid selectable roles.
 *
 * @since 2.0.0
 * @param boolean $add_default_option Adds a new default option.
 * @return array
 */
function wu_get_roles_as_options($add_default_option = false) {

	if ( ! function_exists('get_editable_roles')) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	$roles = [];

	if ($add_default_option) {
		$roles['default'] = __('Use Multisite Ultimate default', 'multisite-ultimate');
	}

	$editable_roles = get_editable_roles();

	foreach ($editable_roles as $role => $details) {
		$roles[ esc_attr($role) ] = translate_user_role($details['name']);
	}

	return $roles;
}
