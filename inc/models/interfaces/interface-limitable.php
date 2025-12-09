<?php
/**
 * Limitable interface.
 *
 * @package WP_Ultimo
 * @subpackage Models
 */

namespace WP_Ultimo\Models\Interfaces;

defined('ABSPATH') || exit;

interface Limitable {
	/**
	 * List of limitations that need to be merged.
	 *
	 * Every model that is limitable (imports this trait)
	 * needs to declare explicitly the limitations that need to be
	 * merged. This allows us to chain the merges, and gives us
	 * a final list of limitations at the end of the process.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function limitations_to_merge();

	/**
	 * Returns the limitations of this particular blog.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $waterfall If we should construct the limitations object recursively.
	 * @param bool $skip_self If we should skip the current limitations.
	 * @return \WP_Ultimo\Objects\Limitations
	 */
	public function get_limitations($waterfall = true, $skip_self = false);


	/**
	 * Checks if this site has limitations or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_limitations();

	/**
	 * Checks if a particular module is being limited.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module Module to check.
	 * @return boolean
	 */
	public function has_module_limitation($module);

	/**
	 * Returns all user role quotas.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_user_role_quotas();

	/**
	 * Proxy method to retrieve the allowed user roles.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_allowed_user_roles();

	/**
	 * Schedules plugins to be activated or deactivated based on the current limitations;
	 *
	 * @since 2.0.5
	 * @return void
	 */
	public function sync_plugins(): void;

	/**
	 * Makes sure we save limitations when we are supposed to.
	 *
	 * This is called on the handle_save method of the inc/admin-pages/class-edit-admin-page.php
	 * for all models that have the trait Limitable.
	 *
	 * @see inc/admin-pages/class-edit-admin-page.php
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_limitations(): void;

	/**
	 * Returns the list of product slugs associated with this model.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_applicable_product_slugs();
}
