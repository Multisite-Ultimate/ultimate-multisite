<?php
/**
 * Configuration Checker for WordPress Multisite setup issues.
 *
 * @package WP_Ultimo
 * @subpackage Admin
 * @since 2.4.7
 */

namespace WP_Ultimo\Admin;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Checks for common configuration issues that can affect multisite installations.
 *
 * @since 2.4.7
 */
class Configuration_Checker {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Initialize the class and register hooks.
	 *
	 * @since 2.4.7
	 * @return void
	 */
	public function init(): void {

		add_action('admin_init', [$this, 'check_cookie_domain_configuration']);
	}

	/**
	 * Checks for COOKIE_DOMAIN configuration issues on subdomain multisite installs.
	 *
	 * When COOKIE_DOMAIN is defined as false on a subdomain multisite installation,
	 * it can cause authentication and session issues across subdomains.
	 *
	 * @since 2.4.7
	 * @return void
	 */
	public function check_cookie_domain_configuration(): void {
		if (! is_network_admin()) {
			return;
		}
		// Only check on subdomain installs
		if ( ! is_subdomain_install()) {
			return;
		}

		// Check if COOKIE_DOMAIN is defined and set to false
		if (defined('COOKIE_DOMAIN') && COOKIE_DOMAIN === false) {
			$message = sprintf(
				// translators: %1$s is 'wp-config.php', %2$s is 'COOKIE_DOMAIN', %3$s is 'false'
				esc_html__('Your %1$s has %2$s set to %3$s, which can cause authentication and session issues on subdomain multisite installations. Please remove this line from your wp-config.php file or set it to an appropriate value.', 'ultimate-multisite'),
				'<strong>wp-config.php</strong>',
				'<strong>COOKIE_DOMAIN</strong>',
				'<strong>false</strong>',
			);
			$message .= '<br><a href="https://developer.wordpress.org/apis/wp-config-php/#set-cookie-domain" target="_blank" rel="noopener noreferrer">' . esc_html__('Learn more about cookie settings', 'ultimate-multisite') . ' &rarr;</a>';

			\WP_Ultimo()->notices->add(
				$message,
				'warning',
				'network-admin',
				'cookie_domain_false_warning'
			);
		}
	}
}
