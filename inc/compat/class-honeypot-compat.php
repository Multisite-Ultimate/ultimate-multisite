<?php
/**
 * Honeypot/ WP Armor Compatibility
 *
 * Fixes login errors with honeypot plugin.
 *
 * @package WP_Ultimo
 * @subpackage Compat
 * @since 2.4.3
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
use WP_Ultimo\Checkout\Checkout_Pages;

defined('ABSPATH') || exit;

/**
 * Honeypot/WP Armor compatibility class.
 *
 * Provides compatibility fixes for Honeypot and WP Armor plugins
 * to ensure proper functionality with WP Ultimo forms.
 */
class Honeypot_Compat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function init(): void {
		add_filter('wu_wp-ultimo/login-form_form_fields', [$this, 'add_honeypot_field']);
	}

	/**
	 * Adds a hidden field which will be updated by honeypot js.
	 *
	 * @param array $fields existing fields.
	 *
	 * @return array
	 */
	public function add_honeypot_field(array $fields): array {
		if (! empty($GLOBALS['wpa_version'])) {
			$fields['wpa_hidden_field'] = [
				'type'    => 'hidden',
				'classes' => 'wpa_initiator',
				'name'    => 'wpa_initiator',
				'value'   => '',
			];
		}

		return $fields;
	}
}
