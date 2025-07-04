<?php
/**
 * Array Helpers
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Helpers\Arr;

/**
 * Turns a multi-dimensional array into a flat array.
 *
 * @since 2.0.0
 *
 * @param array   $array The array to flatten.
 * @param boolean $indexes If we need to add the indexes as well.
 * @return array
 */
function wu_array_flatten($array, $indexes = false) {

	$return = [];

	array_walk_recursive(
		$array,
		function ($x, $index) use (&$return, $indexes) {

			if ($indexes) {
				$return[] = $index;
			}

			$return[] = $x;
		}
	);

	return $return;
}

/**
 * Copy from http://www.php.net/manual/en/function.array-merge-recursive.php#92195
 *
 * The array_merge_recursive does indeed merge arrays, but it converts values with duplicate
 * keys to arrays rather than overwriting the value in the first array with the duplicate
 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
 * this happens (documented behavior):
 *
 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('org value', 'new value'));
 *
 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
 * Matching keys' values in the second array overwrite those in the first array, as is the
 * case with array_merge, i.e.:
 *
 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('new value'));
 *
 * Parameters are passed by reference, though only for performance reasons. They're not
 * altered by this function.
 *
 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
 *
 * @param array $array1 The arrays original.
 * @param array $array2 The array to be merged in.
 * @param bool  $should_sum If we should add up numeric values instead of replacing the original.
 * @return array
 */
function wu_array_merge_recursive_distinct(array &$array1, array &$array2, $should_sum = true) {

	$merged = $array1;

	foreach ($array2 as $key => &$value) {
		if (is_array($value) && isset($merged[ $key ]) && is_array($merged[ $key ])) {
			$merged[ $key ] = wu_array_merge_recursive_distinct($merged[ $key ], $value, $should_sum);
		} elseif (isset($merged[ $key ]) && is_numeric($merged[ $key ]) && is_numeric($value) && $should_sum) {
				$merged[ $key ] = ((int) $merged[ $key ]) + $value;
		} else {
			$merged[ $key ] = $value;
		}
	}

	return $merged;
}

/**
 * Compares two arrays and returns the diff, recursively.
 *
 * This is frequently used to compare Limitation sets so we can have
 * a waterfall of limitations coming from the product, to the
 * membership, down to the site.
 *
 * @since 2.0.0
 *
 * @param array $array1 Array 1.
 * @param array $array2 Array 2.
 * @param array $to_keep List of keys to keep regardless of diff status.
 * @return array
 */
function wu_array_recursive_diff($array1, $array2, $to_keep = []) {

	$arr_return = [];

	$array1 = (array) $array1;
	$array2 = (array) $array2;

	foreach ($array1 as $key => $value) {
		if (array_key_exists($key, $array2)) {
			if (is_array($value)) {
				$array_recursive_diff = wu_array_recursive_diff($value, $array2[ $key ], $to_keep);

				if (count($array_recursive_diff)) {
					$arr_return[ $key ] = $array_recursive_diff;
				}
			} elseif ((! is_null($value) && $array2[ $key ] != $value) || ($value && $array2[ $key ] && in_array($key, $to_keep, true))) {
					$arr_return[ $key ] = $value;
			}
		} else {
			$arr_return[ $key ] = $value;
		}
	}

	return $arr_return;
}

/**
 * Array map implementation to deal with keys.
 *
 * @since 2.0.0
 *
 * @param callable $callable The callback to run.
 * @param array    $array The array to map the keys.
 */
function wu_array_map_keys($callable, $array): array {

	$keys = array_keys($array);

	$keys = array_map($callable, $keys);

	return array_combine($keys, $array);
}

/**
 * Converts a key => value array into an array of objects with key and value entries.
 *
 * Example:
 *
 * - Input:
 * array(
 *    'key'   => 'value',
 *    'other' => 'foobar',
 * );
 *
 * - Output:
 * array(
 *   array(
 *     'id'   => 'key',
 *     'value => 'value',
 *   ),
 *   array(
 *     'id'   => 'other',
 *     'value => 'foobar',
 *   ),
 * );
 *
 * @since 2.0.11
 *
 * @param array  $assoc_array The key => value array.
 * @param string $key_name The name to use for the key entry.
 * @param string $value_name The name to use for the value entry.
 * @return array
 */
function wu_key_map_to_array($assoc_array, $key_name = 'id', $value_name = 'value') {

	$results = [];

	foreach ($assoc_array as $key => &$value) {
		$results[] = [
			$key_name   => $key,
			$value_name => $value,
		];
	}

	return $results;
}

/**
 * Find a value inside an array by a particular key or property.
 *
 * Dot notation is supported.
 *
 * @since 2.0.11
 *
 * @param array   $array The array to be searched.
 * @param string  $property The property to find by. Supports dot notation.
 * @param mixed   $expected The expected property value.
 * @param integer $flag How to return the results. Can be Arr::RESULTS_ALL, Arr::RESULTS_FIRST, and Arr::RESULTS_LAST.
 * @return mixed
 */
function wu_array_find_by($array, $property, $expected, $flag = 0) {

	return Arr::filter_by_property($array, $property, $expected, $flag);
}

/**
 * Finds all the values inside an array by a particular key or property.
 *
 * Dot notation is supported.
 *
 * @since 2.0.11
 *
 * @param array  $array The array to be searched.
 * @param string $property The property to find by. Supports dot notation.
 * @param mixed  $expected The expected property value.
 * @return mixed
 */
function wu_array_find_all_by($array, $property, $expected) {

	return wu_array_find_by($array, $property, $expected, Arr::RESULTS_ALL);
}

/**
 * Finds the first value inside an array by a particular key or property.
 *
 * Dot notation is supported.
 *
 * @since 2.0.11
 *
 * @param array  $array The array to be searched.
 * @param string $property The property to find by. Supports dot notation.
 * @param mixed  $expected The expected property value.
 * @return mixed
 */
function wu_array_find_first_by($array, $property, $expected) {

	return wu_array_find_by($array, $property, $expected, Arr::RESULTS_FIRST);
}

/**
 * Finds the last value inside an array by a particular key or property.
 *
 * Dot notation is supported.
 *
 * @since 2.0.11
 *
 * @param array  $array The array to be searched.
 * @param string $property The property to find by. Supports dot notation.
 * @param mixed  $expected The expected property value.
 * @return mixed
 */
function wu_array_find_last_by($array, $property, $expected) {

	return wu_array_find_by($array, $property, $expected, Arr::RESULTS_LAST);
}
