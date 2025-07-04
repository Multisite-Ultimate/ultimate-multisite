<?php
/**
 * Class used for querying webhooks.
 *
 * @package WP_Ultimo
 * @subpackage Database\Webhooks
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Webhooks;

use WP_Ultimo\Database\Engine\Query;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class used for querying webhooks.
 *
 * @since 2.0.0
 */
class Webhook_Query extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'webhooks';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'w';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = \WP_Ultimo\Database\Webhooks\Webhooks_Schema::class;

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'webhook';

	/**
	 * Plural version for a group of items.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'webhooks';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = \WP_Ultimo\Models\Webhook::class;

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'webhooks';

	/**
	 * If we should use a global cache group.
	 *
	 * @since 2.1.2
	 * @var bool
	 */
	protected $global_cache = true;
}
