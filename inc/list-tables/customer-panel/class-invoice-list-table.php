<?php
/**
 * Customers Payment List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\List_Tables\Payment_List_Table as Parent_Payment_List_Table;

/**
 * Payment List Table class.
 *
 * @since 2.0.0
 */
class Invoice_List_Table extends Parent_Payment_List_Table {

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'hash'         => __('Code', 'multisite-ultimate'),
			'status'       => __('Status', 'multisite-ultimate'),
			'total'        => __('Total', 'multisite-ultimate'),
			'date_created' => __('Created at', 'multisite-ultimate'),
		];

		return $columns;
	}

	/**
	 * Clears the bulk actions.
	 *
	 * @since 2.0.0
	 *
	 * @param string $which Top or bottom.
	 * @return array
	 */
	public function bulk_actions($which = '') {

		return [];
	}
}
