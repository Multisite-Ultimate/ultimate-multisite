<?php
/**
 * Event List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

use WP_Ultimo\Models\Event;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Event List Table class.
 *
 * @since 2.0.0
 */
class Event_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Events\Event_Query::class;

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(
			[
				'singular' => __('Event', 'multisite-ultimate'),  // singular name of the listed records
				'plural'   => __('Events', 'multisite-ultimate'), // plural name of the listed records
				'ajax'     => true,                       // does this table support ajax?
			]
		);
	}

	/**
	 * Returns the markup for the object_type column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Event $item The event being displayed.
	 * @return string
	 */
	public function column_object_type($item) {

		$object_type = $item->get_object_type();

		return "<span class='wu-py-1 wu-px-2 wu-bg-gray-200 wu-rounded-sm wu-leading-none wu-text-gray-700 wu-text-xs wu-font-mono'>{$object_type}</span>";
	}

	/**
	 * Returns the markup for the initiator column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Event $item The event being displayed.
	 * @return string
	 */
	public function column_initiator($item) {

		$object_initiator = $item->get_initiator();

		$object_severity_label = $item->get_severity_label();

		$object_severity_class = $item->get_severity_class();

		$object_label_tooltip = substr($object_severity_label, 0, 1);

		if ('system' === $object_initiator) {
			$avatar = '<span class="dashicons-wu-tools wu-text-gray-700 wu-text-xl"></span>';

			$system_text = ucfirst($object_initiator);

			// phpcs:disable
			$html = "<div class='wu-table-card wu-text-gray-700 wu-p-2 wu-flex wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300'>
				<div class='wu-flex wu-relative wu-h-7 wu-w-7 wu-rounded-full wu-ring-2 wu-ring-white wu-bg-gray-300 wu-items-center wu-justify-center wu-mr-3'>
					{$avatar}

					<span role='tooltip' aria-label='{$object_initiator} - {$object_severity_label}' class='wu-absolute wu-rounded-full wu--mb-2 wu-flex wu-items-center wu-justify-center wu-font-mono wu-bottom-0 wu-right-0 wu-font-bold wu-h-4 wu-w-4 wu-uppercase wu-text-2xs wu-border-solid wu-border-2 wu-border-white {$object_severity_class}'>{$object_label_tooltip}</span>

				</div>
				<div class=''>
					<strong class='wu-block'>{$system_text}</strong>
					<small>" . __('Automatically started', 'multisite-ultimate') . "</small>
				</div>
			</div>";
			// phpcs:enable
		} elseif ('manual' === $object_initiator) {
			$avatar = get_avatar(
				$item->get_author_id(),
				32,
				'identicon',
				'',
				[
					'force_display' => true,
					'class'         => 'wu-rounded-full',
				]
			);

			$display_name = $item->get_author_display_name();

			$id = $item->get_author_id();

			$url_atts = [
				'id' => $item->get_author_id(),
			];

			$initiator_link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

			$email = $item->get_author_email_address();

			$html = "<a href='{$initiator_link}' class='wu-table-card wu-text-gray-700 wu-flex wu-p-2 wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300'>

				<div class='wu-flex wu-relative wu-rounded-full wu-ring-2 wu-ring-white wu-items-center wu-justify-center'>

					{$avatar}

					<span role='tooltip' aria-label='{$object_initiator} - {$object_severity_label}' class='wu-absolute wu-rounded-full wu--mb-2 wu-flex wu-items-center wu-justify-center wu-font-mono wu-bottom-0 wu-right-0 wu-font-bold wu-h-4 wu-w-4 wu-uppercase wu-text-2xs wu-border-solid wu-border-2 wu-border-white {$object_severity_class}'>{$object_label_tooltip}</span>

				</div>

				<div class='wu-pl-2'>
					<strong class='wu-block'> {$display_name} <small class='wu-font-normal'>(#{$id})</small></strong>
					<small>{$email}</small>
				</div>
			</a>";
		} else {
			$not_found = __('No initiator found', 'multisite-ultimate');

			$html = "<div class='wu-table-card  wu-text-gray-700 wu-py-1 wu-px-2 wu-flex wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-relative wu-overflow-hidden'>
				<span class='dashicons dashicons-wu-block wu-text-gray-600 wu-px-1 wu-pr-3'>&nbsp;</span>
				<div class='wu-pl-2'>
					<span class='wu-block wu-py-3 wu-text-gray-600 wu-text-2xs wu-font-bold wu-uppercase'>{$not_found}</span>
				</div>
			</div>";
		}

		return $html;
	}

	/**
	 * Returns the markup for the initiator column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Event $item The event being displayed.
	 * @return string
	 */
	public function column_slug($item) {

		$object_slug = $item->get_slug();

		return "<span class='wu-py-1 wu-px-2 wu-bg-gray-200 wu-rounded-sm wu-text-gray-700 wu-text-xs wu-font-mono'>{$object_slug}</span>";
	}

	/**
	 * Returns the markup for the message column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Event $item The event being displayed.
	 */
	public function column_message($item): string {

		$message = wp_trim_words($item->get_message(), 13);

		$url_atts = [
			'id'    => $item->get_id(),
			'model' => 'event',
		];

		$actions = [
			'view'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-view-event', $url_atts), __('View', 'multisite-ultimate')),
			'delete' => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Delete', 'multisite-ultimate'),
				wu_get_form_url(
					'delete_modal',
					$url_atts
				),
				__('Delete', 'multisite-ultimate')
			),
		];

		return $message . $this->row_actions($actions);
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
			'initiator'    => __('Initiator', 'multisite-ultimate'),
			'message'      => __('Event Message', 'multisite-ultimate'),
			'slug'         => __('SLug', 'multisite-ultimate'),
			'object_type'  => __('Type', 'multisite-ultimate'),
			'date_created' => __('Created at', 'multisite-ultimate'),
			'id'           => __('ID', 'multisite-ultimate'),
		];

		return apply_filters('wu_events_list_table_get_columns', $columns, $this);
	}

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 */
	public function get_filters(): array {

		return [
			'filters'      => [
				'severity' => [
					'label'   => __('Severity', 'multisite-ultimate'),
					'options' => [
						Event::SEVERITY_SUCCESS => __('Success', 'multisite-ultimate'),
						Event::SEVERITY_NEUTRAL => __('Neutral', 'multisite-ultimate'),
						Event::SEVERITY_INFO    => __('Info', 'multisite-ultimate'),
						Event::SEVERITY_WARNING => __('Warning', 'multisite-ultimate'),
						Event::SEVERITY_FATAL   => __('Fatal', 'multisite-ultimate'),
					],
				],
			],
			'date_filters' => [
				'date_created' => [
					'label'   => __('Created At', 'multisite-ultimate'),
					'options' => $this->get_default_date_filter_options(),
				],
			],
		];
	}
}
