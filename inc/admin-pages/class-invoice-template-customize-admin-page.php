<?php
/**
 * Multisite Ultimate Customize/Add New Invoice Template Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use WP_Ultimo\Invoices\Invoice;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Invoice Template Customize/Add New Admin Page.
 */
class Invoice_Template_Customize_Admin_Page extends Customizer_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-customize-invoice-template';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Object ID being customizeed.
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id = 'invoice_template';

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
		'network_admin_menu' => 'wu_customize_invoice_template',
	];

	/**
	 * Overrides the original init to add the required ajax endpoints.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		parent::init();

		add_action('wp_ajax_wu-preview-invoice', [$this, 'generate_invoice_preview']);
	}

	/**
	 * Ajax endpoint to generate the Ajax Preview.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function generate_invoice_preview(): void {

		if ( ! current_user_can('wu_manage_invoice')) {
			return;
		}

		$order = false;

		$payment = wu_mock_payment();

		check_ajax_referer('wu-preview-invoice', 'wu-preview-nonce');
		$invoice = new Invoice($payment, $_GET);

		$invoice->print_file();

		die;
	}

	/**
	 * Returns the preview URL. This is then added to the iframe.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview_url() {

		$url = get_admin_url(wu_get_main_site_id(), 'admin-ajax.php');

		return add_query_arg(
			[
				'action'            => 'wu-preview-invoice',
				'customizer'        => 1,
				'invoice-customize' => 1,
				'wu-preview-nonce'  => wp_create_nonce('wu-preview-invoice'),
			],
			$url
		);
	}

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets(): void {

		$settings = Invoice::get_settings();

		$this->add_save_widget(
			'save',
			[
				'fields' => [
					'note' => [
						'type' => 'note',
						'desc' => __('Changes to this template will be applied to all PDF invoices generated after the change. <br><br>Existing PDF Invoices will not be affected unless explicitly re-generated', 'multisite-ultimate'),
					],
				],
			]
		);

		$custom_logo = wu_get_isset($settings, 'custom_logo');

		$custom_logo_args = wp_get_attachment_image_src($custom_logo, 'full');

		$custom_logo_url = $custom_logo_args ? $custom_logo_args[0] : '';

		$fields = [
			'tab'             => [
				'type'              => 'tab-select',
				'wrapper_classes'   => '',
				'wrapper_html_attr' => [
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'tab',
				],
				'options'           => [
					'general' => __('General', 'multisite-ultimate'),
					'colors'  => __('Colors', 'multisite-ultimate'),
					'images'  => __('Images', 'multisite-ultimate'),
				],
			],

			'paid_tag_text'   => [
				'type'              => 'text',
				'title'             => __('Paid Tag', 'multisite-ultimate'),
				'placeholder'       => __('e.g. Paid.', 'multisite-ultimate'),
				'value'             => wu_get_isset($settings, 'paid_tag_text', __('Paid', 'multisite-ultimate')),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "general")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'paid_tag_text',
				],
			],
			'font'            => [
				'type'              => 'select',
				'title'             => __('Font-Family', 'multisite-ultimate'),
				'value'             => wu_get_isset($settings, 'font', ''),
				'options'           => [
					'DejaVuSansCondensed'  => __('Sans-Serif', 'multisite-ultimate'),
					'DejaVuSerifCondensed' => __('Serif', 'multisite-ultimate'),
					'FreeMono'             => __('Mono', 'multisite-ultimate'),
				],
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "general")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'font',
				],
			],
			'footer_message'  => [
				'type'              => 'textarea',
				'title'             => __('Footer Content', 'multisite-ultimate'),
				'placeholder'       => __('e.g. Extra Info about the Invoice.', 'multisite-ultimate'),
				'value'             => wu_get_isset($settings, 'footer_message', ''),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "general")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'footer_message',
				],
			],

			'primary_color'   => [
				'type'              => 'color-picker',
				'title'             => __('Primary Color', 'multisite-ultimate'),
				'value'             => '#00a1ff',
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "colors")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'primary_color',
				],
			],

			'use_custom_logo' => [
				'type'              => 'toggle',
				'title'             => __('Use Custom Logo', 'multisite-ultimate'),
				'desc'              => __('You can set a different logo to be used on the invoice.', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "images")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'use_custom_logo',
				],
			],
			'custom_logo'     => [
				'type'              => 'image',
				'title'             => __('Custom Logo', 'multisite-ultimate'),
				'desc'              => __('This will be added to the top of the generated PDF.', 'multisite-ultimate'),
				'value'             => '',
				'img'               => $custom_logo_url,
				'stacked'           => true,
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "images") && require("use_custom_logo", true)',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'custom_logo',
				],
			],
		];

		$settings = [
			'footer_message'  => wu_get_isset($settings, 'footer_message', ''),
			'paid_tag_text'   => wu_get_isset($settings, 'paid_tag_text', __('Paid', 'multisite-ultimate')),
			'primary_color'   => wu_get_isset($settings, 'primary_color', '00a1ff'),
			'use_custom_logo' => wu_get_isset($settings, 'use_custom_logo'),
			'custom_logo'     => wu_get_isset($settings, 'custom_logo'),
			'font'            => wu_get_isset($settings, 'font', 'DejaVuSansCondensed'),
		];

		$state = array_merge(
			$settings,
			[
				'tab'     => 'general',
				'refresh' => true,
			]
		);

		$this->add_fields_widget(
			'customizer',
			[
				'title'     => __('Customizer', 'multisite-ultimate'),
				'position'  => 'side',
				'fields'    => $fields,
				'html_attr' => [
					'style'                    => 'margin-top: -6px;',
					'data-wu-app'              => 'invoice_customizer',
					'data-wu-customizer-panel' => true,
					'data-state'               => wp_json_encode($state),
				],
			]
		);
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Customize Invoice Template', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Customize Invoice Template', 'multisite-ultimate');
	}

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return [];
	}

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return [
			'customize_label'   => __('Customize Invoice Template', 'multisite-ultimate'),
			'add_new_label'     => __('Customize Invoice Template', 'multisite-ultimate'),
			'edit_label'        => __('Edit Invoice Template', 'multisite-ultimate'),
			'updated_message'   => __('Invoice Template updated with success!', 'multisite-ultimate'),
			'title_placeholder' => __('Enter Invoice Template Name', 'multisite-ultimate'),
			'title_description' => __('This name is used for internal reference only.', 'multisite-ultimate'),
			'save_button_label' => __('Save Invoice Template', 'multisite-ultimate'),
			'save_description'  => __('Save Invoice Template', 'multisite-ultimate'),
		];
	}

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save(): void {

		// Nonce in handeled in the calling method.
		Invoice::save_settings($_POST); // phpcs:ignore WordPress.Security.NonceVerification

		$url = add_query_arg('updated', '1');

		wp_safe_redirect($url);

		exit;
	}
}
