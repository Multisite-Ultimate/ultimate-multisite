<?php
/**
 * Customers Site List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Site List Table class.
 *
 * @since 2.0.0
 */
class Inside_Events_List_Table extends Event_List_Table {

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

		$first_row = [
			'id'   => [
				'icon'  => 'dashicons-wu-hash wu-align-middle wu-mr-1',
				'label' => __('Event ID', 'multisite-ultimate'),
				'value' => $item->get_id(),
			],
			'slug' => [
				'icon'  => 'dashicons-wu-bookmark1 wu-align-middle wu-mr-1',
				'label' => __('Event Type', 'multisite-ultimate'),
				'value' => wu_slug_to_name($item->get_slug()),
			],
		];

		$object_initiator = $item->get_initiator();

		if ('system' === $object_initiator) {
			$value = sprintf('<span class="dashicons-wu-wp-ultimo wu-align-middle wu-mr-1 wu-text-lg"></span><span class="wu-text-gray-600">%s</span>', __('Automatically processed by Multisite Ultimate', 'multisite-ultimate'));
		} elseif ('manual' === $object_initiator) {
			$avatar = get_avatar(
				$item->get_author_id(),
				16,
				'identicon',
				'',
				[
					'force_display' => true,
					'class'         => 'wu-rounded-full wu-mr-1 wu-align-text-bottom',
				]
			);

			$display_name = $item->get_author_display_name();

			$value = sprintf('<span class="wu-text-gray-600">%s%s</span>', $avatar, $display_name);
		}

		echo wu_responsive_table_row( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			[
				'id'     => '',
				'title'  => sprintf('<span class="wu-font-normal">%s</span>', wp_trim_words($item->get_message(), 15)),
				'url'    => wu_network_admin_url(
					'wp-ultimo-view-event',
					[
						'id' => $item->get_id(),
					]
				),
				'status' => $value,
			],
			$first_row,
			[
				'date_created' => [
					'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
					'label' => '',
					// translators: %s is a placeholder for the human-readable time difference, e.g., "2 hours ago"
					'value' => sprintf(__('Processed %s', 'multisite-ultimate'), wu_human_time_diff($item->get_date_created(), '-1 day')),
				],
			]
		);
	}
}
