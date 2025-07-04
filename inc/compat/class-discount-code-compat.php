<?php
/**
 * Discount Code Compatibility Layer
 *
 * Handles discount code compatibility back-ports to Multisite Ultimate 1.X builds.
 *
 * @package WP_Ultimo
 * @subpackage Compat/Discount_Code_Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles discount code compatibility back-ports to Multisite Ultimate 1.X builds.
 *
 * @since 2.0.0
 */
class Discount_Code_Compat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_filter('update_post_metadata', [$this, 'check_update_coupon'], 10, 5);
	}

	/**
	 * Saves meta data from old plugins on the new plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param null   $null Short-circuit control.
	 * @param int    $object_id Object ID, in this case, of the Post object.
	 * @param string $meta_key The meta key being saved.
	 * @param mixed  $meta_value The meta value.
	 * @param mixed  $prev_value The previous value.
	 * @return null
	 */
	public function check_update_coupon($null, $object_id, $meta_key, $meta_value, $prev_value) {
		/*
		 * Check if we are in the main site of the network.
		 */
		if ( ! is_main_site()) {
			return;
		}

		/*
		 * Check if we have a new entity with this ID.
		 */
		$migrated_discount_code = wu_get_discount_code($object_id);

		if ( ! $migrated_discount_code) {
			return;
		}

		/*
		 * Prevent double prefixing.
		 */
		$meta_key = str_replace('wpu_', '', $meta_key);

		/*
		 * Save using the new meta table.
		 */
		$migrated_discount_code->update_meta('wpu_' . $meta_key, maybe_serialize($meta_value));

		/**
		 * Explicitly returns null so we don't forget that
		 * returning anything else will prevent meta data from being saved.
		 */
		return null;
	}
}
