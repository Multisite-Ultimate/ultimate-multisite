<?php
/**
 * Multisite Ultimate Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.24
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Dashboard Admin Page.
 */
class Migration_Alert_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-migration-alert';

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
	 * @since 2.0.24
	 * @var string
	 */
	protected $highlight_menu_slug = 'wp-ultimo-settings';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.24
	 * @var array
	 */
	protected $supported_panels = [
		'network_admin_menu' => 'manage_network',
	];

	/**
	 * Returns the logo for the wizard.
	 *
	 * @since 2.0.24
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('logo.webp', 'img');
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.24
	 * @return string Title of the page.
	 */
	public function get_title(): string {

		return sprintf(__('Migration', 'multisite-ultimate'));
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.24
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return WP_Ultimo()->is_loaded() ? __('Multisite Ultimate Migration Alert', 'multisite-ultimate') : __('Multisite Ultimate', 'multisite-ultimate');
	}

	/**
	 * Returns the sections for this Wizard.
	 *
	 * @since 2.0.24
	 * @return array
	 */
	public function get_sections() {

		return [
			'alert' => [
				'title'   => __('Alert!', 'multisite-ultimate'),
				'view'    => [$this, 'section_alert'],
				'handler' => [$this, 'handle_proceed'],
			],
		];
	}

	/**
	 * Displays the content of the final section.
	 *
	 * @since 2.0.24
	 * @return void
	 */
	public function section_alert(): void {

		wu_get_template(
			'wizards/setup/alert',
			[
				'screen' => get_current_screen(),
				'page'   => $this,
			]
		);
	}

	/**
	 * Handles the proceed action.
	 *
	 * @since 2.0.24
	 * @return void
	 */
	public function handle_proceed(): void {

		delete_network_option(null, 'wu_setup_finished');
		delete_network_option(null, 'wu_is_migration_done');

		wp_safe_redirect(wu_network_admin_url('wp-ultimo-setup'));

		exit;
	}
}
