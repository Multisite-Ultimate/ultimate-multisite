<?php
/**
 * Installs recommended plugins during the setup wizard.
 *
 * @package WP_Ultimo
 * @subpackage Installers/Recommended_Plugins_Installer
 * @since 2.0.0
 */

namespace WP_Ultimo\Installers;

// Exit if accessed directly
use Psr\Log\LogLevel;

defined('ABSPATH') || exit;

/**
 * Handles installation of recommended plugins from WordPress.org.
 *
 * Uses the same wizard table + AJAX flow as other installers.
 *
 * @since 2.0.0
 */
class Recommended_Plugins_Installer extends Base_Installer {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Return the list of recommended plugin steps for the wizard table.

	 * @since 2.0.0
	 * @return array
	 */
	public function get_steps() {

		$steps = [];

		// Ensure we can detect installed plugins.
		if (! function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Recommended: User Switching (https://wordpress.org/plugins/user-switching/)
		$user_switching_slug                               = 'user-switching';
		$steps[ 'install_plugin_' . $user_switching_slug ] = [
			'done'        => $this->is_plugin_installed($user_switching_slug),
			'title'       => __('User Switching', 'ultimate-multisite'),
			'description' => __('Quickly switch between users for testing and support.', 'ultimate-multisite'),
			'pending'     => __('Pending', 'ultimate-multisite'),
			'installing'  => __('Installing User Switching...', 'ultimate-multisite'),
			'success'     => __('Installed!', 'ultimate-multisite'),
			'help'        => 'https://wordpress.org/plugins/user-switching/',
			'checked'     => true,
		];

		$steps[ 'activate_plugin_' . $user_switching_slug ] = [
			'done'        => $this->is_plugin_active($user_switching_slug),
			'title'       => __('Activate User Switching', 'ultimate-multisite'),
			'description' => __('Activate the User Switching plugin.', 'ultimate-multisite'),
			'pending'     => __('Pending', 'ultimate-multisite'),
			'installing'  => __('Activating User Switching...', 'ultimate-multisite'),
			'success'     => __('Activated!', 'ultimate-multisite'),
			'help'        => 'https://wordpress.org/plugins/user-switching/',
			'checked'     => true,
		];

		return $steps;
	}

	/**
	 * Handle AJAX for our plugin install steps. Matches slugs starting with
	 * `install_plugin_` and installs from WordPress.org by plugin slug.
	 * Also handles `activate_plugin_` steps to activate plugins.
	 *
	 * @since 2.0.0
	 *
	 * @param bool|\WP_Error $status    Current status passed through the filter chain.
	 * @param string         $installer The installer slug (e.g. `install_plugin_user-switching`).
	 * @param object         $wizard    Wizard page instance.
	 * @return void
	 */
	public function handle($status, $installer, $wizard) {

		if (str_starts_with($installer, 'install_plugin_')) {
			$plugin_slug = substr($installer, strlen('install_plugin_'));

			try {
				$result = $this->install_wporg_plugin($plugin_slug);

				if (is_wp_error($result)) {
					return;
				}
			} catch (\Throwable $e) {
				wu_log_add(\WP_Ultimo::LOG_HANDLE, $e->getMessage(), LogLevel::ERROR);
			}

			return;
		}

		if (str_starts_with($installer, 'activate_plugin_')) {
			$plugin_slug = substr($installer, strlen('activate_plugin_'));

			try {
				$result = $this->activate_plugin($plugin_slug);

				if (is_wp_error($result)) {
					return;
				}
			} catch (\Throwable $e) {
				wu_log_add(\WP_Ultimo::LOG_HANDLE, $e->getMessage(), LogLevel::ERROR);
			}

			return;
		}
	}

	/**
	 * Determine if a plugin is already installed by slug.
	 *
	 * @since 2.0.0
	 * @param string $plugin_slug Plugin slug (e.g. 'user-switching').
	 * @return bool
	 */
	protected function is_plugin_installed($plugin_slug) {
		$installed = get_plugins();

		foreach ($installed as $file => $data) {
			if (str_starts_with($file, $plugin_slug . '/')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a plugin is active by slug.
	 *
	 * @since 2.0.0
	 * @param string $plugin_slug Plugin slug (e.g. 'user-switching').
	 * @return bool
	 */
	protected function is_plugin_active($plugin_slug) {
		$plugin_file = $this->get_plugin_file($plugin_slug);

		if (! $plugin_file) {
			return false;
		}

		return is_plugin_active($plugin_file);
	}

	/**
	 * Get the plugin file path for a given slug.
	 *
	 * @since 2.0.0
	 * @param string $plugin_slug Plugin slug (e.g. 'user-switching').
	 * @return string|false Plugin file path or false if not found.
	 */
	protected function get_plugin_file($plugin_slug) {
		$installed = get_plugins();

		foreach ($installed as $file => $data) {
			if (str_starts_with($file, $plugin_slug . '/')) {
				return $file;
			}
		}

		return false;
	}

	/**
	 * Install a plugin from WordPress.org by slug.
	 *
	 * @since 2.0.0
	 * @param string $plugin_slug Plugin slug on wp.org.
	 * @return bool|\WP_Error
	 */
	protected function install_wporg_plugin($plugin_slug) {

		// If already installed, succeed early.
		if ($this->is_plugin_installed($plugin_slug)) {
			return true;
		}

		// Load required WordPress admin includes for installing plugins.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		// Query plugin info to get the download link.
		$api = plugins_api(
			'plugin_information',
			[
				'slug'   => $plugin_slug,
				'fields' => [
					'sections' => false,
				],
			]
		);

		if (is_wp_error($api)) {
			return $api;
		}

		$download_url = $api->download_link ?? '';

		if (! $download_url) {
			return new \WP_Error('no-download-link', __('Unable to resolve plugin download link.', 'ultimate-multisite'));
		}

		$skin     = new \Automatic_Upgrader_Skin([]);
		$upgrader = new \Plugin_Upgrader($skin);

		$results = $upgrader->install($download_url);

		if (is_wp_error($results)) {
			return $results;
		}

		$messages = $upgrader->skin->get_upgrade_messages();

		if (! in_array($upgrader->strings['process_success'], $messages, true)) {
			$error_message = array_pop($messages);
			return new \WP_Error('installation-failed', $error_message ?: __('Installation failed.', 'ultimate-multisite'));
		}

		return true;
	}

	/**
	 * Activate a plugin by slug.
	 *
	 * @since 2.0.0
	 * @param string $plugin_slug Plugin slug (e.g. 'user-switching').
	 * @return bool|\WP_Error
	 */
	protected function activate_plugin($plugin_slug) {

		// Get the plugin file path.
		$plugin_file = $this->get_plugin_file($plugin_slug);

		if (! $plugin_file) {
			return new \WP_Error('plugin-not-found', __('Plugin not found.', 'ultimate-multisite'));
		}

		// If already active, succeed early.
		if (is_plugin_active($plugin_file)) {
			return true;
		}

		// Activate the plugin.
		$result = activate_plugin($plugin_file);

		if (is_wp_error($result)) {
			return $result;
		}

		return true;
	}
}
