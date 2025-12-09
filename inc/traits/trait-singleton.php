<?php
/**
 * A trait to be included in entities to enable REST API endpoints.
 *
 * @package WP_Ultimo
 * @subpackage Apis
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

defined('ABSPATH') || exit;

/**
 * Singleton trait.
 */
trait Singleton {

	/**
	 * Makes sure we are only using one instance of the class
	 *
	 * @var object
	 */
	public static object $instance;

	/**
	 * Returns the instance of WP_Ultimo
	 *
	 * @return static
	 */
	public static function get_instance(): object {

		if ( ! isset(static::$instance) || ! static::$instance instanceof static) {
			static::$instance = new static();

			static::$instance->init();
		}

		return static::$instance;
	}

	/**
	 * Runs only once, at the first instantiation of the Singleton.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		$this->has_parents() && method_exists(get_parent_class($this), 'init') && parent::init();
	}

	/**
	 * Check if the current class has parents.
	 *
	 * @since 2.0.11
	 * @return boolean
	 */
	public function has_parents(): bool {

		return (bool) class_parents($this);
	}

	/**
	 * Private constructor so get_instance() must be used and init() will always be called.
	 */
	final private function __construct() {
	}
}
