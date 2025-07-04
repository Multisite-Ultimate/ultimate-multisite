<?php
/**
 * Multisite Ultimate About Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use WP_Ultimo\Tax\Tax;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate About Admin Page.
 */
class Tax_Rates_Admin_Page extends Base_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-tax-rates';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * This page has no parent, so we need to highlight another sub-menu.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $highlight_menu_slug = 'wp-ultimo-settings';

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = [
		'network_admin_menu' => 'manage_network',
	];

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Tax Rates', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Tax Rates', 'multisite-ultimate');
	}

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Tax Rates', 'multisite-ultimate');
	}

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output(): void {

		do_action('wu_load_tax_rates_list_page');

		$columns = apply_filters(
			'wu_tax_rates_columns',
			[
				'title'    => __('Label', 'multisite-ultimate'),
				'country'  => __('Country', 'multisite-ultimate'),
				'state'    => __('State / Province', 'multisite-ultimate'),
				'city'     => __('City', 'multisite-ultimate'),
				'tax_rate' => __('Tax Rate (%)', 'multisite-ultimate'),
				'move'     => '',
			]
		);

		wu_get_template(
			'taxes/list',
			[
				'columns' => $columns,
				'screen'  => get_current_screen(),
				'types'   => Tax::get_instance()->get_tax_rate_types(),
			]
		);
	}

	/**
	 * Adds the cure bg image here as well.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		parent::register_scripts();

		wp_register_script('wu-tax-rates', wu_get_asset('tax-rates.js', 'js'), ['wu-admin', 'wu-vue', 'underscore', 'wu-selectizer', 'wu-vue-sortable', 'wu-vue-draggable'], wu_get_version(), false);

		wp_localize_script(
			'wu-tax-rates',
			'wu_tax_ratesl10n',
			[
				'name'                                => __('Tax', 'multisite-ultimate'),
				'confirm_message'                     => __('Are you sure you want to delete this rows?', 'multisite-ultimate'),
				'confirm_delete_tax_category_message' => __('Are you sure you want to delete this tax category?', 'multisite-ultimate'),
			]
		);

		wp_enqueue_script('wu-tax-rates');
	}

	/**
	 * Adds field widgets to edit pages with the same Form/Field APIs used elsewhere.
	 *
	 * @see Take a look at /inc/ui/form and inc/ui/field for reference.
	 * @since 2.0.0
	 *
	 * @param string $id ID of the widget.
	 * @param array  $atts Array of attributes to pass to the form.
	 * @return void
	 */
	protected function add_fields_widget($id, $atts = []) {

		$atts = wp_parse_args(
			$atts,
			[
				'widget_id'             => $id,
				'before'                => '',
				'after'                 => '',
				'title'                 => __('Fields', 'multisite-ultimate'),
				'position'              => 'side',
				'screen'                => get_current_screen(),
				'fields'                => [],
				'html_attr'             => [],
				'classes'               => '',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			]
		);

		add_meta_box(
			"wp-ultimo-{$id}-widget",
			$atts['title'],
			function () use ($atts) {

				if (wu_get_isset($atts['html_attr'], 'data-wu-app')) {
					$atts['fields']['loading'] = [
						'type'              => 'note',
						'desc'              => sprintf('<div class="wu-block wu-text-center wu-blinking-animation wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold">%s</div>', __('Loading...', 'multisite-ultimate')),
						'wrapper_html_attr' => [
							'v-if' => 0,
						],
					];
				}

				/**
				 * Instantiate the form for the order details.
				 *
				 * @since 2.0.0
				 */
				$form = new \WP_Ultimo\UI\Form(
					$atts['widget_id'],
					$atts['fields'],
					[
						'views'                 => 'admin-pages/fields',
						'classes'               => 'wu-widget-list wu-striped wu-m-0 wu--mt-2 wu--mb-3 wu--mx-3 ' . $atts['classes'],
						'field_wrapper_classes' => $atts['field_wrapper_classes'],
						'html_attr'             => $atts['html_attr'],
						'before'                => $atts['before'],
						'after'                 => $atts['after'],
					]
				);

				$form->render();
			},
			$atts['screen']->id,
			$atts['position'],
			null
		);
	}
}
