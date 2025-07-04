<?php
/**
 * Membership List Table class.
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
class Membership_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Memberships\Membership_Query::class;

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(
			[
				'singular' => __('Membership', 'multisite-ultimate'),  // singular name of the listed records
				'plural'   => __('Memberships', 'multisite-ultimate'), // plural name of the listed records
				'ajax'     => true,                           // does this table support ajax?
				'add_new'  => [
					'url'     => wu_get_form_url('add_new_membership'),
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

		$_filter_fields                = parent::get_extra_query_fields();
		$_filter_fields['customer_id'] = wu_request('customer_id');

		return $_filter_fields;
	}

	/**
	 * Displays the membership reference code.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Membership $item Membership object.
	 * @return string
	 */
	public function column_hash($item) {

		$url_atts = [
			'id'    => $item->get_id(),
			'model' => 'membership',
		];

		$code = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-membership', $url_atts), $item->get_hash());

		$actions = [
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-membership', $url_atts), __('Edit', 'multisite-ultimate')),
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

		$html = "<span class='wu-font-mono'><strong>{$code}</strong></span>";

		return $html . $this->row_actions($actions);
	}

	/**
	 * Displays the status of the membership.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Membership $item Membership object.
	 * @return string
	 */
	public function column_status($item) {

		$label = $item->get_status_label();

		$class = $item->get_status_class();

		$html = "<span class='wu-bg-gray-200 wu-leading-none wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

		return $html;
	}

	/**
	 * Displays the price of the membership.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Membership $item Membership object.
	 * @return string
	 */
	public function column_amount($item) {

		if (empty($item->get_amount()) && empty($item->get_initial_amount())) {
			return __('Free', 'multisite-ultimate');
		}

		if ($item->is_recurring()) {
			$amount = wu_format_currency($item->get_amount(), $item->get_currency());

			$duration = $item->get_duration();

			$message = sprintf(
				// translators: %1$s the duration, and %2$s the duration unit (day, week, month, etc)
				_n('every %2$s', 'every %1$s %2$s', $duration, 'multisite-ultimate'), // phpcs:ignore
				$duration,
				$item->get_duration_unit()
			);

			if ( ! $item->is_forever_recurring()) {
				$billing_cycles_message = sprintf(
					// translators: %s is the number of billing cycles.
					_n('for %s cycle', 'for %s cycles', $item->get_billing_cycles(), 'multisite-ultimate'),
					$item->get_billing_cycles()
				);

				$message .= ' ' . $billing_cycles_message;
			}
		} else {
			$amount = wu_format_currency($item->get_initial_amount(), $item->get_currency());

			$message = __('one time payment', 'multisite-ultimate');
		}

		return sprintf('%s<br><small>%s</small>', $amount, $message);
	}

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'cb'              => '<input type="checkbox" />',
			'hash'            => wu_tooltip(__('Reference Code', 'multisite-ultimate'), 'dashicons-wu-hash wu-text-xs'),
			'status'          => __('Status', 'multisite-ultimate'),
			'customer'        => __('Customer', 'multisite-ultimate'),
			'product'         => __('Product', 'multisite-ultimate'),
			'amount'          => __('Price', 'multisite-ultimate'),
			'date_created'    => __('Created at', 'multisite-ultimate'),
			'date_expiration' => __('Expiration', 'multisite-ultimate'),
			'id'              => __('ID', 'multisite-ultimate'),
		];

		return $columns;
	}

	/**
	 * Handles the default displaying of datetime columns.
	 *
	 * @since 2.0.0
	 *
	 * @param self $item The membership.
	 * @return string
	 */
	public function column_date_expiration($item) {

		$date = $item->get_date_expiration();

		if (empty($date) || '0000-00-00 00:00:00' === $date) {
			return sprintf('<span>%s</span><br><small>%s</small>', __('Lifetime', 'multisite-ultimate'), __('It never expires', 'multisite-ultimate'));
		}

		if ( ! wu_validate_date($date)) {
			return __('--', 'multisite-ultimate');
		}

		$time = strtotime(get_date_from_gmt($date));

		$formatted_value = date_i18n(get_option('date_format'), $time);

		$placeholder = wu_get_current_time('timestamp') > $time ? __('%s ago', 'multisite-ultimate') : __('In %s', 'multisite-ultimate'); // phpcs:ignore

		$text = $formatted_value . sprintf('<br><small>%s</small>', sprintf($placeholder, human_time_diff($time)));

		return sprintf('<span %s>%s</span>', wu_tooltip_text(date_i18n('Y-m-d H:i:s', $time)), $text);
	}

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 */
	public function get_filters(): array {

		$membership_status = new \WP_Ultimo\Database\Memberships\Membership_Status();

		return [
			'filters'      => [

				/**
				 * Status
				 */
				'status' => [
					'label'   => __('Status', 'multisite-ultimate'),
					'options' => $membership_status::to_array(),
				],

			],
			'date_filters' => [

				/**
				 * Created At
				 */
				'date_created'    => [
					'label'   => __('Created At', 'multisite-ultimate'),
					'options' => $this->get_default_date_filter_options(),
				],

				/**
				 * Expiration Date
				 */
				'date_expiration' => [
					'label'   => __('Expiration Date', 'multisite-ultimate'),
					'options' => $this->get_default_date_filter_options(),
				],

				/**
				 * Renewal Date
				 */
				'date_renewed'    => [
					'label'   => __('Renewal Date', 'multisite-ultimate'),
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
			'all'       => [
				'field' => 'status',
				'url'   => add_query_arg('status', 'all'),
				'label' => __('All Memberships', 'multisite-ultimate'),
				'count' => 0,
			],
			'active'    => [
				'field' => 'status',
				'url'   => add_query_arg('status', 'active'),
				'label' => __('Active', 'multisite-ultimate'),
				'count' => 0,
			],
			'trialing'  => [
				'field' => 'status',
				'url'   => add_query_arg('status', 'trialing'),
				'label' => __('Trialing', 'multisite-ultimate'),
				'count' => 0,
			],
			'pending'   => [
				'field' => 'status',
				'url'   => add_query_arg('status', 'pending'),
				'label' => __('Pending', 'multisite-ultimate'),
				'count' => 0,
			],
			'on-hold'   => [
				'field' => 'status',
				'url'   => add_query_arg('status', 'on-hold'),
				'label' => __('On Hold', 'multisite-ultimate'),
				'count' => 0,
			],
			'expired'   => [
				'field' => 'status',
				'url'   => add_query_arg('status', 'expired'),
				'label' => __('Expired', 'multisite-ultimate'),
				'count' => 0,
			],
			'cancelled' => [
				'field' => 'status',
				'url'   => add_query_arg('status', 'cancelled'),
				'label' => __('Cancelled', 'multisite-ultimate'),
				'count' => 0,
			],
		];
	}
}
