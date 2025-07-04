<?php
/**
 * Class used for querying events.
 *
 * @package WP_Ultimo
 * @subpackage Database\Event
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Checkout_Forms;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_events" database table
 *
 * @since 2.0.0
 */
final class Checkout_Forms_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'forms';

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
	protected $version = '2.0.1-revision.20230601';

	/**
	 * List of table upgrades.
	 *
	 * @var array
	 */
	protected $upgrades = [
		'2.0.1-revision.20230601' => 20_230_601,
	];

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since  2.0.0
	 * @return void
	 */
	protected function set_schema(): void {

		$this->schema = "id bigint(20) NOT NULL auto_increment,
			name tinytext NOT NULL DEFAULT '',
			slug varchar(255),
			active tinyint(4) default 1,
			settings longtext DEFAULT NULL,
			custom_css longtext DEFAULT NULL,
			allowed_countries text DEFAULT NULL,
			date_created datetime DEFAULT NULL,
			date_modified datetime DEFAULT NULL,
			PRIMARY KEY (id)";
	}

	/**
	 * Fixes the datetime columns to accept null.
	 *
	 * @since 2.1.2
	 */
	protected function __20230601(): bool {

		$null_columns = [
			'date_created',
			'date_modified',
		];

		foreach ($null_columns as $column) {
			$query = "ALTER TABLE {$this->table_name} MODIFY COLUMN `{$column}` datetime DEFAULT NULL;";

			$result = $this->get_db()->query($query);

			if ( ! $this->is_success($result)) {
				return false;
			}
		}

		return true;
	}
}
