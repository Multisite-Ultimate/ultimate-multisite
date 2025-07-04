<?php
/**
 * Multisite Ultimate About Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate About Admin Page.
 */
class Placeholders_Admin_Page extends Base_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-template-placeholders';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * This page has no parent, so we need to highlight another sub-menu.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $highlight_menu_slug = 'wp-ultimo-settings';

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = [
		'network_admin_menu' => 'manage_network',
	];

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Edit Template Placeholders', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Template Placeholders', 'multisite-ultimate');
	}

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Edit Template Placeholders', 'multisite-ultimate');
	}

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output(): void {

		do_action('wu_load_edit_placeholders_list_page');

		$columns = apply_filters(
			'wu_edit_placeholders_columns',
			[
				'placeholder' => __('Placeholder', 'multisite-ultimate'),
				'content'     => __('Content', 'multisite-ultimate'),
			]
		);

		wu_get_template(
			'sites/edit-placeholders',
			[
				'columns' => $columns,
				'types'   => [],
			]
		);
	}

	/**
	 * Adds the cure bg image here as well.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		parent::register_scripts();

		wp_register_script('wu-edit-placeholders', wu_get_asset('edit-placeholders.js', 'js'), ['wu-admin', 'wu-vue', 'underscore'], wu_get_version(), false);

		wp_localize_script(
			'wu-edit-placeholders',
			'wu_placeholdersl10n',
			[
				'name'                                => __('Tax', 'multisite-ultimate'),
				'confirm_message'                     => __('Are you sure you want to delete this rows?', 'multisite-ultimate'),
				'confirm_delete_tax_category_message' => __('Are you sure you want to delete this tax category?', 'multisite-ultimate'),
			]
		);

		wp_enqueue_script('wu-edit-placeholders');
	}
}
