<?php
/**
 * Trait for database classes which are network-specific.
 */

namespace WP_Ultimo\Database\Engine;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Use by database classes to make them network-specific.
 *
 * @since 2.4.8
 */
trait Network_Prefix {
	/**
	 * Update the prefix property.
	 */
	private function update_prefix_with_network_id() {
		$current_network_id = get_current_network_id();
		if ($this->prefix && get_main_network_id() !== $current_network_id) {
			$this->prefix .= '_' . get_current_network_id();
		}
	}
}
