<?php
/**
 * Cache Manager Class
 *
 * Handles processes related to cache.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Cache_Manager
 * @since 2.1.2
 */

namespace WP_Ultimo\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to cache.
 *
 * @since 2.1.2
 */
class Cache_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Flush known caching plugins, offers hooks to add more plugins in the future
	 *
	 * @since 2.1.2
	 * @param array $types Optional. Array of cache types to flush. If empty, all caches will be flushed.
	 * @return void
	 */
	public function flush_known_caches(array $types = array()): void {

		/**
		 * Iterate through known caching plugins methods and flush them
		 * This is done by calling this class' methods ended in '_cache_flush'
		 *
		 * To support more caching plugins, just add a method to this class suffixed with '_cache_flush'
		 */
		foreach (get_class_methods($this) as $method) {
			if (str_ends_with($method, '_cache_flush')) {
				// Extract the cache type from the method name
				$cache_type = str_replace('_cache_flush', '', $method);

				// If types are specified and this type is not in the list, skip it
				if (!empty($types) && !in_array($cache_type, $types)) {
					continue;
				}

				$this->$method();
			}
		}

		/**
		 * Hook to additional cleaning
		 */
		do_action('wu_flush_known_caches', $types);
	}

	/**
	 * Flush WPEngine Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function wp_engine_cache_flush() {

		if (class_exists('\WpeCommon') && method_exists('\WpeCommon', 'purge_varnish_cache')) {
			\WpeCommon::purge_memcached(); // WPEngine Cache Flushing
			\WpeCommon::clear_maxcdn_cache(); // WPEngine Cache Flushing
			\WpeCommon::purge_varnish_cache(); // WPEngine Cache Flushing

		}
	}

	/**
	 * Flush WP Rocket Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function wp_rocket_cache_flush() {

		if (function_exists('rocket_clean_domain')) {
			\rocket_clean_domain();
		}
	}

	/**
	 * Flush WP Super Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function wp_super_cache_flush() {

		if (function_exists('wp_cache_clear_cache')) {
			\wp_cache_clear_cache(); // WP Super Cache Flush

		}
	}

	/**
	 * Flush WP Fastest Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function wp_fastest_cache_flush() {

		if (function_exists('wpfc_clear_all_cache')) {
			\wpfc_clear_all_cache(); // WP Fastest Cache Flushing

		}
	}

	/**
	 * Flush W3 Total Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function w3_total_cache_flush() {

		if (function_exists('w3tc_pgcache_flush')) {
			\w3tc_pgcache_flush(); // W3TC Cache Flushing

		}
	}

	/**
	 * Flush Hummingbird Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function hummingbird_cache_flush() {

		if (class_exists('\Hummingbird\WP_Hummingbird') && method_exists('\Hummingbird\WP_Hummingbird', 'flush_cache')) {
			\Hummingbird\WP_Hummingbird::flush_cache(); // Hummingbird Cache Flushing

		}
	}

	/**
	 * Flush WP Optimize Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function wp_optimize_cache_flush() {

		if (class_exists('\WP_Optimize') && method_exists('\WP_Optimize', 'get_page_cache')) {
			$wp_optimize = \WP_Optimize()->get_page_cache();

			if (method_exists($wp_optimize, 'purge')) {
				$wp_optimize->purge(); // WP Optimize Cache Flushing

			}
		}
	}

	/**
	 * Flush Comet Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function comet_cache_flush() {

		if (class_exists('\Comet_Cache') && method_exists('\Comet_Cache', 'clear')) {
			\Comet_Cache::clear(); // Comet Cache Flushing

		}
	}

	/**
	 * Flush LiteSpeed Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function litespeed_cache_flush() {

		if (class_exists('\LiteSpeed_Cache_API') && method_exists('\LiteSpeed_Cache_API', 'purge_all')) {
			\LiteSpeed_Cache_API::purge_all(); // LiteSpeed Cache Flushing

		}
	}

	/**
	 * Flush WordPress Object Cache
	 *
	 * @since 2.1.2
	 * @return void
	 */
	protected function wp_object_cache_flush() {

		// Flush WordPress object cache
		wp_cache_flush();

		// Also flush WP Ultimo specific caches
		$this->flush_wp_ultimo_caches();
	}

	/**
	 * Flush WP Ultimo specific caches
	 *
	 * @since 2.1.2
	 * @return void
	 */
	public function flush_wp_ultimo_caches() {

		// Clear financial data cache
		$this->clear_cache_group('wu_financial_data');

		// Clear filesystem cache
		$this->clear_cache_group('wu_filesystem');

		// Clear queries cache
		$this->clear_cache_group('wu_queries');

		// Clear items cache
		$this->clear_cache_group('wu_items');

		/**
		 * Allow plugins to clear additional WP Ultimo specific caches
		 *
		 * @since 2.1.2
		 */
		do_action('wu_flush_wp_ultimo_caches');
	}

	/**
	 * Clear a specific cache group
	 *
	 * @since 2.1.2
	 * @param string $group Cache group to clear
	 * @return void
	 */
	protected function clear_cache_group(string $group) {

		global $wp_object_cache;

		// Check if the object cache supports group deletion
		if (is_object($wp_object_cache) && method_exists($wp_object_cache, 'delete_group')) {
			$wp_object_cache->delete_group($group);
		}
	}

	/**
	 * Get available cache types that can be flushed
	 *
	 * @since 2.1.2
	 * @return array Array of available cache types
	 */
	public function get_available_cache_types(): array {

		$available_types = array();

		foreach (get_class_methods($this) as $method) {
			if (str_ends_with($method, '_cache_flush')) {
				$cache_type = str_replace('_cache_flush', '', $method);
				$available_types[] = $cache_type;
			}
		}

		/**
		 * Filter the available cache types
		 *
		 * @since 2.1.2
		 * @param array $available_types Array of available cache types
		 */
		return apply_filters('wu_available_cache_types', $available_types);
	}

	/**
	 * Check if a specific cache type is available
	 *
	 * @since 2.1.2
	 * @param string $type Cache type to check
	 * @return bool True if the cache type is available, false otherwise
	 */
	public function is_cache_type_available(string $type): bool {

		$method = $type . '_cache_flush';

		return method_exists($this, $method);
	}
}
