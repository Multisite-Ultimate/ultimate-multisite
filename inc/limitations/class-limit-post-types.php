<?php
/**
 * Post Types Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Post Types Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Post_Types extends Limit_Subtype {

	/**
	 * The module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'post_types';

	/**
	 * Check if we are already above the post quota.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type to check against.
	 * @return boolean
	 */
	public function is_post_above_limit($post_type) {
		/*
		 * Calculate post count based on all different status
		 */
		$post_count = static::get_post_count($post_type);

		// Get the allowed quota
		$quota = $this->{$post_type}->number;

		/**
		 * Checks if a given post type is allowed on this plan
		 * Allow plugin developers to filter the return value
		 *
		 * @since 1.7.0
		 * @param bool $value If the post type is disabled or not
		 */
		return apply_filters('wu_limits_is_post_above_limit', $quota > 0 && $post_count >= $quota);
	}

	/**
	 * Checks if any post types are currently over the limit.
	 *
	 * @return array
	 */
	public function check_all_post_types() {
		$overlimits = [];
		if ( empty($this->limit)) {
			return $overlimits;
		}
		foreach ($this->limit as $post_type => $limit) {

			// We don't reuse is_post_above_limit because we want to see it checks with >= and we only want to use >.
			$post_count = $this->get_post_count($post_type);

			// Get the allowed quota
			$quota = $limit['number'];

			/**
			 * Checks if a given post type is allowed on this plan
			 * Allow plugin developers to filter the return value
			 *
			 * @since 1.7.0
			 * @param bool $value If the post type is disabled or not
			 */
			$is_above_limit = apply_filters('wu_limits_is_post_above_limit', $quota > 0 && ($post_count) > $quota);

			if ($is_above_limit) {
				$overlimits[ $post_type ] = [
					'current' => $post_count,
					'limit'   => (int) $limit['number'],
				];
			}
		}
		return $overlimits;
	}

	/**
	 * Get the post count for this site.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type to check against.
	 * @return int
	 */
	public static function get_post_count($post_type) {

		$count = 0;

		$post_count = wp_count_posts($post_type);

		$statuses = 'attachment' === $post_type ? ['inherit'] : ['publish', 'private'];

		/**
		 * Allow plugin developers to change which post status should be counted
		 * By default, published and private posts are counted
		 *
		 * @since 1.9.1
		 * @param array $post_status The list of post statuses
		 * @param string $post_type  The post type slug
		 * @return array New array of post status
		 */
		$post_statuses = apply_filters('wu_post_count_statuses', $statuses, $post_type);

		foreach ($post_statuses as $post_status) {
			if (isset($post_count->{$post_status})) {
				$count += (int) $post_count->{$post_status};
			}
		}

		/**
		 * Allow plugin developers to change the count total
		 *
		 * @since 1.9.1
		 * @param int $count The total post count
		 * @param object $post_counts WordPress object return by the wp_count_posts fn
		 * @param string $post_type  The post type slug
		 * @return int New total
		 */
		return apply_filters('wu_post_count', $count, $post_count, $post_type);
	}
}
