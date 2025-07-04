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

use WP_Ultimo\List_Tables\Site_List_Table as Parent_Site_List_Table;

/**
 * Site List Table class.
 *
 * @since 2.0.0
 */
class Site_List_Table extends Parent_Site_List_Table {

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct();

		$this->modes = [
			'grid' => __('Grid View'),
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
	 * Clears filters.
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
	 * Clears views.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		return [
			'all' => [
				'field' => 'type',
				'url'   => add_query_arg('type', 'all'),
				'label' => __('Your Sites', 'multisite-ultimate'),
				'count' => 0,
			],
		];
	}

	/**
	 * Get the extra fields based on the request.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_fields() {

		$customer = wu_get_current_customer();

		if ( ! $customer) {
			return [
				'blog_id__in' => ['null_id'], // pass absurd value to make sure the query returns nothing.
			];
		}

		$fields = parent::get_extra_fields();

		$fields = [
			'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'customer_id' => [
					'key'   => 'wu_customer_id',
					'value' => $customer->get_id(),
				],
			],
		];

		return $fields;
	}
}
