<?php
/**
 * Base Custom Database Column Class.
 */

namespace WP_Ultimo\Database\Engine;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * The base class that all other database base classes extend.
 *
 * This class attempts to provide some universal immutability to all other
 * classes that extend it, starting with a magic getter, but likely expanding
 * into a magic call handler and others.
 *
 * @since 1.0.0
 */
class Column extends \BerlinDB\Database\Column {

	use Network_Prefix;

	protected $prefix = 'wu';

	/**
	 * Change the prefix if needed.
	 */
	public function __construct() {
		$this->update_prefix_with_network_id();
		parent::__construct();
	}
}
