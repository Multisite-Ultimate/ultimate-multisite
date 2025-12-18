<?php
/**
 * Settings Porter Helper
 *
 * Handles export and import of Ultimate Multisite settings.
 *
 * @package WP_Ultimo
 * @subpackage Helpers
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Settings Porter Helper class.
 *
 * @since 2.0.0
 */
class Settings_Porter {

	/**
	 * Export settings to JSON format.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array containing 'success' bool, 'data' string (JSON), and 'filename' string.
	 */
	public static function export_settings() {

		$settings = wu_get_all_settings();

		$export_data = [
			'version'    => '1.0.0',
			'plugin'     => 'ultimate-multisite',
			'timestamp'  => time(),
			'site_url'   => get_site_url(),
			'wp_version' => get_bloginfo('version'),
			'settings'   => $settings,
		];

		$filename = sprintf(
			'ultimate-multisite-settings-export-%s-%s.json',
			gmdate('Y-m-d-His'),
			get_current_site()->cookie_domain,
		);

		return [
			'success'  => true,
			'data'     => $export_data,
			'filename' => $filename,
		];
	}

	/**
	 * Validate an uploaded import file.
	 *
	 * @since 2.0.0
	 *
	 * @param array $file The $_FILES array element.
	 * @return \WP_Error|array WP_Error on failure, validated data array on success.
	 */
	public static function validate_import_file($file) {

		// Check for upload errors
		if (UPLOAD_ERR_OK !== $file['error']) {
			return new \WP_Error(
				'upload_error',
				__('File upload failed. Please try again.', 'ultimate-multisite')
			);
		}

		// Check file extension
		$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		if ('json' !== $file_ext) {
			return new \WP_Error(
				'invalid_file_type',
				__('Invalid file type. Please upload a JSON file.', 'ultimate-multisite')
			);
		}

		// Check file size (max 5MB)
		$max_size = 5 * 1024 * 1024;

		if ($file['size'] > $max_size) {
			return new \WP_Error(
				'file_too_large',
				__('File is too large. Maximum size is 5MB.', 'ultimate-multisite')
			);
		}

		// Read and decode JSON
		$json_content = file_get_contents($file['tmp_name']); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if (false === $json_content) {
			return new \WP_Error(
				'read_error',
				__('Failed to read the uploaded file.', 'ultimate-multisite')
			);
		}

		$data = json_decode($json_content, true);

		if (null === $data) {
			return new \WP_Error(
				'invalid_json',
				__('Invalid JSON format. Please upload a valid export file.', 'ultimate-multisite')
			);
		}

		// Validate structure
		if (! isset($data['plugin']) || 'ultimate-multisite' !== $data['plugin']) {
			return new \WP_Error(
				'invalid_format',
				__('This file does not appear to be a valid Ultimate Multisite settings export.', 'ultimate-multisite')
			);
		}

		if (! isset($data['settings']) || ! is_array($data['settings'])) {
			return new \WP_Error(
				'invalid_structure',
				__('The settings data in this file is invalid or missing.', 'ultimate-multisite')
			);
		}

		return $data;
	}

	/**
	 * Import settings from validated data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data The validated import data.
	 * @return void
	 */
	public static function import_settings($data) {

		$settings = $data['settings'];

		// Sanitize settings before import

		$sanitized_settings = array_map(
			function ($value) {
				return wu_clean($value);
			},
			$settings
		);

		// Use the Settings class save_settings method for proper validation
		WP_Ultimo()->settings->save_settings($sanitized_settings);

		do_action('wu_settings_imported', $sanitized_settings, $data);
	}

	/**
	 * Send JSON a file as download to the browser.
	 *
	 * @since 2.0.0
	 *
	 * @param string $json The JSON string.
	 * @param string $filename The filename.
	 * @return void
	 */
	public static function send_download($json, $filename) {

		nocache_headers();

		header('Content-Disposition: attachment; filename=' . $filename);
		header('Content-Length: ' . strlen($json));
		header('Pragma: no-cache');
		header('Expires: 0');
		wp_send_json($json, null, JSON_PRETTY_PRINT);

		exit;
	}
}
