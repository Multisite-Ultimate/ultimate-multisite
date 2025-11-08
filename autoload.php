<?php
/**
 * Manages Composer autoload.
 *
 * @package WP_Ultimo
 * @since 2.3.0
 * @deprecated since 2.4.5, use /vendor/autoload_packages.php instead.
 */
defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload_packages.php';
require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
