<?php
/**
 * Domain schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Domains
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Domains;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Domains Schema Class.
 *
 * @since 2.0.0
 */
class Domains_Schema extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since  2.0.0
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
			'name'       => 'blog_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'aliases'    => ['site_id', 'site'],
			'searchable' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'domain',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true,
			'transition' => true,
		],

		[
			'name'       => 'active',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 1,
			'transition' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'primary_domain',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'secure',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		],

		[
			'name'       => 'stage',
			'type'       => 'enum(\'' . Domain_Stage::CHECKING_DNS . '\', \'' . Domain_Stage::CHECKING_SSL . '\', \'' . Domain_Stage::DONE_WITHOUT_SSL . '\', \'' . Domain_Stage::DONE . '\', \'' . Domain_Stage::FAILED . '\', \'' . Domain_Stage::SSL_FAILED . '\')',
			'default'    => 'checking-dns',
			'transition' => true,
			'sortable'   => true,
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
