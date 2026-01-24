<?php
/**
 * Email Accounts schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Email_Accounts
 * @since 2.3.0
 */

namespace WP_Ultimo\Database\Email_Accounts;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email Accounts Schema Class.
 *
 * @since 2.3.0
 */
class Email_Accounts_Schema extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since  2.3.0
	 * @access public
	 * @var array
	 */
	public $columns = [

		[
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		],

		[
			'name'       => 'customer_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'searchable' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'membership_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'allow_null' => true,
			'searchable' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'site_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'allow_null' => true,
			'aliases'    => ['blog_id'],
			'searchable' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'email_address',
			'type'       => 'varchar',
			'length'     => '255',
			'searchable' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'domain',
			'type'       => 'varchar',
			'length'     => '191',
			'searchable' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'provider',
			'type'       => 'varchar',
			'length'     => '50',
			'searchable' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'status',
			'type'       => 'enum(\'pending\', \'provisioning\', \'active\', \'suspended\', \'failed\')',
			'default'    => 'pending',
			'transition' => true,
			'sortable'   => true,
		],

		[
			'name'     => 'quota_mb',
			'type'     => 'int',
			'unsigned' => true,
			'default'  => 0,
			'sortable' => true,
		],

		[
			'name'       => 'external_id',
			'type'       => 'varchar',
			'length'     => '255',
			'allow_null' => true,
		],

		[
			'name'       => 'password_hash',
			'type'       => 'text',
			'allow_null' => true,
		],

		[
			'name'     => 'purchase_type',
			'type'     => 'enum(\'membership_included\', \'per_account\')',
			'default'  => 'membership_included',
			'sortable' => true,
		],

		[
			'name'       => 'payment_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'allow_null' => true,
		],

		[
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => null,
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		],

		[
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => null,
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		],

	];
}
