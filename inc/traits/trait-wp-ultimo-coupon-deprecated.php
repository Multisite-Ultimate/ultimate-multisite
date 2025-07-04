<?php
/**
 * A trait to be included in entities to WU_Coupon Class depecrated methods.
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

/**
 * WP_Ultimo_Coupon_Deprecated trait.
 */
trait WP_Ultimo_Coupon_Deprecated {

	/**
	 * Generic set for old add-ons.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Meta key to save.
	 * @param mixed  $value The value to save as meta.
	 */
	public function __set($key, $value) {

		/**
		 * Let developers know that this is not going to be supported in the future.
		 *
		 * @since 2.0.0
		 */
		_doing_it_wrong(esc_html($key), esc_html__('Discount Code keys should not be set directly.', 'multisite-ultimate'), '2.0.0');

		$this->meta[ "wpu_{$key}" ] = $value;
	}

	/**
	 * Magic getter to provide backwards compatibility for plans.
	 *
	 * @since 2.0.0
	 *
	 * @throws \Exception Throws an exception when trying to get a key that is not available or back-compat.
	 * @param string $key Property to get.
	 * @return mixed
	 */
	public function __get($key) {

		$value = null;

		switch ($key) {
			default:
				$value = $this->get_meta('wpu_' . $key, false, true);
		}

		if (null === $value) {

			// translators: the placeholder is the key.
			$message = sprintf(__('Discount Codes do not have a %s parameter', 'multisite-ultimate'), $key);

			// throw new \Exception($message);

			return false;
		}

		/**
		 * Let developers know that this is not going to be supported in the future.
		 *
		 * @since 2.0.0
		 */
		_doing_it_wrong(esc_html($key), esc_html__('Discount Code keys should not be accessed directly', 'multisite-ultimate'), '2.0.0');

		return $value;
	}
}
