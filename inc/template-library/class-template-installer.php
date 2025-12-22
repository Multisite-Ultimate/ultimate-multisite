<?php
/**
 * Template Installer.
 *
 * Handles downloading and importing template sites.
 *
 * @package WP_Ultimo\Template_Library
 * @since 2.5.0
 */

namespace WP_Ultimo\Template_Library;

use WP_Error;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Template Installer class.
 *
 * @since 2.5.0
 */
class Template_Installer {

	/**
	 * Downloads a template ZIP from the remote server and imports it.
	 *
	 * @since 2.5.0
	 * @param string $download_url The URL to download the template from.
	 * @param array  $options      Installation options.
	 * @return array|WP_Error Installation result or error.
	 */
	public function install(string $download_url, array $options = []) {

		// Validate download URL is from trusted source
		if (strpos($download_url, MULTISITE_ULTIMATE_UPDATE_URL) !== 0) {
			return new WP_Error(
				'insecure_url',
				__('Download URL is not from a trusted source.', 'ultimate-multisite')
			);
		}

		// Add authentication headers for download
		add_filter('http_request_args', [$this, 'add_auth_headers'], 10, 2);

		// Download the ZIP file
		$tmp_file = download_url($download_url, 300);

		remove_filter('http_request_args', [$this, 'add_auth_headers']);

		if (is_wp_error($tmp_file)) {
			return $tmp_file;
		}

		// Validate ZIP file
		$mime_type = mime_content_type($tmp_file);
		if (! in_array($mime_type, ['application/zip', 'application/x-gzip'], true)) {
			wp_delete_file($tmp_file);
			return new WP_Error(
				'invalid_file',
				__('Downloaded file is not a valid ZIP archive.', 'ultimate-multisite')
			);
		}

		// Import as template site
		$result = $this->import_template($tmp_file, $options);

		// Clean up temp file
		wp_delete_file($tmp_file);

		return $result;
	}

	/**
	 * Adds authentication headers to the download request.
	 *
	 * @since 2.5.0
	 * @param array  $args HTTP request arguments.
	 * @param string $url  Request URL.
	 * @return array Modified arguments.
	 */
	public function add_auth_headers(array $args, string $url): array {

		if (strpos($url, 'ultimatemultisite.com') !== false) {
			$addon_repo   = \WP_Ultimo::get_instance()->get_addon_repository();
			$access_token = $addon_repo->get_access_token();

			if ($access_token) {
				$args['headers']['Authorization'] = 'Bearer ' . $access_token;
			}
		}

		return $args;
	}

	/**
	 * Imports a template ZIP file as a new template site.
	 *
	 * @since 2.5.0
	 * @param string $zip_path Path to the ZIP file.
	 * @param array  $options  Import options.
	 * @return array|WP_Error Import result or error.
	 */
	private function import_template(string $zip_path, array $options) {

		$defaults = [
			'slug'        => 'template',
			'name'        => __('Imported Template', 'ultimate-multisite'),
			'version'     => '1.0.0',
			'delete_zip'  => true,
			'as_template' => true,
		];

		$args = wp_parse_args($options, $defaults);

		// Generate a URL for the new template site
		$new_url = $this->generate_template_url($args['slug']);

		// Use the core importer
		$import_result = wu_exporter_import(
			$zip_path,
			[
				'new_url'    => $new_url,
				'delete_zip' => $args['delete_zip'],
			],
			false // Sync import for now
		);

		if (is_wp_error($import_result)) {
			return $import_result;
		}

		// Get the created site
		$site = wu_exporter_url_to_site($new_url);

		if (! $site) {
			return new WP_Error(
				'site_not_found',
				__('Could not find the imported template site.', 'ultimate-multisite')
			);
		}

		$site_id = $site->blog_id;

		// Mark site as template
		$wu_site = wu_get_site($site_id);
		if ($wu_site) {
			$wu_site->set_type('site_template');
			$wu_site->save();
		}

		// Record the installation
		$this->record_installation(
			$args['slug'],
			$args['version'],
			$site_id,
			$new_url
		);

		return [
			'success'  => true,
			'site_id'  => $site_id,
			'site_url' => $new_url,
			'message'  => __('Template installed successfully!', 'ultimate-multisite'),
		];
	}

	/**
	 * Generates a unique URL for a template site.
	 *
	 * @since 2.5.0
	 * @param string $slug The template slug.
	 * @return string The generated URL.
	 */
	private function generate_template_url(string $slug): string {

		$network_url = network_home_url();
		$path        = sanitize_title('template-' . $slug . '-' . time());

		return trailingslashit($network_url) . $path . '/';
	}

	/**
	 * Records a template installation.
	 *
	 * @since 2.5.0
	 * @param string $template_slug Template slug.
	 * @param string $version       Template version.
	 * @param int    $site_id       Site ID.
	 * @param string $site_url      Site URL.
	 * @return bool
	 */
	public function record_installation(string $template_slug, string $version, int $site_id, string $site_url): bool {

		$installed_templates = $this->get_installed_templates();

		$installed_templates[ $template_slug ] = [
			'version'      => $version,
			'installed_at' => current_time('mysql'),
			'site_id'      => $site_id,
			'site_url'     => $site_url,
		];

		return wu_save_option('installed_templates', $installed_templates);
	}

	/**
	 * Gets list of installed templates.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	public function get_installed_templates(): array {

		return wu_get_option('installed_templates', []);
	}

	/**
	 * Checks if a template is installed.
	 *
	 * @since 2.5.0
	 * @param string $template_slug Template slug.
	 * @return bool
	 */
	public function is_installed(string $template_slug): bool {

		$installed = $this->get_installed_templates();

		return isset($installed[ $template_slug ]);
	}

	/**
	 * Gets installed template info.
	 *
	 * @since 2.5.0
	 * @param string $template_slug Template slug.
	 * @return array|null
	 */
	public function get_installed_template(string $template_slug): ?array {

		$installed = $this->get_installed_templates();

		return $installed[ $template_slug ] ?? null;
	}
}
