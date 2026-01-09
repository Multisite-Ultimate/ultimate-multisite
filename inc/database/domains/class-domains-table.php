<?php
/**
 * Class used for querying domain mappings.
 *
 * @package WP_Ultimo
 * @subpackage Database\Domains
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Domains;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_domain_mapping" database table
 *
 * @since 2.0.0
 */
final class Domains_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'domain_mappings';

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
	protected $version = '2.0.1-revision.20260109';

	/**
	 * Use real callbacks.
	 */
	public function __construct() {
		$this->upgrades = [
			'2.0.1-revision.20230601' => [$this, 'allow_nulls'],
			'2.0.1-revision.20260109' => [$this, 'update_enum'],
		];
		parent::__construct();
	}

	/**
	 * Set up the database schema
	 *
	 * @acces s protected
	 * @since  2.0.0
	 * @return void
	 */
	protected function set_schema(): void {

		$this->schema = "id bigint(20) NOT NULL auto_increment,
			blog_id bigint(20) NOT NULL,
			domain varchar(191) NOT NULL,
			active tinyint(4) default 1,
			primary_domain tinyint(4) default 0,
			secure tinyint(4) default 0,
			stage enum('" . Domain_Stage::CHECKING_DNS . "', '" . Domain_Stage::CHECKING_SSL . "', '" . Domain_Stage::DONE_WITHOUT_SSL . "', '" . Domain_Stage::DONE . "', '" . Domain_Stage::FAILED . "', '" . Domain_Stage::SSL_FAILED . "') DEFAULT '" . Domain_Stage::CHECKING_DNS . "',
			date_created datetime NULL,
			date_modified datetime NULL,
			PRIMARY KEY (id),
			KEY blog_id (blog_id,domain,active),
			KEY domain (domain)";
	}

	/**
	 * Fixes the datetime columns to accept null.
	 *
	 * @since 2.1.2
	 */
	protected function allow_nulls(): bool {

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

	/**
	 * Adds the ssl-failed stage
	 *
	 * @since 2.4.10
	 */
	protected function update_enum(): bool { // phpcs:ignore PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames.MethodDoubleUnderscore

		$query = "ALTER TABLE {$this->table_name} MODIFY COLUMN `stage` enum('" . Domain_Stage::CHECKING_DNS . "', '" . Domain_Stage::CHECKING_SSL . "', '" . Domain_Stage::DONE_WITHOUT_SSL . "', '" . Domain_Stage::DONE . "', '" . Domain_Stage::FAILED . "', '" . Domain_Stage::SSL_FAILED . "') DEFAULT '" . Domain_Stage::CHECKING_DNS . "';";

		$result = $this->get_db()->query($query);

		if ( ! $this->is_success($result)) {
			return false;
		}

		return true;
	}
}
