<?php
/**
 * A trait to be included in entities to WU_Site Class deprecated methods.
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

/**
 * WP_Ultimo_Site_Deprecated trait.
 */
trait WP_Ultimo_Site_Deprecated {

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
			case 'site_owner_id':
				$customer = $this->get_customer();
				$value    = $customer ? $customer->get_user_id() : false;
				break;
		}

		/**
		 * Let developers know that this is not going to be supported in the future.
		 *
		 * @since 2.0.0
		 */
		_doing_it_wrong(esc_html($key), esc_html__('Product keys should not be accessed directly', 'multisite-ultimate'), '2.0.0');

		return $value;
	}

	/**
	 * Deprecated: get_subscription.
	 *
	 * @deprecated 2.0.0
	 *
	 * @return \WP_Ultimo\Models\Membership
	 */
	public function get_subscription() {

		_deprecated_function(self::class, '2.0.0', '\WP_Ultimo\Models\Site::get_membership()');

		return $this->get_membership();
	}
}
