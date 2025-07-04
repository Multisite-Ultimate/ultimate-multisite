<?php
/**
 * Multisite Ultimate Broadcast Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Models\Broadcast;

/**
 * Multisite Ultimate Broadcast Edit/Add New Admin Page.
 */
class Broadcast_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-broadcast';

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
	public $object_id = 'broadcast';

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
	protected $highlight_menu_slug = 'wp-ultimo-broadcasts';

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
		'network_admin_menu' => 'wu_edit_broadcasts',
	];

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets(): void {

		parent::register_widgets();

		$this->add_list_table_widget(
			'events',
			[
				'title'        => __('Events', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
				'query_filter' => [$this, 'events_query_filter'],
			]
		);

		$this->add_save_widget(
			'save',
			[
				'html_attr' => [
					'data-wu-app' => 'save_broadcast',
					'data-state'  => wu_convert_to_state(
						[
							'type' => $this->get_object()->get_type(),
						]
					),
				],
				'fields'    => [
					'type'        => [
						'type'        => 'select',
						'title'       => __('Broadcast Type', 'multisite-ultimate'),
						'placeholder' => __('Type', 'multisite-ultimate'),
						'desc'        => __('Broadcast type cannot be edited.', 'multisite-ultimate'),
						'options'     => [
							'broadcast_email'  => __('Email', 'multisite-ultimate'),
							'broadcast_notice' => __('Admin Notice', 'multisite-ultimate'),
						],
						'value'       => $this->get_object()->get_type(),
						'tooltip'     => '',
						'html_attr'   => [
							'disabled' => 'disabled',
							'name'     => '',
						],
					],
					'notice_type' => [
						'type'              => 'select',
						'title'             => __('Broadcast Status', 'multisite-ultimate'),
						'placeholder'       => __('Status', 'multisite-ultimate'),
						'desc'              => __('This option determines the color of the admin notice.', 'multisite-ultimate'),
						'options'           => [
							'info'    => __('Info (blue)', 'multisite-ultimate'),
							'success' => __('Success (green)', 'multisite-ultimate'),
							'warning' => __('Warning (yellow)', 'multisite-ultimate'),
							'error'   => __('Error (red)', 'multisite-ultimate'),
						],
						'value'             => $this->get_object()->get_notice_type(),
						'tooltip'           => '',
						'wrapper_html_attr' => [
							'v-if'    => 'type === "broadcast_notice"',
							'v-cloak' => 1,
						],
					],
				],
			]
		);

		add_meta_box('wp-ultimo-broadcast-customer-targets', __('Customer Targets', 'multisite-ultimate'), [$this, 'output_default_widget_customer_targets'], get_current_screen()->id, 'side');

		add_meta_box('wp-ultimo-broadcast-product-targets', __('Product Targets', 'multisite-ultimate'), [$this, 'output_default_widget_product_targets'], get_current_screen()->id, 'side');
	}

	/**
	 * Outputs the markup for the customer targets widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_customer_targets(): void {

		$object = $this->get_object();

		$all_targets = $object->get_message_targets();

		$targets = [];

		$customer_targets = wu_get_isset($all_targets, 'customers', '');

		if ($customer_targets) {
			if (is_array($all_targets['customers'])) {
				$all_targets['customers'] = $all_targets['customers'][0];
			}

			$targets = explode(',', (string) $all_targets['customers']);
		}

		$targets_count = count($targets);

		echo '<div class="wu-bg-gray-100 wu--mt-3 wu--mb-6 wu--mx-3">
							<ul class="wu-widget-list">
								<li class="wu-p-2 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-200 wu-border-solid">
									<div class="wu-p-2 wu-mr-1 wu-flex wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-bg-white wu-relative wu-overflow-hidden">';

		switch ($targets) {
			case $targets_count < 0:
				printf(
					"<span class='dashicons dashicons-wu-block wu-text-gray-600 wu-px-1 wu-pr-3'>&nbsp;</span>
						<div class=''>
							<span class='wu-block wu-py-3 wu-text-gray-600 wu-text-2xs wu-font-bold wu-uppercase'>%s</span>
						</div>",
					esc_html__('No customer found', 'multisite-ultimate')
				);

				break;
			case 1 === $targets_count:
				$customer = wu_get_customer($targets[0]);

				$url_atts = [
					'id' => $customer->get_id(),
				];

				$customer_link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

				$avatar = get_avatar(
					$customer->get_user_id(),
					32,
					'identicon',
					'',
					[
						'force_display' => true,
						'class'         => 'wu-rounded-full wu-border-solid wu-border-1 wu-border-white hover:wu-border-gray-400',
					]
				);

				$display_name = $customer->get_display_name();

				$id = $customer->get_id();

				$email = $customer->get_email_address();

				printf(
					'<a href="%s" class="wu-p-1 wu-flex wu-flex-grow wu-rounded wu-items-center wu-no-underline">
										%s
										<div class="wu-pl-2">
												<strong class="wu-block">%s <small class="wu-font-normal">(#%s)</small></strong>
												<small>%s</small>
										</div>
								</a>',
					esc_attr($customer_link),
					esc_html($display_name),
					$avatar, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_html($id),
					esc_html($email)
				);
				break;
			case $targets_count > 1:
				foreach ($targets as $key => $target) {
					$customer = wu_get_customer($target);

					$tooltip_name = $customer->get_display_name();

					$email = $customer->get_email_address();

					$avatar = get_avatar(
						$email,
						32,
						'identicon',
						'',
						[
							'class' => 'wu-rounded-full wu-border-solid wu-border-1 wu-border-white hover:wu-border-gray-400',
						]
					);

					$url_atts = [
						'id' => $customer->get_id(),
					];

					$customer_link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

					printf(
						"<div class='wu-flex wu--mr-4'><a role='tooltip' aria-label='%s' href='%s'>%s</a></div>",
						esc_attr($tooltip_name),
						esc_attr($customer_link),
						$avatar // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
				}

				if ($targets_count < 7) {
					$modal_atts = [
						'action'      => 'wu_modal_targets_display',
						'object_id'   => $object->get_id(),
						'width'       => '400',
						'height'      => '360',
						'target_type' => 'customers',
					];

					printf(
						'<div class="wu-inline-block wu--mr-4">
										<a href="%s" title="%s" class="wubox wu-no-underline"><span class="wu-ml-6 wu-uppercase wu-text-xs wu-text-gray-600 wu-font-bold"> %s %s</span></a>
										</div>',
						esc_attr(wu_get_form_url('view_broadcast_targets', $modal_atts)),
						esc_attr__('Targets', 'multisite-ultimate'),
						esc_html($targets_count),
						esc_html__('Targets', 'multisite-ultimate')
					);
				} else {
					$count = $targets_count - 6;

					$modal_atts = [
						'action'      => 'wu_modal_targets_display',
						'object_id'   => $object->get_id(),
						'width'       => '400',
						'height'      => '360',
						'target_type' => 'customers',
					];

					printf(
						'<div class="wu-inline-block wu-ml-4">
									<a href="%s" title="%s" class="wubox wu-no-underline"><span class="wu-pl-2 wu-uppercase wu-text-xs wu-font-bold"> %s %s</span></a>
									</div>',
						esc_attr(wu_get_form_url('view_broadcast_targets', $modal_atts)),
						esc_attr__('Targets', 'multisite-ultimate'),
						esc_html($targets_count),
						esc_html__('Targets', 'multisite-ultimate')
					);
				}

				break;
		}

		echo '</div></li></ul></div>';
	}

	/**
	 * Outputs the markup for the products targets widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_product_targets(): void {

		$object = $this->get_object();

		$targets = wu_get_broadcast_targets($object->get_id(), 'products');

		$product_targets = [];

		if ($targets) {
			foreach ($targets as $key => $value) {
				$product = wu_get_product($value);

				if ($product) {
					$modal_atts = [
						'action'     => 'wu_modal_product_targets_display',
						'product_id' => $product->get_id(),
						'width'      => '400',
						'height'     => '360',
					];

					$link = wu_get_form_url('view_broadcast_targets', $modal_atts);

					$image = $product->get_featured_image('thumbnail');

					$image = $image ? sprintf('<img class="wu-w-8 wu-h-8 wu-rounded-full" src="%s">', esc_attr($image)) : '<span class="dashicons-wu-image"></span>';

					$plan_customers = wu_get_membership_customers($product->get_id());

					$customer_count = (int) 0;

					if ($plan_customers) {
						$customer_count = count($plan_customers);
					}

					// translators: %s is the number of customers.
					$description = sprintf(__('%s customer(s) targeted.', 'multisite-ultimate'), $customer_count);

					$product_targets[ $key ] = [
						'link'         => $link,
						'avatar'       => $image,
						'display_name' => $product->get_name(),
						'id'           => $product->get_id(),
						'description'  => $description,
					];
				}
			}
		}

		$args = [
			'targets'       => $product_targets,
			'loading_text'  => __('Loading...', 'multisite-ultimate'),
			'wrapper_class' => 'wu-bg-gray-100 wu--mt-3 wu--mb-6 wu--mx-3',
			'modal_class'   => 'wubox',
		];

		wu_get_template('broadcast/widget-targets', $args);
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Broadcast', 'multisite-ultimate') : __('Add new Broadcast', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Broadcast', 'multisite-ultimate');
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
			'edit_label'          => __('Edit Broadcast', 'multisite-ultimate'),
			'add_new_label'       => __('Add new Broadcast', 'multisite-ultimate'),
			'updated_message'     => __('Broadcast updated with success!', 'multisite-ultimate'),
			'title_placeholder'   => __('Enter Broadcast Title', 'multisite-ultimate'),
			'title_description'   => __('This title is used on the message itself, and in the case of a broadcast email, it will be used as the subject.', 'multisite-ultimate'),
			'save_button_label'   => __('Save Broadcast', 'multisite-ultimate'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Broadcast', 'multisite-ultimate'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'multisite-ultimate'),
		];
	}

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function query_filter($args) {

		$extra_args = [
			'object_type' => 'broadcast',
			'object_id'   => absint($this->get_object()->get_id()),
		];

		return array_merge($args, $extra_args);
	}

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Broadcast
	 */
	public function get_object() {

		if (isset($_GET['id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query = new \WP_Ultimo\Database\Broadcasts\Broadcast_Query();

			$item = $query->get_item_by('id', (int) $_GET['id']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! $item) {
				wp_safe_redirect(wu_network_admin_url('wp-ultimo-broadcasts'));

				exit;
			}
			/** @var Broadcast $item */
			return $item;
		}

		return new Broadcast();
	}

	/**
	 * Broadcasts have titles.
	 *
	 * @since 2.0.0
	 */
	public function has_title(): bool {

		return true;
	}

	/**
	 * Wether or not this pages should have an editor field.
	 *
	 * @since 2.0.0
	 */
	public function has_editor(): bool {

		return true;
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
			'object_type' => 'broadcast',
			'object_id'   => absint($this->get_object()->get_id()),
		];

		return array_merge($args, $extra_args);
	}
}
