<?php
/**
 * Template Repository.
 *
 * Manages template data with caching.
 *
 * @package WP_Ultimo\Template_Library
 * @since 2.5.0
 */

namespace WP_Ultimo\Template_Library;

use WP_Error;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Template Repository class.
 *
 * @since 2.5.0
 */
class Template_Repository {

	/**
	 * API Client instance.
	 *
	 * @since 2.5.0
	 * @var API_Client
	 */
	private API_Client $api_client;

	/**
	 * Template Installer instance.
	 *
	 * @since 2.5.0
	 * @var Template_Installer
	 */
	private Template_Installer $installer;

	/**
	 * Transient key for caching templates.
	 *
	 * @since 2.5.0
	 * @var string
	 */
	private const CACHE_KEY = 'wu-templates-list';

	/**
	 * Cache duration in seconds (2 days).
	 *
	 * @since 2.5.0
	 * @var int
	 */
	private const CACHE_DURATION = 2 * DAY_IN_SECONDS;

	/**
	 * In-memory cache of templates.
	 *
	 * @since 2.5.0
	 * @var array|null
	 */
	private ?array $templates = null;

	/**
	 * Constructor.
	 *
	 * @since 2.5.0
	 */
	public function __construct() {

		$this->api_client = new API_Client(MULTISITE_ULTIMATE_UPDATE_URL);
		$this->installer  = new Template_Installer();
	}

	/**
	 * Gets templates with caching.
	 *
	 * @since 2.5.0
	 * @param bool $force_refresh Force refresh from API.
	 * @return array|WP_Error Array of templates or WP_Error.
	 */
	public function get_templates(bool $force_refresh = false) {

		// Return in-memory cache if available
		if (! $force_refresh && null !== $this->templates) {
			return $this->templates;
		}

		// Try to get from transient cache
		if (! $force_refresh && ! wu_is_debug()) {
			$cached = get_site_transient(self::CACHE_KEY);
			if (false !== $cached) {
				$this->templates = $this->mark_installed_templates($cached);
				return $this->templates;
			}
		}

		// Fetch from API
		$templates = $this->api_client->get_templates();

		if (is_wp_error($templates)) {
			return $templates;
		}

		// Cache the result
		set_site_transient(self::CACHE_KEY, $templates, self::CACHE_DURATION);

		// Mark installed templates and store in memory
		$this->templates = $this->mark_installed_templates($templates);

		return $this->templates;
	}

	/**
	 * Marks templates that are already installed.
	 *
	 * @since 2.5.0
	 * @param array $templates Array of templates.
	 * @return array Templates with installed flag.
	 */
	private function mark_installed_templates(array $templates): array {

		$installed = $this->installer->get_installed_templates();

		return array_map(
			function ($template) use ($installed) {
				$template['installed'] = isset($installed[ $template['slug'] ]);

				// Add installed info if available
				if ($template['installed']) {
					$template['installed_info'] = $installed[ $template['slug'] ];
				}

				return $template;
			},
			$templates
		);
	}

	/**
	 * Gets a single template by slug.
	 *
	 * @since 2.5.0
	 * @param string $slug Template slug.
	 * @return array|WP_Error Template data or error.
	 */
	public function get_template(string $slug) {

		$templates = $this->get_templates();

		if (is_wp_error($templates)) {
			return $templates;
		}

		foreach ($templates as $template) {
			if ($template['slug'] === $slug) {
				return $template;
			}
		}

		return new WP_Error(
			'template_not_found',
			sprintf(
				/* translators: %s: template slug */
				__('Template "%s" not found.', 'ultimate-multisite'),
				$slug
			)
		);
	}

	/**
	 * Gets templates filtered by category.
	 *
	 * @since 2.5.0
	 * @param string $category Category slug.
	 * @return array|WP_Error Filtered templates or error.
	 */
	public function get_templates_by_category(string $category) {

		$templates = $this->get_templates();

		if (is_wp_error($templates)) {
			return $templates;
		}

		if ('all' === $category) {
			return $templates;
		}

		return array_filter(
			$templates,
			function ($template) use ($category) {
				foreach ($template['categories'] as $cat) {
					if ($cat['slug'] === $category) {
						return true;
					}
				}
				return false;
			}
		);
	}

	/**
	 * Searches templates by keyword.
	 *
	 * @since 2.5.0
	 * @param string $search Search keyword.
	 * @return array|WP_Error Matching templates or error.
	 */
	public function search_templates(string $search) {

		$templates = $this->get_templates();

		if (is_wp_error($templates)) {
			return $templates;
		}

		$search = strtolower($search);

		return array_filter(
			$templates,
			function ($template) use ($search) {
				// Search in name
				if (stripos($template['name'], $search) !== false) {
					return true;
				}

				// Search in description
				if (stripos($template['description'], $search) !== false) {
					return true;
				}

				// Search in slug
				if (stripos($template['slug'], $search) !== false) {
					return true;
				}

				// Search in industry type
				if (stripos($template['industry_type'], $search) !== false) {
					return true;
				}

				// Search in categories
				foreach ($template['categories'] as $cat) {
					if (stripos($cat['name'], $search) !== false) {
						return true;
					}
				}

				return false;
			}
		);
	}

	/**
	 * Gets all unique categories from templates.
	 *
	 * @since 2.5.0
	 * @return array|WP_Error Categories or error.
	 */
	public function get_categories() {

		$templates = $this->get_templates();

		if (is_wp_error($templates)) {
			return $templates;
		}

		$categories = [];

		foreach ($templates as $template) {
			foreach ($template['categories'] as $category) {
				$slug = $category['slug'];
				if (! isset($categories[ $slug ])) {
					$categories[ $slug ] = $category;
				}
			}
		}

		return array_values($categories);
	}

	/**
	 * Clears the template cache.
	 *
	 * @since 2.5.0
	 * @return bool
	 */
	public function clear_cache(): bool {

		$this->templates = null;
		return delete_site_transient(self::CACHE_KEY);
	}

	/**
	 * Gets the installer instance.
	 *
	 * @since 2.5.0
	 * @return Template_Installer
	 */
	public function get_installer(): Template_Installer {

		return $this->installer;
	}

	/**
	 * Gets the API client instance.
	 *
	 * @since 2.5.0
	 * @return API_Client
	 */
	public function get_api_client(): API_Client {

		return $this->api_client;
	}
}
