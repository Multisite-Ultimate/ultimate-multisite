<?php
/**
 * Class used for querying discount codes' meta data.
 *
 * @package WP_Ultimo
 * @subpackage Database\Discount_Code
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Discount_Codes;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_discount_codemeta" database table
 *
 * @since 2.0.0
 */
final class Discount_Codes_Meta_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'discount_codemeta';

	/**
	 * Is this table global?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $global = true;

	/**
	 * Table current version
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $version = '2.0.0';

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since  2.0.0
	 * @return void
	 */
	protected function set_schema(): void {

		$max_index_length = 191;

		$this->schema = "meta_id bigint(20) unsigned NOT NULL auto_increment,
		wu_discount_code_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext DEFAULT NULL,
		PRIMARY KEY (meta_id),
		KEY wu_discount_code_id (wu_discount_code_id),
		KEY meta_key (meta_key({$max_index_length}))";
	}
}
