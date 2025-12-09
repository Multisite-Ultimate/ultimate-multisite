<?php

namespace WP_Ultimo\Interfaces;

interface Singleton {

	/**
	 * Returns the instance of WP_Ultimo
	 *
	 * @return static
	 */
	public static function get_instance(): object;

	/**
	 * Runs only once, at the first instantiation of the Singleton.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void;
}
