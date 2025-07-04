<?php
/**
 * Adds the Tours UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Tours UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Tours {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Registered tours.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $tours = [];

	/**
	 * Element construct.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_action('wp_ajax_wu_mark_tour_as_finished', [$this, 'mark_as_finished']);

		add_action('admin_enqueue_scripts', [$this, 'register_scripts']);

		add_action('in_admin_footer', [$this, 'enqueue_scripts']);
	}

	/**
	 * Mark the tour as finished for a particular user.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function mark_as_finished(): void {

		check_ajax_referer('wu_tour_finished', 'nonce');

		$id = wu_request('tour_id');

		if ($id) {
			set_user_setting("wu_tour_$id", true);

			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Register the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		WP_Ultimo()->scripts->register_script('wu-shepherd', wu_get_asset('lib/shepherd.js', 'js'), []);

		WP_Ultimo()->scripts->register_script('wu-tours', wu_get_asset('tours.js', 'js'), ['wu-shepherd', 'underscore']);
	}

	/**
	 * Enqueues the scripts, if we need to.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_scripts(): void {

		if ($this->has_tours()) {
			wp_localize_script('wu-tours', 'wu_tours', $this->tours);

			wp_localize_script(
				'wu-tours',
				'wu_tours_vars',
				[
					'ajaxurl' => wu_ajax_url(),
					'nonce'   => wp_create_nonce('wu_tour_finished'),
					'i18n'    => [
						'next'   => __('Next', 'multisite-ultimate'),
						'finish' => __('Close', 'multisite-ultimate'),
					],
				]
			);

			wp_enqueue_script('wu-tours');
		}
	}

	/**
	 * Checks if we have registered tours.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_tours() {

		return ! empty($this->tours);
	}

	/**
	 * Register a new tour.
	 *
	 * @see https://shepherdjs.dev/docs/
	 *
	 * @since 2.0.0
	 *
	 * @param string  $id The id of the tour.
	 * @param array   $steps The tour definition. Check shepherd.js docs.
	 * @param boolean $once Whether or not we will show this more than once.
	 * @return void
	 */
	public function create_tour($id, $steps = [], $once = true): void {

		if (did_action('in_admin_header')) {
			return;
		}

		add_action(
			'in_admin_header',
			function () use ($id, $steps, $once) {

				$force_hide = wu_get_setting('hide_tours', false);

				if ($force_hide) {
					return;
				}

				$finished = (bool) get_user_setting("wu_tour_$id", false);

				$finished = apply_filters('wu_tour_finished', $finished, $id, get_current_user_id());

				if ( ! $finished || ! $once) {
					foreach ($steps as &$step) {
						$step['text'] = is_array($step['text']) ? implode('</p><p>', $step['text']) : $step['text'];

						$step['text'] = sprintf('<p>%s</p>', $step['text']);
					}

					$this->tours[ $id ] = $steps;
				}
			}
		);
	}
}
