<?php
/**
 * Template Library main class.
 *
 * @package WP_Ultimo\Template_Library
 * @since 2.5.0
 */

namespace WP_Ultimo\Template_Library;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Template Library main class.
 *
 * @since 2.5.0
 */
final class Template_Library {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Template Repository instance.
	 *
	 * @since 2.5.0
	 * @var Template_Repository
	 */
	private Template_Repository $repository;

	/**
	 * Initializes the Template Library.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function init(): void {

		$this->repository = new Template_Repository();

		/**
		 * Fires when the Template Library is loaded.
		 *
		 * @since 2.5.0
		 */
		do_action('wu_template_library_loaded');
	}

	/**
	 * Gets the Template Repository instance.
	 *
	 * @since 2.5.0
	 * @return Template_Repository
	 */
	public function get_repository(): Template_Repository {

		return $this->repository;
	}

	/**
	 * Gets templates from the repository.
	 *
	 * @since 2.5.0
	 * @param bool $force_refresh Force refresh from API.
	 * @return array|\WP_Error
	 */
	public function get_templates(bool $force_refresh = false) {

		return $this->repository->get_templates($force_refresh);
	}

	/**
	 * Gets a single template by slug.
	 *
	 * @since 2.5.0
	 * @param string $slug Template slug.
	 * @return array|\WP_Error
	 */
	public function get_template(string $slug) {

		return $this->repository->get_template($slug);
	}

	/**
	 * Installs a template.
	 *
	 * @since 2.5.0
	 * @param string $slug    Template slug.
	 * @param array  $options Installation options.
	 * @return array|\WP_Error Installation result or error.
	 */
	public function install_template(string $slug, array $options = []) {

		$template = $this->get_template($slug);

		if (is_wp_error($template)) {
			return $template;
		}

		if (empty($template['download_url'])) {
			return new \WP_Error(
				'no_download_url',
				__('No download URL available for this template.', 'ultimate-multisite')
			);
		}

		// Merge template info into options
		$options = array_merge(
			[
				'slug'    => $template['slug'],
				'name'    => $template['name'],
				'version' => $template['template_version'],
			],
			$options
		);

		return $this->repository->get_installer()->install($template['download_url'], $options);
	}

	/**
	 * Checks if a template is installed.
	 *
	 * @since 2.5.0
	 * @param string $slug Template slug.
	 * @return bool
	 */
	public function is_template_installed(string $slug): bool {

		return $this->repository->get_installer()->is_installed($slug);
	}

	/**
	 * Clears the template cache.
	 *
	 * @since 2.5.0
	 * @return bool
	 */
	public function clear_cache(): bool {

		return $this->repository->clear_cache();
	}
}
