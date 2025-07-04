<?php
/**
 * Site List Table class.
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
class Site_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Sites\Site_Query::class;

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->modes = [
			'grid' => __('Grid View'),
			'list' => __('List View'),
		];

		parent::__construct(
			[
				'singular' => __('Site', 'multisite-ultimate'),  // singular name of the listed records
				'plural'   => __('Sites', 'multisite-ultimate'), // plural name of the listed records
				'ajax'     => true,                     // does this table support ajax?
				'add_new'  => [
					'url'     => wu_get_form_url('add_new_site'),
					'classes' => 'wubox',
				],
			]
		);
	}

	/**
	 * Overrides the parent method to add pending sites.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $per_page Number of items to display per page.
	 * @param integer $page_number Current page.
	 * @param boolean $count If we should count records or return the actual records.
	 * @return array|int
	 */
	public function get_items($per_page = 5, $page_number = 1, $count = false) {

		$type = wu_request('type');

		if ('pending' === $type) {
			$pending_sites = \WP_Ultimo\Models\Site::get_all_by_type('pending');

			return $count ? count($pending_sites) : $pending_sites;
		}

		$query = [
			'number' => $per_page,
			'offset' => ($page_number - 1) * $per_page,
			'count'  => $count,
			'search' => wu_request('s'),
		];

		if ($type && 'all' !== $type) {
			$query['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'type' => [
					'key'   => 'wu_type',
					'value' => $type,
				],
			];
		}

		$query = apply_filters("wu_{$this->id}_get_items", $query, $this);

		return wu_get_sites($query);
	}

	/**
	 * Render the bulk edit checkbox.
	 *
	 * @param \WP_Ultimo\Models\Site $item Site object.
	 */
	public function column_cb($item): string {

		if ($item->get_type() === 'pending') {
			return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->get_membership_id());
		}

		return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->get_id());
	}

	/**
	 * Displays the content of the name column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Site $item Site object.
	 */
	public function column_path($item): string {

		$url_atts = [
			'id'    => $item->get_id(),
			'model' => 'site',
		];

		$title = $item->get_title();

		$title = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-site', $url_atts), $item->get_title());

		// Concatenate the two blocks
		$title = "<strong>$title</strong>";

		$actions = [
			'edit'      => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-site', $url_atts), __('Edit', 'multisite-ultimate')),
			'duplicate' => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Duplicate Site', 'multisite-ultimate'),
				wu_get_form_url(
					'add_new_site',
					$url_atts
				),
				__('Duplicate', 'multisite-ultimate')
			),
			'delete'    => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Delete', 'multisite-ultimate'),
				wu_get_form_url(
					'delete_modal',
					$url_atts
				),
				__('Delete', 'multisite-ultimate')
			),
		];

		if ($item->get_type() === 'pending') {
			$actions = [
				'duplicate' => sprintf(
					'<a title="%s" class="wubox" href="%s">%s</a>',
					__('Publish Site', 'multisite-ultimate'),
					wu_get_form_url(
						'publish_pending_site',
						['membership_id' => $item->get_membership_id()]
					),
					__('Publish', 'multisite-ultimate')
				),
				'delete'    => sprintf(
					'<a title="%s" class="wubox" href="%s">%s</a>',
					__('Delete', 'multisite-ultimate'),
					wu_get_form_url(
						'delete_modal',
						[
							'id'          => $item->get_membership_id(),
							'model'       => 'membership_meta_pending_site',
							'redirect_to' => rawurlencode(
								(string) wu_network_admin_url(
									'wp-ultimo-sites',
									[
										'type' => 'pending',
										'page' => wu_request('page', 1),
									]
								)
							),
						]
					),
					__('Delete', 'multisite-ultimate')
				),
			];
		}

		return $title . sprintf('<span class="wu-block">%s</span>', make_clickable($item->get_active_site_url())) . $this->row_actions($actions);
	}

	/**
	 * Returns the date of the customer registration.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Site $item Site object.
	 */
	public function column_date_registered($item): string {

		$time = strtotime((string) $item->get_last_login(false));

		return $item->get_date_registered() . sprintf('<br><small>%s</small>', human_time_diff($time));
	}

	/**
	 * Returns the blog_id.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Site $item Site object.
	 * @return string
	 */
	public function column_blog_id($item) {

		return $item->get_type() === \WP_Ultimo\Database\Sites\Site_Type::PENDING ? '--' : $item->get_blog_id();
	}

	/**
	 * Displays the type of the site.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Site $item Site object.
	 * @return string
	 */
	public function column_type($item) {

		$label = $item->get_type_label();

		$class = $item->get_type_class();

		return "<span class='wu-bg-gray-200 wu-py-1 wu-px-2 wu-leading-none wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";
	}

	/**
	 * Column for the domains associated with this site.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Site $item Site object.
	 */
	public function column_domains($item): string {

		$domain = wu_get_domains(
			[
				'blog_id' => $item->get_id(),
				'count'   => true,
			]
		);

		$url_atts = [
			'blog_id' => $item->get_id(),
		];

		$actions = [
			'view' => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-domains', $url_atts), __('View', 'multisite-ultimate')),
		];

		return $domain . $this->row_actions($actions);
	}

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'cb'                => '<input type="checkbox" />',
			'featured_image_id' => '<span class="dashicons-wu-image"></span>',
			'path'              => __('URL', 'multisite-ultimate'),
			'type'              => __('Type', 'multisite-ultimate'),
			'customer'          => __('Customer', 'multisite-ultimate'),
			'membership'        => __('Membership', 'multisite-ultimate'),
			'domains'           => __('Domains', 'multisite-ultimate'),
			'blog_id'           => __('ID', 'multisite-ultimate'),
		];

		return $columns;
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
			'base/sites/grid-item',
			[
				'item'       => $item,
				'list_table' => $this,
			]
		);
	}

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 */
	public function get_filters(): array {

		return [
			'filters'      => [
				'vip' => [
					'label'   => __('VIP Status', 'multisite-ultimate'),
					'options' => [
						'0' => __('Regular Sites', 'multisite-ultimate'),
						'1' => __('VIP Sites', 'multisite-ultimate'),
					],
				],
			],
			'date_filters' => [
				'last_login'      => [
					'label'   => __('Last Login', 'multisite-ultimate'),
					'options' => $this->get_default_date_filter_options(),
				],
				'date_registered' => [
					'label'   => __('Site Since', 'multisite-ultimate'),
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
			'all'            => [
				'field' => 'type',
				'url'   => add_query_arg('type', 'all'),
				'label' => __('All Sites', 'multisite-ultimate'),
				'count' => 0,
			],
			'customer_owned' => [
				'field' => 'type',
				'url'   => add_query_arg('type', 'customer_owned'),
				'label' => __('Customer-Owned', 'multisite-ultimate'),
				'count' => 0,
			],
			'site_template'  => [
				'field' => 'type',
				'url'   => add_query_arg('type', 'site_template'),
				'label' => __('Templates', 'multisite-ultimate'),
				'count' => 0,
			],
			'pending'        => [
				'field' => 'type',
				'url'   => add_query_arg('type', 'pending'),
				'label' => __('Pending', 'multisite-ultimate'),
				'count' => 0,
			],
		];
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = [
			'screenshot' => __('Take Screenshot', 'multisite-ultimate'),
		];

		$actions[ wu_request('type', 'all') === 'pending' ? 'delete-pending' : 'delete' ] = __('Delete', 'multisite-ultimate');

		return $actions;
	}

	/**
	 * Handles the bulk processing.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_single_action(): void {

		$action = $this->current_action();

		if ('duplicate' === $action) {
			$site_id = wu_request('id');

			$site = wu_get_site($site_id);

			if ( ! $site) {
				WP_Ultimo()->notices->add(__('Site not found.', 'multisite-ultimate'), 'error', 'network-admin');

				return;
			}

			$new_site = $site->duplicate();

			$new_path = sprintf('%s%s', trim((string) $new_site->get_path(), '/'), 'copy');

			$new_site->set_template_id($new_site->get_blog_id());

			$new_site->set_blog_id(0);

			$new_site->get_title($new_name);

			$new_site->set_path($new_path);

			$new_site->site_date_registered(wu_get_current_time('mysql', true));

			$result = $new_site->save();

			if (is_wp_error($result)) {
				WP_Ultimo()->notices->add($result->get_error_message(), 'error', 'network-admin');

				return;
			}

			$redirect_url = wu_network_admin_url(
				'wp-ultimo-edit-site',
				[
					'id'      => $new_site->get_id(),
					'updated' => 1,
				]
			);

			wp_safe_redirect($redirect_url);

			exit;
		}
	}
}
