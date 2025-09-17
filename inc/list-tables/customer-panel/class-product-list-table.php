<?php
/**
 * Customers Site List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\List_Tables\Product_List_Table as Parent_Product_List_Table;

/**
 * Product List Table class.
 *
 * @since 2.0.0
 */
class Product_List_Table extends Parent_Product_List_Table {

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct();

		$this->modes = [
			'grid' => __('Grid View', 'ultimate-multisite'),
		];

		$this->current_mode = 'grid';
	}

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		return [];
	}

	/**
	 * Resets the filters.
	 *
	 * @since 2.0.0
	 */
	public function get_filters(): array {

		return [
			'filters'      => [],
			'date_filters' => [],
		];
	}

	/**
	 * Resets bulk actions.
	 *
	 * @since 2.0.0
	 *
	 * @param string $which Top or bottom.
	 * @return array
	 */
	public function bulk_actions($which = '') {

		return [];
	}

	/**
	 * Renders the customer card for grid mode.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Customer $item The customer being shown.
	 * @return void
	 */
	public function single_row_grid($item): void {

		wu_get_template(
			'base/products/grid-item',
			[
				'item'       => $item,
				'list_table' => $this,
			]
		);
	}
}
