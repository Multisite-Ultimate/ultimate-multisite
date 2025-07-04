<?php
/**
 * A trait to be included in entities to WU_Subscription Class deprecated methods.
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

/**
 * WP_Ultimo_Subscription_Deprecated trait.
 */
trait WP_Ultimo_Subscription_Deprecated {

	/**
	 * Magic getter to provide backwards compatibility for subs.
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
			case 'plan_id':
				$value = $this->get_plan_id();
				break;
		}

		/**
		 * Let developers know that this is not going to be supported in the future.
		 *
		 * @since 2.0.0
		 */
		_doing_it_wrong(esc_html($key), esc_html__('Membership keys should not be accessed directly', 'multisite-ultimate'), '2.0.0');

		return $value;
	}
}
