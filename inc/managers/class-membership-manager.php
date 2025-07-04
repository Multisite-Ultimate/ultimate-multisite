<?php
/**
 * Membership Manager
 *
 * Handles processes related to memberships.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Membership_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use Psr\Log\LogLevel;
use WP_Ultimo\Database\Memberships\Membership_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to memberships.
 *
 * @since 2.0.0
 */
class Membership_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api;
	use \WP_Ultimo\Apis\WP_CLI;
	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'membership';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = \WP_Ultimo\Models\Membership::class;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action(
			'init',
			function () {
				Event_Manager::register_model_events('membership', __('Membership', 'multisite-ultimate'), ['created', 'updated']);
			}
		);

		add_action('wu_async_transfer_membership', [$this, 'async_transfer_membership'], 10, 2);

		add_action('wu_async_delete_membership', [$this, 'async_delete_membership'], 10);

		/*
		 * Transitions
		 */
		add_action('wu_transition_membership_status', [$this, 'mark_cancelled_date'], 10, 3);

		add_action('wu_transition_membership_status', [$this, 'transition_membership_status'], 10, 3);

		/*
		 * Deal with delayed/schedule swaps
		 */
		add_action('wu_async_membership_swap', [$this, 'async_membership_swap'], 10);

		/*
		 * Deal with pending sites creation
		 */
		add_action('wp_ajax_wu_publish_pending_site', [$this, 'publish_pending_site']);

		add_action('wp_ajax_wu_check_pending_site_created', [$this, 'check_pending_site_created']);

		add_action('wu_async_publish_pending_site', [$this, 'async_publish_pending_site'], 10);
	}

	/**
	 * Processes a delayed site publish action.
	 *
	 * @since 2.0.11
	 */
	public function publish_pending_site(): void {

		check_ajax_referer('wu_publish_pending_site');

		ignore_user_abort(true);

		// Don't make the request block till we finish, if possible.
		if ( function_exists('fastcgi_finish_request') && version_compare(phpversion(), '7.0.16', '>=') ) {
			wp_send_json(['status' => 'creating-site']);

			fastcgi_finish_request();
		}

		$membership_id = wu_request('membership_id');

		$this->async_publish_pending_site($membership_id);

		exit; // Just exit the request
	}

	/**
	 * Processes a delayed site publish action.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id The membership id.
	 * @return bool|\WP_Error
	 */
	public function async_publish_pending_site($membership_id) {

		$membership = wu_get_membership($membership_id);

		if ( ! $membership) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$status = $membership->publish_pending_site();

		if (is_wp_error($status)) {
			wu_log_add('site-errors', $status, LogLevel::ERROR);
		}

		return $status;
	}

	/**
	 * Processes a delayed site publish action.
	 *
	 * @since 2.0.11
	 */
	public function check_pending_site_created() {

		$membership_id = wu_request('membership_hash');

		$membership = wu_get_membership_by_hash($membership_id);

		if ( ! $membership) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$pending_site = $membership->get_pending_site();

		if ( ! $pending_site) {
			/**
			 * We do not have a pending site, so we can assume the site was created.
			 */
			wp_send_json(['publish_status' => 'completed']);

			exit;
		}

		wp_send_json(['publish_status' => $pending_site->is_publishing() ? 'running' : 'stopped']);

		exit;
	}

	/**
	 * Processes a membership swap.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id The membership id.
	 * @return bool|\WP_Error
	 */
	public function async_membership_swap($membership_id) {

		global $wpdb;

		$membership = wu_get_membership($membership_id);

		if ( ! $membership) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$scheduled_swap = $membership->get_scheduled_swap();

		if (empty($scheduled_swap)) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$order = $scheduled_swap->order;

		$wpdb->query('START TRANSACTION'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		try {
			$membership->swap($order);

			$status = $membership->save();

			if (is_wp_error($status)) {
				$wpdb->query('ROLLBACK');  // phpcs:ignore WordPress.DB.DirectDatabaseQuery

				return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
			}
		} catch (\Throwable $exception) {
			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		/*
		 * Clean up the membership swap order.
		 */
		$membership->delete_scheduled_swap();

		$wpdb->query('COMMIT'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		return true;
	}

	/**
	 * Watches the change in payment status to take action when needed.
	 *
	 * @todo Publishing sites should be done in async.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $old_status The old status of the membership.
	 * @param string  $new_status The new status of the membership.
	 * @param integer $membership_id Payment ID.
	 * @return void
	 */
	public function transition_membership_status($old_status, $new_status, $membership_id): void {

		$allowed_previous_status = [
			Membership_Status::PENDING,
			Membership_Status::ON_HOLD,
		];

		if ( ! in_array($old_status, $allowed_previous_status, true)) {
			return;
		}

		$allowed_status = [
			Membership_Status::ACTIVE,
			Membership_Status::TRIALING,
		];

		if ( ! in_array($new_status, $allowed_status, true)) {
			return;
		}

		/*
		 * Create pending sites.
		 */
		$membership = wu_get_membership($membership_id);

		$membership->publish_pending_site_async();
	}

	/**
	 * Mark the membership date of cancellation.
	 *
	 * @since 2.0.0
	 *
	 * @param string $old_value Old status value.
	 * @param string $new_value New status value.
	 * @param int    $item_id The membership id.
	 * @return void
	 */
	public function mark_cancelled_date($old_value, $new_value, $item_id): void {

		if ('cancelled' === $new_value && $new_value !== $old_value) {
			$membership = wu_get_membership($item_id);

			$membership->set_date_cancellation(wu_get_current_time('mysql', true));

			$membership->save();
		}
	}

	/**
	 * Transfer a membership from a user to another.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id The ID of the membership being transferred.
	 * @param int $target_customer_id The new owner.
	 * @return mixed
	 */
	public function async_transfer_membership($membership_id, $target_customer_id) {

		global $wpdb;

		$membership = wu_get_membership($membership_id);

		$target_customer = wu_get_customer($target_customer_id);

		if ( ! $membership || ! $target_customer || absint($membership->get_customer_id()) === absint($target_customer->get_id())) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$wpdb->query('START TRANSACTION'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		try {
			/*
			 * Get Sites and move them over.
			 */
			$sites = wu_get_sites(
				[
					'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'membership_id' => [
							'key'   => 'wu_membership_id',
							'value' => $membership->get_id(),
						],
					],
				]
			);

			foreach ($sites as $site) {
				$site->set_customer_id($target_customer_id);

				$saved = $site->save();

				if (is_wp_error($saved)) {
					$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

					return $saved;
				}
			}

			/*
			 * Change the membership
			 */
			$membership->set_customer_id($target_customer_id);

			$saved = $membership->save();

			if (is_wp_error($saved)) {
				$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

				return $saved;
			}
		} catch (\Throwable $e) {
			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return new \WP_Error('exception', $e->getMessage());
		}

		$wpdb->query('COMMIT'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		$membership->unlock();

		return true;
	}

	/**
	 * Delete a membership.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id The ID of the membership being deleted.
	 * @return mixed
	 */
	public function async_delete_membership($membership_id) {

		global $wpdb;

		$membership = wu_get_membership($membership_id);

		if ( ! $membership) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$wpdb->query('START TRANSACTION'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		try {
			/*
			 * Get Sites and delete them.
			 */
			$sites = wu_get_sites(
				[
					'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'membership_id' => [
							'key'   => 'wu_membership_id',
							'value' => $membership->get_id(),
						],
					],
				]
			);

			foreach ($sites as $site) {
				$saved = $site->delete();

				if (is_wp_error($saved)) {
					$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

					return $saved;
				}
			}

			/*
			 * Delete the membership
			 */
			$saved = $membership->delete();

			if (is_wp_error($saved)) {
				$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

				return $saved;
			}
		} catch (\Throwable $e) {
			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return new \WP_Error('exception', $e->getMessage());
		}

		$wpdb->query('COMMIT'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		return true;
	}
}
