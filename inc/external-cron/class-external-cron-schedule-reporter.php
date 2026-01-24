<?php
/**
 * External Cron Schedule Reporter
 *
 * Reports WordPress cron schedules to the External Cron Service.
 *
 * @package WP_Ultimo
 * @subpackage External_Cron
 * @since 2.3.0
 */

namespace WP_Ultimo\External_Cron;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * External Cron Schedule Reporter class.
 *
 * @since 2.3.0
 */
class External_Cron_Schedule_Reporter {

	/**
	 * Service client instance.
	 *
	 * @var External_Cron_Service_Client
	 */
	private External_Cron_Service_Client $client;

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 * @param External_Cron_Service_Client $client Service client.
	 */
	public function __construct(External_Cron_Service_Client $client) {

		$this->client = $client;
	}

	/**
	 * Report all schedules to the service.
	 *
	 * @since 2.3.0
	 */
	public function report_all_schedules(): void {

		$granularity = wu_get_setting('external_cron_granularity', 'network');

		if ('network' === $granularity) {
			$this->report_network_schedules();
		} else {
			$this->report_per_site_schedules();
		}
	}

	/**
	 * Report schedules for the entire network (single trigger).
	 *
	 * @since 2.3.0
	 */
	private function report_network_schedules(): void {

		$site_id = wu_get_setting('external_cron_site_id');

		if (empty($site_id)) {
			return;
		}

		// Get schedules from main site.
		$schedules = $this->get_site_schedules(get_main_site_id());

		// Include Action Scheduler jobs.
		$as_schedules = $this->get_action_scheduler_jobs();
		$schedules    = array_merge($schedules, $as_schedules);

		// Send to service.
		$this->client->update_schedules((int) $site_id, $schedules);
	}

	/**
	 * Report schedules for each subsite individually.
	 *
	 * @since 2.3.0
	 */
	private function report_per_site_schedules(): void {

		$sites = get_sites(
			[
				'number'   => 0,
				'fields'   => 'ids',
				'archived' => 0,
				'deleted'  => 0,
			]
		);

		foreach ($sites as $blog_id) {
			$this->report_site_schedules($blog_id);
		}
	}

	/**
	 * Report schedules for a specific site.
	 *
	 * @since 2.3.0
	 * @param int $blog_id Blog ID.
	 */
	private function report_site_schedules(int $blog_id): void {

		// Get the registered site ID for this blog.
		$site_id = get_blog_option($blog_id, 'external_cron_site_id');

		if (empty($site_id)) {
			return;
		}

		$schedules = $this->get_site_schedules($blog_id);

		// Send to service.
		$this->client->update_schedules((int) $site_id, $schedules);
	}

	/**
	 * Get cron schedules for a specific site.
	 *
	 * @since 2.3.0
	 * @param int $blog_id Blog ID.
	 * @return array
	 */
	private function get_site_schedules(int $blog_id): array {

		switch_to_blog($blog_id);

		$crons     = _get_cron_array();
		$schedules = [];

		if (empty($crons)) {
			restore_current_blog();
			return $schedules;
		}

		$wp_schedules = wp_get_schedules();

		foreach ($crons as $timestamp => $cron_hooks) {
			foreach ($cron_hooks as $hook => $events) {
				foreach ($events as $key => $event) {
					$recurrence = $event['schedule'] ?? null;
					$interval   = null;

					if ($recurrence && isset($wp_schedules[ $recurrence ])) {
						$interval = $wp_schedules[ $recurrence ]['interval'];
					}

					$schedules[] = [
						'hook_name'        => $hook,
						'next_run'         => gmdate('Y-m-d H:i:s', $timestamp),
						'recurrence'       => $recurrence,
						'interval_seconds' => $interval,
						'args'             => wp_json_encode($event['args'] ?? []),
					];
				}
			}
		}

		restore_current_blog();

		return $schedules;
	}

	/**
	 * Get Action Scheduler pending jobs.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	private function get_action_scheduler_jobs(): array {

		if (! function_exists('wu_get_scheduled_actions')) {
			return [];
		}

		$actions = wu_get_scheduled_actions(
			[
				'status'   => \ActionScheduler_Store::STATUS_PENDING,
				'per_page' => 500,
			]
		);

		$schedules = [];

		foreach ($actions as $action) {
			$schedule = $action->get_schedule();

			if (! $schedule || ! $schedule->get_date()) {
				continue;
			}

			$is_recurring = method_exists($schedule, 'get_recurrence') && $schedule->get_recurrence();

			$schedules[] = [
				'hook_name'        => $action->get_hook(),
				'next_run'         => $schedule->get_date()->format('Y-m-d H:i:s'),
				'recurrence'       => $is_recurring ? 'recurring' : null,
				'interval_seconds' => $is_recurring ? $schedule->get_recurrence() : null,
				'args'             => wp_json_encode($action->get_args()),
			];
		}

		return $schedules;
	}

	/**
	 * Get count of scheduled jobs.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	public function get_schedule_count(): int {

		$schedules    = $this->get_site_schedules(get_main_site_id());
		$as_schedules = $this->get_action_scheduler_jobs();

		return count($schedules) + count($as_schedules);
	}
}
