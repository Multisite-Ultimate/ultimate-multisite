<?php
/**
 * String Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Converts a string (e.g. 'yes' or 'no' or '1' or '0') to a bool.
 *
 * @param string $input_string The string to convert.
 *
 * @return bool
 * @since 2.0.0
 */
function wu_string_to_bool($input_string) {

	return is_bool($input_string) ? $input_string : ('on' === strtolower($input_string) || 'yes' === strtolower($input_string) || 1 === $input_string || 'true' === strtolower($input_string) || '1' === $input_string);
}

/**
 * Converts a slug to a name.
 *
 * This function turns discount_code into Discount Code, by removing _- and using ucwords.
 *
 * @since 2.0.0
 *
 * @param string $slug The slug to convert.
 * @return string
 */
function wu_slug_to_name($slug) {

	$slug = str_replace(['-', '_'], ' ', (string) $slug);

	return ucwords($slug);
}

/**
 * Replaces dashes with underscores on strings.
 *
 * @since 2.0.0
 *
 * @param string $str String to replace dashes in.
 * @return string
 */
function wu_replace_dashes($str) {

	return str_replace('-', '_', $str);
}

/**
 * Get the initials for a string.
 *
 * E.g. Brazilian People will return BP.
 *
 * @since 2.0.0
 *
 * @param string  $str String to process.
 * @param integer $max_size Number of initials to return.
 * @return string
 */
function wu_get_initials($str, $max_size = 2) {

	$words = explode(' ', $str);

	$initials = '';

	for ($i = 0; $i < $max_size; $i++) {
		if ( ! isset($words[ $i ])) {
			break;
		}

		$initials .= substr($words[ $i ], 0, 1);
	}

	return strtoupper($initials);
}
