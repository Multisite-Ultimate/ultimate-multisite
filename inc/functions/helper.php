<?php
/**
 * Core Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Exception\Runtime_Exception;
use Psr\Log\LogLevel;

/**
 * Returns the Multisite Ultimate version.
 *
 * @since 2.0.0
 * @return string
 */
function wu_get_version() {

	return class_exists(\WP_Ultimo::class) ? \WP_Ultimo::VERSION : '';
}

/**
 * Check the debug status.
 *
 * @since 2.0.11
 * @return bool
 */
function wu_is_debug() {

	return defined('WP_ULTIMO_DEBUG') && WP_ULTIMO_DEBUG;
}

/**
 * Checks if Multisite Ultimate is being loaded as a must-use plugin.
 *
 * @since 2.0.0
 * @return bool
 */
function wu_is_must_use() {

	return defined('WP_ULTIMO_IS_MUST_USE') && WP_ULTIMO_IS_MUST_USE;
}

/**
 * Checks if an array key value is set and returns it.
 *
 * If the key is not set, returns the $default parameter.
 * This function is a helper to serve as a shorthand for the tedious
 * and ugly $var = isset($array['key'])) ? $array['key'] : $default.
 * Using this, that same line becomes wu_get_isset($array, 'key', $default);
 *
 * Since PHP 7.4, this can be replaced by the null-coalesce operator (??)
 * in almost any circunstante.
 *
 * @since 2.0.0
 *
 * @param array|object $array_or_obj Array or object to check key.
 * @param string       $key Key to check.
 * @param mixed        $default_value Default value, if the key is not set.
 * @return mixed
 */
function wu_get_isset($array_or_obj, $key, $default_value = false) {

	if ( ! is_array($array_or_obj)) {
		$array_or_obj = (array) $array_or_obj;
	}

	return $array_or_obj[ $key ] ?? $default_value;
}

/**
 * Returns the main site id for the network.
 *
 * @since 2.0.0
 * @return int
 */
function wu_get_main_site_id() {

	_wu_require_hook('ms_loaded');

	global $current_site;

	return $current_site->blog_id;
}

/**
 * This function return 'slugfied' options terms to be used as options ids.
 *
 * @since 0.0.1
 * @param string $term Returns a string based on the term and this plugin slug.
 * @return string
 */
function wu_slugify($term) {

	return "wp-ultimo_$term";
}

/**
 * Returns the full path to the plugin folder.
 *
 * @since 2.0.11
 * @param string $dir Path relative to the plugin root you want to access.
 */
function wu_path($dir): string {

	return WP_ULTIMO_PLUGIN_DIR . $dir; // @phpstan-ignore-line
}

/**
 * Returns the URL to the plugin folder.
 *
 * @since 2.0.11
 * @param string $dir Path relative to the plugin root you want to access.
 * @return string
 */
function wu_url($dir) {

	return apply_filters('wp_ultimo_url', WP_ULTIMO_PLUGIN_URL . $dir); // @phpstan-ignore-line
}

/**
 * Shorthand to retrieving variables from $_GET, $_POST and $_REQUEST;
 *
 * @since 2.0.0
 *
 * @param string $key Key to retrieve.
 * @param mixed  $default_value Default value, when the variable is not available.
 * @return mixed
 */
function wu_request($key, $default_value = false) {

	$value = isset($_REQUEST[ $key ]) ? wu_clean(stripslashes_deep($_REQUEST[ $key ])) : $default_value; // phpcs:ignore WordPress.Security.NonceVerification

	return apply_filters('wu_request', $value, $key, $default_value);
}

/**
 * Throws an exception if a given hook was not yet run.
 *
 * @since 2.0.11
 *
 * @param string $hook The hook to check. Defaults to 'ms_loaded'.
 * @throws Runtime_Exception When the hook has not yet run.
 * @return void
 */
function _wu_require_hook($hook = 'ms_loaded') { // phpcs:ignore

	if ( ! did_action($hook)) {
		$message = "This function can not yet be run as it relies on processing that happens on hook {$hook}.";

		throw new Runtime_Exception(esc_html($message));
	}
}

/**
 * Checks if reflection is available.
 *
 * Opcache settings can stripe comments and
 * make reflection unavailable.
 *
 * @since 2.0.11
 * @return boolean
 */
function wu_are_code_comments_available() {

	static $res;

	if (null === $res) {
		$res = (bool) (new \ReflectionFunction(__FUNCTION__))->getDocComment();
	}

	return $res;
}

/**
 * Join string into a single path string.
 *
 * @since 2.0.11
 * @param string ...$parts The parts of the path to join.
 * @return string The URL string.
 */
function wu_path_join(...$parts): string {

	if (count($parts) === 0) {
		return '';
	}

	$prefix = (DIRECTORY_SEPARATOR === $parts[0]) ? DIRECTORY_SEPARATOR : '';

	$processed = array_filter(array_map(fn($part) => rtrim((string) $part, DIRECTORY_SEPARATOR), $parts), fn($part) => ! empty($part));

	return $prefix . implode(DIRECTORY_SEPARATOR, $processed);
}


/**
 * Add a log entry to chosen file.
 *
 * @since 2.0.0
 *
 * @param string           $handle Name of the log file to write to.
 * @param string|\WP_Error $message Log message to write.
 * @param string           $log_level Log level to write.
 * @return void
 */
function wu_log_add($handle, $message, $log_level = LogLevel::INFO) {

	\WP_Ultimo\Logger::add($handle, $message, $log_level);
}

/**
 * Clear entries from chosen file.
 *
 * @since 2.0.0
 *
 * @param mixed $handle Name of the log file to clear.
 * @return void
 */
function wu_log_clear($handle) {

	\WP_Ultimo\Logger::clear($handle);
}

/**
 * Maybe log errors to the file.
 *
 * @since 2.0.0
 *
 * @param \Throwable $e The exception object.
 * @return void
 */
function wu_maybe_log_error($e) {

	if (defined('WP_DEBUG') && WP_DEBUG) {
		error_log($e); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}

/**
 * Get the function caller.
 *
 * @since 2.0.0
 *
 * @param integer $depth The depth of the backtrace.
 * @return string|null
 */
function wu_get_function_caller($depth = 1) {

	$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth + 1);

	$caller = $backtrace[ $depth ]['function'] ?? null;

	return $caller;
}

/**
 * Checks if a particular plugin is skipped in a CLI context.
 *
 * @since 2.1.0
 *
 * @param mixed $plugin The plugin slug. E.g. wp-ultimo.
 */
function wu_cli_is_plugin_skipped($plugin = null): bool {

	if ( ! class_exists(\WP_CLI::class)) {
		return false;
	}

	$skipped_plugins = \WP_CLI::get_config('skip-plugins');

	if (is_bool($skipped_plugins)) {
		return true;
	}

	$skipped_plugins = array_map(fn($plugin_slug) => trim((string) $plugin_slug), explode(',', (string) $skipped_plugins));

	return in_array($plugin, $skipped_plugins, true);
}

/**
 * Capture errors and exceptions thrown inside the callback to prevent breakage.
 *
 * @since 2.1.0
 *
 * @todo Implement the logging portion of the feature.
 * @param \Callable $func A callable to be run inside the capture block.
 * @param bool      $log Wether or not captured errors should be logged to a file.
 *
 * @return void
 */
function wu_ignore_errors($func, $log = false) {

	try {
		call_user_func($func);
	} catch (\Throwable $exception) {

		// Ignore it or log it.
	}
}


/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored. Based of wc_clean from WooCommerce.
 *
 * @since 2.4.0
 * @param string|array $variable Data to sanitize.
 * @return string|array
 */
function wu_clean($variable) {
	if ( is_array($variable) ) {
		return array_map('wu_clean', $variable);
	} else {
		return is_scalar($variable) ? sanitize_text_field($variable) : $variable;
	}
}

/**
 * Get allowed HTML tags and attributes for wp_kses including SVG support and all style attributes.
 *
 * @since 2.4.0
 * @return array Allowed HTML tags and attributes.
 */
function wu_kses_allowed_html() {
	$svg_attributes = [
		'class'             => true,
		'id'                => true,
		'style'             => true,
		'xmlns'             => true,
		'fill'              => true,
		'stroke'            => true,
		'stroke-width'      => true,
		'stroke-linecap'    => true,
		'stroke-linejoin'   => true,
		'stroke-dasharray'  => true,
		'stroke-dashoffset' => true,
		'stroke-miterlimit' => true,
		'fill-opacity'      => true,
		'stroke-opacity'    => true,
		'opacity'           => true,
		'transform'         => true,
		'clip-path'         => true,
		'mask'              => true,
		'filter'            => true,
		'aria-hidden'       => true,
		'aria-labelledby'   => true,
		'aria-describedby'  => true,
		'role'              => true,
		'focusable'         => true,
	];

	return wp_kses_allowed_html('post') + [
		'svg'            => $svg_attributes + [
			'width'               => true,
			'height'              => true,
			'viewbox'             => true,
			'preserveaspectratio' => true,
			'version'             => true,
			'baseprofile'         => true,
		],
		'g'              => $svg_attributes,
		'defs'           => $svg_attributes,
		'title'          => $svg_attributes,
		'desc'           => $svg_attributes,
		'path'           => $svg_attributes + [
			'd'          => true,
			'pathLength' => true,
		],
		'circle'         => $svg_attributes + [
			'cx' => true,
			'cy' => true,
			'r'  => true,
		],
		'ellipse'        => $svg_attributes + [
			'cx' => true,
			'cy' => true,
			'rx' => true,
			'ry' => true,
		],
		'rect'           => $svg_attributes + [
			'x'      => true,
			'y'      => true,
			'width'  => true,
			'height' => true,
			'rx'     => true,
			'ry'     => true,
		],
		'line'           => $svg_attributes + [
			'x1' => true,
			'y1' => true,
			'x2' => true,
			'y2' => true,
		],
		'polyline'       => $svg_attributes + [
			'points' => true,
		],
		'polygon'        => $svg_attributes + [
			'points' => true,
		],
		'text'           => $svg_attributes + [
			'x'            => true,
			'y'            => true,
			'dx'           => true,
			'dy'           => true,
			'rotate'       => true,
			'textLength'   => true,
			'lengthAdjust' => true,
		],
		'tspan'          => $svg_attributes + [
			'x'            => true,
			'y'            => true,
			'dx'           => true,
			'dy'           => true,
			'rotate'       => true,
			'textLength'   => true,
			'lengthAdjust' => true,
		],
		'use'            => $svg_attributes + [
			'href'       => true,
			'xlink:href' => true,
			'x'          => true,
			'y'          => true,
			'width'      => true,
			'height'     => true,
		],
		'image'          => $svg_attributes + [
			'href'                => true,
			'xlink:href'          => true,
			'x'                   => true,
			'y'                   => true,
			'width'               => true,
			'height'              => true,
			'preserveaspectratio' => true,
		],
		'linearGradient' => $svg_attributes + [
			'x1'                => true,
			'y1'                => true,
			'x2'                => true,
			'y2'                => true,
			'gradientUnits'     => true,
			'gradientTransform' => true,
		],
		'radialGradient' => $svg_attributes + [
			'cx'                => true,
			'cy'                => true,
			'r'                 => true,
			'fx'                => true,
			'fy'                => true,
			'gradientUnits'     => true,
			'gradientTransform' => true,
		],
		'stop'           => $svg_attributes + [
			'offset'       => true,
			'stop-color'   => true,
			'stop-opacity' => true,
		],
		'clipPath'       => $svg_attributes + [
			'clipPathUnits' => true,
		],
		'mask'           => $svg_attributes + [
			'maskUnits'        => true,
			'maskContentUnits' => true,
			'x'                => true,
			'y'                => true,
			'width'            => true,
			'height'           => true,
		],
	] + array_fill_keys(['div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'b', 'i', 'ul', 'ol', 'li', 'a', 'img'], ['style' => true]);
}
