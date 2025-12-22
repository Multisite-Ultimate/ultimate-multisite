<?php
/**
 * Database Manager Class
 *
 * @package WP_Ultimo\Site_Exporter\Database
 * @since 2.5.0
 */

// phpcs:disable WordPress.DB.PreparedSQL, WordPress.NamingConventions, WordPress.PHP.YodaConditions

namespace WP_Ultimo\Site_Exporter\Database;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class Manager
 *
 * Some functions adapted from Better-Search-Replace.
 *
 * @see https://github.com/ExpandedFronts/Better-Search-Replace/blob/master/includes/class-bsr-db.php
 * @package WP_Ultimo\Site_Exporter\Database
 */
class Manager {

	/**
	 * WordPress Database Class.
	 *
	 * @var \wpdb
	 */
	private \wpdb $wpdb;

	/**
	 * DatabaseManager constructor.
	 *
	 * @param \wpdb $wpdb The WordPress database object.
	 */
	public function __construct(\wpdb $wpdb) {

		$this->wpdb = $wpdb;
	}

	/**
	 * Returns an array of tables in the database.
	 *
	 * If multisite && mainsite: all tables of the site
	 * If multisite && subsite: all tables of current blog
	 * If single site: all tables of the site
	 *
	 * @param int $blog_id The blog ID.
	 * @return array
	 */
	public function get_tables(int $blog_id = 0): array {

		if (function_exists('is_multisite') && is_multisite()) {
			if (is_main_site() && $blog_id === 0) {
				$tables = $this->wpdb->get_col("SHOW TABLES LIKE'" . $this->wpdb->base_prefix . "%'");
			} else {
				$tables = $this->wpdb->get_col(
					"SHOW TABLES LIKE '" . $this->wpdb->base_prefix . absint($blog_id) . "\_%'"
				);
			}
		} else {
			$tables = $this->wpdb->get_col("SHOW TABLES LIKE'" . $this->wpdb->base_prefix . "%'");
		}

		return $tables;
	}

	/**
	 * Returns an array containing the size of each database table.
	 *
	 * @return array Table => Table Size in KB
	 */
	public function get_sizes(): array {

		$sizes  = [];
		$tables = $this->wpdb->get_results('SHOW TABLE STATUS', ARRAY_A);

		if (is_array($tables) && ! empty($tables)) {
			foreach ($tables as $table) {
				$size = round($table['Data_length'] / 1024, 2);
				// Translators: %s is the value of the size in kByte.
				$sizes[ $table['Name'] ] = sprintf(__('(%s KB)', 'ultimate-multisite'), $size);
			}
		}

		return $sizes;
	}

	/**
	 * Returns the number of rows in a table.
	 *
	 * @param string $table The table name.
	 * @return int
	 */
	public function get_rows(string $table): int {

		$table = esc_sql($table);

		return (int) $this->wpdb->get_var("SELECT COUNT(*) FROM $table");
	}

	/**
	 * Gets the columns in a table.
	 *
	 * @param string $table The table to check.
	 * @return array 1st Element: Primary Key, 2nd Element All Columns
	 */
	public function get_columns(string $table): array {

		$primary_key = null;
		$columns     = [];
		$fields      = $this->wpdb->get_results('DESCRIBE ' . $table);

		if (is_array($fields)) {
			foreach ($fields as $column) {
				$columns[] = $column->Field;
				if ('PRI' === $column->Key) {
					$primary_key = $column->Field;
				}
			}
		}

		return [$primary_key, $columns];
	}

	/**
	 * Get table content.
	 *
	 * @param string $table The Table Name.
	 * @param int    $start The start row.
	 * @param int    $end   Number of Rows to be fetched.
	 * @return array|null
	 */
	public function get_table_content(string $table, int $start, int $end): ?array {

		$data = $this->wpdb->get_results("SELECT * FROM $table LIMIT $start, $end", ARRAY_A);

		return $data;
	}

	/**
	 * Update table.
	 *
	 * @param string $table      The table name.
	 * @param array  $update_sql The update SQL parts.
	 * @param array  $where_sql  The where SQL parts.
	 * @return int|false
	 */
	public function update(string $table, array $update_sql, array $where_sql) {

		$sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $update_sql) .
			' WHERE ' . implode(' AND ', array_filter($where_sql));

		return $this->wpdb->query($sql);
	}

	/**
	 * Get table structure.
	 *
	 * @param string $table The table name.
	 * @return array|object|null
	 */
	public function get_table_structure(string $table) {

		return $this->wpdb->get_results("DESCRIBE $table");
	}

	/**
	 * Returns a SQL CREATE TABLE Statement for the table provided in $table.
	 *
	 * @param string $table The Name of the table we want to create the statement for.
	 * @return array|object|null
	 */
	public function get_create_table_statement(string $table) {

		return $this->wpdb->get_results("SHOW CREATE TABLE $table", ARRAY_N);
	}

	/**
	 * Flush table.
	 *
	 * @return void
	 */
	public function flush(): void {

		$this->wpdb->flush();
	}

	/**
	 * Get base prefix.
	 *
	 * @return string
	 */
	public function get_base_prefix(): string {

		return $this->wpdb->base_prefix;
	}
}
