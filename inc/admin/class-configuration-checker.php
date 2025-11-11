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

		add_action('network_admin_init', [$this, 'check_cookie_domain_configuration']);
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

		// Only check on subdomain installs
		if ( ! is_subdomain_install()) {
			return;
		}

		// Check if COOKIE_DOMAIN is defined and set to false
		if (defined('COOKIE_DOMAIN') && COOKIE_DOMAIN === false) {
			$message = sprintf(
				// translators: %1$s is the opening code tag, %2$s is the closing code tag, %3$s is a link to WordPress documentation
				__('Your <strong>wp-config.php</strong> has %1$sCOOKIE_DOMAIN%2$s set to %1$sfalse%2$s, which can cause authentication and session issues on subdomain multisite installations. Please remove this line from your wp-config.php file or set it to an appropriate value. %3$s', 'ultimate-multisite'),
				'<code>',
				'</code>',
				'<a href="https://developer.wordpress.org/apis/wp-config-php/#cookie-settings" target="_blank" rel="noopener noreferrer">' . __('Learn more about cookie settings', 'ultimate-multisite') . ' &rarr;</a>'
			);

			\WP_Ultimo()->notices->add(
				$message,
				'warning',
				'network-admin',
				'cookie_domain_false_warning'
			);
		}
	}
}
