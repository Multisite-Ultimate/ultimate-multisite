<?php
/**
 * Site Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the current site.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Site
 */
function wu_get_current_site() {

	return new \WP_Ultimo\Models\Site(get_blog_details());
}

/**
 * Returns the site object
 *
 * @since 2.0.0
 *
 * @param int $id The id of the site.
 * @return \WP_Ultimo\Models\Site
 */
function wu_get_site($id) {

	return \WP_Ultimo\Models\Site::get_by_id($id);
}

/**
 * Gets a site based on the hash.
 *
 * @since 2.0.0
 *
 * @param string $hash The hash for the payment.
 * @return \WP_Ultimo\Models\Site|false
 */
function wu_get_site_by_hash($hash) {

	return \WP_Ultimo\Models\Site::get_by_hash($hash);
}

/**
 * Queries sites.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Site[]
 */
function wu_get_sites($query = []) {

	if ( ! empty($query['search'])) {
		$domain_ids = wu_get_domains(
			[
				'number' => -1,
				'search' => '*' . $query['search'] . '*',
				'fields' => ['blog_id'],
			]
		);

		$domain_ids = array_column($domain_ids, 'blog_id');

		if ( ! empty($domain_ids)) {
			$query['blog_id__in'] = $domain_ids;

			unset($query['search']);
		}
	}

	return \WP_Ultimo\Models\Site::query($query);
}

/**
 * Returns the list of Site Templates.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return array
 */
function wu_get_site_templates($query = []) {

	$query = wp_parse_args(
		$query,
		[
			'number' => 9999, // By default, we try to get ALL available templates.
		]
	);

	return \WP_Ultimo\Models\Site::get_all_by_type('site_template', $query);
}

/**
 * Parses a URL and breaks it into different parts
 *
 * @since 2.0.0
 *
 * @param string $domain The domain to break up.
 * @return object
 */
function wu_handle_site_domain($domain) {

	global $current_site;

	if (! str_contains($domain, 'http')) {
		$domain = "https://{$domain}";
	}

	$parsed = wp_parse_url($domain);

	return (object) $parsed;
}

/**
 * Creates a new site.
 *
 * @since 2.0.0
 *
 * @param array $site_data Site data.
 * @return \WP_Error|\WP_Ultimo\Models\Site
 */
function wu_create_site($site_data) {

	$current_site = get_current_site();

	$site_data = wp_parse_args(
		$site_data,
		[
			'domain'                => $current_site->domain,
			'path'                  => '/',
			'title'                 => false,
			'type'                  => false,
			'template_id'           => false,
			'featured_image_id'     => 0,
			'duplication_arguments' => false,
			'public'                => true,
		]
	);

	$site = new \WP_Ultimo\Models\Site($site_data);

	$site->set_public($site_data['public']);

	$saved = $site->save();

	return is_wp_error($saved) ? $saved : $site;
}

/**
 * Returns the correct domain/path combination when creating a new site.
 *
 * @since 2.0.0
 *
 * @param string      $path_or_subdomain The site path.
 * @param string|bool $base_domain The domain selected.
 * @return object Object with a domain and path properties.
 */
function wu_get_site_domain_and_path($path_or_subdomain = '/', $base_domain = false) {

	global $current_site;

	$path_or_subdomain = trim($path_or_subdomain, '/');

	$domain = $base_domain ?: $current_site->domain;

	$d = new \stdClass();

	if (is_multisite() && is_subdomain_install()) {
		/*
		 * Treat for the www. case.
		 */
		$domain = str_replace('www.', '', (string) $domain);

		$d->domain = "{$path_or_subdomain}.{$domain}";

		$d->path = '/';

		return $d;
	}

	$d->domain = $domain;

	$d->path = "/{$path_or_subdomain}";

	/**
	 * Allow developers to manipulate the domain/path pairs.
	 *
	 * This can be useful for a number of things, such as implementing some
	 * sort of staging solution, different servers, etc.
	 *
	 * @since 2.0.0
	 * @param object $d The current object containing a domain and path keys.
	 * @param string $path_or_subdomain The original path/subdomain passed to the function.
	 * @return object An object containing a domain and path keys.
	 */
	return apply_filters('wu_get_site_domain_and_path', $d, $path_or_subdomain);
}
