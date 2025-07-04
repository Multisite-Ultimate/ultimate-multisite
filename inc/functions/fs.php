<?php
/**
 * File System Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the main site uploads dir array from WordPress.
 *
 * @since 2.0.11
 * @return array
 */
function wu_get_main_site_upload_dir() {

	global $current_site;

	is_multisite() && switch_to_blog($current_site->blog_id);

	if ( ! defined('WP_CONTENT_URL')) {
		define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	}

	$uploads = wp_upload_dir(null, false);

	is_multisite() && restore_current_blog();

	return $uploads;
}

/**
 * Creates a Multisite Ultimate folder inside the uploads folder - if needed - and return its path.
 *
 * @since 2.0.11
 *
 * @param string $folder Name of the folder.
 * @param string ...$path Additional path segments to be attached to the folder path.
 * @return string The path to the folder
 */
function wu_maybe_create_folder($folder, ...$path) {

	$uploads = wu_get_main_site_upload_dir();

	$folder_path = trailingslashit($uploads['basedir'] . '/' . $folder);

	/*
	 * Checks if the folder exists.
	 */
	if ( ! file_exists($folder_path)) {

		// Creates the Folder
		wp_mkdir_p($folder_path);

		// Creates htaccess
		$htaccess = $folder_path . '.htaccess';

		if ( ! file_exists($htaccess)) {
			$fp = @fopen($htaccess, 'w'); // phpcs:ignore

			@fwrite($fp, 'deny from all'); // phpcs:ignore

			@fclose($fp); // phpcs:ignore
		}

		// Creates index
		$index = $folder_path . 'index.html';

		if ( ! file_exists($index)) {
			$fp = @fopen($index, 'w'); // phpcs:ignore

			@fwrite($fp, ''); // phpcs:ignore

			@fclose($fp); // phpcs:ignore
		}
	}

	return $folder_path . implode('/', $path);
}

/**
 * Gets the URL for the folders created with maybe_create_folder().
 *
 * @see wu_maybe_create_folder()
 * @since 2.0.0
 *
 * @param string $folder The name of the folder.
 * @return string
 */
function wu_get_folder_url($folder) {

	$uploads = wu_get_main_site_upload_dir();

	$folder_url = trailingslashit($uploads['baseurl'] . '/' . $folder);

	return set_url_scheme($folder_url);
}
