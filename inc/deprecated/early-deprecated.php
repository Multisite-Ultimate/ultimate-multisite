<?php
/**
 * Contains deprecated functions that get loaded at sunrise.
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Deprecated: WU_Domain_Mapping
 *
 * This class was rewritten from scratch.
 * The methods below are helper methods that are being implemented to
 * prevent fatal errors.
 *
 * @deprecated 2.0.0
 */
class WU_Domain_Mapping {

	/**
	 * Deprecated: get_ip_address
	 *
	 * @deprecated 2.0.0
	 * @return string
	 */
	public static function get_ip_address() {

		_deprecated_function(__METHOD__, '2.0.0', '\WP_Ultimo\Domain_Mapping\Helper::get_network_public_ip()');

		$ip = \WP_Ultimo\Domain_Mapping\Helper::get_network_public_ip();

		return apply_filters('wu_domain_mapping_get_ip_address', $ip, sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR'] ?? '')));
	}

	/**
	 * Deprecated: get_hosting_support_text
	 *
	 * @deprecated 2.0.0
	 * @return string
	 */
	public static function get_hosting_support_text() {

		_deprecated_function(__METHOD__, '2.0.0');

		return '';
	}
}
