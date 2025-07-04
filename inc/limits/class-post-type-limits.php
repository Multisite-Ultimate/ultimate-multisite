<?php
/**
 * Handles limitations to post types, uploads and more.
 *
 * @todo We need to move posts on downgrade.
 * @package WP_Ultimo
 * @subpackage Limits
 * @since 2.0.0
 */

namespace WP_Ultimo\Limits;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles limitations to post types, uploads and more.
 *
 * @since 2.0.0
 */
class Post_Type_Limits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		/**
		 * Emulated post types.
		 *
		 * @since 2.0.6
		 */
		if (is_main_site() && is_network_admin()) {
			add_action('init', [$this, 'register_emulated_post_types'], 999);
		}

		/**
		 * Allow plugin developers to short-circuit the limitations.
		 *
		 * You can use this filter to run arbitrary code before any of the limits get initiated.
		 * If you filter returns any truthy value, the process will move on, if it returns any falsy value,
		 * the code will return and none of the hooks below will run.
		 *
		 * @since 1.7.0
		 * @return bool
		 */
		if ( ! apply_filters('wu_apply_plan_limits', wu_get_current_site()->has_limitations())) {
			return;
		}

		if ( ! wu_get_current_site()->has_module_limitation('post_types')) {
			return;
		}

		add_action('load-post-new.php', [$this, 'limit_posts']);

		add_filter('wp_handle_upload', [$this, 'limit_media']);

		add_filter('media_upload_tabs', [$this, 'limit_tabs']);

		add_action('current_screen', [$this, 'limit_restoring'], 10);

		add_filter('wp_insert_post_data', [$this, 'limit_draft_publishing'], 10, 2);
	}

	/**
	 * Emulates post types to avoid having to have plugins active on the main site.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function register_emulated_post_types(): void {

		$emulated_post_types = wu_get_setting('emulated_post_types', []);

		if (is_array($emulated_post_types) && ! empty($emulated_post_types)) {
			foreach ($emulated_post_types as $pt) {
				$pt = (object) $pt;

				$existing_pt = get_post_type_object($pt->post_type);

				if ($existing_pt) {
					continue;
				}

				register_post_type(
					$pt->post_type,
					[
						'label'               => $pt->label,
						'exclude_from_search' => true,
						'public'              => true,
						'show_in_menu'        => false,
						'has_archive'         => false,
						'can_export'          => false,
						'delete_with_user'    => false,
					]
				);
			}
		}
	}

	/**
	 * Prevents users from trashing posts and restoring them later to bypass the limitation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function limit_restoring(): void {

		// nonce is checked in a different action.
		if (isset($_REQUEST['action']) && 'untrash' === $_REQUEST['action']) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->limit_posts();
		}
	}

	/**
	 * Limit the posts after the user reach his plan limits
	 *
	 * @since 1.0.0
	 * @since 1.5.4 Checks for blocked post types
	 */
	public function limit_posts(): void {

		if (is_main_site()) {
			return;
		}

		$screen = get_current_screen();

		if ( ! wu_get_current_site()->get_limitations()->post_types->{$screen->post_type}->enabled) {
			$upgrade_message = __('Your plan does not support this post type.', 'multisite-ultimate');

			wp_die(esc_html($upgrade_message), esc_html(__('Limit Reached', 'multisite-ultimate')), ['back_link' => true]);
		}

		// Check if that is more than our limit
		if (wu_get_current_site()->get_limitations()->post_types->is_post_above_limit($screen->post_type)) {
			$upgrade_message = __('You reached your plan\'s post limit.', 'multisite-ultimate');

			wp_die(esc_html($upgrade_message), esc_html__('Limit Reached', 'multisite-ultimate'), ['back_link' => true]);
		}
	}

	/**
	 * Checks if the user is trying to publish a draft post.
	 *
	 * If that's the case, only allow him to do it if the post count is not above the quota.
	 *
	 * @since 1.7.0
	 * @param array $data Info being saved on posts.
	 * @param array $modified_data Data that is changing. We are interested in publish.
	 * @return array
	 */
	public function limit_draft_publishing($data, $modified_data) {

		global $current_screen;

		if (empty($current_screen)) {
			return $data;
		}

		if (get_post_status($modified_data['ID']) === 'publish') {
			return $data; // If the post is already published, no need to make changes

		}

		if (isset($data['post_status']) && 'publish' !== $data['post_status']) {
			return $data;
		}

		$post_type = $data['post_type'] ?? 'post';

		$post_type_limits = wu_get_current_site()->get_limitations()->post_types;

		if ( ! $post_type_limits->{$current_screen->post_type}->enabled || $post_type_limits->is_post_above_limit($post_type)) {
			$data['post_status'] = 'draft';
		}

		return $data;
	}

	/**
	 * Limits uploads of items to the media library.
	 *
	 * @since 2.0.0
	 *
	 * @param array $file $_FILE array being passed.
	 * @return mixed
	 */
	public function limit_media($file) {

		if ( ! wu_get_current_site()->get_limitations()->post_types->attachment->enabled) {
			$file['error'] = __('Your plan does not support media upload.', 'multisite-ultimate');

			return $file;
		}

		$post_count = wp_count_posts('attachment');

		$post_count = $post_count->inherit;

		$quota = wu_get_current_site()->get_limitations()->post_types->attachment->number;

		// This bit is for the flash uploader
		if ('application/octet-stream' === $file['type'] && isset($file['tmp_name'])) {
			$file_size = getimagesize($file['tmp_name']);

			if (isset($file_size['error']) && 0 !== $file_size['error']) {
				$file['error'] = "Unexpected Error: {$file_size['error']}";

				return $file;
			} else {
				$file['type'] = $file_size['mime'];
			}
		}

		if ($quota > 0 && $post_count >= $quota) {

			// translators: %d is the number of images allowed.
			$file['error'] = sprintf(__('You reached your media upload limit of %d images. Upgrade your account to unlock more media uploads.', 'multisite-ultimate'), $quota, '#');
		}

		return $file;
	}

	/**
	 * Remove the upload tabs if the quota is over.
	 *
	 * @since 2.0.0
	 *
	 * @param array $tabs Tabs of the media gallery upload modal.
	 * @return array
	 */
	public function limit_tabs($tabs) {

		$post_count = wp_count_posts('attachment');

		$post_count = $post_count->inherit;

		$quota = wu_get_current_site()->get_limitations()->post_types->attachment->number;

		if ($quota > 0 && $post_count > $quota) {
			unset($tabs['type']);

			unset($tabs['type_url']);
		}

		return $tabs;
	}
}
