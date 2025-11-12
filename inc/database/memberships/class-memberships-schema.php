<?php
/**
 * Membership schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Memberships
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Memberships;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Memberships Schema Class.
 *
 * @since 2.0.0
 */
class Memberships_Schema extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var array
	 */
	public $columns = [

		// id
		[
			'name'       => 'id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'extra'      => 'auto_increment',
			'primary'    => true,
			'sortable'   => true,
			'searchable' => true,
		],

		// customer_id
		[
			'name'     => 'customer_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
		],

		// user_id
		[
			'name'       => 'user_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => null,
			'allow_null' => true,
		],

		[
			'name'       => 'migrated_from_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'sortable'   => true,
			'allow_null' => true,
		],

		// object_id
		[
			'name'       => 'plan_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'sortable'   => true,
			'transition' => true,
		],

		// addons
		[
			'name' => 'addon_products',
			'type' => 'longtext',
		],

		// currency
		[
			'name'     => 'currency',
			'type'     => 'varchar',
			'length'   => '20',
			'default'  => 'USD',
			'sortable' => true,
		],

		[
			'name'       => 'initial_amount',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		],

		[
			'name'       => 'recurring',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 1,
			'transition' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'auto_renew',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'duration',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true,
			'transition' => true,
		],

		[
			'name'    => 'duration_unit',
			'type'    => 'enum(\'day\', \'month\', \'week\', \'year\')',
			'default' => 'none',
		],

		[
			'name'       => 'amount',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		],

		// date_created
		[
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => null,
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		],

		// date_activated
		[
			'name'       => 'date_activated',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		],

		// date_trial_end
		[
			'name'       => 'date_trial_end',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		],

		// date_renewed
		[
			'name'       => 'date_renewed',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		],

		// date_cancellation
		[
			'name'       => 'date_cancellation',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		],

		// date_expiration
		[
			'name'       => 'date_expiration',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'transition' => true,
			'allow_null' => true,
		],

		// date_payment_plan_completed
		[
			'name'       => 'date_payment_plan_completed',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'transition' => true,
			'allow_null' => true,
		],

		// auto_renew
		[
			'name'       => 'auto_renew',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'transition' => true,
		],

		// times_billed
		[
			'name'       => 'times_billed',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true,
			'transition' => true,
		],

		// billing_cycles
		[
			'name'     => 'billing_cycles',
			'type'     => 'smallint',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		],

		// status
		[
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '12',
			'default'    => 'pending',
			'sortable'   => true,
			'transition' => true,
		],

		// gateway_customer_id
		[
			'name'       => 'gateway_customer_id',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
			'transition' => true,
		],

		// gateway_subscription_id
		[
			'name'       => 'gateway_subscription_id',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
			'transition' => true,
		],

		// gateway
		[
			'name'       => 'gateway',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
		],

		// signup_method
		[
			'name'    => 'signup_method',
			'type'    => 'tinytext',
			'default' => '',
		],

		// subscription_key
		[
			'name'       => 'subscription_key',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
		],

		// upgraded_from
		[
			'name'     => 'upgraded_from',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '',
		],

		// date_modified
		[
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => null,
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// disabled
		[
			'name'     => 'disabled',
			'type'     => 'smallint',
			'unsigned' => true,
			'default'  => '',
			'pattern'  => '%d',
		],

	];
}
