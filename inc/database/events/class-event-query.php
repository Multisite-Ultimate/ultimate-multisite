<?php
/**
 * Class used for querying events.
 *
 * @package WP_Ultimo
 * @subpackage Database\Events
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Events;

use WP_Ultimo\Database\Engine\Query;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class used for querying events.
 *
 * @since 2.0.0
 */
class Event_Query extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'events';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'e';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = \WP_Ultimo\Database\Events\Events_Schema::class;

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'event';

	/**
	 * Plural version for a group of items.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'events';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = \WP_Ultimo\Models\Event::class;

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'events';

	/**
	 * If we should use a global cache group.
	 *
	 * @since 2.1.2
	 * @var bool
	 */
	protected $global_cache = true;
}
