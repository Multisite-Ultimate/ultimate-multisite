<?php
/**
 * Interface for billable models.
 *
 * @package WP_Ultimo
 * @subpackage Models\Interfaces
 * @since 2.0.0
 */

namespace WP_Ultimo\Models\Interfaces;

defined('ABSPATH') || exit;

/**
 * Billable interface.
 */
interface Billable {

	/**
	 * Returns the default billing address.
	 *
	 * Classes that implement this trait need to implement
	 * this method.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Billing_Address
	 */
	public function get_default_billing_address();

	/**
	 * Gets the billing address for this object.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Billing_Address
	 */
	public function get_billing_address();

	/**
	 * Sets the billing address.
	 *
	 * @since 2.0.0
	 *
	 * @param array|\WP_Ultimo\Objects\Billing_Address $billing_address The billing address.
	 * @return void
	 */
	public function set_billing_address($billing_address): void;
}
