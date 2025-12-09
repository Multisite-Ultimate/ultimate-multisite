<?php
/**
 * Ultimate Multisite activation and deactivation hooks
 *
 * @package WP_Ultimo
 * @subpackage Hooks
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Ultimate Multisite activation and deactivation hooks
 *
 * @since 2.0.0
 */
class Hooks {

	/**
	 * Static-only class.
	 */
	private function __construct() {}

	/**
	 * Register the activation and deactivation hooks
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function init(): void {

		/**
	 * Runs on Ultimate Multisite activation
	 */
		register_activation_hook(WP_ULTIMO_PLUGIN_FILE, [self::class, 'on_activation']);

		/**
		 * Runs on Ultimate Multisite deactivation
		 */
		register_deactivation_hook(WP_ULTIMO_PLUGIN_FILE, [self::class, 'on_deactivation']);

		/**
		 * Runs the activation hook.
		 */
		add_action('plugins_loaded', [self::class, 'on_activation_do'], 1);
	}

	/**
	 *  Runs when Ultimate Multisite is activated
	 *
	 * @since 1.9.6 It now uses hook-based approach, it is up to each sub-class to attach their own routines.
	 * @since 1.2.0
	 */
	public static function on_activation(): void {

		wu_log_add(\WP_Ultimo::LOG_HANDLE, __('Activating Ultimate Multisite...', 'ultimate-multisite'));

		/*
		 * Set the activation flag
		 */
		update_network_option(null, 'wu_activation', 'yes');
	}

	/**
	 * Runs whenever the activation flag is set.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function on_activation_do(): void {

		if (wu_request('activate') && get_network_option(null, 'wu_activation') === 'yes') {

			// Removes the flag
			delete_network_option(null, 'wu_activation');

			/*
			 * Update the sunrise meta file.
			 */
			\WP_Ultimo\Sunrise::maybe_tap('activating');

			/**
			 * Let other parts of the plugin attach their routines for activation
			 *
			 * @since 1.9.6
			 * @return void
			 */
			do_action('wu_activation');
		}
	}

	/**
	 * Runs when Ultimate Multisite is deactivated
	 *
	 * @since 1.9.6 It now uses hook-based approach, it is up to each sub-class to attach their own routines.
	 * @since 1.2.0
	 */
	public static function on_deactivation(): void {

		wu_log_add(\WP_Ultimo::LOG_HANDLE, __('Deactivating Ultimate Multisite...', 'ultimate-multisite'));

		/*
		 * Update the sunrise meta file.
		 */
		\WP_Ultimo\Sunrise::maybe_tap('deactivating');

		/**
		 * Let other parts of the plugin attach their routines for deactivation
		 *
		 * @since 1.9.6
		 * @return void
		 */
		do_action('wu_deactivation');
	}
}
