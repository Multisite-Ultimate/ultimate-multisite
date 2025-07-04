<?php
/**
 * Admin Panel Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the HTML markup of a empty state page.
 *
 * @since 2.0.0
 *
 * @param array $args List of the page arguments.
 * @return string
 */
function wu_render_empty_state($args = []) {

	$args = wp_parse_args(
		$args,
		[
			'message'                  => __('This is not yet available...', 'multisite-ultimate'),
			'sub_message'              => __('We\'re still working on this part of the product.', 'multisite-ultimate'),
			'link_label'               => __('&larr; Go Back', 'multisite-ultimate'),
			'link_url'                 => 'javascript:history.go(-1)',
			'link_classes'             => '',
			'link_icon'                => '',
			'display_background_image' => true,
		]
	);

	return wu_get_template_contents('base/empty-state', $args);
}

/**
 * Checks if should use wrap container or not based on user setting.
 *
 * @since 2.0.0
 */
function wu_wrap_use_container() {

	echo get_user_setting('wu_use_container', false) ? 'admin-lg:wu-container admin-lg:wu-mx-auto' : '';
}

/**
 * Renders the responsive table single-line.
 *
 * @since 2.0.0
 *
 * @param array $args Main arguments.
 * @param array $first_row The first row of icons + labels.
 * @param array $second_row The second row, on the right.
 * @return string
 */
function wu_responsive_table_row($args = [], $first_row = [], $second_row = []) {

	$args = wp_parse_args(
		$args,
		[
			'id'     => '',
			'title'  => __('No Title', 'multisite-ultimate'),
			'url'    => '#',
			'status' => '',
			'image'  => '',
		]
	);

	return wu_get_template_contents('base/responsive-table-row', compact('args', 'first_row', 'second_row'));
}
