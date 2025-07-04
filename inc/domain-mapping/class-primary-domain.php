<?php
/**
 * Handles redirects to the primary domain of a site with mappings
 *
 * @package WP_Ultimo
 * @subpackage Domain_Mapping
 * @since 2.0.0
 */

namespace WP_Ultimo\Domain_Mapping;

use WP_Ultimo\Domain_Mapping;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles redirects to the primary domain of a site with mappings
 *
 * @since 2.0.0
 */
class Primary_Domain {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Adds the hooks
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_action('wu_domain_mapping_load', [$this, 'add_hooks'], -20);
	}

	/**
	 * Adds the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_hooks(): void {

		add_action('template_redirect', [$this, 'redirect_to_primary_domain']);

		add_action('admin_init', [$this, 'maybe_redirect_to_mapped_or_network_domain']);

		add_action('login_init', [$this, 'maybe_redirect_to_mapped_or_network_domain']);
	}

	/**
	 * Redirects the site to its primary mapped domain, if any.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function redirect_to_primary_domain(): void {

		$should_redirect = true;

		if (is_preview()) {
			$should_redirect = false;
		}

		if (is_customize_preview()) {
			$should_redirect = false;
		}

		/**
		 * Allow developers to short-circuit the redirection, preventing it
		 * from happening.
		 *
		 * @since 2.0.0
		 * @param $should_redirect If we should redirect or not.
		 *
		 * @return bool
		 */
		if (apply_filters('wu_should_redirect_to_primary_domain', $should_redirect) === false) {
			return;
		}

		if ( ! function_exists('wu_get_domains')) {
			return;
		}
		$current_host = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'] ?? ''));

		$domains = wu_get_domains(
			[
				'blog_id'        => get_current_blog_id(),
				'primary_domain' => 1,
				'active'         => 1,
				'domain__not_in' => [$current_host],
			]
		);

		if (empty($domains)) {
			return;
		}

		$primary_domain = $domains[0];

		if ($primary_domain->get_domain() !== $current_host && $primary_domain->is_active()) {
			$url = wu_get_current_url();

			$new_url = Domain_Mapping::get_instance()->replace_url($url, $primary_domain);

			wp_redirect(set_url_scheme($new_url));

			exit;
		}
	}

	/**
	 * Handles redirects to mapped ot network domain for the admin panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_redirect_to_mapped_or_network_domain(): void {

		if ('GET' !== (sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'] ?? ''))) || wp_doing_ajax()) {
			return;
		}

		/*
		 * The visitor is actively trying to logout. Let them do it!
		 */
		if (wu_request('action', 'nothing') === 'logout' || wu_request('loggedout')) {
			return;
		}

		$site = wu_get_current_site();

		$mapped_domain = $site->get_primary_mapped_domain();

		if ( ! $mapped_domain) {
			return;
		}

		$redirect_settings = wu_get_setting('force_admin_redirect', 'both');

		if ('both' === $redirect_settings) {
			return;
		}

		$current_url = wp_parse_url(wu_get_current_url());

		$mapped_url = wp_parse_url($mapped_domain->get_url());

		$current_url_to_compare = $current_url['host'];

		$mapped_url_to_compare = $mapped_url['host'];

		$query_args = [];

		if (wu_get_isset($current_url, 'query')) {
			wp_parse_str($current_url['query'], $query_args);
		}

		$redirect_url = false;

		if ('force_map' === $redirect_settings && $current_url_to_compare !== $mapped_url_to_compare) {
			$redirect_url = Domain_Mapping::get_instance()->replace_url(wu_get_current_url(), $mapped_domain);

			$query_args = array_map(fn($value) => Domain_Mapping::get_instance()->replace_url($value, $mapped_domain), $query_args);
		} elseif ('force_network' === $redirect_settings && $current_url_to_compare === $mapped_url_to_compare) {
			$redirect_url = wu_restore_original_url(wu_get_current_url(), $site->get_id());

			$query_args = array_map(fn($value) => wu_restore_original_url($value, $site->get_id()), $query_args);
		}

		if ($redirect_url) {
			wp_redirect(add_query_arg($query_args, $redirect_url));

			exit;
		}
	}
}
