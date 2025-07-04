<?php
/**
 * Class used for querying payment.
 *
 * @package WP_Ultimo
 * @subpackage Database\Payments
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Payments;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_payments" database table
 *
 * @since 2.0.0
 */
final class Payments_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'payments';

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
		'2.0.1-revision.20210417' => 20_210_417,
		'2.0.1-revision.20210607' => 20_210_607,
		'2.0.1-revision.20230601' => 20_230_601,
	];

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since  2.0.0
	 * @return void
	 */
	protected function set_schema(): void {

		$this->schema = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			status varchar(20) NOT NULL DEFAULT 'pending',
			customer_id bigint(20) unsigned NOT NULL default '0',
			membership_id bigint(20) unsigned NOT NULL default '0',
			parent_id bigint(20) unsigned NOT NULL default '0',
			product_id bigint(9) NOT NULL default '0',
			migrated_from_id bigint(20) DEFAULT NULL,
			discount_code tinytext NOT NULL default '',
			currency varchar(10) NOT NULL DEFAULT 'USD',
			subtotal decimal(13,4) default 0,
			refund_total decimal(13,4) default 0,
			tax_total decimal(13,4) default 0,
			discount_total decimal(13,4) default 0,
			total decimal(13,4) default 0,
			gateway tinytext NOT NULL default '',
			gateway_payment_id tinytext DEFAULT NULL,
			date_created datetime NULL,
			date_modified datetime NULL,
			PRIMARY KEY (id),
			KEY customer_id (customer_id),
			KEY membership_id (membership_id),
			KEY parent_id (parent_id),
			KEY product_id (product_id),
			KEY status (status)";
	}

	/**
	 * Adds the refund_total column.
	 *
	 * This does not work on older versions of MySQL, so we needed
	 * the other migration below.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function __20210417() { // phpcs:ignore

		$result = $this->column_exists('refund_total');

		// Maybe add column
		if (empty($result)) {
			$query = "ALTER TABLE {$this->table_name} ADD COLUMN `refund_total` decimal(13,4) default 0 AFTER `subtotal`;";

			$result = $this->get_db()->query($query);
		}

		// Return success/fail
		return $this->is_success($result);
	}

	/**
	 * Adds the refund_total column.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function __20210607() { // phpcs:ignore

		$result = $this->column_exists('refund_total');

		// Maybe add column
		if (empty($result)) {
			$query_set = "SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';";

			$result_set = $this->get_db()->query($query_set);

			if ($this->is_success($result_set) === false) {
				return false;
			}

			$query = "ALTER TABLE {$this->table_name} ADD COLUMN `refund_total` decimal(13,4) default 0 AFTER `subtotal`;";

			$result = $this->get_db()->query($query);
		}

		// Return success/fail
		return $this->is_success($result);
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
