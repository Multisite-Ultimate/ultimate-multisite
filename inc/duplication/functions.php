<?php
defined('ABSPATH') || exit;

if ( ! class_exists('MUCD_Functions') ) {

	/**
	 * Multisite Ultimate Clone Duplicator Functions class.
	 *
	 * Provides utility functions for site duplication operations,
	 * including path validation and file system helpers.
	 */
	class MUCD_Functions {

		/**
		 * Check if a path is valid MS-windows path
		 *
		 * @since 0.2.0
		 * @param  string $path the path.
		 * @return boolean true | false
		 */
		public static function valid_windows_dir_path($path) {
			if (strpos($path, ':') == 1 && preg_match('/[a-zA-Z]/', $path[0])) {
						$tmp  = substr($path, 2);
						$bool = preg_match('/^[^*?"<>|:]*$/', $tmp);
						return (1 == $bool); // so that it will return only true and false
			}

					return false;
		}

		/**
		 * Check if a path is valid UNIX path
		 *
		 * @since 0.2.0
		 * @param  string $path The path to validate.
		 * @return bool   True if valid, false otherwise.
		 */
		public static function valid_unix_dir_path($path) {
			$reg  = '/^(\/([a-zA-Z0-9+$_.-])+)*\/?$/';
			$bool = preg_match($reg, $path);
			return (1 == $bool);
		}

		/**
		 * Check if a path is valid MS-windows or UNIX path
		 *
		 * @since 0.2.0
		 * @param  string $path The path to validate.
		 * @return bool   True if valid, false otherwise.
		 */
		public static function valid_path($path) {
			return (self::valid_unix_dir_path($path) || self::valid_windows_dir_path($path));
		}

		/**
		 * Removes completely a blog from the network
		 *
		 * @since 0.2.0
		 * @param  int $blog_id the blog id.
		 */
		public static function remove_blog($blog_id): void {
			switch_to_blog($blog_id);
			$wp_upload_info = wp_upload_dir();
			$dir            = str_replace(' ', '\\ ', trailingslashit($wp_upload_info['basedir']));
			restore_current_blog();

			wpmu_delete_blog($blog_id, true);

			// wpmu_delete_blog leaves an empty site upload directory, that we want to remove :
			MUCD_Files::rrmdir($dir);
		}

		/**
		 * Check if site is duplicable
		 *
		 * @since 0.2.0
		 * @param  int $blog_id the blog id.
		 * @return boolean true | false
		 */
		public static function is_duplicable($blog_id): bool {
			if ( get_site_option('mucd_duplicables', 'all') === 'all') {
				return true;
			}

			return get_blog_option($blog_id, 'mucd_duplicable', 'no') === 'yes';
		}

		/**
		 * Get all duplicable sites
		 *
		 * @since 0.2.0
		 * @return array of blog data
		 */
		public static function get_site_list() {
			$site_list     = [];
			$network_blogs = self::get_sites(apply_filters('mucd_get_site_list_args', []));
			foreach ( $network_blogs as $blog ) {
				if (self::is_duplicable($blog['blog_id']) && MUCD_SITE_DUPLICATION_EXCLUDE != $blog['blog_id']) {
					$site_list[] = $blog;
				}
			}

			return $site_list;
		}

		/**
		 * Check if a value is in an array for a specific key
		 *
		 * @since 0.2.0
		 * @param  mixed  $value the value.
		 * @param  array  $array the array.
		 * @param  string $key  the key.
		 * @return boolean true | false
		 */
		public static function value_in_array($value, $array, $key): bool {
			foreach ($array as $row) {
				if (isset($row[ $key ]) && $row[ $key ] == $value) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Get upload directory of the entire network
		 *
		 * @since 0.2.0
		 * @return string path of the upload directory
		 */
		public static function get_primary_upload_dir() {
			$current_blog = get_current_blog_id();
			switch_to_blog(MUCD_PRIMARY_SITE_ID);
			$wp_upload_info = wp_upload_dir();
			switch_to_blog($current_blog);

			return $wp_upload_info['basedir'];
		}

		/**
		 * Check if site exists
		 *
		 * @since 1.3.0
		 * @param  int $blog_id The blog ID to check.
		 * @return bool True if site exists, false otherwise.
		 */
		public static function site_exists($blog_id) {
			return (get_blog_details($blog_id) !== false);
		}

		/**
		 * Set locale to en_US
		 *
		 * @since 1.3.1
		 */
		public static function set_locale_to_en_US(): void {

			// Bugfix Pierre Dargham : relocating this declaration outside of the call to add_filter
			// PHP < 5.3 does not accept anonymous functions
			/**
			 * Set locale to en_US.
			 *
			 * @since 1.3.1
			 * @param string $locale Current locale.
			 * @return string en_US locale.
			 */
			function mucd_locale_en_us($locale): string {
				return 'en_US';
			}

			add_filter('locale', 'mucd_locale_en_us');
		}

		/**
		 * Get network data for a given id.
		 *
		 * @author wp-cli
		 * @see https://github.com/wp-cli/wp-cli/blob/master/php/commands/site.php
		 *
		 * @param int $network_id The network ID to retrieve data for.
		 * @return bool|array False if no network found with given id, array otherwise.
		 */
		public static function get_network($network_id) {
			global $wpdb;

			// Load network data
			$networks = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT * FROM $wpdb->site WHERE id = %d",
					$network_id
				)
			);

			if ( ! empty($networks) ) {
				// Only care about domain and path which are set here
				return $networks[0];
			}

			return false;
		}

		/**
		 * Get sites using WordPress core functions with fallback for older versions.
		 *
		 * @since 1.0.0
		 * @param array $args Arguments for retrieving sites.
		 * @return array List of sites.
		 */
		public static function get_sites($args = []) {
			if (version_compare(get_bloginfo('version'), '4.6', '>=')) {
				$defaults = ['number' => MUCD_MAX_NUMBER_OF_SITE];
				$args     = wp_parse_args($args, $defaults);
				$args     = apply_filters('mucd_get_sites_args', $args);
				$sites    = get_sites($args);
				foreach ($sites as $key => $site) {
					$sites[ $key ] = (array) $site;
				}

				return $sites;
			} else {
				$defaults = ['limit' => MUCD_MAX_NUMBER_OF_SITE];
				$args     = apply_filters('mucd_get_sites_args', $args);
				$args     = wp_parse_args($args, $defaults);
				return get_sites($args);
			}
		}

		/**
		 * Deactivate the plugin if we are not on a multisite installation
		 *
		 * @since 0.2.0
		 */
		public static function check_if_multisite(): void {
			if ( ! function_exists('is_multisite') || ! is_multisite()) {
				deactivate_plugins(plugin_basename(__FILE__));
				wp_die('multisite-clone-duplicator works only for multisite installation');
			}
		}

		/**
		 * Deactivate the plugin if we are not on the network admin
		 *
		 * @since 1.4.0
		 */
		public static function check_if_network_admin(): void {
			if ( ! is_network_admin() ) {
				deactivate_plugins(plugin_basename(__FILE__));
				wp_die('multisite-clone-duplicator works only as multisite network-wide plugin');
			}
		}
	}
}
