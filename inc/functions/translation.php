<?php
/**
 * Translation Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Get the translatable version of a string.
 *
 * @param string $str The string to get.
 *
 * @return string
 */
function wu_get_translatable_string($str) {

	if ( is_string($str) === false) {
		return $str;
	}

	$translatable_strings = include WP_ULTIMO_PLUGIN_DIR . '/data/translatable-strings.php';

	$translatable_strings = apply_filters('wu_translatable_strings', $translatable_strings, $str);

	return wu_get_isset($translatable_strings, $str, $str);
}
