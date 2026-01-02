<?php
/**
 * Class used for querying email accounts.
 *
 * @package WP_Ultimo
 * @subpackage Database\Email_Accounts
 * @since 2.3.0
 */

namespace WP_Ultimo\Database\Email_Accounts;

use WP_Ultimo\Database\Engine\Query;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class used for querying email accounts.
 *
 * @since 2.3.0
 */
class Email_Account_Query extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since  2.3.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'email_accounts';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since  2.3.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'ea';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since  2.3.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = Email_Accounts_Schema::class;

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since  2.3.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'email_account';

	/**
	 * Plural version for a group of items.
	 *
	 * @since  2.3.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'email_accounts';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since  2.3.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = \WP_Ultimo\Models\Email_Account::class;

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since  2.3.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'email_accounts';

	/**
	 * If we should use a global cache group.
	 *
	 * @since 2.3.0
	 * @var bool
	 */
	protected $global_cache = true;

	/**
	 * Sets up the email account query, based on the query vars passed.
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param string|array $query Array of query arguments.
	 */
	public function __construct($query = []) {

		parent::__construct($query);
	}
}
