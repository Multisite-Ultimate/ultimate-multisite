<?php
/**
 * Class used for querying domain mappings.
 *
 * @package WP_Ultimo
 * @subpackage Database\Webhook
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Webhooks;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_webhooks" database table
 *
 * @since 2.0.0
 */
final class Webhooks_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'webhooks';

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

		// phpcs:disable

		$this->schema = "id bigint(20) NOT NULL auto_increment,
			migrated_from_id bigint(20) DEFAULT NULL,
			name varchar(191) NOT NULL,
			webhook_url varchar(191) NOT NULL,
			event varchar(40) NOT NULL,
			event_count int(10) default 0,
			active tinyint(4) default 1,
			hidden tinyint(4) default 0,
			integration varchar(191) NOT NULL,
			date_last_failed datetime NOT NULL,
			date_created datetime NULL,
			date_modified datetime NULL,
			PRIMARY KEY (id),
			KEY event (event)";

			// phpcs:enable
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
