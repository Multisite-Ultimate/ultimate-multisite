<?php

use Psr\Log\LogLevel;

if ( ! class_exists('MUCD_Data') ) {

	class MUCD_Data {

		private static $to_site_id;

		/**
		 * Copy and Update tables from a site to another
		 *
		 * @since 0.2.0
		 * @param  int $from_site_id duplicated site id
		 * @param  int $to_site_id   new site id
		 */
		public static function copy_data($from_site_id, $to_site_id): void {
			self::$to_site_id = $to_site_id;

			// Copy
			$saved_options = self::db_copy_tables($from_site_id, $to_site_id);
			$blog_meta     = self::db_copy_blog_meta($from_site_id, $to_site_id);
			// Update
			self::db_update_data($from_site_id, $to_site_id, $saved_options);
		}

		public static function db_copy_blog_meta($from_site_id, $to_site_id): void {

			global $wpdb;

			// Delete everything
			$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				_get_meta_table('blog'),
				[
					'blog_id' => $to_site_id,
				]
			);

			$meta = get_site_meta($from_site_id);

			foreach ($meta as $meta_key => $list) {
				foreach ($list as $value) {
					add_site_meta($to_site_id, $meta_key, $value);
				}
			}
		}

		/**
		 * Copy tables from a site to another
		 *
		 * @since 0.2.0
		 * @param  int $from_site_id duplicated site id
		 * @param  int $to_site_id   new site id
		 */
		public static function db_copy_tables($from_site_id, $to_site_id) {
			global $wpdb;

			// Source Site information
			$from_site_prefix        = $wpdb->get_blog_prefix($from_site_id);                    // prefix
			$from_site_prefix_length = strlen((string) $from_site_prefix);                           // prefix length

			// Destination Site information
			$to_site_prefix        = $wpdb->get_blog_prefix($to_site_id);                        // prefix
			$to_site_prefix_length = strlen((string) $to_site_prefix);                               // prefix length

			// Options that should be preserved in the new blog.
			$saved_options = MUCD_Option::get_saved_option();

			foreach ($saved_options as $option_name => $option_value) {
				$saved_options[ $option_name ] = get_blog_option($to_site_id, $option_name);
			}

			// Bugfix : escape '_' , '%' and '/' character for mysql 'like' queries
			$from_site_prefix_like = $wpdb->esc_like($from_site_prefix);

			// SCHEMA - TO FIX for HyperDB
			$schema = DB_NAME;

			// Get sources Tables
			if (MUCD_PRIMARY_SITE_ID == $from_site_id) {
				$from_site_table = self::get_primary_tables($from_site_prefix);
			} else {
				$sql_query       = $wpdb->prepare('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = \'%s\' AND TABLE_NAME LIKE \'%s\'', $schema, $from_site_prefix_like . '%');
				$from_site_table = self::do_sql_query($sql_query, 'col');
			}

			// var_dump($from_site_table);

			// $a = 6;
			// $b = 9;

			// [$from_site_table[$a], $from_site_table[$b]] = [$from_site_table[$b], $from_site_table[$a]];

			// var_dump($from_site_table);

			// die;

			$tables_to_ignore = [
				'actionscheduler_actions',
				'actionscheduler_claims',
				'actionscheduler_groups',
				'actionscheduler_logs',
			];

			foreach ($from_site_table as $table) {
				$table_base_name = substr((string) $table, $from_site_prefix_length);

				if (in_array($table_base_name, $tables_to_ignore, true)) {
					continue;
				}

				$table_name = $to_site_prefix . $table_base_name;

				// Drop table if exists
				self::do_sql_query('DROP TABLE IF EXISTS `' . $table_name . '`');

				// Create new table from source table
				// self::do_sql_query('CREATE TABLE IF NOT EXISTS `' . $table_name . '` LIKE `' . $schema . '`.`' . $table . '`');

				$create_statement = self::do_sql_query('SHOW CREATE TABLE `' . $table . '`', 'row_array');

				$create_statement_sql = str_replace($from_site_prefix, $to_site_prefix, (string) $create_statement[1]);

				$wpdb->get_results('SET foreign_key_checks = 0'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

				self::do_sql_query($create_statement_sql);

				// echo($create_statement_sql);

				// die;

				// Populate database with data from source table
				self::do_sql_query('INSERT `' . $table_name . '` SELECT * FROM `' . $schema . '`.`' . $table . '`');

				$wpdb->get_results('SET foreign_key_checks = 1'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			}

			// apply key options from new blog.
			self::db_restore_data($to_site_id, $saved_options);

			return $saved_options;
		}

		/**
		 * Get tables to copy if duplicated site is primary site
		 *
		 * @since 0.2.0
		 * @param  array of string $from_site_tables all tables of duplicated site
		 * @param  string          $from_site_prefix db prefix of duplicated site
		 * @return array of strings : the tables
		 */
		public static function get_primary_tables($from_site_prefix) {

			$default_tables = MUCD_Option::get_primary_tables_to_copy();

			foreach ($default_tables as $k => $default_table) {
				$default_tables[ $k ] = $from_site_prefix . $default_table;
			}

			return $default_tables;
		}


		/**
		 * Updated tables from a site to another
		 *
		 * @since 0.2.0
		 * @param  int $from_site_id duplicated site id
		 * @param  int $to_site_id   new site id
		 */
		public static function db_update_data($from_site_id, $to_site_id, $saved_options): void {

			global $wpdb;

			$to_blog_prefix = $wpdb->get_blog_prefix($to_site_id);

			// Looking for uploads dirs
			switch_to_blog($from_site_id);

			$dir             = wp_upload_dir();
			$from_upload_url = str_replace(network_site_url(), get_bloginfo('url') . '/', $dir['baseurl']);
			$from_blog_url   = get_blog_option($from_site_id, 'siteurl');

			restore_current_blog();

			switch_to_blog($to_site_id);

			$dir           = wp_upload_dir();
			$to_upload_url = str_replace(network_site_url(), get_bloginfo('url') . '/', $dir['baseurl']);
			$to_blog_url   = get_blog_option($to_site_id, 'siteurl');

			restore_current_blog();

			$tables = [];

			// Bugfix : escape '_' , '%' and '/' character for mysql 'like' queries
			$to_blog_prefix_like = $wpdb->esc_like($to_blog_prefix);

			$results = self::do_sql_query('SHOW TABLES LIKE \'' . $to_blog_prefix_like . '%\'', 'col', false);

			foreach ( $results as $k => $v ) {
				$tables[ str_replace($to_blog_prefix, '', (string) $v) ] = [];
			}

			foreach ( $tables as $table => $col) {
				$results = self::do_sql_query('SHOW COLUMNS FROM `' . $to_blog_prefix . $table . '`', 'col', false);

				$columns = [];

				foreach ( $results as $k => $v ) {
					$columns[] = $v;
				}

				$tables[ $table ] = $columns;
			}

			$default_tables = MUCD_Option::get_fields_to_update();

			foreach ( $default_tables as $table => $field) {
				$tables[ $table ] = $field;
			}

			$from_site_prefix = $wpdb->get_blog_prefix($from_site_id);
			$to_site_prefix   = $wpdb->get_blog_prefix($to_site_id);

			$string_to_replace = [
				wu_replace_scheme($from_upload_url) => wu_replace_scheme($to_upload_url),
				wu_replace_scheme($from_blog_url)   => wu_replace_scheme($to_blog_url),
				$from_site_prefix                   => $to_site_prefix,
			];

			$string_to_replace = apply_filters('mucd_string_to_replace', $string_to_replace, $from_site_id, $to_site_id);

			foreach ( $tables as $table => $field) {
				foreach ( $string_to_replace as $from_string => $to_string) {
					self::update($to_blog_prefix . $table, $field, $from_string, $to_string);
				}
			}

			self::db_restore_data($to_site_id, $saved_options);
		}

		/**
		 * Restore options that should be preserved in the new blog
		 *
		 * @since 0.2.0
		 * @param  int $from_site_id duplicated site id
		 * @param  int $to_site_id   new site id
		 */
		public static function db_restore_data($to_site_id, $saved_options): void {

			switch_to_blog($to_site_id);

			foreach ( $saved_options as $option_name => $option_value ) {
				try {
						update_option($option_name, $option_value);
				} catch (\Throwable $exception) {
					// ...nothing
				}
			}

			restore_current_blog();
		}

		/**
		 * Updates a table
		 *
		 * @since 0.2.0
		 * @param  string          $table to update
		 * @param  array of string $fields to update
		 * @param  string          $from_string original string to replace
		 * @param  string          $to_string new string
		 */
		public static function update($table, $fields, $from_string, $to_string): void {
			if (is_array($fields) || ! empty($fields)) {
				global $wpdb;

				foreach ($fields as $field) {

					// Bugfix : escape '_' , '%' and '/' character for mysql 'like' queries
					$from_string_like = $wpdb->esc_like($from_string);

					$results = $wpdb->query("SET SQL_MODE='ALLOW_INVALID_DATES';"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

					$sql_query = $wpdb->prepare(
						'
                        SELECT `' . $field . '` FROM `' . $table . '` WHERE `' . $field . '` LIKE "%s" ',
						'%' . $from_string_like . '%'
					);

					$results = self::do_sql_query($sql_query, 'results', false);

					if ($results) {
						$update = 'UPDATE `' . $table . '` SET `' . $field . '` = "%s" WHERE `' . $field . '` = "%s"';

						foreach ($results as $result => $row) {
							$old_value = $row[ $field ];
							$new_value = self::try_replace($row, $field, $from_string, $to_string);
							$sql_query = $wpdb->prepare($update, $new_value, $old_value);
							$results   = self::do_sql_query($sql_query);
						}
					}
				}
			}
		}

		/**
		 * Replace $from_string with $to_string in $val
		 * Warning : if $to_string already in $val, no replacement is made
		 *
		 * @since 0.2.0
		 * @param  string $val
		 * @param  string $from_string
		 * @param  string $to_string
		 * @return string the new string
		 */
		public static function replace($val, $from_string, $to_string) {
			$new = $val;
			if (is_string($val)) {
				$pos = strpos($val, $to_string);
				if (false === $pos) {
					$new = str_replace($from_string, $to_string, $val);
				}
			}

			return $new;
		}

		/**
		 * Replace recursively $from_string with $to_string in $val
		 *
		 * @since 0.2.0
		 * @param  mixte (string|array) $val
		 * @param  string               $from_string
		 * @param  string               $to_string
		 * @return string the new string
		 */
		public static function replace_recursive($val, $from_string, $to_string) {
			$unset = [];
			if (is_array($val)) {
				foreach ($val as $k => $v) {
					$val[ $k ] = self::try_replace($val, $k, $from_string, $to_string);
				}
			} else {
				$val = self::replace($val, $from_string, $to_string);
			}

			foreach ($unset as $k) {
				unset($val[ $k ]);
			}

			return $val;
		}

		/**
		 * Try to replace $from_string with $to_string in a row
		 *
		 * @since 0.2.0
		 * @param  array  $row the row
		 * @param  array  $field the field
		 * @param  string $from_string
		 * @param  string $to_string
		 * @return the new data
		 */
		public static function try_replace($row, $field, $from_string, $to_string) {
			if (is_serialized($row[ $field ])) {
				$double_serialize = false;
				$row[ $field ]    = @unserialize($row[ $field ]);

				// FOR SERIALISED OPTIONS, like in wp_carousel plugin
				if (is_serialized($row[ $field ])) {
					$row[ $field ]    = @unserialize($row[ $field ]);
					$double_serialize = true;
				}

				if (is_array($row[ $field ])) {
					$row[ $field ] = self::replace_recursive($row[ $field ], $from_string, $to_string);
				} elseif (is_object($row[ $field ]) || $row[ $field ] instanceof __PHP_Incomplete_Class) {
					$array_object = (array) $row[ $field ];
					$array_object = self::replace_recursive($array_object, $from_string, $to_string);
					foreach ($array_object as $key => $value) {
						try {
							$row[ $field ]->$key = $value;
						} catch (\Throwable $exception) {
							// ...nothing
						}
					}
				} else {
					$row[ $field ] = self::replace($row[ $field ], $from_string, $to_string);
				}

				$row[ $field ] = serialize($row[ $field ]);

				// Pour des options comme wp_carousel...
				if ($double_serialize) {
					$row[ $field ] = serialize($row[ $field ]);
				}
			} else {
				$row[ $field ] = self::replace($row[ $field ], $from_string, $to_string);
			}

			return $row[ $field ];
		}

		/**
		 * Runs a WPDB query
		 *
		 * @since 0.2.0
		 * @param  string  $sql_query the query
		 * @param  string  $type type of result
		 * @param  boolean $log log the query, or not
		 * @return $results of the query
		 */
		public static function do_sql_query($sql_query, $type = '', $log = true) {
			global $wpdb;

			$wpdb->suppress_errors();

			switch ($type) {
				case 'col':
					$results = $wpdb->get_col($sql_query);  // phpcs:ignore WordPress.DB
					break;
				case 'row':
					$results = $wpdb->get_row($sql_query);  // phpcs:ignore WordPress.DB
					break;
				case 'row_array':
					$results = $wpdb->get_row($sql_query, ARRAY_N); // phpcs:ignore WordPress.DB
					break;
				case 'var':
					$results = $wpdb->get_var($sql_query); // phpcs:ignore WordPress.DB
					break;
				case 'results':
					$results = $wpdb->get_results($sql_query, ARRAY_A); // phpcs:ignore WordPress.DB
					break;
				default:
					$results = $wpdb->query($sql_query); // phpcs:ignore WordPress.DB
					break;
			}

			if ($log) {
				MUCD_Duplicate::write_log('SQL :' . $sql_query);
				MUCD_Duplicate::write_log('Result :' . var_export($results, true));
			}

			if ('' != $wpdb->last_error) {
				self::sql_error($sql_query, $wpdb->last_error);
			}

			$wpdb->suppress_errors(false);

			return $results;
		}

		/**
		 * Stop process on SQL Error, print and log error, removes the new blog
		 *
		 * @since 0.2.0
		 * @param  string $sql_query the query
		 * @param  string $sql_error the error
		 */
		public static function sql_error($sql_query, $sql_error): void {
			wu_log_add('site-duplication-errors', sprintf('Got error "%s" while running: %s', $sql_error, $sql_query), LogLevel::ERROR);
		}
	}
}
