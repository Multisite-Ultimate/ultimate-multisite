<?php
/**
 * Domain List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

use WP_Ultimo\Models\Domain;
use WP_Ultimo\Database\Domains\Domain_Stage;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Domain List Table class.
 *
 * @since 2.0.0
 */
class Domain_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Domains\Domain_Query::class;

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(
			[
				'singular' => __('Domain', 'multisite-ultimate'),  // singular name of the listed records
				'plural'   => __('Domains', 'multisite-ultimate'), // plural name of the listed records
				'ajax'     => true,                       // does this table support ajax?
				'add_new'  => [
					'url'     => wu_get_form_url('add_new_domain'),
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

		$_filter_fields = parent::get_extra_query_fields();

		if (wu_request('blog_id')) {
			$_filter_fields['blog_id'] = wu_request('blog_id');
		}

		return $_filter_fields;
	}

	/**
	 * Displays the content of the domain column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Domain $item Domain object.
	 */
	public function column_domain($item): string {

		$url_atts = [
			'id'    => $item->get_id(),
			'model' => 'domain',
		];

		$domain = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-domain', $url_atts), $item->get_domain());

		$html = "<span class='wu-font-mono'><strong>{$domain}</strong></span>";

		$actions = [
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-domain', $url_atts), __('Edit', 'multisite-ultimate')),
			'delete' => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'multisite-ultimate'), wu_get_form_url('delete_modal', $url_atts), __('Delete', 'multisite-ultimate')),
		];

		return $html . $this->row_actions($actions);
	}

	/**
	 * Displays the content of the active column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Domain $item Domain object.
	 * @return string
	 */
	public function column_active($item) {

		return $item->is_active() ? __('Yes', 'multisite-ultimate') : __('No', 'multisite-ultimate');
	}

	/**
	 * Displays the content of the primary domain column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Domain $item Domain object.
	 * @return string
	 */
	public function column_primary_domain($item) {

		return $item->is_primary_domain() ? __('Yes', 'multisite-ultimate') : __('No', 'multisite-ultimate');
	}

	/**
	 * Displays the content of the secure column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Domain $item Domain object.
	 * @return string
	 */
	public function column_secure($item) {

		return $item->is_secure() ? __('Yes', 'multisite-ultimate') : __('No', 'multisite-ultimate');
	}

	/**
	 * Returns the markup for the stage column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Domain $item The domain being displayed.
	 * @return string
	 */
	public function column_stage($item) {

		$label = $item->get_stage_label();

		$class = $item->get_stage_class();

		return "<span class='wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-leading-none wu-font-mono $class'>{$label}</span>";
	}

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'cb'             => '<input type="checkbox" />',
			'domain'         => __('Domain', 'multisite-ultimate'),
			'stage'          => __('Stage', 'multisite-ultimate'),
			'blog_id'        => __('Site', 'multisite-ultimate'),
			'active'         => __('Active', 'multisite-ultimate'),
			'primary_domain' => __('Primary', 'multisite-ultimate'),
			'secure'         => __('HTTPS', 'multisite-ultimate'),
			'id'             => __('ID', 'multisite-ultimate'),
		];

		return $columns;
	}

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 */
	public function get_filters(): array {

		return [
			'filters'      => [

				/**
				 * Active
				 */
				'active'         => [
					'label'   => __('Active', 'multisite-ultimate'),
					'options' => [
						0 => __('Inactive', 'multisite-ultimate'),
						1 => __('Active', 'multisite-ultimate'),
					],
				],

				/**
				 * Primay
				 */
				'primary_domain' => [
					'label'   => __('Is Primary', 'multisite-ultimate'),
					'options' => [
						0 => __('Not Primary Domain', 'multisite-ultimate'),
						1 => __('Primary Domain', 'multisite-ultimate'),
					],
				],

				/**
				 * Secure (HTTPS)
				 */
				'secure'         => [
					'label'   => __('HTTPS', 'multisite-ultimate'),
					'options' => [
						0 => __('Non-HTTPS', 'multisite-ultimate'),
						1 => __('HTTPS', 'multisite-ultimate'),
					],
				],

				/**
				 * Stage
				 */
				'stage'          => [
					'label'   => __('Verification Stage', 'multisite-ultimate'),
					'options' => Domain_Stage::to_array(),
				],

			],
			'date_filters' => [],
		];
	}
}
