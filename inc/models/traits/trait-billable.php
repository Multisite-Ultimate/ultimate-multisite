<?php
/**
 * A trait to handle billable models.
 *
 * @package WP_Ultimo
 * @subpackage Models\Traits
 * @since 2.0.0
 */

namespace WP_Ultimo\Models\Traits;

use WP_Ultimo\Objects\Billing_Address;

defined('ABSPATH') || exit;

/**
 * Singleton trait.
 */
trait Billable {

	/**
	 * The billing address.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Objects\Billing_Address
	 */
	protected $billing_address;

	/**
	 * Returns the default billing address.
	 *
	 * Classes that implement this trait need to implement
	 * this method.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Billing_Address
	 */
	abstract public function get_default_billing_address();

	/**
	 * Gets the billing address for this object.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Billing_Address
	 */
	public function get_billing_address() {

		if (null === $this->billing_address) {
			$billing_address = $this->get_meta('wu_billing_address');

			$this->billing_address = is_a($billing_address, '\WP_Ultimo\Objects\Billing_Address') ? $billing_address : $this->get_default_billing_address();
		}

		return $this->billing_address;
	}

	/**
	 * Sets the billing address.
	 *
	 * @since 2.0.0
	 *
	 * @param array|\WP_Ultimo\Objects\Billing_Address $billing_address The billing address.
	 * @return void
	 */
	public function set_billing_address($billing_address): void {

		if (is_array($billing_address)) {
			$billing_address = new Billing_Address($billing_address);
		}

		$this->meta['wu_billing_address'] = $billing_address;

		$this->billing_address = $billing_address;
	}
}
