<?php
/**
 * Multisite Ultimate Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Dashboard Admin Page.
 */
class Customer_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-customers';

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
	 * Initializes the class
	 *
	 * @since 2.1
	 * @return void
	 */
	public function init(): void {

		parent::init();

		add_action('plugins_loaded', [$this, 'export_customers']);
	}

	/**
	 * Export customers in .csv file
	 *
	 * @since 2.1
	 * @return void
	 */
	public function export_customers(): void {

		if (wu_request('wu_action') !== 'wu_export_customers') {
			return;
		}

		if ( ! wp_verify_nonce(wu_request('nonce'), 'wu_export_customers')) {
			wp_die(esc_html__('You do not have permissions to access this file.', 'multisite-ultimate'));
		}

		$customer_data = array_map(
			function ($customer) {

				$memberships = $customer->get_memberships();

				$membership_amount = count($memberships);

				$memberships_ids = array_map(fn($membership) => $membership->get_id(), $memberships);

				$billing_address = array_map(fn($field) => $field['value'], $customer->get_billing_address()->get_fields());

				return array_merge(
					[
						$customer->get_id(),
						$customer->get_user_id(),
						$customer->get_hash(),
						$customer->get_email_verification(),
						$customer->get_user()->user_email,
						$customer->has_trialed(),
						$customer->get_last_ip(),
						$customer->is_vip(),
						$customer->get_signup_form(),
						$membership_amount,
						implode('|', $memberships_ids),
					],
					$billing_address,
					[
						$customer->get_last_login(),
						$customer->get_date_registered(),
					]
				);
			},
			wu_get_customers()
		);

		$billing_fields = array_keys(\WP_Ultimo\Objects\Billing_Address::fields());

		$headers = array_merge(
			[
				'id',
				'user_id',
				'customer_hash',
				'email_verification',
				'user_email',
				'has_trialed',
				'customer_last_ip',
				'vip',
				'signup_form',
				'membership_amount',
				'membership_ids',
			],
			$billing_fields,
			[
				'last_login',
				'date_registered',
			]
		);

		$file_name = sprintf('wp-ultimo-customers-(%s)', gmdate('Y-m-d', wu_get_current_time('timestamp')));

		wu_generate_csv($file_name, array_merge([$headers], $customer_data));

		die;
	}

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
		'network_admin_menu' => 'wu_read_customers',
	];

	/**
	 * Register ajax forms that we use for payments.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {
		/*
		 * Add new Customer
		 */
		wu_register_form(
			'add_new_customer',
			[
				'render'     => [$this, 'render_add_new_customer_modal'],
				'handler'    => [$this, 'handle_add_new_customer_modal'],
				'capability' => 'wu_invite_customers',
			]
		);
	}

	/**
	 * Renders the add new customer modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_new_customer_modal(): void {

		$fields = [
			'type'          => [
				'type'      => 'tab-select',
				'html_attr' => [
					'v-model' => 'type',
				],
				'options'   => [
					'existing' => __('Existing User', 'multisite-ultimate'),
					'new'      => __('Invite New', 'multisite-ultimate'),
				],
			],
			'user_id'       => [
				'type'              => 'model',
				'title'             => __('Existing User', 'multisite-ultimate'),
				'placeholder'       => __('Search WordPress user...', 'multisite-ultimate'),
				'tooltip'           => '',
				'min'               => 1,
				'wrapper_html_attr' => [
					'v-show' => "require('type', 'existing')",
				],
				'html_attr'         => [
					'data-model'        => 'user',
					'data-value-field'  => 'ID',
					'data-label-field'  => 'display_name',
					'data-search-field' => 'display_name',
					'data-max-items'    => 1,
				],
			],
			'username'      => [
				'type'              => 'text',
				'title'             => __('Username', 'multisite-ultimate'),
				'placeholder'       => __('E.g. johnsmith', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show' => "require('type', 'new')",
				],
			],
			'email_address' => [
				'type'              => 'email',
				'title'             => __('Email Address', 'multisite-ultimate'),
				'placeholder'       => __('E.g. customer@wpultimo.dev', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show' => "require('type', 'new')",
				],
			],
			'set_password'  => [
				'type'              => 'toggle',
				'title'             => __('Set Password', 'multisite-ultimate'),
				'desc'              => __('If not set, the user will be asked to set a password after accepting the invite.', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show' => "require('type', 'new')",
				],
				'html_attr'         => [
					'v-model' => 'set_password',
				],
			],
			'password'      => [
				'type'              => 'password',
				'title'             => __('Password', 'multisite-ultimate'),
				'placeholder'       => __('E.g. p@$$w0rd', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show' => "require('type', 'new') && require('set_password', true)",
				],
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Create Customer', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					// 'v-bind:disabled' => '!confirmed',
				],
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'add_new_customer',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'add_new_customer',
					'data-state'  => wp_json_encode(
						[
							'set_password' => false,
							'type'         => 'existing',
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles creation of a new customer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_new_customer_modal(): void {

		if (wu_request('type', 'existing') === 'new') {
			$customer_data = [
				'email'    => wu_request('email_address'),
				'username' => wu_request('username'),
				'password' => wu_request('password', false),
				'meta'     => [],
			];
		} else {
			$customer_data = [
				'user_id' => wu_request('user_id', 0),
			];
		}

		/*
			* Tries to create the customer
			*/
		$customer = wu_create_customer($customer_data);

		if (is_wp_error($customer)) {
			wp_send_json_error($customer);
		}

		wp_send_json_success(
			[
				'redirect_url' => wu_network_admin_url(
					'wp-ultimo-edit-customer',
					[
						'id' => $customer->get_id(),
					]
				),
			]
		);
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
			'deleted_message' => __('Customer removed successfully.', 'multisite-ultimate'),
			'search_label'    => __('Search Customer', 'multisite-ultimate'),
		];
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Customers', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Customers', 'multisite-ultimate');
	}

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Customers', 'multisite-ultimate');
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
				'label'   => __('Add Customer', 'multisite-ultimate'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_customer'),
			],
			[
				'label' => __('Export as CSV', 'multisite-ultimate'),
				'icon'  => 'wu-export',
				'url'   => add_query_arg(
					[
						'wu_action' => 'wu_export_customers',
						'nonce'     => wp_create_nonce('wu_export_customers'),
					]
				),
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

		return new \WP_Ultimo\List_Tables\Customer_List_Table();
	}
}
