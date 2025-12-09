<?php
/**
 * Customers' Membership List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Membership List Table class.
 *
 * @since 2.0.0
 */
class Customers_Membership_List_Table extends Membership_List_Table {

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'responsive' => '',
		];

		return $columns;
	}

	/**
	 * Renders the inside column responsive.
	 *
	 * @since 2.0.0
	 *
	 * @param object $item The item being rendered.
	 * @return void
	 */
	public function column_responsive($item): void {

		$p = $item->get_plan();

		$expired = strtotime((string) $item->get_date_expiration()) <= time();

		$product_count = 1 + count($item->get_addon_ids());

		// translators: %s is the product name, %2$s is the count of other products.
		$products_list = $p ? sprintf(_n('Contains %1$s', 'Contains %1$s and %2$s other product(s)', $product_count, 'ultimate-multisite'), $p->get_name(), count($item->get_addon_ids())) : ''; // phpcs:ignore

		wu_responsive_table_row(
			[
				'id'     => $item->get_id(),
				'title'  => $item->get_hash(),
				'url'    => wu_network_admin_url(
					'wp-ultimo-edit-membership',
					[
						'id' => $item->get_id(),
					]
				),
				'status' => $this->column_status($item),
			],
			[
				'total'    => [
					'icon'  => 'dashicons-wu-shopping-bag1 wu-align-middle wu-mr-1',
					'label' => __('Payment Total', 'ultimate-multisite'),
					'value' => $item->get_price_description(),
				],
				'products' => [
					'icon'  => 'dashicons-wu-package wu-align-middle wu-mr-1',
					'label' => __('Products', 'ultimate-multisite'),
					'value' => $products_list,
				],
				'gateway'  => [
					'icon'  => 'dashicons-wu-credit-card2 wu-align-middle wu-mr-1',
					'label' => __('Gateway', 'ultimate-multisite'),
					'value' => wu_slug_to_name($item->get_gateway()),
				],
			],
			[
				'date_expiration' => [
					'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
					'label' => __('Expires', 'ultimate-multisite'),
					// translators: %s is a placeholder for the human-readable time difference, e.g., "2 hours ago"
					'value' => sprintf($expired ? __('Expired %s', 'ultimate-multisite') : __('Expiring %s', 'ultimate-multisite'), wu_human_time_diff(strtotime((string) $item->get_date_expiration()))),
				],
				'date_created'    => [
					'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
					'label' => __('Created at', 'ultimate-multisite'),
					// translators: %s is a placeholder for the human-readable time difference, e.g., "2 hours ago"
					'value' => sprintf(__('Created %s', 'ultimate-multisite'), wu_human_time_diff(strtotime((string) $item->get_date_created()))),
				],
			]
		);
	}
}
