<?php
/**
 * Class used for querying forms' meta data.
 *
 * @package WP_Ultimo
 * @subpackage Database\Checkout_Forms
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Checkout_Forms;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_formmeta" database table
 *
 * @since 2.0.0
 */
final class Checkout_Forms_Meta_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'formmeta';

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
		wu_form_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext DEFAULT NULL,
		PRIMARY KEY (meta_id),
		KEY wu_form_id (wu_form_id),
		KEY meta_key (meta_key({$max_index_length}))";
	}
}
