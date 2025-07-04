<?php
/**
 * Payment List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Payment List Table class.
 *
 * @since 2.0.0
 */
class Line_Item_List_Table extends Payment_List_Table {

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
				'singular' => __('Line Item', 'multisite-ultimate'),  // singular name of the listed records
				'plural'   => __('Line Items', 'multisite-ultimate'), // plural name of the listed records
				'ajax'     => true,                         // does this table support ajax?
			]
		);
	}

	/**
	 * Get the payment object.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Payment
	 */
	public function get_payment() {

		$payment_id = wu_request('id');

		return wu_get_payment($payment_id);
	}

	/**
	 * Overrides the parent get_items to add a total line.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $per_page Items per page. This gets overridden as well.
	 * @param integer $page_number The page number.
	 * @param boolean $count Return as count or not.
	 * @return array
	 */
	public function get_items($per_page = 5, $page_number = 1, $count = false) {

		$payment = $this->get_payment();

		$items = $payment->get_line_items();

		if ($count) {
			return count($items);
		}

		return $items;
	}

	/**
	 * Displays the name of the product and description being hired.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Line_Item $item Payment object.
	 * @return string
	 */
	public function column_service($item) {

		if ( ! $item) {
			return '--';
		}

		$url_atts = [
			'id'           => $this->get_payment()->get_id(),
			'line_item_id' => $item->get_id(),
		];

		$actions = [
			'edit'   => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Edit Item', 'multisite-ultimate'), wu_get_form_url('edit_line_item', $url_atts), __('Edit', 'multisite-ultimate')),
			'delete' => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete Item', 'multisite-ultimate'), wu_get_form_url('delete_line_item', $url_atts), __('Delete', 'multisite-ultimate')),
		];

		$html = sprintf('<span class="wu-block wu-text-gray-700">%s</span>', $item->get_title());

		$html .= sprintf('<span class="wu-block wu-text-gray-600 wu-text-xs">%s</span>', $item->get_description());

		return $html . $this->row_actions($actions);
	}

	/**
	 * Displays the tax rate for the item.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_unit_price($item) {

		$html = wu_format_currency($item->get_unit_price());

		// translators: %s is the quantity of items in the cart
		$quantity = sprintf(__('Quantity: %s', 'multisite-ultimate'), $item->get_quantity());

		return $html . sprintf('<small class="wu-block">%s</small>', $quantity);
	}

	/**
	 * Displays the tax rate for the item.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_tax_total($item) {

		$html = wu_format_currency($item->get_tax_total());

		$tax_rate = '';

		if ($item->get_tax_type() === 'percentage' && $item->get_tax_rate()) {
			$tax_rate = $item->get_tax_rate() . '%';
		}

		$tax_label = $item->get_tax_rate() ? ($item->get_tax_label() ?: __('Tax Applied', 'multisite-ultimate')) : __('No Taxes Applied', 'multisite-ultimate');

		return $html . sprintf('<small class="wu-block">%s (%s)</small>', $tax_rate, $tax_label);
	}

	/**
	 * Displays the tax rate for the item.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_discounts_total($item) {

		$html = wu_format_currency($item->get_discount_total());

		$tax_rate = '';

		if ($item->get_discount_type() === 'percentage' && $item->get_discount_rate()) {
			$tax_rate = $item->get_discount_rate() . '%';
		}

		$tax_label = $item->get_discount_rate() ? ($item->get_discount_label() ?: __('Discount', 'multisite-ultimate')) : __('No discount', 'multisite-ultimate');

		return $html . sprintf('<small class="wu-block">%s (%s)</small>', $tax_rate, $tax_label);
	}

	/**
	 * Displays the total column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_total($item) {

		return wu_format_currency($item->get_total());
	}

	/**
	 * Displays the subtotal column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_subtotal($item) {

		return wu_format_currency($item->get_subtotal());
	}

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'service'         => __('Service', 'multisite-ultimate'),
			'unit_price'      => __('Unit Price', 'multisite-ultimate'),
			'discounts_total' => __('discounts', 'multisite-ultimate'),
			'subtotal'        => __('Subtotal', 'multisite-ultimate'),
			'tax_total'       => __('Taxes', 'multisite-ultimate'),
			'total'           => __('Total', 'multisite-ultimate'),
		];

		return $columns;
	}

	/**
	 * Leaves no sortable items on the columns.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sortable_columns() {

		return [];
	}
}
