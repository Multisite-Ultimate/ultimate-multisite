<?php
/**
 * Customers Payment List Table class.
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
class Customers_Payment_List_Table extends Payment_List_Table {

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

		echo wu_responsive_table_row( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			[
				'id'     => $item->get_id(),
				'title'  => $item->get_hash(),
				'url'    => wu_network_admin_url(
					'wp-ultimo-edit-payment',
					[
						'id' => $item->get_id(),
					]
				),
				'status' => $this->column_status($item),
			],
			[
				'total'   => [
					'icon'  => 'dashicons-wu-shopping-bag1 wu-align-middle wu-mr-1',
					'label' => __('Payment Total', 'multisite-ultimate'),
					'value' => wu_format_currency($item->get_total()),
				],
				'gateway' => [
					'icon'  => 'dashicons-wu-credit-card2 wu-align-middle wu-mr-1',
					'label' => __('Gateway', 'multisite-ultimate'),
					'value' => wu_slug_to_name($item->get_gateway()),
				],
			],
			[
				'date_created' => [
					'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
					'label' => '',
					// translators: %s is a placeholder for the human-readable time difference, e.g., "2 hours ago"
					'value' => sprintf(__('Created %s', 'multisite-ultimate'), wu_human_time_diff(strtotime((string) $item->get_date_created()))),
				],
			]
		);
	}
}
