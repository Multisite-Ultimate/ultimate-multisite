<?php
/**
 * Multisite Ultimate My_Account Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Admin_Pages\Base_Customer_Facing_Admin_Page;

/**
 * Multisite Ultimate My_Account Admin Page.
 */
class Account_Admin_Page extends Base_Customer_Facing_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'account';

	/**
	 * Menu position. This is only used for top-level menus
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $position = 101_010_101;

	/**
	 * Dashicon to be used on the menu item. This is only used on top-level menus
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $menu_icon = 'dashicons-wu-email';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Should we hide admin notices on this page?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hide_admin_notices = true;

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
		'admin_menu'      => 'exist',
		'user_admin_menu' => 'exist',
	];

	/**
	 * The current site instance.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Site
	 */
	protected $current_site;

	/**
	 * The current membership instance.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Membership
	 */
	protected $current_membership;

	/**
	 * Checks if we need to add this page.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->current_site = wu_get_current_site();

		$this->current_membership = $this->current_site->get_membership();

		$this->register_page_settings();

		if ($this->current_site->get_type() === 'customer_owned') {
			parent::__construct();
		}
	}

	/**
	 * Loads the current site and membership.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function page_loaded(): void {

		$this->current_site = wu_get_current_site();

		$this->current_membership = $this->current_site->get_membership();

		$this->add_notices();
	}

	/**
	 * Adds notices after a membership is changed.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function add_notices() {

		$nonce = wu_request('nonce');

		$update_type = wu_request('updated');

		if (empty($update_type)) {
			return;
		}

		$update_message = apply_filters('wu_account_update_message', __('Your account was successfully updated.', 'multisite-ultimate'), $update_type);

		WP_Ultimo()->notices->add($update_message);
	}

	/**
	 * Allow child classes to add hooks to be run once the page is loaded.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
	 * @since 1.8.2
	 * @return void
	 */
	public function hooks() {}

	/**
	 * Allow child classes to add screen options; Useful for pages that have list tables.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function screen_options() {}

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets(): void {

		\WP_Ultimo\UI\Current_Membership_Element::get_instance()->as_metabox(get_current_screen()->id);

		\WP_Ultimo\UI\Billing_Info_Element::get_instance()->as_metabox(get_current_screen()->id, 'side');

		\WP_Ultimo\UI\Invoices_Element::get_instance()->as_metabox(get_current_screen()->id, 'side');

		\WP_Ultimo\UI\Site_Actions_Element::get_instance()->as_metabox(get_current_screen()->id, 'side');

		\WP_Ultimo\UI\Account_Summary_Element::get_instance()->as_metabox(get_current_screen()->id);

		\WP_Ultimo\UI\Limits_Element::get_instance()->as_metabox(get_current_screen()->id);

		\WP_Ultimo\UI\Domain_Mapping_Element::get_instance()->as_metabox(get_current_screen()->id, 'side');

		\WP_Ultimo\UI\Login_Form_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes');

		\WP_Ultimo\UI\Simple_Text_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes');

		\WP_Ultimo\UI\Current_Site_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes', ['show_admin_link' => false]);
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Account', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Account', 'multisite-ultimate');
	}

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Account', 'multisite-ultimate');
	}

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output(): void {
		/*
		 * Renders the base edit page layout, with the columns and everything else =)
		 */
		wu_get_template(
			'base/dash',
			[
				'screen'            => get_current_screen(),
				'page'              => $this,
				'has_full_position' => false,
			]
		);
	}
}
