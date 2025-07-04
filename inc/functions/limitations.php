<?php
/**
 * Limitations Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Checks if the current site has a certain product associated to it.
 *
 * This is useful for controlling the display of certain info
 * based on the plans and other products attached to a membership -
 * and as a consequence - to a site.
 *
 * For example, to display something only to the customers of the
 * products with the "premium" slug, we'd have something like this:
 *
 * if (wu_has_product('premium')) {
 *
 *    // premium content here.
 *
 * } else {
 *
 *   // Content for non-members.
 *
 * }.
 *
 * One important things to keep in mind is that this function
 * does not check for the status of that site's membership by default.
 * If that's something that you need, pass the second param "blocking" as true.
 * If the blocking flag is set, the function only returns true if the site has
 * the product and if the membership is active.
 *
 * Another important note:
 * This function behaves differently when called in the context of
 * the main site or a regular site. In these cases, we loop through
 * all of the customer's memberships to try to find at least one
 * with the requested product. This makes this function useful
 * to control access to content on the main site, for example.
 *
 * @since 2.0.0
 * @see wu_is_membership_active()
 *
 * @todo Implement search algo for main site and regular site.
 * @param string|array $product_slug Product slug to check. Can also be an array of product slugs.
 *                                   Will return true if ANY slug on the array is present.
 * @param bool         $blocking When set to true, this flag also validates the active status of the membership.
 * @param string       $site_id  The site ID to test.
 * @return boolean
 */
function wu_has_product($product_slug, $blocking = false, $site_id = '') {

	if ( ! is_array($product_slug)) {
		$product_slug = [$product_slug];
	}

	if (empty($site_id)) {
		$site_id = get_current_blog_id();
	}

	$site = wu_get_site($site_id);

	if (empty($site)) {
		return new \WP_Error('site-not-found', __('Invalid site ID', 'multisite-ultimate'));
	}

	$membership = $site->get_membership();

	if (empty($membership)) {
		return true;
	}

	$applicable_products_slugs = $membership->get_applicable_product_slugs();

	$contains_product = empty(array_intersect($product_slug, $applicable_products_slugs)) === false;

	$active_status = true;

	if ($blocking) {
		$active_status = $membership->is_active();
	}

	return $contains_product && $active_status;
}

/**
 * Checks if the membership associated with a site is active.
 *
 * @since 2.0.0
 *
 * @param string $site_id The site ID to test.
 * @return bool
 */
function wu_is_membership_active($site_id = '') {

	if (empty($site_id)) {
		$site_id = get_current_blog_id();
	}

	$site = wu_get_site($site_id);

	if (empty($site)) {
		return new \WP_Error('site-not-found', __('Invalid site ID', 'multisite-ultimate'));
	}

	$membership = $site->get_membership();

	if (empty($membership)) {
		return true;
	}

	return $membership->is_active();
}

/**
 * Register a new Limitation module.
 *
 * @since 2.0.0
 *
 * @param string $id The id of the limitation module.
 * @param string $class_name The module class name.
 * @return void
 */
function wu_register_limit_module($id, $class_name) {

	add_filter(
		'wu_limit_classes',
		function ($classes) use ($id, $class_name) {

			$id = sanitize_title($id);

			$classes[ $id ] = $class_name;

			return $classes;
		}
	);
}

/**
 * Generate the modal link to search for an upgrade path.
 *
 * @since 2.0.0
 *
 * @param array $args The module and type of limit that needs upgrading.
 * @return string
 */
function wu_generate_upgrade_to_unlock_url($args) {

	$args = wp_parse_args(
		$args,
		[
			'module' => false,
			'type'   => false,
		]
	);

	$membership = wu_get_current_site()->get_membership();

	if ( ! $membership) {
		return '';
	}

	$upgrade_url = wu_get_membership_update_url($membership);

	$url = add_query_arg($args, $upgrade_url);

	/**
	 * Allow developers to change the upgrade to unlock URL
	 *
	 * @param string $url  The upgrade URL.
	 * @param array  $args The module and type of limit that needs upgrading.
	 */
	return apply_filters('wu_upgrade_to_unlock_url', $url, $args);
}

/**
 * Generates a Unlock to Upgrade button for the upgrade modal.
 *
 * @since 2.0.0
 *
 * @param string $title The title of the modal and label of the button.
 * @param array  $args The module and type of limit that needs upgrading.
 * @return string
 */
function wu_generate_upgrade_to_unlock_button($title, $args) {

	$args = wp_parse_args(
		$args,
		[
			'module'  => false,
			'type'    => false,
			'classes' => '',
		]
	);

	$url = wu_generate_upgrade_to_unlock_url(
		[
			'module' => $args['module'],
			'type'   => $args['type'],
		]
	);

	$element = sprintf(
		'<a href="%s" title="%s" class="%s">%s</a>',
		$url,
		$title,
		$args['classes'],
		$title
	);

	return $element;
}

/**
 * Activate a plugin(s) via Job Queue.
 *
 * @since 2.0.0
 *
 * @param int          $site_id The site ID.
 * @param string|array $plugins The plugin or list of plugins to activate.
 * @param boolean      $network_wide If we want to activate it network-wide.
 * @param boolean      $silent IF we should do the process silently - true by default.
 * @return void
 */
function wu_async_activate_plugins($site_id, $plugins, $network_wide = false, $silent = true) {

	wu_enqueue_async_action(
		'wu_async_handle_plugins',
		[
			'action'       => 'activate',
			'site_id'      => $site_id,
			'plugins'      => $plugins,
			'network_wide' => $network_wide,
			'silent'       => $silent,
		]
	);
}

/**
 * Deactivates a plugin(s) via Job Queue.
 *
 * @since 2.0.0
 *
 * @param int          $site_id The site ID.
 * @param string|array $plugins The plugin or list of plugins to activate.
 * @param boolean      $network_wide If we want to activate it network-wide.
 * @param boolean      $silent IF we should do the process silently - true by default.
 * @return void
 */
function wu_async_deactivate_plugins($site_id, $plugins, $network_wide = false, $silent = true) {

	wu_enqueue_async_action(
		'wu_async_handle_plugins',
		[
			'action'       => 'deactivate',
			'site_id'      => $site_id,
			'plugins'      => $plugins,
			'network_wide' => $network_wide,
			'silent'       => $silent,
		]
	);
}

/**
 * Switch themes via Job Queue.
 *
 * @since 2.0.0
 *
 * @param int    $site_id The site ID.
 * @param string $theme_stylesheet The theme stylesheet.
 * @return void
 */
function wu_async_switch_theme($site_id, $theme_stylesheet) {

	wu_enqueue_async_action(
		'wu_async_switch_theme',
		[
			'site_id'          => $site_id,
			'theme_stylesheet' => $theme_stylesheet,
		]
	);
}
