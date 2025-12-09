<?php
/**
 * Ultimate Multisite Customize/Add New Email Template Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Ultimate Multisite Email Template Customize/Add New Admin Page.
 */
class Email_Template_Customize_Admin_Page extends Customizer_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-customize-email-template';

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
	public $object_id = 'email_template';

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
	protected $highlight_menu_slug = 'wp-ultimo-broadcasts';

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
		'network_admin_menu' => 'wu_customize_email_template',
	];

	/**
	 * Overrides the init method to add additional hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		parent::init();

		add_action('wp_ajax_wu-email-template-preview', [$this, 'email_template_preview']);
	}

	/**
	 * Return the page object
	 *
	 * @since 2.0.0
	 *
	 * @return object $this The Current Object
	 */
	public function get_object() {

		return $this;
	}

	/**
	 * Renders the preview of a given form being customized.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function email_template_preview(): void {

		$object = $this;

		$content = wpautop(
			'

			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nulla diam, iaculis sit amet tellus sit amet, tempus hendrerit risus. Proin elementum aliquet lorem ut cursus. Ut varius pharetra magna, eu malesuada metus feugiat id. Aenean cursus purus et massa commodo pretium id ut erat. Suspendisse erat odio, auctor ac elit eget, rhoncus iaculis nulla. Aliquam turpis leo, egestas eget dui a, imperdiet ullamcorper felis. Suspendisse ut lacinia mauris.

			Phasellus vitae diam euismod diam tristique faucibus. Proin gravida, augue in molestie porttitor, orci justo aliquam mauris, et commodo mauris nisi vitae tortor. Mauris vulputate fringilla purus et finibus. Duis lacus turpis, tincidunt vel dui ac, fermentum aliquet dolor. Donec auctor tristique consequat. In pharetra lacus quis mi dictum, ut dapibus eros bibendum. Donec tristique nibh ac sem bibendum, at feugiat turpis molestie. Suspendisse eget eleifend nunc. Sed tempor varius nisi non tincidunt. Sed leo arcu, feugiat dapibus sollicitudin a, tincidunt eu ligula. Nam ut arcu id arcu auctor vulputate non molestie quam. Nunc non diam mauris. Praesent erat est, posuere sit amet hendrerit non, molestie eget sem. Cras ac tempor est.'
		);

		$content .= '<table cellpadding="0" cellspacing="0" style="width:100%; font-family:Roboto,HelveticaNeue,sans-serif; font-size: 15px">
<tbody><tr>
<td style="text-align: right; width: 120px; padding: 8px; background: #eee; border: 1px solid #eee; border-width: 1px 0;"><b>Amount:</b></td>
<td style="padding: 8px; background: #fff; border: 1px solid #eee;"><a href="#" style="color: #29abe2; text-decoration: none;" rel="nofollow">$99.00 USD</a></td>
</tr>
<tr>
<td style="text-align: right; width: 120px; padding: 8px; background: #eee;"><b>Paid with:</b></td>
<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee; border-top-width: 0;">Credit Card</td>
</tr>
<tr>
<td style="text-align: right; width: 120px; padding: 8px; background: #eee; border: 1px solid #eee; border-width: 1px 0;"><b>External ID:</b></td>
<td style="padding: 8px; background: #fff; border: 1px solid #eee; border-top-width: 0;"><a href="#" style="color: #29abe2; text-decoration: none;" rel="nofollow">ch_1IBe2OFmXz63vF5vkusIdsyv</a></td>
</tr>
<tr>
<td style="text-align: right; width: 120px; padding: 8px; background: #eee;"><b>ID:</b></td>
<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee; border-top-width: 0;"><a href="#" style="color: #29abe2; text-decoration: none;" rel="nofollow">342257</a></td>
</tr>
<tr>
<td style="text-align: right; width: 120px; padding: 8px; background: #eee; border: 1px solid #eee; border-width: 1px 0;"><b>Processed at:</b></td>
<td style="padding: 8px; background: #fff; border: 1px solid #eee; border-top-width: 0;">Jan 20, 2021 GMT</td>
</tr>
<tr>
<td style="text-align: right; width: 120px; padding: 8px; background: #eee;"><b>Invoice:</b></td>
<td style="padding: 8px; background: #fdfdfd; border: 1px solid #eee; border-top-width: 0;"><a href="#" style="color: #29abe2; text-decoration: none;" rel="nofollow">Download PDF</a></td>
</tr>
<tr>
<td style="text-align: right; width: 120px; padding: 8px; background: #eee; border: 1px solid #eee; border-width: 1px 0;"><b>Type:</b></td>
<td style="padding: 8px; background: #fff; border: 1px solid #eee; border-top-width: 0;">Initial Payment</td>
</tr>
</tbody></table>';

		/*
		 * use arbitrary field to determine if this is the first request for the preview.
		 */
		$first_request = ! wu_request('background_color');

		wu_get_template(
			'broadcast/emails/base',
			[
				'site_name'         => get_network_option(null, 'site_name'),
				'site_url'          => get_site_url(),
				'logo_url'          => wu_get_network_logo(),
				'content'           => $content,
				'subject'           => __('Sample Subject', 'ultimate-multisite'),
				'is_editor'         => true,
				'template_settings' => [
					'use_custom_logo'         => wu_string_to_bool(wu_request('use_custom_logo', $first_request ? $object->get_setting('use_custom_logo', false) : false)),
					'custom_logo'             => wu_request('custom_logo', $object->get_setting('custom_logo', false)),
					'background_color'        => wu_request('background_color', $object->get_setting('background_color', '#f9f9f9')),
					'title_color'             => wu_request('title_color', $object->get_setting('title_color', '#000000')),
					'title_size'              => wu_request('title_size', $object->get_setting('title_size', 'h3')),
					'title_align'             => wu_request('title_align', $object->get_setting('title_align', 'center')),
					'title_font'              => wu_request('title_font', $object->get_setting('title_font', 'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif')),
					'content_color'           => wu_request('content_color', $object->get_setting('content_color', '#000000')),
					'content_align'           => wu_request('content_align', $object->get_setting('content_align', 'left')),
					'content_font'            => wu_request('content_font', $object->get_setting('content_font', 'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif')),
					'footer_text'             => wu_request('footer_text', $object->get_setting('footer_text', '')),
					'footer_font'             => wu_request('footer_font', $object->get_setting('footer_font', 'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif')),
					'footer_color'            => wu_request('footer_color', $object->get_setting('footer_color', '#000000')),
					'footer_align'            => wu_request('footer_align', $object->get_setting('footer_align', 'center')),
					'display_company_address' => wu_string_to_bool(wu_request('display_company_address', $first_request ? $object->get_setting('display_company_address', true) : false)),
				],
			]
		);

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
				'action'     => 'wu-email-template-preview',
				'customizer' => 1,
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
				'html_attr' => [
					'data-wu-app' => 'save',
					'data-state'  => wu_convert_to_state(),
				],
				'fields'    => [
					'note' => [
						'type' => 'note',
						'desc' => __('System emails and broadcasts will be sent using this template.', 'ultimate-multisite'),
					],
				],
			]
		);

		$settings = $this->get_attributes();

		$custom_logo = wu_get_isset($settings, 'custom_logo');

		$custom_logo_args = wp_get_attachment_image_src($custom_logo, 'full');

		$custom_logo_url = $custom_logo_args ? $custom_logo_args[0] : '';

		$fields = [
			'tab'                     => [
				'type'              => 'tab-select',
				'wrapper_classes'   => '',
				'wrapper_html_attr' => [
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'tab',
				],
				'options'           => [
					'header'  => __('Header', 'ultimate-multisite'),
					'content' => __('Content', 'ultimate-multisite'),
					'footer'  => __('Footer', 'ultimate-multisite'),
				],
			],
			'use_custom_logo'         => [
				'type'              => 'toggle',
				'title'             => __('Use Custom Logo', 'ultimate-multisite'),
				'desc'              => __('You can set a different logo to be used on the system emails.', 'ultimate-multisite'),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "header")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'use_custom_logo',
				],
			],
			'custom_logo'             => [
				'type'              => 'image',
				'stacked'           => true,
				'title'             => __('Custom Logo', 'ultimate-multisite'),
				'desc'              => __('The custom logo is used in the email header, if HTML emails are used.', 'ultimate-multisite'),
				'value'             => $custom_logo,
				'img'               => $custom_logo_url,
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "header") && require("use_custom_logo", true)',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'custom_logo',
				],
			],
			'background_color'        => [
				'type'              => 'color-picker',
				'title'             => __('Background Color', 'ultimate-multisite'),
				'tooltip'           => __('The cover background color of the email.', 'ultimate-multisite'),
				'value'             => '#00a1ff',
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "header")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'background_color',
				],
			],
			'title_color'             => [
				'type'              => 'color-picker',
				'title'             => __('Title Color', 'ultimate-multisite'),
				'value'             => '#00a1ff',
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "header")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'title_color',
				],
			],
			'title_size'              => [
				'type'              => 'select',
				'title'             => __('Title Size', 'ultimate-multisite'),
				'value'             => wu_get_isset($settings, 'title_size'),
				'options'           => [
					'h1' => __('h1', 'ultimate-multisite'),
					'h2' => __('h2', 'ultimate-multisite'),
					'h3' => __('h3', 'ultimate-multisite'),
					'h4' => __('h4', 'ultimate-multisite'),
					'h5' => __('h5', 'ultimate-multisite'),
				],
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "header")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'title_size',
				],
			],
			'title_align'             => [
				'type'              => 'select',
				'title'             => __('Title Align', 'ultimate-multisite'),
				'tooltip'           => __('Aligment of the font in the title.', 'ultimate-multisite'),
				'value'             => wu_get_isset($settings, 'title_align', ''),
				'options'           => [
					'left'   => __('Left', 'ultimate-multisite'),
					'center' => __('Center', 'ultimate-multisite'),
					'right'  => __('Right', 'ultimate-multisite'),
				],
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "header")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'title_align',
				],
			],
			'title_font'              => [
				'type'              => 'select',
				'title'             => __('Title Font-Family', 'ultimate-multisite'),
				'value'             => wu_get_isset($settings, 'title_font', ''),
				'options'           => [
					'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif' => __('Helvetica', 'ultimate-multisite'),
					'Arial, Helvetica, sans-serif'       => __('Arial', 'ultimate-multisite'),
					'Times New Roman, Times, serif'      => __('Times New Roman', 'ultimate-multisite'),
					'Lucida Console, Courier, monospace' => __('Lucida', 'ultimate-multisite'),
				],
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "header")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'title_font',
				],
			],
			'content_color'           => [
				'type'              => 'color-picker',
				'title'             => __('Content Color', 'ultimate-multisite'),
				'value'             => '#000000',
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "content")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'content_color',
				],
			],
			'content_align'           => [
				'type'              => 'select',
				'title'             => __('Content Alignment', 'ultimate-multisite'),
				'tooltip'           => __('Alignment of the font in the main email content.', 'ultimate-multisite'),
				'value'             => wu_get_isset($settings, 'content_align', ''),
				'options'           => [
					'left'   => __('Left', 'ultimate-multisite'),
					'center' => __('Center', 'ultimate-multisite'),
					'right'  => __('Right', 'ultimate-multisite'),
				],
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "content")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'content_align',
				],
			],
			'content_font'            => [
				'type'              => 'select',
				'title'             => __('Content Font-Family', 'ultimate-multisite'),
				'value'             => wu_get_isset($settings, 'content_font', ''),
				'options'           => [
					'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif' => __('Helvetica', 'ultimate-multisite'),
					'Arial, Helvetica, sans-serif'       => __('Arial', 'ultimate-multisite'),
					'Times New Roman, Times, serif'      => __('Times New Roman', 'ultimate-multisite'),
					'Lucida Console, Courier, monospace' => __('Lucida', 'ultimate-multisite'),
				],
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "content")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'content_font',
				],
			],
			'display_company_address' => [
				'type'              => 'toggle',
				'title'             => __('Display Company Address', 'ultimate-multisite'),
				'desc'              => __('Toggle to show/hide your company address.', 'ultimate-multisite'),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "footer")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'display_company_address',
				],
			],
			'footer_text'             => [
				'type'              => 'textarea',
				'title'             => __('Footer Content', 'ultimate-multisite'),
				'placeholder'       => __('e.g. Extra info in the email footer.', 'ultimate-multisite'),
				'value'             => wu_get_isset($settings, 'footer_text', ''),
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "footer")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'footer_text',
				],
			],
			'footer_font'             => [
				'type'              => 'select',
				'title'             => __('Footer Font-Family', 'ultimate-multisite'),
				'value'             => wu_get_isset($settings, 'footer_font', ''),
				'options'           => [
					'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif' => __('Helvetica', 'ultimate-multisite'),
					'Arial, Helvetica, sans-serif'       => __('Arial', 'ultimate-multisite'),
					'Times New Roman, Times, serif'      => __('Times New Roman', 'ultimate-multisite'),
					'Lucida Console, Courier, monospace' => __('Lucida', 'ultimate-multisite'),
				],
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "footer")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'footer_font',
				],
			],
			'footer_color'            => [
				'type'              => 'color-picker',
				'title'             => __('Footer Color', 'ultimate-multisite'),
				'value'             => '#000000',
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "footer")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'footer_color',
				],
			],
			'footer_align'            => [
				'type'              => 'select',
				'title'             => __('Footer Alignment', 'ultimate-multisite'),
				'tooltip'           => __('Alignment of the font in the main email footer.', 'ultimate-multisite'),
				'value'             => wu_get_isset($settings, 'footer_align', ''),
				'options'           => [
					'left'   => __('Left', 'ultimate-multisite'),
					'center' => __('Center', 'ultimate-multisite'),
					'right'  => __('Right', 'ultimate-multisite'),
				],
				'wrapper_html_attr' => [
					'v-show'  => 'require("tab", "footer")',
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model.lazy' => 'footer_align',
				],
			],
		];

		$state = array_merge(
			$settings,
			[
				'tab'     => 'header',
				'refresh' => true,
			]
		);

		$this->add_fields_widget(
			'customizer',
			[
				'title'     => __('Customizer', 'ultimate-multisite'),
				'position'  => 'side',
				'fields'    => $fields,
				'html_attr' => [
					'style'                    => 'margin-top: -6px;',
					'data-wu-app'              => 'email_template_customizer',
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

		return __('Customize Email Template:', 'ultimate-multisite');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Customize Email Template', 'ultimate-multisite');
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
			'customize_label'     => __('Customize Email Template', 'ultimate-multisite'),
			'add_new_label'       => __('Customize Email Template', 'ultimate-multisite'),
			'edit_label'          => __('Edit Email Template', 'ultimate-multisite'),
			'updated_message'     => __('Email Template updated with success!', 'ultimate-multisite'),
			'title_placeholder'   => __('Enter Email Template Name', 'ultimate-multisite'),
			'title_description'   => __('This name is used for internal reference only.', 'ultimate-multisite'),
			'save_button_label'   => __('Save Template', 'ultimate-multisite'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Email Template', 'ultimate-multisite'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'ultimate-multisite'),
		];
	}

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save(): void {
		// Calling method performs the nonce check.
		$allowed_settings = [
			'use_custom_logo',
			'custom_logo',
			'display_company_address',
			'background_color',
			'title_color',
			'title_size',
			'title_align',
			'title_font',
			'content_color',
			'content_align',
			'content_font',
			'footer_font',
			'footer_text',
			'footer_color',
			'footer_align',
		];

		$settings_to_save = [];

		foreach ($allowed_settings as $setting) {
			if (isset($_POST[ $setting ])) { // phpcs:ignore WordPress.Security.NonceVerification
				$value = wp_unslash($_POST[ $setting ]); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				switch ($setting) {
					case 'background_color':
					case 'title_color':
					case 'content_color':
					case 'footer_color':
						$settings_to_save[ $setting ] = sanitize_hex_color($value);
						break;
					case 'use_custom_logo':
					case 'display_company_address':
						$settings_to_save[ $setting ] = wu_string_to_bool($value);
						break;
					case 'custom_logo':
						$settings_to_save[ $setting ] = absint($value);
						break;
					case 'title_size':
						// Validate against allowed title sizes
						$allowed_sizes                = ['h1', 'h2', 'h3', 'h4', 'h5'];
						$settings_to_save[ $setting ] = in_array($value, $allowed_sizes, true) ? $value : 'h3';
						break;
					case 'title_align':
					case 'content_align':
					case 'footer_align':
						// Validate against allowed alignments
						$allowed_aligns               = ['left', 'center', 'right'];
						$settings_to_save[ $setting ] = in_array($value, $allowed_aligns, true) ? $value : 'center';
						break;
					case 'title_font':
					case 'content_font':
					case 'footer_font':
						// Validate against allowed fonts
						$allowed_fonts                = [
							'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif',
							'Arial, Helvetica, sans-serif',
							'Times New Roman, Times, serif',
							'Lucida Console, Courier, monospace',
						];
						$settings_to_save[ $setting ] = in_array($value, $allowed_fonts, true) ? $value : 'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif';
						break;
					case 'footer_text':
					default:
						$settings_to_save[ $setting ] = sanitize_text_field($value);
						break;
				}
			}
		}

		$this->save_settings($settings_to_save);

		$url = add_query_arg('updated', '1');

		wp_safe_redirect($url);

		exit;
	}

	/**
	 * Get the value of attributes.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_attributes() {

		$saved_atts = static::get_settings();

		$attributes = wp_parse_args($saved_atts, static::get_default_settings());

		return $attributes;
	}

	/**
	 * Gets the default email template settings.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_default_settings() {

		return [
			'use_custom_logo'         => false,
			'custom_logo'             => false,
			'display_company_address' => true,
			'background_color'        => '#f1f1f1',
			'title_color'             => '#000000',
			'title_size'              => 'h3',
			'title_align'             => 'center',
			'title_font'              => 'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif',
			'content_color'           => '#000000',
			'content_align'           => 'left',
			'content_font'            => 'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif',
			'footer_font'             => 'Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif',
			'footer_text'             => '',
			'footer_color'            => '#000000',
			'footer_align'            => 'center',
		];
	}

	/**
	 * Returns the list of saved settings to customize the email template.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public static function get_settings() {

		return wu_get_option('email_template', []);
	}

	/**
	 * Returns a specific email template setting.
	 *
	 * @since 2.0.0
	 *
	 * @param string $setting The setting name.
	 * @param string $default_value In case there's no option.
	 * @return string With the requested setting.
	 */
	public function get_setting($setting, $default_value = false) {

		if ($setting) {
			$return = wu_get_option('email_template', []);

			if ($return && isset($return[ $setting ])) {
				$return = $return[ $setting ];
			} else {
				$return = $default_value;
			}

			return $return;
		}

		return $default_value;
	}

	/**
	 * Save settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings_to_save List of settings to save.
	 * @return boolean
	 */
	public function save_settings($settings_to_save) {

		$allowed_keys = $this->get_attributes();

		foreach ($settings_to_save as $setting_to_save => $value) {
			if ( ! array_key_exists($setting_to_save, $allowed_keys)) {
				unset($settings_to_save[ $setting_to_save ]);
			}
		}

		return wu_save_option('email_template', $settings_to_save);
	}
}
