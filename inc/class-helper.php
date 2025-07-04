<?php
/**
 * Multisite Ultimate helper methods for including and rendering files, assets, etc
 *
 * @package WP_Ultimo
 * @subpackage Helper
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate helper methods for including and rendering files, assets, etc
 *
 * @since 2.0.0
 */
class Helper {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Returns the full path to the plugin folder
	 *
	 * @since 0.0.1
	 * @param string $dir Path relative to the plugin root you want to access.
	 * @return string
	 */
	public function path($dir) {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_path');

		return wu_path($dir);
	}

	/**
	 * Returns the URL to the plugin folder.
	 *
	 * @since 0.0.1
	 * @param string $dir Path relative to the plugin root you want to access.
	 * @return string
	 */
	public function url($dir) {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_url');

		return wu_url($dir);
	}

	/**
	 * Shorthand for url('assets/img'). Returns the URL for assets inside the assets folder.
	 *
	 * @since 0.0.1
	 * @param string $asset Asset file name with the extension.
	 * @param string $assets_dir Assets sub-directory. Defaults to 'img'.
	 * @param string $base_dir   Base dir. Defaults to 'assets'.
	 * @return string
	 */
	public function get_asset($asset, $assets_dir = 'img', $base_dir = 'assets') {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_get_asset');

		return wu_get_asset($asset, $assets_dir, $base_dir);
	}

	/**
	 * Renders a view file from the view folder.
	 *
	 * @since 0.0.1
	 * @param string       $view View file to render. Do not include the .php extension.
	 * @param boolean      $vars Key => Value pairs to be made available as local variables inside the view scope.
	 * @param string|false $default_view View to be used if the view passed is not found. Used as fallback.
	 * @return void
	 */
	public function render($view, $vars = false, $default_view = false): void {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_get_template');

		wu_get_template($view, $vars, $default_view);
	}

	/**
	 * This function return 'slugfied' options terms to be used as options ids.
	 *
	 * @since 0.0.1
	 * @param string $term Returns a string based on the term and this plugin slug.
	 * @return string
	 */
	public function slugfy($term) {

		// even the name is wrong, wtf!

		_deprecated_function(__METHOD__, '2.0.11', 'wu_slugify');

		return wu_slugify($term);
	}

	/**
	 * Get the value of a slugified network option
	 *
	 * @since 1.9.6
	 * @param string $option_name Option name.
	 * @param mixed  $default_value The default value.
	 * @return mixed
	 */
	public function get_option($option_name = 'settings', $default_value = []) {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_get_option');

		return wu_get_option($option_name, $default_value);
	}

	/**
	 * Save slugified network option.
	 *
	 * @since 1.9.6
	 * @param string $option_name The option name to save.
	 * @param mixed  $value       The new value of the option.
	 * @return boolean
	 */
	public function save_option($option_name = 'settings', $value = false) {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_save_option');

		return wu_save_option($option_name, $value);
	}

	/**
	 * Delete slugified network option
	 *
	 * @since 1.9.6
	 * @param string $option_name The option name to delete.
	 * @return boolean
	 */
	public function delete_option($option_name) {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_delete_option');

		return wu_delete_option($option_name);
	}

	/**
	 * Gets the URL for the folders created with maybe_create_folder().
	 *
	 * @see $this->maybe_create_folder()
	 * @since 2.0.0
	 *
	 * @param string $folder The name of the folder.
	 * @return string
	 */
	public function get_folder_url($folder) {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_get_folder_url');

		return wu_get_folder_url($folder);
	}

	/**
	 * Creates a Multisite Ultimate folder inside the uploads folder. Returns the path to the folder.
	 *
	 * @since 2.0.0
	 *
	 * @deprecated
	 * @param string $folder Name of the folder.
	 * @return string
	 */
	public function maybe_create_folder($folder) {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_maybe_create_folder');

		return wu_maybe_create_folder($folder);
	}

	/**
	 * Drop our custom tables.
	 *
	 * @since 2.0.0
	 * @throws \Exception In case of failures, an exception is thrown.
	 *
	 * @return void
	 */
	public function drop_tables(): void {

		_deprecated_function(__METHOD__, '2.0.11', 'wu_drop_tables');

		wu_drop_tables();
	}
}
