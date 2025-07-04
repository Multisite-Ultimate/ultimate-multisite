<?php
/**
 * Class used for querying posts.
 *
 * @package WP_Ultimo
 * @subpackage Database\Posts
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Posts;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_post" database table
 *
 * @since 2.0.0
 */
final class Posts_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'posts';

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

		$this->schema = "id bigint(20) NOT NULL AUTO_INCREMENT,
      author_id bigint(20) NOT NULL,
      type tinytext NOT NULL DEFAULT '',
			slug tinytext NOT NULL DEFAULT '',
			title tinytext NOT NULL DEFAULT '',
			content longtext NOT NULL default '',
			excerpt longtext NOT NULL default '',
			date_created datetime NULL,
			date_modified datetime NULL,
			list_order tinyint default 10,
			status varchar(100) NOT NULL default 'draft',
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
