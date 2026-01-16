<?php
/**
 * Rating Notice Manager
 *
 * Displays a friendly reminder to rate Ultimate Multisite on WordPress.org
 * after 30 days of installation.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Rating_Notice_Manager
 * @since 2.4.10
 */

namespace WP_Ultimo\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the rating reminder notice.
 *
 * @since 2.4.10
 */
class Rating_Notice_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The network option key for storing installation timestamp.
	 *
	 * @var string
	 */
	const INSTALLATION_TIMESTAMP_OPTION = 'wu_installation_timestamp';

	/**
	 * The dismissible key for the rating notice.
	 *
	 * @var string
	 */
	const NOTICE_DISMISSIBLE_KEY = 'wu_rating_reminder';

	/**
	 * Number of days to wait before showing the notice.
	 *
	 * @var int
	 */
	const DAYS_BEFORE_NOTICE = 30;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.4.10
	 * @return void
	 */
	public function init(): void {

		add_action('wu_activation', [$this, 'store_installation_timestamp']);

		add_action('admin_init', [$this, 'maybe_add_rating_notice']);
	}

	/**
	 * Stores the installation timestamp on plugin activation.
	 *
	 * Only stores the timestamp if it hasn't been set before,
	 * so re-activations don't reset the timer.
	 *
	 * @since 2.4.10
	 * @return void
	 */
	public function store_installation_timestamp(): void {

		$existing_timestamp = get_network_option(null, self::INSTALLATION_TIMESTAMP_OPTION);

		if (empty($existing_timestamp)) {
			update_network_option(null, self::INSTALLATION_TIMESTAMP_OPTION, time());
		}
	}

	/**
	 * Checks if the rating notice should be displayed and adds it.
	 *
	 * @since 2.4.10
	 * @return void
	 */
	public function maybe_add_rating_notice(): void {

		if (! is_network_admin()) {
			return;
		}

		if (! $this->should_show_notice()) {
			return;
		}

		$this->add_rating_notice();
	}

	/**
	 * Determines if the rating notice should be shown.
	 *
	 * @since 2.4.10
	 * @return bool
	 */
	protected function should_show_notice(): bool {

		$installation_timestamp = get_network_option(null, self::INSTALLATION_TIMESTAMP_OPTION);

		if (empty($installation_timestamp)) {
			return false;
		}

		$days_since_installation = (time() - $installation_timestamp) / DAY_IN_SECONDS;

		return $days_since_installation >= self::DAYS_BEFORE_NOTICE;
	}

	/**
	 * Adds the rating reminder notice.
	 *
	 * @since 2.4.10
	 * @return void
	 */
	protected function add_rating_notice(): void {

		$review_url = 'https://wordpress.org/support/plugin/developer/reviews/#new-post';

		$message = sprintf(
			/* translators: %1$s opening strong tag, %2$s closing strong tag, %3$s review link opening tag, %4$s link closing tag */
			__('Hey there! You\'ve been using %1$sUltimate Multisite%2$s for a while now. If it\'s been helpful for your network, we\'d really appreciate a quick review on WordPress.org. Your feedback helps other users discover the plugin and motivates us to keep improving it. %3$sLeave a review%4$s', 'ultimate-multisite'),
			'<strong>',
			'</strong>',
			'<a href="' . esc_url($review_url) . '" target="_blank" rel="noopener">',
			' &rarr;</a>'
		);

		$actions = [
			[
				'title' => __('Leave a Review', 'ultimate-multisite'),
				'url'   => $review_url,
			],
		];

		\WP_Ultimo()->notices->add(
			$message,
			'info',
			'network-admin',
			self::NOTICE_DISMISSIBLE_KEY,
			$actions
		);
	}
}
