<?php
/**
 * Multisite Ultimate Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use WP_Ultimo\Settings;
use WP_Ultimo\UI\Form;
use WP_Ultimo\UI\Field;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Dashboard Admin Page.
 */
class Settings_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-settings';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Dashicon to be used on the menu item. This is only used on top-level menus
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $menu_icon = 'dashicons-wu-wp-ultimo';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

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
		'network_admin_menu' => 'wu_read_settings',
	];

	/**
	 * Should we hide admin notices on this page?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hide_admin_notices = false;

	/**
	 * Should we force the admin menu into a folded state?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $fold_menu = false;

	/**
	 * Holds the section slug for the URLs.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $section_slug = 'tab';

	/**
	 * Defines if the step links on the side are clickable or not.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $clickable_navigation = true;

	/**
	 * Allow child classes to register scripts and styles that can be loaded on the output function, for example.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_scripts(): void {

		wp_enqueue_editor();

		parent::register_scripts();

		/*
		 * Adds Vue.
		 */
		wp_enqueue_script('wu-vue-apps');

		wp_enqueue_script('wu-fields');

		wp_enqueue_style('wp-color-picker');
	}

	/**
	 * Registers widgets to the edit page.
	 *
	 * This implementation register the default save widget.
	 * Child classes that wish to inherit that widget while registering other,
	 * can do such by adding a parent::register_widgets() to their own register_widgets() method.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_widgets(): void {

		parent::register_widgets();

		wu_register_settings_side_panel(
			'login-and-registration',
			[
				'title'  => __('Checkout Forms', 'multisite-ultimate'),
				'render' => [$this, 'render_checkout_forms_side_panel'],
			]
		);

		wu_register_settings_side_panel(
			'sites',
			[
				'title'  => __('Template Previewer', 'multisite-ultimate'),
				'render' => [$this, 'render_site_template_side_panel'],
			]
		);

		wu_register_settings_side_panel(
			'sites',
			[
				'title'  => __('Placeholder Editor', 'multisite-ultimate'),
				'render' => [$this, 'render_site_placeholders_side_panel'],
			]
		);

		wu_register_settings_side_panel(
			'payment-gateways',
			[
				'title'  => __('Invoices', 'multisite-ultimate'),
				'render' => [$this, 'render_invoice_side_panel'],
			]
		);

		wu_register_settings_side_panel(
			'emails',
			[
				'title'  => __('System Emails', 'multisite-ultimate'),
				'render' => [$this, 'render_system_emails_side_panel'],
			]
		);

		wu_register_settings_side_panel(
			'emails',
			[
				'title'  => __('Email Template', 'multisite-ultimate'),
				'render' => [$this, 'render_email_template_side_panel'],
			]
		);
	}

	/**
	 * Renders the addons side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_checkout_forms_side_panel(): void {
		?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php esc_html_e('Checkout Forms', 'multisite-ultimate'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Checkout Forms', 'multisite-ultimate'); ?>" src="<?php echo esc_attr(wu_get_asset('sidebar/checkout-forms.webp')); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php esc_html_e('You can create multiple Checkout Forms for different occasions (seasonal campaigns, launches, etc)!', 'multisite-ultimate'); ?>
				</p>

			</div>

			<?php if (current_user_can('wu_edit_checkout_forms')) : ?>

				<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
					<a class="button wu-w-full wu-text-center" href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-checkout-forms')); ?>">
						<?php esc_html_e('Manage Checkout Forms &rarr;', 'multisite-ultimate'); ?>
					</a>
				</div>

			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Renders the site template side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_site_template_side_panel(): void {

		?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php esc_html_e('Customize the Template Previewer', 'multisite-ultimate'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize the Template Previewer', 'multisite-ultimate'); ?>" src="<?php echo esc_attr(wu_get_asset('sidebar/site-template.webp')); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php esc_html_e('Did you know that you can customize colors, logos, and more options of the Site Template Previewer top-bar?', 'multisite-ultimate'); ?>
				</p>

			</div>

			<?php if (current_user_can('wu_edit_sites')) : ?>

				<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
					<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-customize-template-previewer')); ?>">
						<?php esc_html_e('Go to Customizer &rarr;', 'multisite-ultimate'); ?>
					</a>
				</div>

			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Renders the site placeholder side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_site_placeholders_side_panel(): void {

		?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php esc_html_e('Customize the Template Placeholders', 'multisite-ultimate'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize the Template Placeholders', 'multisite-ultimate'); ?>" src="<?php echo esc_attr(wu_get_asset('sidebar/template-placeholders.webp')); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php esc_html_e('If you are using placeholder substitutions inside your site templates, use this tool to add, remove, or change the default content of those placeholders.', 'multisite-ultimate'); ?>
				</p>

			</div>

			<?php if (current_user_can('wu_edit_sites')) : ?>

				<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
					<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-template-placeholders')); ?>">
						<?php esc_html_e('Edit Placeholders &rarr;', 'multisite-ultimate'); ?>
					</a>
				</div>

			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Renders the invoice side panel
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_invoice_side_panel(): void {

		?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php esc_html_e('Customize the Invoice Template', 'multisite-ultimate'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize the Invoice Template', 'multisite-ultimate'); ?>" src="<?php echo esc_attr(wu_get_asset('sidebar/invoice-template.webp')); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php esc_html_e('Did you know that you can customize colors, logos, and more options of the Invoice PDF template?', 'multisite-ultimate'); ?>
				</p>

			</div>

			<?php if (current_user_can('wu_edit_payments')) : ?>

				<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
					<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-customize-invoice-template')); ?>">
						<?php esc_html_e('Go to Customizer &rarr;', 'multisite-ultimate'); ?>
					</a>
				</div>

			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Renders system emails side panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_system_emails_side_panel(): void {

		?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php esc_html_e('Customize System Emails', 'multisite-ultimate'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize System Emails', 'multisite-ultimate'); ?>" src="<?php echo esc_attr(wu_get_asset('sidebar/system-emails.webp')); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php esc_html_e('You can completely customize the contents of the emails sent out by Multisite Ultimate when particular events occur, such as Account Creation, Payment Failures, etc.', 'multisite-ultimate'); ?>
				</p>

			</div>

			<?php if (current_user_can('wu_edit_broadcasts')) : ?>

				<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
					<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-emails')); ?>">
						<?php esc_html_e('Customize System Emails &rarr;', 'multisite-ultimate'); ?>
					</a>
				</div>

			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Renders the email template side panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_email_template_side_panel(): void {

		?>

		<div class="wu-widget-inset">

			<div class="wu-p-4">

				<span class="wu-text-gray-700 wu-font-bold wu-uppercase wu-tracking-wide wu-text-xs">
					<?php esc_html_e('Customize Email Template', 'multisite-ultimate'); ?>
				</span>

				<div class="wu-py-2">
					<img class="wu-w-full" alt="<?php esc_attr_e('Customize Email Template', 'multisite-ultimate'); ?>" src="<?php echo esc_attr(wu_get_asset('sidebar/email-template.webp')); ?>">
				</div>

				<p class="wu-text-gray-600 wu-p-0 wu-m-0">
					<?php esc_html_e('If your network is using the HTML email option, you can customize the look and feel of the email template.', 'multisite-ultimate'); ?>
				</p>

			</div>

			<?php if (current_user_can('wu_edit_broadcasts')) : ?>

				<div class="wu-p-4 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">
					<a class="button wu-w-full wu-text-center" target="_blank" href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-customize-email-template')); ?>">
						<?php esc_html_e('Customize Email Template &rarr;', 'multisite-ultimate'); ?>
					</a>
				</div>

			<?php endif; ?>

		</div>

		<?php
	}

	// phpcs:enable

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Settings', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Settings', 'multisite-ultimate');
	}

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output(): void {
		/*
		 * Enqueue the base Dashboard Scripts
		 */
		wp_enqueue_media();
		wp_enqueue_script('dashboard');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('media');
		wp_enqueue_script('wu-vue');
		wp_enqueue_script('wu-selectizer');
		wp_enqueue_script('wu-settings-loader', wu_get_asset('settings-loader.js', 'js'), ['wu-functions'], wu_get_version(), true);

		do_action('wu_render_settings');

		wu_get_template(
			'base/settings',
			[
				'screen'               => get_current_screen(),
				'page'                 => $this,
				'classes'              => '',
				'sections'             => $this->get_sections(),
				'current_section'      => $this->get_current_section(),
				'clickable_navigation' => $this->clickable_navigation,
			]
		);
	}

	/**
	 * Returns the list of settings sections.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		return WP_Ultimo()->settings->get_sections();
	}

	/**
	 * Default handler for step submission. Simply redirects to the next step.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function default_handler(): void {

		if ( ! current_user_can('wu_edit_settings')) {
			wp_die(esc_html__('You do not have the permissions required to change settings.', 'multisite-ultimate'));
		}
		// Nonce processed in the calling method.
		if ( ! isset($_POST['active_gateways']) && 'payment-gateways' === wu_request('tab')) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$_POST['active_gateways'] = [];
		}

		WP_Ultimo()->settings->save_settings($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		wp_safe_redirect(add_query_arg('updated', 1, wu_get_current_url()));

		exit;
	}

	/**
	 * Default method for views.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function default_view(): void {

		$sections = $this->get_sections();

		$section_slug = $this->get_current_section();

		$section = $this->current_section;

		$fields = array_filter($section['fields'], fn($item) => current_user_can($item['capability']));

		uasort($fields, 'wu_sort_by_order');

		/*
		 * Get Field to save
		 */
		$fields['save'] = [
			'type'            => 'submit',
			'title'           => __('Save Settings', 'multisite-ultimate'),
			'classes'         => 'button button-primary button-large wu-ml-auto wu-w-full md:wu-w-auto',
			'wrapper_classes' => 'wu-sticky wu-bottom-0 wu-save-button wu-mr-px wu-w-full md:wu-w-auto',
			'html_attr'       => [
				'v-on:click' => 'send("window", "wu_block_ui", "#wpcontent")',
			],
		];

		if ( ! current_user_can('wu_edit_settings')) {
			$fields['save']['html_attr']['disabled'] = 'disabled';
		}

		$form = new Form(
			$section_slug,
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu--mt-5 wu--mx-in wu--mb-in',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-py-5 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'style'        => '',
					'data-on-load' => 'remove_block_ui',
					'data-wu-app'  => str_replace('-', '_', $section_slug),
					'data-state'   => wp_json_encode(wu_array_map_keys('wu_replace_dashes', Settings::get_instance()->get_all(true))),
				],
			]
		);

		$form->render();
	}
}
