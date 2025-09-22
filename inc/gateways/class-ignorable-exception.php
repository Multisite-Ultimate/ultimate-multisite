<?php
/**
 * Ignorable Exception.
 *
 * @package WP_Ultimo
 * @subpackage Gateways
 * @since 2.0.7
 */

namespace WP_Ultimo\Gateways;

defined('ABSPATH') || exit;

/**
 * This exception will be caught but will not trigger a 500.
 *
 * @since 2.0.7
 */
class Ignorable_Exception extends \Exception {}
