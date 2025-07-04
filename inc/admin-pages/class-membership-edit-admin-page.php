<?php
/**
 * Multisite Ultimate Membership Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WordPressCS\WordPress\Sniffs\Security\NonceVerificationSniff;
use WP_Ultimo\Database\Memberships\Membership_Status;

/**
 * Multisite Ultimate Membership Edit/Add New Admin Page.
 */
class Membership_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-membership';

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
	public $object_id = 'membership';

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
	protected $highlight_menu_slug = 'wp-ultimo-memberships';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Marks the page as a swap preview.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $is_swap_preview = false;

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
		'network_admin_menu' => 'wu_edit_memberships',
	];

	/**
	 * Override the page load.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded(): void {

		parent::page_loaded();

		/*
		 * Adds the swap notices, if needed.
		 */
		$this->add_swap_notices();
	}

	/**
	 * Displays swap notices, if necessary.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function add_swap_notices() {

		$swap_order = $this->get_object()->get_scheduled_swap();

		if ( ! $swap_order || wu_request('preview-swap')) {
			return;
		}

		$actions = [
			'preview' => [
				'title' => __('Preview', 'multisite-ultimate'),
				'url'   => add_query_arg('preview-swap', 1),
			],
		];

		$date = new \DateTime($swap_order->scheduled_date);

		// translators: %s is the date, using the site format options
		$message = sprintf(__('There is a change scheduled to take place on this membership in <strong>%s</strong>. You can preview the changes here. Scheduled changes are usually created by downgrades.', 'multisite-ultimate'), $date->format(get_option('date_format')));

		WP_Ultimo()->notices->add($message, 'warning', 'network-admin', false, $actions);
	}

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	public function register_scripts(): void {

		parent::register_scripts();

		wp_enqueue_editor();
	}

	/**
	 * Register ajax forms that we use for membership.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {
		/*
		 * Transfer membership - Confirmation modal
		 */
		wu_register_form(
			'transfer_membership',
			[
				'render'     => [$this, 'render_transfer_membership_modal'],
				'handler'    => [$this, 'handle_transfer_membership_modal'],
				'capability' => 'wu_transfer_memberships',
			]
		);

		/*
		 * Edit/Add product
		 */
		wu_register_form(
			'edit_membership_product',
			[
				'render'  => [$this, 'render_edit_membership_product_modal'],
				'handler' => [$this, 'handle_edit_membership_product_modal'],
			]
		);

		/*
		 * Change Plan
		 */
		wu_register_form(
			'change_membership_plan',
			[
				'render'  => [$this, 'render_change_membership_plan_modal'],
				'handler' => [$this, 'handle_change_membership_plan_modal'],
			]
		);

		/*
		 * Delete Product
		 */
		wu_register_form(
			'remove_membership_product',
			[
				'render'  => [$this, 'render_remove_membership_product'],
				'handler' => [$this, 'handle_remove_membership_product'],
			]
		);

		add_filter(
			'wu_data_json_success_delete_membership_modal',
			fn($data_json) => [
				'redirect_url' => wu_network_admin_url('wp-ultimo-memberships', ['deleted' => 1]),
			]
		);
	}

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_transfer_membership_modal(): void {

		$membership = wu_get_membership(wu_request('id'));

		if ( ! $membership) {
			return;
		}

		$fields = [
			'confirm'            => [
				'type'      => 'toggle',
				'title'     => __('Confirm Transfer', 'multisite-ultimate'),
				'desc'      => __('This will start the transfer of assets from one customer to another.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'submit_button'      => [
				'type'            => 'submit',
				'title'           => __('Start Transfer', 'multisite-ultimate'),
				'placeholder'     => __('Start Transfer', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!confirmed',
				],
			],
			'id'                 => [
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			],
			'target_customer_id' => [
				'type'  => 'hidden',
				'value' => wu_request('target_customer_id'),
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'total-actions',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'true',
					'data-state'  => wp_json_encode(
						[
							'confirmed' => false,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the deletion of line items.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_transfer_membership_modal(): void {

		$membership = wu_get_membership(wu_request('id'));

		if ( ! $membership) {
			wp_send_json_error(new \WP_Error('not-found', __('Membership not found.', 'multisite-ultimate')));
		}

		$target_customer = wu_get_customer(wu_request('target_customer_id'));

		if ( ! $target_customer) {
			wp_send_json_error(new \WP_Error('not-found', __('Target customer not found.', 'multisite-ultimate')));
		}

		if ($target_customer->get_id() === $membership->get_customer_id()) {
			wp_send_json_error(new \WP_Error('not-found', __('Cannot transfer to the same customer.', 'multisite-ultimate')));
		}

		/*
		 * Lock the membership to prevent memberships.
		 */
		$membership->lock();

		/*
		 * Enqueue task
		 */
		wu_enqueue_async_action(
			'wu_async_transfer_membership',
			[
				'membership_id'      => $membership->get_id(),
				'target_customer_id' => $target_customer->get_id(),
			],
			'membership'
		);

		wp_send_json_success(
			[
				'redirect_url' => wu_network_admin_url(
					'wp-ultimo-edit-membership',
					[
						'id'               => $membership->get_id(),
						'transfer-started' => 1,
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
	public function register_widgets(): void {

		parent::register_widgets();

		$labels = $this->get_labels();

		$label = $this->get_object()->get_status_label();

		$class = $this->get_object()->get_status_class();

		$tag = "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

		$gateway_message = false;

		if ( ! empty($this->get_object()->get_gateway())) {
			$gateway = wu_get_gateway($this->get_object()->get_gateway());

			$gateway_message = $gateway ? $gateway->get_amount_update_message() : '';
		}

		$this->add_fields_widget(
			'at_a_glance',
			[
				'title'                 => __('At a Glance', 'multisite-ultimate'),
				'position'              => 'normal',
				'classes'               => 'wu-overflow-hidden wu-widget-inset',
				'field_wrapper_classes' => 'wu-w-1/3 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t-0 wu-border-l-0 wu-border-r wu-border-b-0 wu-border-gray-300 wu-border-solid wu-float-left wu-relative',
				'fields'                => [
					'status'        => [
						'type'          => 'text-display',
						'title'         => __('Membership Status', 'multisite-ultimate'),
						'display_value' => $tag,
						'tooltip'       => '',
					],
					'hash'          => [
						'copy'          => true,
						'type'          => 'text-display',
						'title'         => __('Reference ID', 'multisite-ultimate'),
						'display_value' => $this->get_object()->get_hash(),
					],
					'total_grossed' => [
						'type'            => 'text-display',
						'title'           => __('Total Grossed', 'multisite-ultimate'),
						'display_value'   => wu_format_currency($this->get_object()->get_total_grossed(), $this->get_object()->get_currency()),
						'wrapper_classes' => 'sm:wu-border-r-0',
					],
				],
			]
		);

		$this->add_list_table_widget(
			'membership-products',
			[
				'position' => 'normal',
				'title'    => __('Products', 'multisite-ultimate'),
				'table'    => new \WP_Ultimo\List_Tables\Membership_Line_Item_List_Table(),
				'after'    => $this->output_widget_products(),
			]
		);

		$this->add_list_table_widget(
			'payments',
			[
				'title'        => __('Payments', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Customers_Payment_List_Table(),
				'query_filter' => [$this, 'payments_query_filter'],
			]
		);

		$this->add_list_table_widget(
			'sites',
			[
				'title'        => __('Sites', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Memberships_Site_List_Table(),
				'query_filter' => [$this, 'sites_query_filter'],
			]
		);

		$this->add_list_table_widget(
			'customer',
			[
				'title'        => __('Linked Customer', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Site_Customer_List_Table(),
				'query_filter' => [$this, 'customer_query_filter'],
			]
		);

		$this->add_tabs_widget(
			'options',
			[
				'title'    => __('Membership Options', 'multisite-ultimate'),
				'position' => 'normal',
				'sections' => apply_filters(
					'wu_membership_options_sections',
					[
						'general'      => [
							'title'  => __('General', 'multisite-ultimate'),
							'desc'   => __('General membership options', 'multisite-ultimate'),
							'icon'   => 'dashicons-wu-globe',
							'fields' => [
								'blocking' => [
									'type'  => 'toggle',
									'title' => __('Is Blocking?', 'multisite-ultimate'),
									'desc'  => __('Should we block access to the site, plugins, themes, and services after the expiration date is reached?', 'multisite-ultimate'),
									'value' => true,
								],
							],
						],
						'billing_info' => [
							'title'  => __('Billing Info', 'multisite-ultimate'),
							'desc'   => __('Billing information for this particular membership.', 'multisite-ultimate'),
							'icon'   => 'dashicons-wu-address',
							'fields' => $this->get_object()->get_billing_address()->get_fields(),
						],
					],
					$this->get_object()
				),
			]
		);

		/*
		 * Hide sensitive things when in swap preview.
		 */
		if ( ! $this->is_swap_preview) {
			$this->add_list_table_widget(
				'events',
				[
					'title'        => __('Events', 'multisite-ultimate'),
					'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
					'query_filter' => [$this, 'events_query_filter'],
				]
			);
		}

		$regular_fields = [
			'status'              => [
				'type'              => 'select',
				'title'             => __('Status', 'multisite-ultimate'),
				'desc'              => __('The membership current status.', 'multisite-ultimate'),
				'value'             => $this->get_object()->get_status(),
				'options'           => Membership_Status::to_array(),
				'tooltip'           => '',
				'html_attr'         => [
					'v-model' => 'status',
				],
				'wrapper_html_attr' => [
					'v-cloak' => '1',
				],
			],
			'cancellation_reason' => [
				'type'              => 'textarea',
				'title'             => __('Cancellation Reason', 'multisite-ultimate'),
				'desc'              => __('The reason why the customer cancelled this membership.', 'multisite-ultimate'),
				'value'             => $this->get_object()->get_cancellation_reason(),
				'wrapper_html_attr' => [
					'v-show'  => 'status == \'cancelled\'',
					'v-cloak' => '1',
				],
			],
			'cancel_gateway'      => [
				'type'              => 'toggle',
				'title'             => __('Cancel on gateway', 'multisite-ultimate'),
				'desc'              => __('If enable we will cancel the subscription on payment method', 'multisite-ultimate'),
				'value'             => false,
				'wrapper_html_attr' => [
					'v-show'  => ! empty($this->get_object()->get_gateway_customer_id()) ? 'status == \'cancelled\'' : 'false',
					'v-cloak' => '1',
				],
			],
			'preview-swap'        => [
				'type'  => 'hidden',
				'value' => wu_request('preview-swap', 0),
			],
			'customer_id'         => [
				'type'              => 'model',
				'title'             => __('Customer', 'multisite-ultimate'),
				'placeholder'       => __('Search a Customer...', 'multisite-ultimate'),
				'desc'              => __('The owner of this membership.', 'multisite-ultimate'),
				'value'             => $this->get_object()->get_customer_id(),
				'tooltip'           => '',
				'html_attr'         => [
					'data-base-link'    => wu_network_admin_url('wp-ultimo-edit-customer', ['id' => '']),
					'v-model'           => 'customer_id',
					'data-model'        => 'customer',
					'data-value-field'  => 'id',
					'data-label-field'  => 'display_name',
					'data-search-field' => 'display_name',
					'data-max-items'    => 1,
					'data-selected'     => $this->get_object()->get_customer() ? wp_json_encode($this->get_object()->get_customer()->to_search_results()) : '',
				],
				'wrapper_html_attr' => [
					'v-cloak' => '1',
				],
			],
			'transfer_note'       => [
				'type'              => 'note',
				'desc'              => __('Changing the customer will transfer this membership and all its assets, including sites, to the new customer.', 'multisite-ultimate'),
				'classes'           => 'wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
				'wrapper_html_attr' => [
					'v-show'  => '(original_customer_id != customer_id) && customer_id',
					'v-cloak' => '1',
				],
			],
			'submit_save'         => [
				'type'              => 'submit',
				'title'             => $labels['save_button_label'],
				'placeholder'       => $labels['save_button_label'],
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'html_attr'         => [],
				'wrapper_html_attr' => [
					'v-show'  => 'original_customer_id == customer_id || !customer_id',
					'v-cloak' => '1',
				],
			],
			'transfer'            => [
				'type'              => 'link',
				'display_value'     => __('Transfer Membership', 'multisite-ultimate'),
				'wrapper_classes'   => 'wu-bg-gray-200',
				'classes'           => 'button wubox wu-w-full wu-text-center',
				'wrapper_html_attr' => [
					'v-show'  => 'original_customer_id != customer_id && customer_id',
					'v-cloak' => '1',
				],
				'html_attr'         => [
					'v-bind:href' => "'" . wu_get_form_url(
						'transfer_membership',
						[
							'id'                 => $this->get_object()->get_id(),
							'target_customer_id' => '',
						]
					) . "=' + customer_id",
					'title'       => __('Transfer Membership', 'multisite-ultimate'),
				],
			],
		];

		if ($this->get_object()->is_locked()) {
			unset($regular_fields['transfer_note']);

			unset($regular_fields['transfer']);

			$regular_fields['submit_save']['title']                 = __('Locked', 'multisite-ultimate');
			$regular_fields['submit_save']['value']                 = 'none';
			$regular_fields['submit_save']['html_attr']['disabled'] = 'disabled';
		}

		$this->add_fields_widget(
			'save',
			[
				'html_attr' => [
					'data-wu-app' => 'membership_save',
					'data-state'  => wp_json_encode(
						[
							'status'               => $this->get_object()->get_status(),
							'original_customer_id' => $this->get_object()->get_customer_id(),
							'customer_id'          => $this->get_object()->get_customer_id(),
							'plan_id'              => $this->get_object()->get_plan_id(),
						]
					),
				],
				'fields'    => $regular_fields,
			]
		);

		$this->add_fields_widget(
			'pricing',
			[
				'title'     => __('Billing Amount', 'multisite-ultimate'),
				'html_attr' => [
					'data-wu-app' => 'true',
					'data-state'  => wp_json_encode(
						[
							'is_recurring'            => $this->get_object()->is_recurring(),
							'is_auto_renew'           => $this->get_object()->should_auto_renew(),
							'amount'                  => $this->get_object()->get_amount(),
							'initial_amount'          => $this->get_object()->get_initial_amount(),
							'duration'                => $this->get_object()->get_duration(),
							'duration_unit'           => $this->get_object()->get_duration_unit(),
							'gateway'                 => $this->get_object()->get_gateway(),
							'gateway_subscription_id' => $this->get_object()->get_gateway_subscription_id(),
							'gateway_customer_id'     => $this->get_object()->get_gateway_customer_id(),
						]
					),
				],
				'fields'    => [
					// Fields for price
					'_initial_amount'               => [
						'type'              => 'text',
						'title'             => __('Initial Amount', 'multisite-ultimate'),
						// translators: %s is a price placeholder value.
						'placeholder'       => sprintf(__('E.g. %s', 'multisite-ultimate'), wu_format_currency(199)),
						'desc'              => __('The initial amount collected on the first payment.', 'multisite-ultimate'),
						'value'             => $this->get_object()->get_initial_amount(),
						'money'             => true,
						'html_attr'         => [
							'v-model' => 'initial_amount',
						],
						'wrapper_html_attr' => [
							'v-cloak' => '1',
						],
					],
					'initial_amount'                => [
						'type'      => 'hidden',
						'html_attr' => [
							'v-model' => 'initial_amount',
						],
					],
					'recurring'                     => [
						'type'              => 'toggle',
						'title'             => __('Is Recurring', 'multisite-ultimate'),
						'desc'              => __('Use this option to manually enable or disable this membership.', 'multisite-ultimate'),
						'value'             => $this->get_object()->is_recurring(),
						'html_attr'         => [
							'v-model' => 'is_recurring',
						],
						'wrapper_html_attr' => [
							'v-cloak' => '1',
						],
					],
					'amount'                        => [
						'type'      => 'hidden',
						'html_attr' => [
							'v-model' => 'amount',
						],
					],
					'recurring_amount_group'        => [
						'type'              => 'group',
						'title'             => __('Recurring Amount', 'multisite-ultimate'),
						// translators: placeholder %1$s is the amount, %2$s is the duration (such as 1, 2, 3), and %3$s is the unit (such as month, year, week)
						'desc'              => sprintf(__('The customer will be charged %1$s every %2$s %3$s(s).', 'multisite-ultimate'), '{{ wu_format_money(amount) }}', '{{ duration }}', '{{ duration_unit }}'),
						'wrapper_html_attr' => [
							'v-show'  => 'is_recurring',
							'v-cloak' => '1',
						],
						'fields'            => [
							'_amount'       => [
								'type'            => 'text',
								'value'           => $this->get_object()->get_amount(),
								'placeholder'     => wu_format_currency('99'),
								'wrapper_classes' => '',
								'money'           => true,
								'html_attr'       => [
									'v-model' => 'amount',
								],
							],
							'duration'      => [
								'type'            => 'number',
								'value'           => $this->get_object()->get_duration(),
								'placeholder'     => '',
								'wrapper_classes' => 'wu-mx-2 wu-w-1/3',
								'min'             => 0,
								'html_attr'       => [
									'v-model' => 'duration',
									'steps'   => 1,
								],
							],
							'duration_unit' => [
								'type'            => 'select',
								'value'           => $this->get_object()->get_duration_unit(),
								'placeholder'     => '',
								'wrapper_classes' => 'wu-w-2/3',
								'html_attr'       => [
									'v-model' => 'duration_unit',
								],
								'options'         => [
									'day'   => __('Days', 'multisite-ultimate'),
									'week'  => __('Weeks', 'multisite-ultimate'),
									'month' => __('Months', 'multisite-ultimate'),
									'year'  => __('Years', 'multisite-ultimate'),
								],
							],
						],
					],
					'recurring_note'                => [
						'type'              => 'note',
						'desc'              => $gateway_message,
						'classes'           => 'wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
						'wrapper_html_attr' => [
							'v-show'  => implode(
								' && ',
								[
									'is_recurring',
									'gateway',
									'gateway === "' . $this->get_object()->get_gateway() . '"',
									'gateway_subscription_id === "' . $this->get_object()->get_gateway_subscription_id() . '"',
									'gateway_customer_id === "' . $this->get_object()->get_gateway_customer_id() . '"',
									'(' . implode(
										' || ',
										[
											$this->get_object()->get_amount() . ' !== amount',
											$this->get_object()->get_duration() . ' != duration',
											'"' . $this->get_object()->get_duration_unit() . '" !== duration_unit',
										]
									) . ')',
								]
							),
							'v-cloak' => '1',
						],
					],
					'billing_cycles'                => [
						'type'              => 'number',
						'title'             => __('Billing Cycles', 'multisite-ultimate'),
						'placeholder'       => __('E.g. 0', 'multisite-ultimate'),
						'desc'              => __('How many times should we bill this customer. Leave 0 to charge until cancelled.', 'multisite-ultimate'),
						'value'             => $this->get_object()->get_billing_cycles(),
						'min'               => 0,
						'wrapper_html_attr' => [
							'v-show'  => 'is_recurring',
							'v-cloak' => '1',
						],
					],
					'times_billed'                  => [
						'type'              => 'number',
						'title'             => __('Times Billed', 'multisite-ultimate'),
						'desc'              => __('The number of times this membership was billed so far.', 'multisite-ultimate'),
						'value'             => $this->get_object()->get_times_billed(),
						'min'               => 0,
						'wrapper_html_attr' => [
							'v-show'  => 'is_recurring',
							'v-cloak' => '1',
						],
					],

					'auto_renew'                    => [
						'type'              => 'toggle',
						'title'             => __('Auto-Renew?', 'multisite-ultimate'),
						'desc'              => __('Activating this will tell the gateway to try to automatically charge for this membership.', 'multisite-ultimate'),
						'value'             => $this->get_object()->should_auto_renew(),
						'wrapper_html_attr' => [
							'v-show'  => 'is_recurring',
							'v-cloak' => '1',
						],
						'html_attr'         => [
							'v-model' => 'is_auto_renew',
						],
					],
					'gateway'                       => [
						'type'              => 'text',
						'title'             => __('Gateway', 'multisite-ultimate'),
						'placeholder'       => __('e.g. stripe', 'multisite-ultimate'),
						'description'       => __('e.g. stripe', 'multisite-ultimate'),
						'desc'              => __('Payment gateway used to process the payment.', 'multisite-ultimate'),
						'value'             => $this->get_object()->get_gateway(),
						'wrapper_classes'   => 'wu-w-full',
						'html_attr'         => [
							'v-on:input'   => 'gateway = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, "")',
							'v-bind:value' => 'gateway',
						],
						'wrapper_html_attr' => [
							'v-cloak' => '1',
						],
					],
					'gateway_customer_id_group'     => [
						'type'              => 'group',
						'desc'              => function (): string {

							$gateway_id = $this->get_object()->get_gateway();

							if (empty($this->get_object()->get_gateway_customer_id())) {
								return '';
							}

							$url = apply_filters("wu_{$gateway_id}_remote_customer_url", $this->get_object()->get_gateway_customer_id());

							if ($url) {
								return sprintf('<a class="wu-text-gray-800 wu-text-center wu-w-full wu-no-underline" href="%s" target="_blank">%s</a>', esc_attr($url), __('View on Gateway &rarr;', 'multisite-ultimate'));
							}

							return '';
						},
						'wrapper_html_attr' => [
							'v-show'  => 'is_recurring && is_auto_renew',
							'v-cloak' => '1',
						],
						'fields'            => [
							'gateway_customer_id' => [
								'type'              => 'text',
								'title'             => __('Gateway Customer ID', 'multisite-ultimate'),
								'placeholder'       => __('Gateway Customer ID', 'multisite-ultimate'),
								'value'             => $this->get_object()->get_gateway_customer_id(),
								'tooltip'           => '',
								'wrapper_classes'   => 'wu-w-full',
								'wrapper_html_attr' => [],
								'html_attr'         => [
									'v-model' => 'gateway_customer_id',
								],
							],
						],
					],

					'gateway_subscription_id_group' => [
						'type'              => 'group',
						'desc'              => function (): string {

							$gateway_id = $this->get_object()->get_gateway();

							if (empty($this->get_object()->get_gateway_subscription_id())) {
								return '';
							}

							$url = apply_filters("wu_{$gateway_id}_remote_subscription_url", $this->get_object()->get_gateway_subscription_id());

							if ($url) {
								return sprintf('<a class="wu-text-gray-800 wu-text-center wu-w-full wu-no-underline" href="%s" target="_blank">%s</a>', esc_attr($url), __('View on Gateway &rarr;', 'multisite-ultimate'));
							}

							return '';
						},
						'wrapper_html_attr' => [
							'v-show'  => 'is_recurring && is_auto_renew',
							'v-cloak' => '1',
						],
						'fields'            => [
							'gateway_subscription_id' => [
								'type'              => 'text',
								'title'             => __('Gateway Subscription ID', 'multisite-ultimate'),
								'placeholder'       => __('Gateway Subscription ID', 'multisite-ultimate'),
								'value'             => $this->get_object()->get_gateway_subscription_id(),
								'tooltip'           => '',
								'wrapper_classes'   => 'wu-w-full',
								'wrapper_html_attr' => [],
								'html_attr'         => [
									'v-model' => 'gateway_subscription_id',
								],
							],
						],
					],

					'gateway_note'                  => [
						'type'              => 'note',
						'desc'              => __('We will try to cancel the old subscription on the gateway.', 'multisite-ultimate'),
						'classes'           => 'wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
						'wrapper_html_attr' => [
							'v-show'  => 'is_recurring && (' . implode(
								' || ',
								[
									'"' . $this->get_object()->get_gateway() . '" !== "" && gateway !== "' . $this->get_object()->get_gateway() . '"',
									'"' . $this->get_object()->get_gateway_subscription_id() . '" !== "" && gateway_subscription_id !== "' . $this->get_object()->get_gateway_subscription_id() . '"',
									'"' . $this->get_object()->get_gateway_customer_id() . '" !== "" && gateway_customer_id !== "' . $this->get_object()->get_gateway_customer_id() . '"',
								]
							) . ')',
							'v-cloak' => '1',
						],
					],
				],
			]
		);

		$timestamp_fields = [];

		$timestamps = [
			'date_expiration'   => __('Expires at', 'multisite-ultimate'),
			'date_renewed'      => __('Last Renewed at', 'multisite-ultimate'),
			'date_trial_end'    => __('Trial Ends at', 'multisite-ultimate'),
			'date_cancellation' => __('Cancelled at', 'multisite-ultimate'),
		];

		foreach ($timestamps as $timestamp_name => $timestamp_label) {
			$value = $this->get_object()->{"get_$timestamp_name"}();

			$timestamp_fields[ $timestamp_name ] = [
				'title'         => $timestamp_label,
				'type'          => 'text-edit',
				'date'          => true,
				'edit'          => true,
				'display_value' => $this->edit ? $value : '',
				'value'         => $value,
				'placeholder'   => '2020-04-04 12:00:00',
				'html_attr'     => [
					'wu-datepicker'   => 'true',
					'data-format'     => 'Y-m-d H:i:S',
					'data-allow-time' => 'true',
				],
			];
		}

		if ( ! $this->get_object()->is_lifetime()) {
			$timestamp_fields['convert_to_lifetime'] = [
				'type'              => 'submit',
				'title'             => __('Convert to Lifetime', 'multisite-ultimate'),
				'value'             => 'convert_to_lifetime',
				'classes'           => 'button wu-w-full',
				'wrapper_html_attr' => [],
			];
		}

		$this->add_fields_widget(
			'membership-timestamps',
			[
				'title'  => __('Important Timestamps', 'multisite-ultimate'),
				'fields' => $timestamp_fields,
			]
		);
	}

	/**
	 * Renders the widget used to display the product list.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function output_widget_products() {

		return wu_get_template_contents(
			'memberships/product-list',
			[
				'membership' => $this->get_object(),
			]
		);
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Membership', 'multisite-ultimate') : __('Add new Membership', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Membership', 'multisite-ultimate');
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
			'edit_label'          => __('Edit Membership', 'multisite-ultimate'),
			'add_new_label'       => __('Add new Membership', 'multisite-ultimate'),
			'updated_message'     => __('Membership updated with success!', 'multisite-ultimate'),
			'title_placeholder'   => __('Enter Membership Name', 'multisite-ultimate'),
			'title_description'   => __('This name will be used on pricing tables, invoices, and more.', 'multisite-ultimate'),
			'save_button_label'   => __('Save Membership', 'multisite-ultimate'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Membership', 'multisite-ultimate'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'multisite-ultimate'),
		];
	}

	/**
	 * Filters the list table to return only relevant payments.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function payments_query_filter($args) {

		$args['membership_id'] = $this->get_object()->get_id();

		return $args;
	}

	/**
	 * Filters the list table to return only relevant sites.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function sites_query_filter($args) {

		$args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'membership_id' => [
				'key'   => 'wu_membership_id',
				'value' => $this->get_object()->get_id(),
			],
		];

		return $args;
	}

	/**
	 * Filters the list table to return only relevant customer.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function customer_query_filter($args) {

		$args['id'] = $this->get_object()->get_customer_id();

		return $args;
	}

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function events_query_filter($args) {

		$extra_args = [
			'object_type' => 'membership',
			'object_id'   => absint($this->get_object()->get_id()),
		];

		return array_merge($args, $extra_args);
	}

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Membership
	 */
	public function get_object() {

		if (null !== $this->object) {
			return $this->object;
		}

		$item_id = wu_request('id', 0);

		$item = wu_get_membership($item_id);

		if ( ! $item) {
			wp_safe_redirect(wu_network_admin_url('wp-ultimo-memberships'));

			exit;
		}

		$this->object = $item;

		/**
		 * Deal with scheduled swaps.
		 */
		if (wu_request('preview-swap')) {
			$swap_order = $this->get_object()->get_scheduled_swap();

			if ( ! $swap_order) {
				return $this->object;
			}

			$this->is_swap_preview = true;

			$actions = [
				'preview' => [
					'title' => __('&larr; Go back', 'multisite-ultimate'),
					'url'   => remove_query_arg('preview-swap', wu_get_current_url()),
				],
			];

			$date = new \DateTime($swap_order->scheduled_date);

			// translators: %s is the date, using the site format options
			$message = sprintf(__('This is a <strong>preview</strong>. This page displays the final stage of the membership after the changes scheduled for <strong>%s</strong>. Saving here will persist these changes, so be careful.', 'multisite-ultimate'), $date->format(get_option('date_format')));

			WP_Ultimo()->notices->add($message, 'info', 'network-admin', false, $actions);

			$this->object->swap($swap_order->order);
		}

		return $this->object;
	}

	/**
	 * Memberships have titles.
	 *
	 * @since 2.0.0
	 */
	public function has_title(): bool {

		return false;
	}

	/**
	 * Handle convert to lifetime.
	 *
	 * @since 2.0.0
	 */
	protected function handle_convert_to_lifetime(): bool {

		$object = $this->get_object();

		$object->set_date_expiration(null);

		$save = $object->save();

		if (is_wp_error($save)) {
			$errors = implode('<br>', $save->get_error_messages());

			WP_Ultimo()->notices->add($errors, 'error', 'network-admin');

			return false;
		}

		$array_params = [
			'updated' => 1,
		];

		if (false === $this->edit) {
			$array_params['id'] = $object->get_id();
		}

		$url = add_query_arg($array_params);

		wp_safe_redirect($url);

		return true;
	}

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return true
	 */
	public function handle_save() {

		$object = $this->get_object();

		// Cancel membership on gateway
		if ( (bool) wu_request('cancel_gateway', false) && wu_request('status', Membership_Status::CANCELLED)) {
			$gateway = wu_get_gateway(wu_request('gateway'));

			if ($gateway) {
				$gateway->process_cancellation($object, $object->get_customer());

				$_POST['gateway'] = '';
			}
		}

		if (wu_request('submit_button') === 'convert_to_lifetime') {
			return $this->handle_convert_to_lifetime();
		}

		$_POST['auto_renew'] = (bool) wu_request('auto_renew', false);

		$billing_address = $object->get_billing_address();

		$billing_address->attributes($_POST); // phpcs:ignore WordPress.Security.NonceVerification

		$valid_address = $billing_address->validate();

		if (is_wp_error($valid_address)) {
			$errors = implode('<br>', $valid_address->get_error_messages());

			WP_Ultimo()->notices->add($errors, 'error', 'network-admin');

			return false;
		}

		$object->set_billing_address($billing_address);

		ob_start();

		$status = parent::handle_save();

		if ($this->is_swap_preview) {
			ob_clean();

			$object->delete_scheduled_swap();

			$array_params = [
				'updated' => 1,
			];

			$url = add_query_arg($array_params);

			$url = remove_query_arg('preview-swap', $url);

			wp_safe_redirect($url);

			return true;
		}

		return $status;
	}

	/**
	 * Renders the add/edit line items form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_edit_membership_product_modal(): void {

		$membership = wu_get_membership(wu_request('id'));

		if ( ! $membership) {
			return;
		}

		$gateway_message = false;

		if ( ! empty($membership->get_gateway())) {
			$gateway = wu_get_gateway($membership->get_gateway());

			$gateway_message = $gateway ? $gateway->get_amount_update_message() : '';
		}

		$fields = [
			'product_id'    => [
				'type'        => 'model',
				'title'       => __('Product', 'multisite-ultimate'),
				'placeholder' => __('Search product...', 'multisite-ultimate'),
				'value'       => '',
				'tooltip'     => '',
				'html_attr'   => [
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
					'data-selected'     => '',
				],
			],
			'quantity'      => [
				'type'            => 'number',
				'title'           => __('Quantity', 'multisite-ultimate'),
				'value'           => 1,
				'placeholder'     => 1,
				'wrapper_classes' => 'wu-w-1/2',
				'html_attr'       => [
					'min'      => 1,
					'required' => 'required',
				],
			],
			'update_price'  => [
				'type'      => 'toggle',
				'title'     => __('Update Pricing', 'multisite-ultimate'),
				'desc'      => __('Checking this box will update the membership pricing. Otherwise, the products will be added without changing the membership prices.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'update_pricing',
				],
			],
			'transfer_note' => [
				'type'              => 'note',
				'desc'              => $gateway_message,
				'classes'           => 'sm:wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
				'wrapper_html_attr' => [
					'v-show'  => 'update_pricing',
					'v-cloak' => '1',
				],
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Add Product', 'multisite-ultimate'),
				'placeholder'     => __('Add Product', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
			],
			'id'            => [
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			],
		];

		if ( ! $gateway_message) {
			unset($fields['transfer_note']);
		}

		$form = new \WP_Ultimo\UI\Form(
			'edit_membership_product',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'edit_membership_product',
					'data-state'  => wu_convert_to_state(
						[
							'update_pricing' => 0,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the add/edit of line items.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_edit_membership_product_modal(): void {

		$membership = wu_get_membership(wu_request('id'));

		if ( ! $membership) {
			$error = new \WP_Error('membership-not-found', __('Membership not found.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		$product = wu_get_product(wu_request('product_id'));

		if ( ! $product) {
			$error = new \WP_Error('product-not-found', __('Product not found.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		$membership->add_product($product->get_id(), (int) wu_request('quantity', 1));

		// if we are updating the pricing, we need to update the membership price.
		if (wu_request('update_price')) {
			$value_to_add = wu_get_membership_product_price($membership, $product->get_id(), (int) wu_request('quantity', 1));

			if (is_wp_error($value_to_add)) {
				wp_send_json_error($value_to_add);
			}

			$membership->set_amount($membership->get_amount() + $value_to_add);
		}

		$saved = $membership->save();

		if (is_wp_error($saved)) {
			wp_send_json_error($saved);
		}
		$referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_url(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

		wp_send_json_success(
			[
				'redirect_url' => add_query_arg('updated', 1, $referer),
			]
		);
	}

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_remove_membership_product(): void {

		$membership = wu_get_membership(wu_request('id'));

		if ( ! $membership) {
			return;
		}

		$gateway_message = false;

		if ($membership->get_gateway()) {
			$gateway = wu_get_gateway($membership->get_gateway());

			$gateway_message = $gateway ? $gateway->get_amount_update_message() : '';
		}

		$fields = [
			'quantity'      => [
				'type'            => 'number',
				'title'           => __('Quantity', 'multisite-ultimate'),
				'value'           => 1,
				'placeholder'     => 1,
				'wrapper_classes' => 'wu-w-1/2',
				'html_attr'       => [
					'min'      => 1,
					'required' => 'required',
				],
			],
			'update_price'  => [
				'type'      => 'toggle',
				'title'     => __('Update Pricing?', 'multisite-ultimate'),
				'desc'      => __('Checking this box will update the membership pricing. Otherwise, the products will be added without changing the membership prices.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'update_pricing',
				],
			],
			'transfer_note' => [
				'type'              => 'note',
				'desc'              => $gateway_message,
				'classes'           => 'sm:wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
				'wrapper_html_attr' => [
					'v-show'  => 'update_pricing',
					'v-cloak' => '1',
				],
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Remove Product', 'multisite-ultimate'),
				'placeholder'     => __('Remove Product', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
			],
			'id'            => [
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			],
			'product_id'    => [
				'type'  => 'hidden',
				'value' => wu_request('product_id', 0),
			],
		];

		if ( ! $gateway_message) {
			unset($fields['transfer_note']);
		}

		$form = new \WP_Ultimo\UI\Form(
			'edit_membership_product',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'edit_membership_product',
					'data-state'  => wu_convert_to_state(
						[
							'update_pricing' => 0,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the deletion of line items.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_remove_membership_product(): void {

		$membership = wu_get_membership(wu_request('id'));

		if ( ! $membership) {
			$error = new \WP_Error('membership-not-found', __('Membership not found.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		$product = wu_get_product(wu_request('product_id'));

		if ( ! $product) {
			$error = new \WP_Error('product-not-found', __('Product not found.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		// Get the existing quantity by filtering the products array.
		$existing_quantity = array_filter($membership->get_addon_products(), fn($item) => $item['product']->get_id() === $product->get_id())[0]['quantity'];

		$quantity = (int) wu_request('quantity', 1);
		$quantity = $quantity > $existing_quantity ? $existing_quantity : $quantity;

		$membership->remove_product($product->get_id(), $quantity);

		// if we are updating the pricing, we need to update the membership price.
		if (wu_request('update_price')) {
			$value_to_remove = wu_get_membership_product_price($membership, $product->get_id(), $quantity);

			if (is_wp_error($value_to_remove)) {
				wp_send_json_error($value_to_remove);
			}

			$membership->set_amount($membership->get_amount() - $value_to_remove);
		}

		$saved = $membership->save();

		if (is_wp_error($saved)) {
			wp_send_json_error($saved);
		}
		$referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_url(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

		wp_send_json_success(
			[
				'redirect_url' => add_query_arg('updated', 1, $referer),
			]
		);
	}

	/**
	 * Renders the add/edit line items form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_change_membership_plan_modal(): void {

		$membership = wu_get_membership(wu_request('id'));

		if ( ! $membership) {
			return;
		}

		$product = wu_get_product(wu_request('product_id'));

		if ( ! $product) {
			return;
		}

		$gateway_message = false;

		if ($membership->get_gateway()) {
			$gateway = wu_get_gateway($membership->get_gateway());

			$gateway_message = $gateway ? $gateway->get_amount_update_message() : '';
		}

		$fields = [
			'plan_id'       => [
				'type'        => 'model',
				'title'       => __('Plan', 'multisite-ultimate'),
				'placeholder' => __('Search new Plan...', 'multisite-ultimate'),
				'desc'        => __('Select a new plan for this membership.', 'multisite-ultimate'),
				'value'       => $product->get_id(),
				'tooltip'     => '',
				'html_attr'   => [
					'data-model'        => 'plan',
					'v-model'           => 'plan_id',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
					'data-selected'     => wp_json_encode($product->to_search_results()),
				],
			],
			'update_price'  => [
				'type'      => 'toggle',
				'title'     => __('Update Pricing', 'multisite-ultimate'),
				'desc'      => __('Checking this box will update the membership pricing. Otherwise, the products will be added without changing the membership prices.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'update_pricing',
				],
			],
			'transfer_note' => [
				'type'              => 'note',
				'desc'              => $gateway_message,
				'classes'           => 'sm:wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
				'wrapper_html_attr' => [
					'v-show'  => 'update_pricing',
					'v-cloak' => '1',
				],
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Change Product', 'multisite-ultimate'),
				'placeholder'     => __('Change Product', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:class'    => 'plan_id == original_plan_id ? "button-disabled" : ""',
					'v-bind:disabled' => 'plan_id == original_plan_id',
				],
			],
			'id'            => [
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			],
		];

		if ( ! $gateway_message) {
			unset($fields['transfer_note']);
		}

		$form = new \WP_Ultimo\UI\Form(
			'change_membership_plan',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'change_membership_plan',
					'data-state'  => wu_convert_to_state(
						[
							'update_pricing'   => 0,
							'original_plan_id' => $product->get_id(),
							'plan_id'          => $product->get_id(),
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the add/edit of line items.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_change_membership_plan_modal(): void {

		$membership = wu_get_membership(wu_request('id'));

		if ( ! $membership) {
			$error = new \WP_Error('membership-not-found', __('Membership not found.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		$plan = wu_get_product(wu_request('plan_id'));

		if ( ! $plan) {
			$error = new \WP_Error('plan-not-found', __('Plan not found.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		$original_plan_id = $membership->get_plan_id();

		if (absint($original_plan_id) === absint($plan->get_id())) {
			$error = new \WP_Error('same-plan', __('No change performed. The same plan selected.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		$membership->set_plan_id($plan->get_id());

		// if we are updating the pricing, we need to update the membership price.
		if (wu_request('update_price')) {
			$value_to_add = wu_get_membership_product_price($membership, $plan->get_id(), 1);

			if (is_wp_error($value_to_add)) {
				wp_send_json_error($value_to_add);
			}

			$value_to_remove = wu_get_membership_product_price($membership, $original_plan_id, 1);

			if (is_wp_error($value_to_remove)) {
				wp_send_json_error($value_to_remove);
			}

			$membership->set_amount($membership->get_amount() + $value_to_add - $value_to_remove);
		}

		$saved = $membership->save();

		if (is_wp_error($saved)) {
			wp_send_json_error($saved);
		}
		$referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_url(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

		wp_send_json_success(
			[
				'redirect_url' => add_query_arg('updated', 1, $referer),
			]
		);
	}
}
