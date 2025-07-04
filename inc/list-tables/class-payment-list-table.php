<?php
/**
 * Payment List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

use WP_Ultimo\Database\Payments\Payment_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Payment List Table class.
 *
 * @since 2.0.0
 */
class Payment_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Payments\Payment_Query::class;

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(
			[
				'singular' => __('Payment', 'multisite-ultimate'),
				'plural'   => __('Payments', 'multisite-ultimate'),
				'ajax'     => true,
				'add_new'  => [
					'url'     => wu_get_form_url('add_new_payment'),
					'classes' => 'wubox',
				],
			]
		);
	}

	/**
	 * Adds the extra search field when the search element is present.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_query_fields() {

		$_filter_fields                  = parent::get_extra_query_fields();
		$_filter_fields['membership_id'] = wu_request('membership_id', false);
		$_filter_fields['customer_id']   = wu_request('customer_id', false);
		$_filter_fields['parent_id__in'] = ['0', 0, '', null];

		return $_filter_fields;
	}

	/**
	 * Displays the payment reference code.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_hash($item) {

		$url_atts = [
			'id' => $item->get_id(),
		];

		$code = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-payment', $url_atts), $item->get_hash());

		$actions = [
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-payment', $url_atts), __('Edit', 'multisite-ultimate')),
			'delete' => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Delete', 'multisite-ultimate'),
				wu_get_form_url(
					'delete_modal',
					[
						'model' => 'payment',
						'id'    => $item->get_id(),
					]
				),
				__('Delete', 'multisite-ultimate')
			),
		];

		$html = "<span class='wu-font-mono'><strong>{$code}</strong></span>";

		return $html . $this->row_actions($actions);
	}

	/**
	 * Displays the membership photo and special status.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_status($item) {

		$label = $item->get_status_label();

		$class = $item->get_status_class();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-inline-block wu-leading-none wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";
	}

	/**
	 * Returns the number of subscriptions owned by this membership.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_product($item) {

		$product = $item->get_product();

		if ( ! $product) {
			return __('No product found', 'multisite-ultimate');
		}

		$url_atts = [
			'product_id' => $product->get_id(),
		];

		$actions = [
			'view' => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-product', $url_atts), __('View', 'multisite-ultimate')),
		];

		$html = $product->get_name();

		return $html . $this->row_actions($actions);
	}

	/**
	 * Displays the column for the total amount of the payment.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_total($item) {

		$gateway = wu_slug_to_name($item->get_gateway());

		return wu_format_currency($item->get_total()) . "<small class='wu-block'>{$gateway}</small>";
	}

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'cb'           => '<input type="checkbox" />',
			'hash'         => wu_tooltip(__('Reference Code', 'multisite-ultimate'), 'dashicons-wu-hash wu-text-xs'),
			'status'       => __('Status', 'multisite-ultimate'),
			'customer'     => __('Customer', 'multisite-ultimate'),
			'membership'   => __('Membership', 'multisite-ultimate'),
			'total'        => __('Total', 'multisite-ultimate'),
			'date_created' => __('Created at', 'multisite-ultimate'),
			'id'           => __('ID', 'multisite-ultimate'),
		];

		return $columns;
	}

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 */
	public function get_filters(): array {

		return [
			'filters'      => [

				/**
				 * Status
				 */
				'status'  => [
					'label'   => __('Status', 'multisite-ultimate'),
					'options' => [
						'pending'   => __('Pending', 'multisite-ultimate'),
						'completed' => __('Completed', 'multisite-ultimate'),
						'refund'    => __('Refund', 'multisite-ultimate'),
						'partial'   => __('Partial', 'multisite-ultimate'),
						'failed'    => __('Failed', 'multisite-ultimate'),
					],
				],

				/**
				 * Gateway
				 */
				'gateway' => [
					'label'   => __('Gateway', 'multisite-ultimate'),
					'options' => [
						'free'   => __('Free', 'multisite-ultimate'),
						'manual' => __('Manual', 'multisite-ultimate'),
						'paypal' => __('Paypal', 'multisite-ultimate'),
						'stripe' => __('Stripe', 'multisite-ultimate'),
					],
				],
			],
			'date_filters' => [

				/**
				 * Created At
				 */
				'date_created' => [
					'label'   => __('Created At', 'multisite-ultimate'),
					'options' => $this->get_default_date_filter_options(),
				],
			],
		];
	}

	/**
	 * Returns the pre-selected filters on the filter bar.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		return [
			'all'                          => [
				'field' => 'status',
				'url'   => add_query_arg('status', 'all'),
				'label' => __('All Payments', 'multisite-ultimate'),
				'count' => 0,
			],
			Payment_Status::COMPLETED      => [
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::COMPLETED),
				'label' => __('Completed', 'multisite-ultimate'),
				'count' => 0,
			],
			Payment_Status::PENDING        => [
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::PENDING),
				'label' => __('Pending', 'multisite-ultimate'),
				'count' => 0,
			],
			Payment_Status::PARTIAL_REFUND => [
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::PARTIAL_REFUND),
				'label' => __('Partially Refunded', 'multisite-ultimate'),
				'count' => 0,
			],
			Payment_Status::REFUND         => [
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::REFUND),
				'label' => __('Refunded', 'multisite-ultimate'),
				'count' => 0,
			],
			Payment_Status::FAILED         => [
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::FAILED),
				'label' => __('Failed', 'multisite-ultimate'),
				'count' => 0,
			],
		];
	}
}
