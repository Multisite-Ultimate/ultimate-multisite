<?php
/**
 * Multisite Ultimate Customize/Add New Template Previewer Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use WP_Ultimo\UI\Template_Previewer;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Template Previewer Customize/Add New Admin Page.
 */
class Template_Previewer_Customize_Admin_Page extends Customizer_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-customize-template-previewer';

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
		'network_admin_menu' => 'wu_customize_invoice_template',
	];

	/**
	 * Returns the preview URL. This is then added to the iframe.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview_url() {

		$url = get_site_url(null);

		return add_query_arg(
			[
				'customizer' => 1,
				Template_Previewer::get_instance()->get_preview_parameter() => 1,
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

		$this->add_save_widget(
			'save',
			[
				'fields' => [
					'preview_url_parameter' => [
						'type'  => 'text',
						'title' => __('URL Parameter', 'multisite-ultimate'),
						'desc'  => __('This is the URL parameter Multisite Ultimate will use to generate the template preview URLs.', 'multisite-ultimate'),
						'value' => Template_Previewer::get_instance()->get_setting('preview_url_parameter', 'template-preview'),
					],
					'enabled'               => [
						'type'      => 'toggle',
						'title'     => __('Active', 'multisite-ultimate'),
						'desc'      => __('If your site templates are not loading, you can disable the top-bar using this setting.', 'multisite-ultimate'),
						'value'     => Template_Previewer::get_instance()->get_setting('enabled', true),
						'html_attr' => [],
					],
				],
			]
		);

		$custom_logo_id = Template_Previewer::get_instance()->get_setting('custom_logo');

		$custom_logo = wp_get_attachment_image_src($custom_logo_id, 'full');

		$custom_logo = $custom_logo ? $custom_logo[0] : false;

		$fields = [
			'tab'                         => [
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

			'display_responsive_controls' => [
				'type'              => 'toggle',
				'title'             => __('Show Responsive Controls', 'multisite-ultimate'),
				'desc'              => __('Toggle to show or hide the responsive controls.', 'multisite-ultimate'),
				'value'             => true,
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "general")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'display_responsive_controls',
				],
			],
			'button_text'                 => [
				'type'              => 'text',
				'title'             => __('Button Text', 'multisite-ultimate'),
				'value'             => __('Use this Template', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "general")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'button_text',
				],
			],

			'bg_color'                    => [
				'type'              => 'color-picker',
				'title'             => __('Background Color', 'multisite-ultimate'),
				'desc'              => __('Choose the background color for the top-bar.', 'multisite-ultimate'),
				'value'             => '#f9f9f9',
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "colors")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'bg_color',
				],
			],
			'button_bg_color'             => [
				'type'              => 'color-picker',
				'title'             => __('Button BG Color', 'multisite-ultimate'),
				'desc'              => __('Pick the background color for the button.', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "colors")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'button_bg_color',
				],
			],

			'use_custom_logo'             => [
				'type'              => 'toggle',
				'title'             => __('Use Custom Logo', 'multisite-ultimate'),
				'desc'              => __('You can set a different logo to be used on the top-bar.', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "images")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'use_custom_logo',
				],
			],
			'custom_logo'                 => [
				'type'              => 'image',
				'stacked'           => true,
				'title'             => __('Custom Logo', 'multisite-ultimate'),
				'desc'              => __('The logo is displayed on the preview page top-bar.', 'multisite-ultimate'),
				'value'             => $custom_logo_id,
				'img'               => $custom_logo,
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "images") && require("use_custom_logo", true)',
					'v-cloak' => 1,
				],
			],
		];

		$settings = Template_Previewer::get_instance()->get_settings();

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
					'data-wu-app'              => 'site_template_customizer',
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

		return __('Customize Template Previewer', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Customize Template Previewer', 'multisite-ultimate');
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
			'customize_label'   => __('Customize Template Previewer', 'multisite-ultimate'),
			'add_new_label'     => __('Customize Template Previewer', 'multisite-ultimate'),
			'edit_label'        => __('Edit Template Previewer', 'multisite-ultimate'),
			'updated_message'   => __('Template Previewer updated with success!', 'multisite-ultimate'),
			'title_placeholder' => __('Enter Template Previewer Name', 'multisite-ultimate'),
			'title_description' => __('This name is used for internal reference only.', 'multisite-ultimate'),
			'save_button_label' => __('Save Changes', 'multisite-ultimate'),
			'save_description'  => '',
		];
	}

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save(): void {

		// Nonce checked in calling method.
		$settings = Template_Previewer::get_instance()->save_settings($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$array_params = [
			'updated' => 1,
		];

		$url = add_query_arg($array_params);

		wp_safe_redirect($url);

		exit;
	}
}
