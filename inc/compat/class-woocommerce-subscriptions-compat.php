<?php
/**
 * WooCommerce Subscriptions Compatibility Layer
 *
 * Handles WooCommerce Subscriptions staging mode detection
 *
 * @package WP_Ultimo
 * @subpackage Compat/WooCommerce_Subscriptions_Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles WooCommerce Subscriptions staging mode detection
 *
 * When a site's URL changes (through duplication or primary domain mapping),
 * WooCommerce Subscriptions detects the URL change and enters "staging mode",
 * which disables automatic payments and subscription emails. This class resets
 * the stored site URL to match the new site's URL, preventing the staging mode
 * from being triggered.
 *
 * @since 2.0.0
 */
class WooCommerce_Subscriptions_Compat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_action('wu_duplicate_site', [$this, 'reset_staging_mode_on_duplication']);

		add_action('wu_domain_became_primary', [$this, 'reset_staging_mode_on_primary_domain_change'], 10, 3);
	}

	/**
	 * Resets WooCommerce Subscriptions staging mode after site duplication.
	 *
	 * @since 2.0.0
	 *
	 * @param array $site Info about the duplicated site containing 'site_id'.
	 * @return void
	 */
	public function reset_staging_mode_on_duplication(array $site): void {

		if (! isset($site['site_id'])) {
			return;
		}

		$this->reset_staging_mode((int) $site['site_id']);
	}

	/**
	 * Resets WooCommerce Subscriptions staging mode when a primary domain is set.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Domain $domain  The domain that became primary.
	 * @param int                      $blog_id The blog ID of the affected site.
	 * @param bool                     $was_new Whether this is a newly created domain.
	 * @return void
	 */
	public function reset_staging_mode_on_primary_domain_change($domain, int $blog_id, bool $was_new): void {

		$this->reset_staging_mode($blog_id);
	}

	/**
	 * Resets WooCommerce Subscriptions staging mode detection for a site.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id The ID of the site.
	 * @return void
	 */
	public function reset_staging_mode(int $site_id): void {

		if (! $site_id) {
			return;
		}

		if (! $this->is_woocommerce_subscriptions_active($site_id)) {
			return;
		}

		switch_to_blog($site_id);

		try {
			$site_url = get_site_url();

			if (empty($site_url) || ! is_string($site_url)) {
				return;
			}

			$scheme = wp_parse_url($site_url, PHP_URL_SCHEME);

			if (empty($scheme) || ! is_string($scheme)) {
				return;
			}

			/*
			 * Generate the obfuscated key that WooCommerce Subscriptions uses.
			 * It inserts '_[wc_subscriptions_siteurl]_' in the middle of the URL.
			 */
			$scheme_with_separator   = $scheme . '://';
			$site_url_without_scheme = str_replace($scheme_with_separator, '', $site_url);

			if (empty($site_url_without_scheme) || ! is_string($site_url_without_scheme)) {
				return;
			}

			$obfuscated_url = $scheme_with_separator . substr_replace(
				$site_url_without_scheme,
				'_[wc_subscriptions_siteurl]_',
				intval(strlen($site_url_without_scheme) / 2),
				0
			);

			update_option('wc_subscriptions_siteurl', $obfuscated_url);

			delete_option('wcs_ignore_duplicate_siteurl_notice');
		} finally {
			restore_current_blog();
		}
	}

	/**
	 * Checks if WooCommerce Subscriptions is active on a site.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id The ID of the site to check.
	 * @return bool True if WooCommerce Subscriptions is active, false otherwise.
	 */
	protected function is_woocommerce_subscriptions_active(int $site_id): bool {

		if (! function_exists('is_plugin_active_for_network')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if (is_plugin_active_for_network('woocommerce-subscriptions/woocommerce-subscriptions.php')) {
			return true;
		}

		switch_to_blog($site_id);

		$active_plugins = get_option('active_plugins', []);

		restore_current_blog();

		return in_array('woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins, true);
	}
}
