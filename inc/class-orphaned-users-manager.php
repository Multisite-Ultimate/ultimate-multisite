<?php
/**
 * Orphaned Users Manager.
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
 * Manages orphaned users cleanup.
 *
 * @since 2.0.0
 */
class Orphaned_Users_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Sets up the listeners.
	 *
	 * @since 2.0.0
	 */
	public function init(): void {

		add_action('plugins_loaded', [$this, 'register_forms']);
		add_action('wu_settings_other', [$this, 'register_settings_field']);
	}

	/**
	 * Register ajax forms for orphaned users management.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {

		wu_register_form(
			'orphaned_users_delete',
			[
				'render'     => [$this, 'render_orphaned_users_delete_modal'],
				'handler'    => [$this, 'handle_orphaned_users_delete_modal'],
				'capability' => 'manage_network',
			]
		);
	}

	/**
	 * Register settings field for orphaned users cleanup.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_settings_field(): void {
		wu_register_settings_field(
			'other',
			'cleanup_orphaned_users',
			[
				'title'             => __('Cleanup Orphaned User Accounts', 'ultimate-multisite'),
				'desc'              => __('Remove user accounts that are not members of any site and are not super administrators.', 'ultimate-multisite'),
				'type'              => 'link',
				'display_value'     => __('Check for Orphaned Users', 'ultimate-multisite'),
				'classes'           => 'button button-secondary wu-ml-0 wubox',
				'wrapper_html_attr' => [
					'style' => 'margin-bottom: 20px;',
				],
				'html_attr'         => [
					'href'       => wu_get_form_url('orphaned_users_delete'),
					'wu-tooltip' => __('Scan and cleanup user accounts with no site memberships', 'ultimate-multisite'),
				],
			]
		);
	}

	/**
	 * Renders the orphaned users deletion confirmation modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_orphaned_users_delete_modal(): void {

		$orphaned_users = $this->find_orphaned_users();

		$user_count = count($orphaned_users);
		if (! $user_count) {
			printf(
				'<div class="wu-p-4 wu-bg-green-100 wu-border wu-border-green-400 wu-text-green-700 wu-rounded">
						<h3 class="wu-mt-0 wu-mb-2">%s</h3>
						<p>%s</p>
					</div>',
				esc_html__('No Issues Found', 'ultimate-multisite'),
				esc_html__('No orphaned user accounts found.', 'ultimate-multisite')
			);
			return;
		}

		$user_list = '<div class="wu-max-h-32 wu-overflow-y-auto wu-bg-white wu-p-2 wu-border wu-rounded wu-mb-4">';
		foreach ($orphaned_users as $user) {
			$user_list .= '<div class="wu-text-xs wu-py-1">';
			$user_list .= '<strong>' . esc_html($user->user_login) . '</strong> (' . esc_html($user->user_email) . ')';
			$user_list .= '<div class="wu-text-gray-500 wu-text-xs">ID: ' . esc_html($user->ID) . ' | Registered: ' . esc_html($user->user_registered) . '</div>';
			$user_list .= '</div>';
		}
		$user_list .= '</div>';

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
						/* translators: %d: number of orphaned users */
						esc_html(_n('Confirm Deletion of %d Orphaned User', 'Confirm Deletion of %d Orphaned Users', $user_count, 'ultimate-multisite')),
						$user_count
					),
					esc_html__('You are about to permanently delete the following user accounts:', 'ultimate-multisite'),
					$user_list,
					esc_html__('Warning:', 'ultimate-multisite'),
					esc_html__('This action cannot be undone and will result in permanent data loss. Please ensure you have a complete database backup before proceeding.', 'ultimate-multisite')
				),
				'wrapper_classes' => 'wu-w-full',
			],
			'submit'       => [
				'type'            => 'submit',
				'title'           => __('Yes, Delete These Users', 'ultimate-multisite'),
				'value'           => 'delete',
				'classes'         => 'button button-primary',
				'wrapper_classes' => 'wu-items-end',
			],
		];

		$form = new Form(
			'orphaned-users-delete',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'orphaned_users_delete',
					'data-state'  => wp_json_encode(
						[
							'orphaned_users' => array_map(
								function ($user) {
									return [
										'ID'         => $user->ID,
										'user_login' => $user->user_login,
										'user_email' => $user->user_email,
									];
								},
								$orphaned_users
							),
							'user_count'     => $user_count,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the orphaned users deletion.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_orphaned_users_delete_modal(): void {

		if (! current_user_can('manage_network')) {
			wp_die(esc_html__('You do not have the required permissions.', 'ultimate-multisite'));
		}

		$start_time = microtime(true);

		$orphaned_users = $this->find_orphaned_users();

		if (empty($orphaned_users)) {
			wp_send_json_error(['message' => __('No orphaned users found.', 'ultimate-multisite')]);
		}

		$deleted_count = $this->delete_orphaned_users($orphaned_users);

		$end_time       = microtime(true);
		$execution_time = round($end_time - $start_time, 2);

		$redirect_to = wu_network_admin_url(
			'wp-ultimo-settings',
			[
				'tab'     => 'other',
				'deleted' => $deleted_count,
				'time'    => $execution_time,
				'type'    => 'users',
			]
		);

		wp_send_json_success(
			[
				'redirect_url' => $redirect_to,
				'message'      => sprintf(
					/* translators: %1$d: number of deleted users, %2$s: execution time */
					__('Successfully deleted %1$d orphaned users in %2$s seconds.', 'ultimate-multisite'),
					$deleted_count,
					$execution_time
				),
			]
		);
	}

	/**
	 * Find orphaned user accounts.
	 *
	 * @since 2.0.0
	 * @return array List of orphaned user objects
	 */
	public function find_orphaned_users(): array {

		$orphaned_users = [];

		// Get all users
		$users = get_users(
			[
				'blog_id' => 0,
				'number'  => '',
				'fields'  => ['ID', 'user_login', 'user_email', 'user_registered'],
			]
		);

		foreach ($users as $user) {
			// Skip super administrators - they always have network access
			if (is_super_admin($user->ID)) {
				continue;
			}

			$blogs = get_blogs_of_user($user->ID, true);

			// If user has no roles on any site, they are orphaned
			if (empty($blogs)) {
				$orphaned_users[] = $user;
			}
		}

		return $orphaned_users;
	}

	/**
	 * Delete orphaned users.
	 *
	 * @since 2.0.0
	 * @param array $users List of user objects to delete.
	 * @return int Number of successfully deleted users
	 */
	public function delete_orphaned_users(array $users): int {

		// Ensure required WordPress admin includes are loaded before deletion functions are used.
		if (! function_exists('wp_delete_user')) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		if (is_multisite() && ! function_exists('wpmu_delete_user')) {
			require_once ABSPATH . 'wp-admin/includes/ms.php';
		}

		$deleted_count = 0;

		foreach ($users as $user) {
			// Double-check that this user is actually orphaned and not a super admin
			if (is_super_admin($user->ID)) {
				continue;
			}

			// Use WordPress core function to properly delete user and their data
			$result = is_multisite() ? wpmu_delete_user($user->ID) : wp_delete_user($user->ID);

			if ($result) {
				++$deleted_count;
			}
		}

		return $deleted_count;
	}
}
