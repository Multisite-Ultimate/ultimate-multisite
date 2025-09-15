<?php
/**
 * Orphaned Tables Manager.
 *
 * @package WP_Ultimo
 * @subpackage Managers
 * @since 2.0.0
 */

namespace WP_Ultimo;

use WP_Ultimo\UI\Form;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Manages orphaned database tables cleanup.
 *
 * @since 2.0.0
 */
class Orphaned_Tables_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Sets up the listeners.
	 *
	 * @since 2.0.0
	 */
	public function init(): void {

		global $wp_version;

		// Only run if WordPress version is 6.2 or greater
		if (version_compare($wp_version, '6.2', '<')) {
			return;
		}

		add_action('plugins_loaded', [$this, 'register_forms']);
		add_action('wu_settings_other', [$this, 'register_settings_field']);
	}

	/**
	 * Register ajax forms for orphaned tables management.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {

		wu_register_form(
			'orphaned_tables_delete',
			[
				'render'     => [$this, 'render_orphaned_tables_delete_modal'],
				'handler'    => [$this, 'handle_orphaned_tables_delete_modal'],
				'capability' => 'manage_network',
			]
		);
	}

	public function register_settings_field(): void {
		wu_register_settings_field(
			'other',
			'cleanup_orphaned_tables',
			[
				'title'             => __('Cleanup Orphaned Database Tables', 'multisite-ultimate'),
				'desc'              => __('Remove database tables from deleted sites that were not properly cleaned up.', 'multisite-ultimate'),
				'type'              => 'link',
				'display_value'     => __('Check for Orphaned Tables', 'multisite-ultimate'),
				'classes'           => 'button button-secondary wu-ml-0 wubox',
				'wrapper_html_attr' => [
					'style' => 'margin-bottom: 20px;',
				],
				'html_attr'         => [
					'href'       => wu_get_form_url('orphaned_tables_delete'),
					'wu-tooltip' => __('Scan and cleanup database tables from deleted sites', 'multisite-ultimate'),
				],
			]
		);
	}

	/**
	 * Renders the orphaned tables deletion confirmation modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_orphaned_tables_delete_modal(): void {

		$orphaned_tables = $this->find_orphaned_tables();

		$table_count = count($orphaned_tables);
		if (! $table_count) {
			printf(
				'<div class="wu-p-4 wu-bg-red-100 wu-border wu-border-red-400 wu-text-red-700 wu-rounded">
							<h3 class="wu-mt-0 wu-mb-2">%s</h3>
							<p>%s</p>
						</div>',
				esc_html__('Not Found', 'multisite-ultimate'),
				esc_html__('No Orphaned Tables found.', 'multisite-ultimate')
			);
			return;
		}

		$table_list = '<div class="wu-max-h-32 wu-overflow-y-auto wu-bg-white wu-p-2 wu-border wu-rounded wu-mb-4">';
		foreach ($orphaned_tables as $table) {
			$table_list .= '<div class="wu-text-xs wu-font-mono wu-py-1">' . esc_html($table) . '</div>';
		}
		$table_list .= '</div>';

		$fields = [
			'confirmation' => [
				'type'            => 'note',
				'desc'            => sprintf(
					'<div class="wu-p-4 wu-bg-red-100 wu-border wu-border-red-400 wu-text-red-700 wu-rounded">
						<h3 class="wu-mt-0 wu-mb-2">%s</h3>
						<p class="wu-mb-2">%s</p>
						%s
						<p class="wu-text-sm wu-mb-4">
							<strong>%s</strong> %s
						</p>
					</div>',
					sprintf(
						/* translators: %d: number of orphaned tables */
						esc_html(_n('Confirm Deletion of %d Orphaned Table', 'Confirm Deletion of %d Orphaned Tables', $table_count, 'multisite-ultimate')),
						$table_count
					),
					esc_html__('You are about to permanently delete the following database tables:', 'multisite-ultimate'),
					$table_list,
					esc_html__('Warning:', 'multisite-ultimate'),
					esc_html__('This action cannot be undone. Please ensure you have a database backup before proceeding.', 'multisite-ultimate')
				),
				'wrapper_classes' => 'wu-w-full',
			],
			'submit'       => [
				'type'            => 'submit',
				'title'           => __('Yes, Delete These Tables', 'multisite-ultimate'),
				'value'           => 'delete',
				'classes'         => 'button button-primary',
				'wrapper_classes' => 'wu-items-end',
			],
		];

		$form = new Form(
			'orphaned-tables-delete',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'orphaned_tables_delete',
					'data-state'  => wp_json_encode(
						[
							'orphaned_tables' => $orphaned_tables,
							'table_count'     => $table_count,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the orphaned tables deletion.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_orphaned_tables_delete_modal(): void {

		if (! current_user_can('manage_network')) {
			wp_die(esc_html__('You do not have the required permissions.', 'multisite-ultimate'));
		}

		if (empty($orphaned_tables) || ! is_array($orphaned_tables)) {
			$orphaned_tables = $this->find_orphaned_tables();
		}

		$deleted_count = $this->delete_orphaned_tables($orphaned_tables);

		$redirect_to = wu_network_admin_url(
			'wp-ultimo-settings',
			[
				'tab'     => 'other',
				'deleted' => $deleted_count,
			]
		);

		wp_send_json_success(
			[
				'redirect_url' => $redirect_to,
			]
		);
	}

	/**
	 * Find orphaned database tables.
	 *
	 * @since 2.0.0
	 * @return array List of orphaned table names
	 */
	public function find_orphaned_tables(): array {

		global $wpdb;

		$orphaned_tables = [];

		// Get all site IDs
		$site_ids = get_sites(
			[
				'fields' => 'ids',
				'number' => 0,
			]
		);

		// Get all tables from the database
		$all_tables = $wpdb->get_col('SHOW TABLES'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		foreach ($all_tables as $table) {
			// Check if table matches multisite pattern (prefix + number + underscore)
			$pattern = '/^' . preg_quote($wpdb->prefix, '/') . '([0-9]+)_(.+)/';

			if (preg_match($pattern, $table, $matches)) {
				$site_id      = (int) $matches[1];
				$table_suffix = $matches[2];

				// Skip if this is the main site (usually ID 1)
				if (1 === $site_id) {
					continue;
				}

				// Check if site ID exists in active sites
				if (! in_array($site_id, $site_ids, true)) {
					$orphaned_tables[] = $table;
				}
			}
		}

		return $orphaned_tables;
	}

	/**
	 * Delete orphaned tables.
	 *
	 * @since 2.0.0
	 * @param array $tables List of table names to delete.
	 * @return int Number of successfully deleted tables
	 */
	public function delete_orphaned_tables(array $tables): int {

		global $wpdb;

		$deleted_count = 0;

		foreach ($tables as $table) {
			// Sanitize table name to prevent SQL injection
			$table = sanitize_key($table);

			// Verify the table still exists and matches our pattern
			$pattern = '/^' . preg_quote($wpdb->prefix, '/') . '([0-9]+)_(.+)/';
			if (! preg_match($pattern, $table)) {
				continue;
			}

			// Use DROP TABLE IF EXISTS for safety
			$result = $wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $table)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder

			if (false !== $result) {
				++$deleted_count;
			}
		}

		return $deleted_count;
	}
}
