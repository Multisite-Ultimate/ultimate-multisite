<?php
/**
 * Multisite Ultimate Customer Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Memberships\Membership_Status;

/**
 * Multisite Ultimate Customer Edit/Add New Admin Page.
 */
class Customer_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-customer';

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
	public $object_id = 'customer';

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
	protected $highlight_menu_slug = 'wp-ultimo-customers';

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
		'network_admin_menu' => 'wu_edit_customers',
	];

	/**
	 * Allow child classes to add hooks to be run once the page is loaded.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
	 * @since 1.8.2
	 * @return void
	 */
	public function hooks(): void {

		parent::hooks();

		add_action('wu_page_edit_redirect_handlers', [$this, 'handle_send_verification_notice']);

		add_filter('removable_query_args', [$this, 'remove_query_args']);
	}

	/**
	 * Allow child classes to register scripts and styles that can be loaded on the output function, for example.
	 *
	 * @return void
	 * @since 1.8.2
	 */
	public function register_scripts(): void {

		parent::register_scripts();

		wp_enqueue_style('wu-flags');

		wp_enqueue_script_module('wu-flags-polyfill');

		wp_enqueue_editor();

		wp_enqueue_media();
	}

	/**
	 * Register ajax forms that we use for membership.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function register_forms(): void {
		/*
		 * Transfer customer - Confirmation modal
		 */
		wu_register_form(
			'transfer_customer',
			[
				'render'     => [$this, 'render_transfer_customer_modal'],
				'handler'    => [$this, 'handle_transfer_customer_modal'],
				'capability' => 'wu_transfer_customer',
			]
		);

		/*
		 * Adds the hooks to handle deletion.
		 */
		add_filter('wu_form_fields_delete_customer_modal', [$this, 'customer_extra_delete_fields'], 10, 2);

		add_filter('wu_form_attributes_delete_customer_modal', [$this, 'customer_extra_form_attributes']);

		add_action('wu_after_delete_customer_modal', [$this, 'customer_after_delete_actions']);
	}

	/**
	 * Renders the transfer confirmation form.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function render_transfer_customer_modal(): void {

		$user = wu_get_customer(wu_request('id'));

		if ( ! $user) {
			return;
		}

		$fields = [
			'confirm'        => [
				'type'      => 'toggle',
				'title'     => __('Confirm Transfer', 'multisite-ultimate'),
				'desc'      => __('This will start the transfer of assets from one user to another.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'submit_button'  => [
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
			'id'             => [
				'type'  => 'hidden',
				'value' => $user->get_id(),
			],
			'target_user_id' => [
				'type'  => 'hidden',
				'value' => wu_request('target_user_id'),
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
					'data-wu-app' => 'transfer_customer',
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
	 * Handles the transfer of customer.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function handle_transfer_customer_modal(): void {

		global $wpdb;

		$customer    = wu_get_customer(wu_request('id'));
		$target_user = get_user_by('id', wu_request('target_user_id'));

		if ( ! $customer) {
			wp_send_json_error(new \WP_Error('not-found', __('Customer not found.', 'multisite-ultimate')));
		}

		if ( ! $target_user) {
			wp_send_json_error(new \WP_Error('not-found', __('User not found.', 'multisite-ultimate')));
		}

		$customer->set_user_id($target_user->ID);

		$saved = $customer->save();

		if (is_wp_error($saved)) {
			wp_send_json_error($saved);
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
	 * Adds the extra fields to the customer delete modal.
	 *
	 * @param array  $fields original array of fields.
	 * @param object $customer The customer object.
	 *
	 * @since 2.0.0
	 */
	public function customer_extra_delete_fields($fields, $customer): array {

		$custom_fields = [
			'delete_all'                => [
				'type'      => 'toggle',
				'title'     => __('Delete everything', 'multisite-ultimate'),
				'desc'      => __('Sites, payments and memberships.', 'multisite-ultimate'),
				'html_attr' => [
					'v-bind:value' => 'delete_all_confirmed',
					'v-model'      => 'delete_all_confirmed',
				],
			],
			're_assignment_customer_id' => [
				'type'              => 'model',
				'title'             => __('Re-assignment to customer', 'multisite-ultimate'),
				'placeholder'       => __('Select Customer...', 'multisite-ultimate'),
				'html_attr'         => [
					'data-model'        => 'customer',
					'data-value-field'  => 'id',
					'data-label-field'  => 'display_name',
					'data-search-field' => 'display_name',
					'data-max-items'    => 1,
					'data-exclude'      => wp_json_encode([$customer->get_id()]),
				],
				'wrapper_html_attr' => [
					'v-show' => '!delete_all_confirmed',
				],
			],
		];

		if ( ! current_user_can('wu_delete_sites') || ! current_user_can('wu_delete_memberships') || ! current_user_can('wu_delete_payments')) {
			unset($custom_fields['delete_all']);
		}

		if ( ! current_user_can('wu_transfer_sites') || ! current_user_can('wu_transfer_memberships') || ! current_user_can('wu_edit_payments')) {
			unset($custom_fields['re_assignment_customer_id']);
		}

		return array_merge($custom_fields, $fields);
	}

	/**
	 * Adds the extra form attributes to the delete modal.
	 *
	 * @param array $form_attributes Form attributes.
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function customer_extra_form_attributes($form_attributes) {

		$custom_state = json_decode((string) $form_attributes['html_attr']['data-state'], true);

		$custom_state['delete_all_confirmed'] = false;

		$form_attributes['html_attr']['data-state'] = wp_json_encode($custom_state);

		return $form_attributes;
	}

	/**
	 * Enqueues actions to be run after a customer is deleted.
	 *
	 * @param object $customer The customer object.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function customer_after_delete_actions($customer): void {

		$delete_all = wu_request('delete_all');

		if ($delete_all) {
			foreach ($customer->get_memberships() as $membership) {

				/**
				 * Enqueue task
				 */
				wu_enqueue_async_action(
					'wu_async_delete_membership',
					[
						'membership_id' => $membership->get_id(),
					],
					'membership'
				);
			}

			foreach ($customer->get_payments() as $payment) {

				/**
				 * Enqueue task
				 */
				wu_enqueue_async_action(
					'wu_async_delete_payment',
					[
						'payment_id' => $payment->get_id(),
					],
					'payment'
				);
			}
		} else {
			$re_assignment_customer = wu_get_customer(wu_request('re_assignment_customer_id'));

			if ($re_assignment_customer) {
				foreach ($customer->get_memberships() as $membership) {

					/**
					 * Enqueue task
					 */
					wu_enqueue_async_action(
						'wu_async_transfer_membership',
						[
							'membership_id'      => $membership->get_id(),
							'target_customer_id' => $re_assignment_customer->get_id(),
						],
						'membership'
					);
				}

				foreach ($customer->get_payments() as $payment) {

					/**
					 * Enqueue to the future
					 */
					wu_enqueue_async_action(
						'wu_async_transfer_payment',
						[
							'payment_id'         => $payment->get_id(),
							'target_customer_id' => $re_assignment_customer->get_id(),
						],
						'payment'
					);
				}
			}
		}
	}

	/**
	 * Generates the list of meta fields.
	 *
	 * @return array
	 * @since 2.0.11
	 */
	public function generate_customer_meta_fields() {

		$custom_meta_keys = wu_get_all_customer_meta($this->get_object()->get_id(), true);

		$meta_fields_set = [];

		$meta_fields_unset = [];

		foreach ($custom_meta_keys as $key => $value) {
			$field_location_breadcrumbs = [
				__(
					'orphan field - the original form no longer exists',
					'multisite-ultimate'
				),
			];

			$form = wu_get_isset($value, 'form');

			if ($form) {
				$field_location_breadcrumbs = [
					$form,
					wu_get_isset($value, 'step'),
					wu_get_isset($value, 'id'),
				];
			}

			$location = sprintf(
				'<small><strong>%s</strong> %s</small>',
				__('Location:', 'multisite-ultimate'),
				implode(' &rarr; ', array_filter($field_location_breadcrumbs))
			);

			$options = wu_get_isset($value, 'options', []);

			if ($options) {
				$options = array_combine(
					array_column($options, 'key'),
					array_column($options, 'label')
				);
			}

			$options = array_merge(['' => '--'], $options);

			$field_data = [
				'title'   => wu_get_isset($value, 'title', wu_slug_to_name($key)),
				'type'    => wu_get_isset($value, 'type', 'text'),
				'desc'    => wu_get_isset($value, 'description', '') . $location,
				'options' => $options,
				'tooltip' => wu_get_isset($value, 'tooltip', ''),
				'value'   => wu_get_customer_meta($this->get_object()->get_id(), $key),
			];

			if ('hidden' === $field_data['type']) {
				$field_data['type'] = 'text';
			}

			if ('image' === $field_data['type']) {
				$image_attributes  = wp_get_attachment_image_src((int) $field_data['value'], 'full');
				$field_data['img'] = $image_attributes ? $image_attributes[0] : '';
			}

			if (wu_get_isset($value, 'exists')) {
				$meta_fields_set[ "meta_key_$key" ] = $field_data;
			} else {
				$field_data['wrapper_html_attr'] = [
					'v-show' => 'display_unset_fields',
				];

				$meta_fields_unset[ "meta_key_$key" ] = $field_data;
			}
		}

		$collapsible_header = [];

		if ($meta_fields_unset) {
			$collapsible_header['display_unset_fields'] = [
				'title'           => __('Display unset fields', 'multisite-ultimate'),
				'desc'            => __(
					'If fields were added after the customer creation or onto a different form, they will not have a set value for this customer. You can manually set those here.',
					'multisite-ultimate'
				),
				'type'            => 'toggle',
				'wrapper_classes' => 'wu-bg-gray-100',
				'html_attr'       => [
					'v-model' => 'display_unset_fields',
				],
			];
		}

		$final_fields = array_merge($meta_fields_set, $collapsible_header, $meta_fields_unset);

		if (empty($final_fields)) {
			$final_fields['empty'] = [
				'type'    => 'note',
				'desc'    => __('No custom meta data collected and no custom fields found.', 'multisite-ultimate'),
				'classes' => 'wu-text-center',
			];
		}

		$final_fields['display_new_meta_repeater'] = [
			'title'           => __('Manually add custom meta fields', 'multisite-ultimate'),
			'desc'            => __('Add new custom meta fields to this customer.', 'multisite-ultimate'),
			'type'            => 'toggle',
			'wrapper_classes' => 'wu-bg-gray-100',
			'html_attr'       => [
				'v-model' => 'new_meta_fields_show',
			],
		];

		$default_meta_value = fn(string $type, $value = '', bool $is_default = false) => [
			'title'             => __('Value', 'multisite-ultimate'),
			'type'              => $type,
			'value'             => $value,
			'wrapper_classes'   => 'wu-w-1/4 wu-ml-2',
			'wrapper_html_attr' => [
				'v-show' => ($is_default ? '!new_meta_field.type || ' : '') . "new_meta_field.type === '$type'",
			],
			'html_attr'         => [
				'v-model'     => 'new_meta_field.value',
				'v-bind:name' => '"new_meta_fields[" + index + "][value]"',
			],
		];

		$new_meta_fields = [
			'new_meta_fields' => [
				'type'              => 'group',
				'wrapper_classes'   => 'wu-relative',
				'wrapper_html_attr' => [
					'v-for' => '(new_meta_field, index) in new_meta_fields',
				],
				'fields'            => [
					'new_meta_remove'         => [
						'type'            => 'note',
						'desc'            => sprintf(
							'<a title="%s" class="wu-no-underline wu-inline-block wu-text-gray-600" href="#" @click.prevent="() => new_meta_fields.splice(index, 1)"><span class="dashicons-wu-squared-cross"></span></a>',
							__('Remove', 'multisite-ultimate')
						),
						'wrapper_classes' => 'wu-absolute wu-top-0 wu-right-0',
					],
					'new_meta_slug'           => [
						'title'           => __('Slug', 'multisite-ultimate'),
						'type'            => 'text',
						'value'           => '',
						'wrapper_classes' => 'wu-w-1/4',
						'html_attr'       => [
							'v-on:input'  => "new_meta_field.slug = \$event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, '')",
							'v-model'     => 'new_meta_field.slug',
							'v-bind:name' => '"new_meta_fields[" + index + "][slug]"',
						],
					],
					'new_meta_title'          => [
						'title'           => __('Title', 'multisite-ultimate'),
						'type'            => 'text',
						'value'           => '',
						'wrapper_classes' => 'wu-w-1/4 wu-ml-2',
						'html_attr'       => [
							'v-bind:name' => '"new_meta_fields[" + index + "][title]"',
						],
					],
					'new_meta_type'           => [
						'title'           => __('Type', 'multisite-ultimate'),
						'type'            => 'select',
						'options'         => [
							'text'     => __('Text', 'multisite-ultimate'),
							'textarea' => __('Textarea', 'multisite-ultimate'),
							'checkbox' => __('Checkbox', 'multisite-ultimate'),
							'color'    => __('Color', 'multisite-ultimate'),
							'image'    => __('Image', 'multisite-ultimate'),
						],
						'wrapper_classes' => 'wu-w-1/4 wu-ml-2',
						'html_attr'       => [
							'v-model'     => 'new_meta_field.type',
							'v-bind:name' => '"new_meta_fields[" + index + "][type]"',
						],
					],
					'new_meta_value_text'     => $default_meta_value('text', '', true),
					'new_meta_value_textarea' => $default_meta_value('textarea'),
					'new_meta_value_checkbox' => $default_meta_value('checkbox', true),
					'new_meta_value_color'    => $default_meta_value('color', '#4299e1'),
					'new_meta_value_image'    => array_merge(
						$default_meta_value('image'),
						[
							'content_wrapper_classes' => 'wu-mt-2',
							'stacked'                 => true,
						]
					),
				],
			],
			'repeat_option'   => [
				'type'            => 'submit',
				'title'           => __('+ Add meta field', 'multisite-ultimate'),
				'classes'         => 'button wu-self-end',
				'wrapper_classes' => 'wu-bg-whiten wu-items-end',
				'html_attr'       => [
					'v-on:click.prevent' => '() => new_meta_fields.push({
						type: "text",
						slug: "",
					})',
				],
			],
		];

		$final_fields['new_meta_fields_wrapper'] = [
			'type'              => 'group',
			'classes'           => 'wu-grid',
			'wrapper_html_attr' => [
				'v-show' => 'new_meta_fields_show',
			],
			'fields'            => $new_meta_fields,
		];

		return $final_fields;
	}

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @return void
	 * @since 1.8.2
	 */
	public function register_widgets(): void {

		parent::register_widgets();

		$labels = $this->get_labels();

		$this->add_fields_widget(
			'at_a_glance',
			[
				'title'                 => __('At a Glance', 'multisite-ultimate'),
				'position'              => 'normal',
				'classes'               => 'wu-overflow-hidden wu-m-0 wu--mt-1 wu--mx-3 wu--mb-3',
				'field_wrapper_classes' => 'wu-w-1/3 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t-0 wu-border-l-0 wu-border-r wu-border-b-0 wu-border-gray-300 wu-border-solid wu-float-left wu-relative',
				'html_attr'             => [
					'style' => 'margin-top: -6px;',
				],
				'fields'                => [
					'id'            => [
						'type'          => 'text-display',
						'copy'          => true,
						'title'         => __('Customer ID', 'multisite-ultimate'),
						'display_value' => $this->get_object()->get_id(),
						'tooltip'       => '',
					],
					'last_login'    => [
						'edit'          => false,
						'title'         => __('Last Login', 'multisite-ultimate'),
						'type'          => 'text-edit',
						'value'         => $this->edit ? $this->get_object()->get_last_login(false) : __(
							'No date',
							'multisite-ultimate'
						),
						'display_value' => $this->edit ? $this->get_object()->get_last_login(false) : false,
					],
					'total_grossed' => [
						'type'          => 'text-display',
						'title'         => __('Total Grossed', 'multisite-ultimate'),
						'display_value' => wu_format_currency($this->get_object()->get_total_grossed()),
						'tooltip'       => '',
					],
				],
			]
		);

		$this->add_list_table_widget(
			'memberships',
			[
				'title'        => __('Memberships', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Customers_Membership_List_Table(),
				'query_filter' => [$this, 'memberships_query_filter'],
			]
		);

		$this->add_tabs_widget(
			'options',
			[
				'title'    => __('Customer Options', 'multisite-ultimate'),
				'position' => 'normal',
				'sections' => apply_filters(
					'wu_customer_options_sections',
					[
						'general'      => [
							'title'  => __('General', 'multisite-ultimate'),
							'desc'   => __('General options for the customer.', 'multisite-ultimate'),
							'icon'   => 'dashicons-wu-globe',
							'fields' => [
								'vip' => [
									'type'    => 'toggle',
									'title'   => __('VIP', 'multisite-ultimate'),
									'desc'    => __('Set this customer as a VIP.', 'multisite-ultimate'),
									'tooltip' => '',
									'value'   => $this->get_object()->is_vip(),
								],
							],
						],
						'billing_info' => [
							'title'  => __('Billing Info', 'multisite-ultimate'),
							'desc'   => __('Billing information for this particular customer', 'multisite-ultimate'),
							'icon'   => 'dashicons-wu-address',
							'fields' => $this->get_object()->get_billing_address()->get_fields(),
						],
						'custom_meta'  => [
							'title'  => __('Custom Meta', 'multisite-ultimate'),
							'desc'   => __('Custom data collected via Multisite Ultimate forms.', 'multisite-ultimate'),
							'icon'   => 'dashicons-wu-database wu-pt-px',
							'fields' => $this->generate_customer_meta_fields(),
							'state'  => [
								'display_unset_fields' => false,
								'new_meta_fields_show' => false,
								'new_meta_fields'      => [
									[
										'type' => 'text',
										'slug' => '',
									],
								],
							],
						],
						// @todo: bring these back
				// phpcs:disable
				// 'payment_methods' => array(
				// 	'title'  => __('Payment Methods', 'multisite-ultimate'),
				// 	'desc'   => __('Add extra information to this customer.', 'multisite-ultimate'),
				// 	'icon'   => 'dashicons-wu-credit-card',
				// 	'fields' => apply_filters('wu_customer_payment_methods', array(), $this->get_object(), $this),
				// ),
				// phpcs:enable
					],
					$this->get_object()
				),
			]
		);

		$this->add_list_table_widget(
			'payments',
			[
				'title'        => __('Payments', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Customers_Payment_List_Table(),
				'query_filter' => [$this, 'memberships_query_filter'],
			]
		);

		$this->add_list_table_widget(
			'sites',
			[
				'title'        => __('Sites', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Customers_Site_List_Table(),
				'query_filter' => [$this, 'sites_query_filter'],
			]
		);

		$this->add_list_table_widget(
			'events',
			[
				'title'        => __('Events', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
				'query_filter' => [$this, 'events_query_filter'],
			]
		);

		$this->add_fields_widget(
			'save',
			[
				'html_attr' => [
					'data-wu-app' => 'customer_save',
					'data-state'  => wp_json_encode(
						[
							'original_user_id'            => $this->get_object()->get_user_id(),
							'user_id'                     => $this->get_object()->get_user_id(),
							'original_email_verification' => $this->get_object()->get_email_verification(),
							'email_verification'          => $this->get_object()->get_email_verification(),
						]
					),
				],
				'before'    => wu_get_template_contents(
					'customers/widget-avatar',
					[
						'customer' => $this->get_object(),
						'user'     => $this->get_object()->get_user(),
					]
				),
				'fields'    => [
					'user_id'            => [
						'type'              => 'model',
						'title'             => __('User', 'multisite-ultimate'),
						'placeholder'       => __('Search WordPress user...', 'multisite-ultimate'),
						'desc'              => __('The WordPress user associated to this customer.', 'multisite-ultimate'),
						'value'             => $this->get_object()->get_user_id(),
						'tooltip'           => '',
						'min'               => 1,
						'html_attr'         => [
							'v-model'           => 'user_id',
							'data-model'        => 'user',
							'data-value-field'  => 'ID',
							'data-label-field'  => 'display_name',
							'data-search-field' => 'display_name',
							'data-max-items'    => 1,
							'data-selected'     => wp_json_encode($this->get_object()->get_user()->data),
						],
						'wrapper_html_attr' => [
							'v-cloak' => '1',
						],
					],
					'transfer_note'      => [
						'type'              => 'note',
						'desc'              => __(
							'Changing the user will transfer the customer and all its assets to the new user.',
							'multisite-ultimate'
						),
						'classes'           => 'wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
						'wrapper_html_attr' => [
							'v-show'  => '(original_user_id != user_id) && user_id',
							'v-cloak' => '1',
						],
					],
					'email_verification' => [
						'type'              => 'select',
						'title'             => __('Email Verification', 'multisite-ultimate'),
						'placeholder'       => __('Select Status', 'multisite-ultimate'),
						'desc'              => __(
							'The email verification status. This gets automatically switched to Verified when the customer verifies their email address.',
							'multisite-ultimate'
						),
						'options'           => [
							'none'     => __('None', 'multisite-ultimate'),
							'pending'  => __('Pending', 'multisite-ultimate'),
							'verified' => __('Verified', 'multisite-ultimate'),
						],
						'value'             => $this->get_object()->get_email_verification(),
						'tooltip'           => '',
						'wrapper_html_attr' => [
							'v-cloak' => '1',
						],
						'html_attr'         => [
							'v-model' => 'email_verification',
						],
					],
					'confirm_membership' => [
						'type'              => 'toggle',
						'title'             => __('Activate Memberships', 'multisite-ultimate'),
						'desc'              => __(
							'If you toggle this option, this change in status will also activate the related pending memberships. If any sites are pending, they are also going to be published automatically.',
							'multisite-ultimate'
						),
						'value'             => 0,
						'wrapper_html_attr' => [
							'v-if'    => 'email_verification !== original_email_verification && email_verification === "verified" && original_email_verification === "pending"',
							'v-cloak' => '1',
						],
					],
					'send_verification'  => [
						'type'              => 'submit',
						'title'             => __('Re-send Verification Email &rarr;', 'multisite-ultimate'),
						'value'             => 'send_verification',
						'classes'           => 'button wu-w-full',
						'wrapper_html_attr' => [
							'v-if'    => 'email_verification === "pending" && original_email_verification == "pending"',
							'v-cloak' => '1',
						],
					],
					'submit_save'        => [
						'type'              => 'submit',
						'title'             => $labels['save_button_label'],
						'placeholder'       => $labels['save_button_label'],
						'value'             => 'save',
						'classes'           => 'button button-primary wu-w-full',
						'wrapper_html_attr' => [
							'v-show'  => 'original_user_id == user_id || !user_id',
							'v-cloak' => '1',
						],
					],
					'transfer'           => [
						'type'              => 'link',
						'display_value'     => __('Transfer Customer', 'multisite-ultimate'),
						'wrapper_classes'   => 'wu-bg-gray-200',
						'classes'           => 'button wubox wu-w-full wu-text-center',
						'wrapper_html_attr' => [
							'v-show'  => 'original_user_id != user_id && user_id',
							'v-cloak' => '1',
						],
						'html_attr'         => [
							'v-bind:href' => "'" . wu_get_form_url(
								'transfer_customer',
								[
									'id'             => $this->get_object()->get_id(),
									'target_user_id' => '',
								]
							) . "=' + user_id",
							'title'       => __('Transfer Customer', 'multisite-ultimate'),
						],
					],
				],
			]
		);

		$this->add_fields_widget(
			'last-login',
			[
				'title'  => __('Last Login & IPs', 'multisite-ultimate'),
				'fields' => [
					'last_login' => [
						'edit'          => true,
						'title'         => __('Last Login', 'multisite-ultimate'),
						'type'          => 'text-edit',
						'date'          => true,
						'value'         => $this->edit ? $this->get_object()->get_last_login(false) : __(
							'No date',
							'multisite-ultimate'
						),
						'display_value' => $this->edit ? $this->get_object()->get_last_login(false) : false,
						'placeholder'   => '2020-04-04 12:00:00',
						'html_attr'     => [
							'wu-datepicker'   => 'true',
							'data-format'     => 'Y-m-d H:i:S',
							'data-allow-time' => 'true',
						],
					],
					'ips'        => [
						'title'         => __('IP Address', 'multisite-ultimate'),
						'type'          => 'text-edit',
						'display_value' => $this->get_object()->get_last_ip(),
					],
					'country'    => [
						'title'         => __('IP Address Country', 'multisite-ultimate'),
						'type'          => 'text-edit',
						'display_value' => [$this, 'render_country'],
					],
				],
			]
		);
	}

	/**
	 * Render the IP info flag.
	 *
	 * @return string
	 * @since 2.0.0
	 */
	public function render_country() {

		$country_code = $this->get_object()->get_meta('ip_country');

		$country_name = wu_get_country_name($country_code);

		if ($country_code) {
			$html = sprintf(
				'<span>%s</span><span class="wu-flag-icon wu-w-5 wu-ml-1" %s>%s</span>',
				$country_name,
				wu_tooltip_text($country_name),
				wu_get_flag_emoji((string) $country_code)
			);
		} else {
			$html = $country_name;
		}

		return $html;
	}

	/**
	 * Returns the title of the page.
	 *
	 * @return string Title of the page.
	 * @since 2.0.0
	 */
	public function get_title() {

		return $this->edit ? __('Edit Customer', 'multisite-ultimate') : __('Add new Customer', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @return string Menu label of the page.
	 * @since 2.0.0
	 */
	public function get_menu_title() {

		return __('Edit Customer', 'multisite-ultimate');
	}

	/**
	 * Returns the action links for that page.
	 *
	 * @return array
	 * @since 1.8.2
	 */
	public function action_links() {

		return [];
	}

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function get_labels() {

		return [
			'edit_label'          => __('Edit Customer', 'multisite-ultimate'),
			'add_new_label'       => __('Add new Customer', 'multisite-ultimate'),
			'updated_message'     => __('Customer updated with success!', 'multisite-ultimate'),
			'title_placeholder'   => __('Enter Customer', 'multisite-ultimate'),
			'title_description'   => '',
			'save_button_label'   => __('Save Customer', 'multisite-ultimate'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Customer', 'multisite-ultimate'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'multisite-ultimate'),
		];
	}

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @param array $args Query args passed to the list table.
	 *
	 * @return array Modified query args.
	 * @since 2.0.0
	 */
	public function memberships_query_filter($args) {

		$args['customer_id'] = $this->get_object()->get_id();

		return $args;
	}

	/**
	 * Filters the list table to return only relevant sites.
	 *
	 * @param array $args Query args passed to the list table.
	 *
	 * @return array Modified query args.
	 * @since 2.0.0
	 */
	public function sites_query_filter($args) {

		$args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery
			'customer_id' => [
				'key'   => 'wu_customer_id',
				'value' => $this->get_object()->get_id(),
			],
		];

		return $args;
	}

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @param array $args Query args passed to the list table.
	 *
	 * @return array Modified query args.
	 * @since 2.0.0
	 */
	public function events_query_filter($args) {

		$extra_args = [
			'object_type' => 'customer',
			'object_id'   => absint($this->get_object()->get_id()),
		];

		return array_merge($args, $extra_args);
	}

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @return \WP_Ultimo\Models\Customer
	 * @since 2.0.0
	 */
	public function get_object() {

		if (null !== $this->object) {
			return $this->object;
		}

		$item_id = wu_request('id', 0);

		$item = wu_get_customer($item_id);

		if ( ! $item || $item->get_type() !== 'customer') {
			wp_safe_redirect(wu_network_admin_url('wp-ultimo-customers'));

			exit;
		}

		$this->object = $item;

		return $this->object;
	}

	/**
	 * Customers have titles.
	 *
	 * @since 2.0.0
	 */
	public function has_title(): bool {

		return false;
	}

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function handle_save(): void {

		// Nonce handled in calling method.
        // phpcs:disable WordPress.Security.NonceVerification
		if (isset($_POST['submit_button']) && 'send_verification' === $_POST['submit_button']) {
			$customer = $this->get_object();

			$customer->send_verification_email();

			$redirect_url = wu_network_admin_url(
				'wp-ultimo-edit-customer',
				[
					'id'                       => $customer->get_id(),
					'notice_verification_sent' => 1,
				]
			);

			wp_safe_redirect($redirect_url);

			exit;
		}

		$object = $this->get_object();

		$_POST['vip'] = wu_request('vip');

		$billing_address = $object->get_billing_address();

		$billing_address->attributes($_POST);

		$valid_address = $billing_address->validate();

		if (is_wp_error($valid_address)) {
			$errors = implode('<br>', $valid_address->get_error_messages());

			WP_Ultimo()->notices->add($errors, 'error', 'network-admin');

			return;
		}

		$object->set_billing_address($billing_address);

		/**
		 * Deal with publishing sites and memberships
		 */
		$should_confirm_membership = wu_request('confirm_membership', false);

		if ($should_confirm_membership) {
			$this->confirm_memberships();
		}

		/**
		 * Deal with custom metadata
		 */
		$custom_meta_keys = wu_get_all_customer_meta($this->get_object()->get_id(), true);

		foreach ($custom_meta_keys as $key => $value) {
			wu_update_customer_meta(
				$object->get_id(),
				$key,
				wu_request("meta_key_$key"),
				$value['type'],
				$value['title']
			);
		}

		foreach (wu_get_isset($_POST, 'new_meta_fields', []) as $meta_field) {
			$slug = sanitize_key(wu_get_isset($meta_field, 'slug', ''));

			if (empty($slug) || $this->restricted_customer_meta_keys($slug)) {
				continue;
			}

			$title = ! empty($meta_field['title']) ? $meta_field['title'] : wu_slug_to_name($slug);
			$type  = ! empty($meta_field['type']) ? $meta_field['type'] : 'text';
			$value = wu_get_isset($meta_field, 'value', '');

			wu_update_customer_meta($object->get_id(), $slug, $value, $type, $title);
		}

		unset($_POST['new_meta_fields']);
		// phpcs:enable

		parent::handle_save();
	}

	/**
	 * Checks whether a meta slug is restricted or not.
	 *
	 * @param string $meta_slug The meta slug to be verified.
	 *
	 * @return bool
	 * @since 2.3.0
	 */
	public function restricted_customer_meta_keys(string $meta_slug): bool {

		$restricted_meta = [
			'wu_verification_key',
			'wu_billing_address',
			'ip_state',
			'ip_country',
			'wu_has_trialed',
			'wu_custom_meta_keys',
		];

		return in_array($meta_slug, $restricted_meta, true);
	}

	/**
	 * Handles the email verification sent notice
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function handle_send_verification_notice(): void {

		if (isset($_GET['notice_verification_sent'])) : // phpcs:ignore WordPress.Security.NonceVerification ?>

			<div id="message" class="updated notice wu-admin-notice notice-success is-dismissible below-h2">
				<p><?php esc_html_e('Verification email sent!', 'multisite-ultimate'); ?></p>
			</div>

			<?php
		endif;
	}

	/**
	 * Adds removable query args to the WP database.
	 *
	 * @param array $removable_query_args Contains the removable args.
	 *
	 * @return array
	 */
	public function remove_query_args($removable_query_args) {

		if ( ! is_array($removable_query_args)) {
			return $removable_query_args;
		}

		$removable_query_args[] = 'notice_verification_sent';

		return $removable_query_args;
	}

	/**
	 * Confirms the memberships related to a customer.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	protected function confirm_memberships() {

		$memberships = $this->get_object()->get_memberships();

		foreach ($memberships as $membership) {
			if ($membership->get_status() === Membership_Status::PENDING) {
				$membership->set_status(Membership_Status::ACTIVE);

				$membership->save();
			}
		}
	}
}
