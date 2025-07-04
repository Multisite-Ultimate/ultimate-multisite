<?php
/**
 * Multisite Ultimate Webhook Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Webhook Admin Page.
 */
class Webhook_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-webhooks';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

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
		'network_admin_menu' => 'wu_read_webhooks',
	];

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		parent::register_scripts();

		wp_register_script('wu-webhook-page', wu_get_asset('webhook-page.js', 'js'), ['jquery', 'wu-sweet-alert'], \WP_Ultimo::VERSION, true);

		wp_localize_script(
			'wu-webhook-page',
			'wu_webhook_page',
			[
				'nonce' => wp_create_nonce('wu_webhook_send_test'),
				'i18n'  => [
					'error_title'   => __('Webhook Test', 'multisite-ultimate'),
					'error_message' => __('An error occurred when sending the test webhook, please try again.', 'multisite-ultimate'),
					'copied'        => __('Copied!', 'multisite-ultimate'),
				],
			]
		);

		wp_enqueue_script('wu-webhook-page');
	}

	/**
	 * Register ajax forms that we use for add new webhooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {
		/*
		 * Add new webhook.
		 */
		wu_register_form(
			'add_new_webhook_modal',
			[
				'render'     => [$this, 'render_add_new_webhook_modal'],
				'handler'    => [$this, 'handle_add_new_webhook_modal'],
				'capability' => 'wu_edit_webhooks',
			]
		);
	}

	/**
	 * Renders the add new webhook modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function render_add_new_webhook_modal(): void {

		$events = wu_get_event_types();

		$event_options = [];

		foreach ($events as $slug => $event) {
			$event_options[ $slug ] = $event['name'];
		}

		$fields = [
			'name'          => [
				'type'        => 'text',
				'title'       => __('Webhook Name', 'multisite-ultimate'),
				'desc'        => __('A name to easily identify your webhook.', 'multisite-ultimate'),
				'placeholder' => __('E.g. Zapier Integration', 'multisite-ultimate'),
			],
			'event'         => [
				'title'   => __('Event', 'multisite-ultimate'),
				'type'    => 'select',
				'desc'    => __('The event that will trigger the webhook.', 'multisite-ultimate'),
				'options' => $event_options,
			],
			'webhook_url'   => [
				'type'        => 'url',
				'title'       => __('Webhook Url', 'multisite-ultimate'),
				'desc'        => __('The url of your webhook.', 'multisite-ultimate'),
				'placeholder' => __('E.g. https://example.com/', 'multisite-ultimate'),
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Add New Webhook', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					// 'v-bind:disabled' => '!confirmed',
				],
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'edit_line_item',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'edit_line_item',
					'data-state'  => wp_json_encode(
						[
							'event' => '',
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the add new webhook modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_new_webhook_modal(): void {

		// Nonce check handled in calling method.
		$status = wu_create_webhook($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if (is_wp_error($status)) {
			wp_send_json_error($status);
		} else {
			wp_send_json_success(
				[
					'redirect_url' => wu_network_admin_url(
						'wp-ultimo-edit-webhook',
						[
							'id' => $status->get_id(),
						]
					),
				]
			);
		}
	}

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {}

	/**
	 * Returns an array with the labels for the edit page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function get_labels() {

		return [
			'deleted_message' => __('Webhook removed successfully.', 'multisite-ultimate'),
			'search_label'    => __('Search Webhook', 'multisite-ultimate'),
		];
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Webhooks', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Webhooks', 'multisite-ultimate');
	}

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Webhooks', 'multisite-ultimate');
	}

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return [
			[
				'label'   => __('Add New Webhook', 'multisite-ultimate'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_webhook_modal'),
			],
		];
	}


	/**
	 * Loads the list table for this particular page.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\List_Tables\Base_List_Table
	 */
	public function table() {

		return new \WP_Ultimo\List_Tables\Webhook_List_Table();
	}
}
