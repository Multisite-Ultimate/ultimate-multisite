<?php
/**
 * Multisite Ultimate Event View Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Models\Event;

/**
 * Multisite Ultimate Event View Admin Page.
 */
class Event_View_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-view-event';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Object ID being edited.
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id = 'event';

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
	protected $highlight_menu_slug = 'wp-ultimo-events';

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
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = [
		'network_admin_menu' => 'wu_read_events',
	];

	/**
	 * Allow child classes to register scripts and styles that can be loaded on the output function, for example.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_scripts(): void {

		parent::register_scripts();

		\WP_Ultimo\Scripts::get_instance()->register_script('wu-event-view', wu_get_asset('event-view-page.js', 'js'), ['jquery']);

		wp_enqueue_script('wu-event-view');

		wp_enqueue_script('clipboard');

		wp_enqueue_script('wu-vue');
	}

	/**
	 * Register ajax forms that we use for membership.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {
		/*
		 * Delete Event - Confirmation modal
		 */

		add_filter(
			'wu_data_json_success_delete_event_modal',
			fn($data_json) => [
				'redirect_url' => wu_network_admin_url('wp-ultimo-events', ['deleted' => 1]),
			]
		);
	}

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets(): void {

		parent::register_widgets();

		add_meta_box('wp-ultimo-message', __('Event Message', 'multisite-ultimate'), [$this, 'output_default_widget_message'], get_current_screen()->id, 'normal', 'default');

		add_meta_box('wp-ultimo-initiator', __('Event', 'multisite-ultimate'), [$this, 'output_default_widget_initiator'], get_current_screen()->id, 'side', 'default');

		add_meta_box('wp-ultimo-payload', __('Event Payload', 'multisite-ultimate'), [$this, 'output_default_widget_payload'], get_current_screen()->id, 'normal', 'default');

		$this->add_info_widget(
			'info',
			[
				'title'    => __('Timestamps', 'multisite-ultimate'),
				'position' => 'side',
				'modified' => false,
			]
		);
	}

	/**
	 * Outputs the markup for the default Save widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_message(): void {

		wu_get_template(
			'events/widget-message',
			[
				'screen' => get_current_screen(),
				'page'   => $this,
				'labels' => $this->get_labels(),
				'object' => $this->get_object(),
			]
		);
	}

	/**
	 * Outputs the markup for the payload widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_payload(): void {

		$object = $this->get_object();

		wu_get_template(
			'events/widget-payload',
			[
				'title'        => __('Event Payload', 'multisite-ultimate'),
				'loading_text' => __('Loading Payload', 'multisite-ultimate'),
				'payload'      => wp_json_encode($object->get_payload(), JSON_PRETTY_PRINT),
			]
		);
	}

	/**
	 * Outputs the markup for the initiator widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_initiator(): void {

		$object = $this->get_object();

		$args = [
			'object' => $object,
		];

		wu_get_template('events/widget-initiator', $args);
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Event', 'multisite-ultimate') : __('Add new Event', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Event', 'multisite-ultimate');
	}

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return [];
	}

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return [
			'edit_label'          => __('Edit Event', 'multisite-ultimate'),
			'add_new_label'       => __('Add new Event', 'multisite-ultimate'),
			'updated_message'     => __('Event updated with success!', 'multisite-ultimate'),
			'title_placeholder'   => __('Enter Event', 'multisite-ultimate'),
			'title_description'   => '',
			'save_button_label'   => __('Save Event', 'multisite-ultimate'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Event', 'multisite-ultimate'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'multisite-ultimate'),
		];
	}

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Event
	 */
	public function get_object() {

		if (isset($_GET['id'])) { // phpcs:ignore WordPress.Security.NonceVerification
			$query = new \WP_Ultimo\Database\Events\Event_Query();

			$item = $query->get_item_by('id', (int) $_GET['id']); // phpcs:ignore WordPress.Security.NonceVerification

			if ($item) {
				return $item;
			}
		}

		wp_safe_redirect(wu_network_admin_url('wp-ultimo-events'));

		exit;
	}

	/**
	 * Events have titles.
	 *
	 * @since 2.0.0
	 */
	public function has_title(): bool {

		return false;
	}

	/**
	 * Handles the save of this form.
	 *
	 * @since 2.0.0
	 */
	public function handle_save(): bool {

		return true;
	}
}
