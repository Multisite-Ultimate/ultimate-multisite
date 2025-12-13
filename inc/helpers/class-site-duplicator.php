<?php
/**
 * Exposes the public API to handle site duplication.
 *
 * @package WP_Ultimo
 * @subpackage Helper
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers;

use Psr\Log\LogLevel;

// Exit if accessed directly
defined('ABSPATH') || exit;

require_once WP_ULTIMO_PLUGIN_DIR . '/inc/duplication/duplicate.php';

if ( ! defined('MUCD_PRIMARY_SITE_ID')) {
	define('MUCD_PRIMARY_SITE_ID', get_current_network_id()); // phpcs:ignore
}
if ( ! defined('MUCD_NETWORK_PAGE_DUPLICATE_COPY_FILE_ERROR')) {
	// translators: %s the file path that failed.
	define('MUCD_NETWORK_PAGE_DUPLICATE_COPY_FILE_ERROR', __('Failed to copy files : check permissions on <strong>%s</strong>', 'ultimate-multisite')); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
}
if ( ! defined('MUCD_NETWORK_PAGE_DUPLICATE_VIEW_LOG')) {
	define('MUCD_NETWORK_PAGE_DUPLICATE_VIEW_LOG', __('View log', 'ultimate-multisite')); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
}
if ( ! defined('MUCD_MAX_NUMBER_OF_SITE')) {
	define('MUCD_MAX_NUMBER_OF_SITE', 5000); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
}

/**
 * Exposes the public API to handle site duplication.
 *
 * The decision to create a buffer interface (this file), as the API layer
 * for the duplication functions is simple: it allows us to swith the duplication
 * component used without breaking backwards-compatibility in the future.
 *
 * @since 2.0.0
 */
class Site_Duplicator {

	/**
	 * Static-only class.
	 */
	private function __construct() {}

	/**
	 * Duplicate an existing network site.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $from_site_id ID of the site you wish to copy.
	 * @param string $title Title of the new site.
	 * @param array  $args List of duplication parameters, check Site_Duplicator::process_duplication for reference.
	 * @return int|\WP_Error ID of the newly created site or error.
	 */
	public static function duplicate_site($from_site_id, $title, $args = []) {

		$args['from_site_id'] = $from_site_id;
		$args['title']        = $title;

		$duplicate_site = self::process_duplication($args);

		if (is_wp_error($duplicate_site)) {

			// translators: %s id the template site id and %s is the error message returned.
			$message = sprintf(__('Attempt to duplicate site %1$d failed: %2$s', 'ultimate-multisite'), $from_site_id, $duplicate_site->get_error_message());

			wu_log_add('site-duplication', $message, LogLevel::ERROR);

			return $duplicate_site;
		}

		// translators: %1$d is the ID of the site template used, and %2$d is the id of the new site.
		$message = sprintf(__('Attempt to duplicate site %1$d successful - New site id: %2$d', 'ultimate-multisite'), $from_site_id, $duplicate_site);

		wu_log_add('site-duplication', $message);

		return $duplicate_site;
	}

	/**
	 * Replace the contents of a site with the contents of another.
	 *
	 * @since 2.0.0
	 *
	 * @param int   $from_site_id Site to get the data from.
	 * @param int   $to_site_id Site to override.
	 * @param array $args List of duplication parameters, check Site_Duplicator::process_duplication for reference.
	 * @return int|false ID of the created site.
	 */
	public static function override_site($from_site_id, $to_site_id, $args = []) {

		$to_site = wu_get_site($to_site_id);

		if (! $to_site) {
			wu_log_add('site-duplication', sprintf('Target site %d not found', $to_site_id), LogLevel::ERROR);
			return false;
		}

		$to_site_membership_id = $to_site->get_membership_id();

		$to_site_membership = $to_site->get_membership();

		$to_site_customer = $to_site_membership ? $to_site_membership->get_customer() : false;

		// Determine email - use customer email if available, otherwise use site admin email
		$email = $to_site_customer ? $to_site_customer->get_email_address() : get_blog_option($to_site_id, 'admin_email');

		$args = wp_parse_args(
			$args,
			[
				'email'        => $email,
				'title'        => $to_site->get_title(),
				'path'         => $to_site->get_path(),
				'from_site_id' => $from_site_id,
				'to_site_id'   => $to_site_id,
				'meta'         => $to_site->meta,
			]
		);

		$duplicate_site_id = self::process_duplication($args);

		if (is_wp_error($duplicate_site_id)) {

			// translators: %s id the template site id and %s is the error message returned.
			$message = sprintf(__('Attempt to override site %1$d with data from site %2$d failed: %3$s', 'ultimate-multisite'), $from_site_id, $to_site_id, $duplicate_site_id->get_error_message());

			wu_log_add('site-duplication', $message, LogLevel::ERROR);

			return false;
		}

		$new_to_site = wu_get_site($duplicate_site_id);

		$new_to_site->set_membership_id($to_site_membership_id);

		$new_to_site->set_customer_id($to_site->get_customer_id());

		$new_to_site->set_template_id($from_site_id);

		$new_to_site->set_type('customer_owned');

		$new_to_site->set_title($to_site->get_title());

		$saved = $new_to_site->save();

		if (is_wp_error($saved)) {
			// translators: %s id the template site id and %s is the error message returned.
			$message = sprintf(__('Attempt to override site %1$d with data from site %2$d failed: %3$s', 'ultimate-multisite'), $from_site_id, $to_site_id, $saved->get_error_message());

			wu_log_add('site-duplication', $message, LogLevel::ERROR);
			return false;
		}

		// translators: %1$d is the ID of the site template used, and %2$d is the ID of the overriden site.
		$message = sprintf(__('Attempt to override site %1$d with data from site %2$d successful.', 'ultimate-multisite'), $from_site_id, $duplicate_site_id);

		wu_log_add('site-duplication', $message);

		return $saved;
	}

	/**
	 * Processes a site duplication.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args List of parameters of the duplication.
	 *                    - email Email of the admin user to be created.
	 *                    - title Title of the (new or not) site.
	 *                    - path  Path of the new site.
	 *                    - from_site_id ID of the template site being used.
	 *                    - to_site_id   ID of the target site. Can be false to create new site.
	 *                    - keep_users   If we should keep users or not. Defaults to true.
	 *                    - copy_files   If we should copy the uploaded files or not. Defaults to true.
	 *                    - public       If the (new or not) site should be public. Defaults to true.
	 *                    - domain       The domain of the new site.
	 *                    - network_id   The network ID to allow for multi-network support.
	 * @return int|\WP_Error The Site ID.
	 */
	protected static function process_duplication($args) {

		global $current_site, $wpdb;

		$args = wp_parse_args(
			$args,
			[
				'email'        => '',    // Required arguments.
				'title'        => '',    // Required arguments.
				'path'         => '/',   // Required arguments.
				'from_site_id' => false, // Required arguments.
				'to_site_id'   => false,
				'keep_users'   => true,
				'public'       => true,
				'domain'       => $current_site->domain,
				'copy_files'   => wu_get_setting('copy_media', true),
				'network_id'   => get_current_network_id(),
				'meta'         => [],
				'user_id'      => 0,
			]
		);

		// Checks
		$args = (object) $args;

		$site_domain = $args->domain . $args->path;

		$wpdb->hide_errors();

		if ( ! $args->from_site_id) {
			return new \WP_Error('from_site_id_required', __('You need to provide a valid site to duplicate.', 'ultimate-multisite'));
		}

		$user_id = ! empty($args->user_id) ? $args->user_id : self::create_admin($args->email, $site_domain);

		if (is_wp_error($user_id)) {
			return $user_id;
		}

		if ( ! $args->to_site_id) {
			$meta = array_merge($args->meta, ['public' => $args->public]);

			$args->to_site_id = wpmu_create_blog($args->domain, $args->path, $args->title, $user_id, $meta, $args->network_id);

			$wpdb->show_errors();
		}

		if (is_wp_error($args->to_site_id)) {
			return $args->to_site_id;
		}

		if ( ! is_numeric($args->to_site_id)) {
			return new \WP_Error('site_creation_failed', __('An attempt to create a new site failed.', 'ultimate-multisite'));
		}

		if ( ! is_super_admin($user_id) && ! get_user_option('primary_blog', $user_id)) {
			update_user_option($user_id, 'primary_blog', $args->to_site_id, true);
		}

		\MUCD_Duplicate::bypass_server_limit();

		if ($args->copy_files) {
			\MUCD_Files::copy_files($args->from_site_id, $args->to_site_id);
		}

		/**
		 * Supress email change notification on site duplication processes.
		 */
		add_filter('send_site_admin_email_change_email', '__return_false');

		\MUCD_Data::copy_data($args->from_site_id, $args->to_site_id);

		if ($args->keep_users) {
			\MUCD_Duplicate::copy_users($args->from_site_id, $args->to_site_id);
		}

		wp_cache_flush();

		/**
		 * Allow developers to hook after a site duplication happens.
		 *
		 * @since 1.9.4
		 * @return void
		 */
		do_action(
			'wu_duplicate_site',
			[
				'site_id' => $args->to_site_id,
			]
		);

		/*
		 * Reset WooCommerce Subscriptions staging mode detection
		 * to prevent the duplicated site from being locked in staging mode.
		 */
		self::reset_woocommerce_subscriptions_staging_mode($args->to_site_id);

		return $args->to_site_id;
	}

	/**
	 * Creates an admin user if no user exists with this email.
	 *
	 * @since 2.0.0
	 * @param  string $email The email.
	 * @param  string $domain The domain.
	 * @return int|\WP_Error Id of the user created.
	 */
	public static function create_admin($email, $domain) {

		// Create New site Admin if not exists
		$password = 'N/A';

		$user_id = email_exists($email);

		if ( ! $user_id) { // Create a new user with a random password

			$password = wp_generate_password(12, false);

			$user_id = wpmu_create_user($domain, $password, $email);

			if (false === $user_id) {
				return new \WP_Error('user_creation_error', __('We were not able to create a new admin user for the site being duplicated.', 'ultimate-multisite'));
			} else {
				wp_new_user_notification($user_id);
			}
		}

		return $user_id;
	}

	/**
	 * Resets WooCommerce Subscriptions staging mode detection for a duplicated site.
	 *
	 * When a site is duplicated, WooCommerce Subscriptions detects the URL change
	 * and enters "staging mode", which disables automatic payments and subscription
	 * emails. This method resets the stored site URL to match the new site's URL,
	 * preventing the staging mode from being triggered.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id The ID of the newly duplicated site.
	 * @return void
	 */
	protected static function reset_woocommerce_subscriptions_staging_mode($site_id) {

		if ( ! $site_id) {
			return;
		}
		// Ensure plugin.php is loaded for is_plugin_active_for_network()
		if ( ! function_exists('is_plugin_active_for_network')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// Check if WooCommerce Subscriptions is active on the site
		if ( ! is_plugin_active_for_network('woocommerce-subscriptions/woocommerce-subscriptions.php')) {
			switch_to_blog($site_id);

			$active_plugins = get_option('active_plugins', []);

			restore_current_blog();

			if ( ! in_array('woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins, true)) {
				return;
			}
		}

		// Switch to the duplicated site context
		switch_to_blog($site_id);

		try {
			// Get the current site URL
			$site_url = get_site_url();

			// Validate that we have a non-empty site URL
			if (empty($site_url) || ! is_string($site_url)) {
				// Skip updates if site URL is invalid
				return;
			}

			// Parse the URL scheme and validate the result
			$scheme = wp_parse_url($site_url, PHP_URL_SCHEME);

			// Validate wp_parse_url returned a valid scheme
			if (empty($scheme) || ! is_string($scheme)) {
				// Skip updates if URL parsing failed
				return;
			}

			// Generate the obfuscated key that WooCommerce Subscriptions uses
			// It inserts '_[wc_subscriptions_siteurl]_' in the middle of the URL
			$scheme_with_separator   = $scheme . '://';
			$site_url_without_scheme = str_replace($scheme_with_separator, '', $site_url);

			// Validate the URL without scheme is a non-empty string
			if (empty($site_url_without_scheme) || ! is_string($site_url_without_scheme)) {
				// Skip updates if URL manipulation failed
				return;
			}

			$obfuscated_url = $scheme_with_separator . substr_replace(
				$site_url_without_scheme,
				'_[wc_subscriptions_siteurl]_',
				intval(strlen($site_url_without_scheme) / 2),
				0
			);

			// Update the WooCommerce Subscriptions site URL option
			update_option('wc_subscriptions_siteurl', $obfuscated_url);

			// Delete the "ignore notice" option to ensure a clean state
			delete_option('wcs_ignore_duplicate_siteurl_notice');
		} finally {
			// Always restore the original blog context, even if errors or exceptions occur
			restore_current_blog();
		}
	}
}
