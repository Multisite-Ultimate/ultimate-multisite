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

	static $sites = array();
	$blog_id      = get_current_blog_id();

	if ( ! isset($sites[ $blog_id ]) ) {
		$sites[ $blog_id ] = new \WP_Ultimo\Models\Site(get_blog_details($blog_id));
	}
	return $sites[ $blog_id ];
}

/**
 * Returns the site object
 *
 * @since 2.0.0
 *
 * @param int $id The id of the site.
 * @return \WP_Ultimo\Models\Site|false
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

/**
 * Generates a URL-safe slug from a site title.
 *
 * Takes a site title like "Your Cool Site" and converts it to "yourcoolsite"
 * for use as a subdomain or path.
 *
 * @since 2.0.0
 *
 * @param string $site_title The site title to convert.
 * @return string URL-safe slug.
 */
function wu_generate_site_url_from_title($site_title) {

	if (empty($site_title)) {
		return '';
	}

	// Convert to lowercase and remove HTML entities
	$slug = strtolower(html_entity_decode(trim((string) $site_title), ENT_QUOTES, 'UTF-8'));

	// Remove any remaining non-alphanumeric characters
	$slug = preg_replace('/[^a-z0-9-]/', '', $slug);

	// Fallback if empty after cleaning
	if (empty($slug)) {
		$slug = 'site' . wp_rand(1000, 9999);
	} elseif (is_numeric($slug[0]) || '-' === $slug[0]) {
		// Ensure it starts with a letter (WordPress requirement)
		$slug = 'site' . $slug;
	}
	return $slug;
}

/**
 * Generates a site title from an email address.
 *
 * Takes an email like "john.doe@example.com" and converts it to "John Doe Site"
 * or falls back to using the domain part if the username is generic.
 *
 * @since 2.0.0
 *
 * @param string $email The email address to use for generation.
 * @return string Generated site title.
 */
function wu_generate_site_title_from_email($email) {

	if (empty($email) || ! is_email($email)) {
		return '';
	}

	$email_parts = explode('@', $email);
	$username    = $email_parts[0];
	$domain      = $email_parts[1];

	// Common generic email prefixes to avoid
	$generic_prefixes = [
		'admin',
		'administrator',
		'info',
		'contact',
		'support',
		'help',
		'sales',
		'marketing',
		'hello',
		'hi',
		'mail',
		'email',
		'test',
		'demo',
		'sample',
		'example',
		'noreply',
		'no-reply',
	];

	$title_parts = [];

	// Check if username is not generic
	if (! in_array(strtolower($username), $generic_prefixes, true)) {
		// Split on common separators
		$name_parts = preg_split('/[._\-+]/', $username);

		foreach ($name_parts as $part) {
			$part = trim($part);
			if (! empty($part) && ! is_numeric($part)) {
				// Capitalize first letter of each part
				$title_parts[] = ucfirst(strtolower($part));
			}
		}
	}

	// If we don't have good name parts, use domain
	if (empty($title_parts)) {
		$domain_part   = strtok($domain, '.');
		$title_parts[] = ucfirst($domain_part);
	}

	// Create title
	$title = implode(' ', $title_parts);

	// Add "Site" suffix if title is short
	if (strlen($title) < 8) {
		$title .= ' Site';
	}

	return $title;
}

/**
 * Generates a unique site URL with collision detection.
 *
 * Takes a base URL slug and ensures it's unique by checking against existing sites.
 * Appends numbers if needed to avoid collisions.
 *
 * @since 2.0.0
 *
 * @param string $base_url The base URL slug to use.
 * @param string $domain The domain to check against (optional).
 * @return string Unique site URL.
 */
function wu_generate_unique_site_url($base_url, $domain = null) {

	if (empty($base_url)) {
		$base_url = 'site' . wp_rand(1000, 9999);
	}

	// Clean the base URL
	$base_url = wu_generate_site_url_from_title($base_url);

	$site_url = $base_url;
	$counter  = 0;

	// Keep checking until we find a unique URL
	while (true) {
		$d = wu_get_site_domain_and_path($site_url, $domain);

		if (! domain_exists($d->domain, $d->path)) {
			break;
		}

		++$counter;
		$site_url = $base_url . $counter;

		// Safety net to prevent infinite loops
		if ($counter > 9999) {
			$site_url = $base_url . wp_rand(10000, 99999);
			break;
		}
	}

	return $site_url;
}
