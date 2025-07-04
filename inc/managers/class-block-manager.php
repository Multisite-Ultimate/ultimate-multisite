<?php
/**
 * Block Manager
 *
 * Manages the registering of gutenberg blocks.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Block
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the ajax form registering, rendering, and permissions checking.
 *
 * @since 2.0.0
 */
class Block_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		global $wp_version;

		$hook = version_compare($wp_version, '5.8', '<') ? 'block_categories' : 'block_categories_all';

		add_filter($hook, [$this, 'add_wp_ultimo_block_category'], 1, 2);
	}

	/**
	 * Adds wp-ultimo as a Block category on Gutenberg.
	 *
	 * @since 2.0.0
	 *
	 * @param array    $categories List of categories.
	 * @param \WP_Post $post Post being edited.
	 * @return array
	 */
	public function add_wp_ultimo_block_category($categories, $post) {

		return array_merge(
			$categories,
			[
				[
					'slug'  => 'wp-ultimo',
					'title' => __('Multisite WaaS', 'multisite-ultimate'),
				],
			]
		);
	}
}
