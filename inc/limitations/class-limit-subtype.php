<?php
/**
 * Subtypes Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Subtypes Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Subtype extends Limit {

	/**
	 * The check method is what gets called when allowed is called.
	 *
	 * Each module needs to implement a check method, that returns a boolean.
	 * This check can take any form the developer wants.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed  $value_to_check Value to check.
	 * @param mixed  $limit The list of limits in this modules.
	 * @param string $type Type for sub-checking.
	 * @return bool
	 */
	public function check($value_to_check, $limit, $type = '') {
		/*
		 * If no type is passed, bail.
		 */
		if ( ! $type) {
			return false;
		}

		/*
		 * Set default values for inexistent post types.
		 * By default they are enabled and unlimited.
		 */
		$post_type_limit = (object) wu_get_isset($limit, $type, $this->get_default_permissions($type));

		/*
		 * Case post type disabled.
		 */
		if ( ! $post_type_limit->enabled) {
			return false;
		}

		/*
		 * Case unlimited posts
		 */
		if (absint($post_type_limit->number) === 0) {
			return true;
		}

		return absint($value_to_check) < absint($post_type_limit->number);
	}

	/**
	 * Adds a magic getter for subtypes.
	 *
	 * @since 2.0.0
	 *
	 * @param string $sub_type The sub type.
	 * @return object
	 */
	public function __get($sub_type) {

		$type = (object) wu_get_isset($this->get_limit(), $sub_type, $this->get_default_permissions($sub_type));

		return (object) wp_parse_args($type, $this->get_default_permissions($sub_type));
	}

	/**
	 * Returns default permissions.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type Type for sub-checking.
	 * @return array
	 */
	public function get_default_permissions($type) {

		return [
			'enabled' => true,
			'number'  => '', // unlimited
		];
	}

	/**
	 * Checks if a theme exists on the current module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type.
	 * @return bool
	 */
	public function exists($type) {

		$results = wu_get_isset($this->get_limit(), $type, []);

		return wu_get_isset($results, 'number', 'not-set') !== 'not-set';
	}

	/**
	 * Handles limits on post submission.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_limit() {

		$received = wu_get_isset(wu_clean(wp_unslash($_POST['modules'][ $this->id ] ?? [])), 'limit', []); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		foreach ($received as $post_type => &$limitations) {
			$limitations['enabled'] = (bool) wu_get_isset(wu_clean(wp_unslash($_POST['modules'][ $this->id ]['limit'][ $post_type ] ?? [])), 'enabled', false); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		return $received;
	}
}
