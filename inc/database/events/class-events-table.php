<?php
/**
 * Class used for querying events.
 *
 * @package WP_Ultimo
 * @subpackage Database\Event
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Events;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_events" database table
 *
 * @since 2.0.0
 */
final class Events_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'events';

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
			severity tinyint(4),
			initiator enum('system', 'manual'),
			author_id bigint(20) NOT NULL default '0',
			object_id bigint(20) NOT NULL default '0',
			object_type varchar(20) DEFAULT 'network',
			slug varchar(255),
			payload longtext,
			date_created datetime NULL,
			PRIMARY KEY (id),
			KEY severity (severity),
			KEY author_id (author_id),
			KEY initiator (initiator)";
	}

	/**
	 * Fixes the datetime columns to accept null.
	 *
	 * @since 2.1.2
	 */
	protected function __20230601(): bool {

		$null_columns = [
			'date_created',
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
