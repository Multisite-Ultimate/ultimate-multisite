<?php
/**
 * Adds the Email Accounts Element UI to the Customer Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.3.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\Models\Email_Account;
use WP_Ultimo\Database\Email_Accounts\Email_Account_Status;
use WP_Ultimo\Models\Site;
use WP_Ultimo\Models\Membership;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Email Accounts Element UI to the Customer Panel.
 *
 * @since 2.3.0
 */
class Email_Accounts_Element extends Base_Element {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The id of the element.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	public $id = 'email-accounts';

	/**
	 * Controls if this is a public element.
	 *
	 * @since 2.3.0
	 * @var boolean
	 */
	protected $public = true;

	/**
	 * The current site.
	 *
	 * @since 2.3.0
	 * @var Site
	 */
	protected $site;

	/**
	 * The current membership.
	 *
	 * @since 2.3.0
	 * @var Membership
	 */
	protected $membership;

	/**
	 * The current customer.
	 *
	 * @since 2.3.0
	 * @var \WP_Ultimo\Models\Customer
	 */
	protected $customer;

	/**
	 * The icon of the UI element.
	 *
	 * @since 2.3.0
	 * @param string $context One of the values: block, elementor or bb.
	 * @return string
	 */
	public function get_icon($context = 'block'): string {

		if ('elementor' === $context) {
			return 'eicon-email-field';
		}

		return 'dashicons-wu-mail';
	}

	/**
	 * The title of the UI element.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_title() {

		return __('Email Accounts', 'ultimate-multisite');
	}

	/**
	 * The description of the UI element.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_description() {

		return __('Adds the email accounts management block.', 'ultimate-multisite');
	}

	/**
	 * The list of fields to be added to Gutenberg.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function fields() {

		$fields = [];

		$fields['header'] = [
			'title' => __('General', 'ultimate-multisite'),
			'desc'  => __('General', 'ultimate-multisite'),
			'type'  => 'header',
		];

		$fields['title'] = [
			'type'    => 'text',
			'title'   => __('Title', 'ultimate-multisite'),
			'value'   => __('Email Accounts', 'ultimate-multisite'),
			'desc'    => __('Leave blank to hide the title completely.', 'ultimate-multisite'),
			'tooltip' => '',
		];

		return $fields;
	}

	/**
	 * The list of keywords for this element.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function keywords() {

		return [
			'WP Ultimo',
			'Ultimate Multisite',
			'Email',
			'Mail',
		];
	}

	/**
	 * List of default parameters for the element.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function defaults() {

		return [
			'title' => __('Email Accounts', 'ultimate-multisite'),
		];
	}

	/**
	 * Initializes the singleton.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function init(): void {

		parent::init();

		// Check if email accounts feature is enabled
		if ( ! wu_get_setting('enable_email_accounts', false)) {
			$this->set_display(false);
			return;
		}

		if ($this->is_preview()) {
			$this->site     = wu_mock_site();
			$this->customer = wu_mock_customer();
			return;
		}

		$this->site     = wu_get_current_site();
		$this->customer = wu_get_current_customer();

		if ( ! $this->site || ! $this->customer) {
			$this->set_display(false);
			return;
		}

		// Check if membership has email accounts enabled
		$this->membership = $this->site->get_membership();

		if ($this->membership && $this->membership->has_limitations()) {
			$limitations = $this->membership->get_limitations();

			if ( ! isset($limitations->email_accounts) || ! $limitations->email_accounts->is_enabled()) {
				$this->set_display(false);
				return;
			}
		}

		add_action('plugins_loaded', [$this, 'register_forms']);
	}

	/**
	 * Loads the required scripts.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function register_scripts(): void {

		add_wubox();
	}

	/**
	 * Register ajax forms.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function register_forms(): void {

		wu_register_form(
			'user_create_email_account',
			[
				'render'     => [$this, 'render_create_email_form'],
				'handler'    => [$this, 'handle_create_email_form'],
				'capability' => 'exist',
			]
		);

		wu_register_form(
			'user_view_email_credentials',
			[
				'render'     => [$this, 'render_credentials_modal'],
				'handler'    => '__return_empty_string',
				'capability' => 'exist',
			]
		);

		wu_register_form(
			'user_delete_email_account',
			[
				'render'     => [$this, 'render_delete_email_form'],
				'handler'    => [$this, 'handle_delete_email_form'],
				'capability' => 'exist',
			]
		);

		wu_register_form(
			'user_view_dns_instructions',
			[
				'render'     => [$this, 'render_dns_instructions_modal'],
				'handler'    => '__return_empty_string',
				'capability' => 'exist',
			]
		);
	}

	/**
	 * Renders the create email account form.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function render_create_email_form(): void {

		$providers        = wu_get_enabled_email_providers();
		$provider_options = [];

		foreach ($providers as $id => $provider) {
			$provider_options[ $id ] = $provider->get_title();
		}

		// Get available domains for this customer
		$site    = wu_get_site(wu_request('current_site'));
		$domains = [];

		if ($site) {
			// Add the site's primary domain
			$site_domain             = wp_parse_url(get_site_url($site->get_id()), PHP_URL_HOST);
			$domains[ $site_domain ] = $site_domain;

			// Add any mapped domains
			$mapped_domains = wu_get_domains(
				[
					'blog_id' => $site->get_id(),
					'active'  => true,
				]
			);
			foreach ($mapped_domains as $domain) {
				$domains[ $domain->get_domain() ] = $domain->get_domain();
			}
		}

		$fields = [
			'provider'      => [
				'type'        => 'select',
				'title'       => __('Email Provider', 'ultimate-multisite'),
				'placeholder' => __('Select a provider', 'ultimate-multisite'),
				'options'     => $provider_options,
				'html_attr'   => [
					'v-model' => 'provider',
				],
			],
			'domain'        => [
				'type'        => 'select',
				'title'       => __('Domain', 'ultimate-multisite'),
				'placeholder' => __('Select a domain', 'ultimate-multisite'),
				'options'     => $domains,
				'html_attr'   => [
					'v-model' => 'domain',
				],
			],
			'username'      => [
				'type'        => 'text',
				'title'       => __('Username', 'ultimate-multisite'),
				'placeholder' => __('e.g. info, support, admin', 'ultimate-multisite'),
				'html_attr'   => [
					'v-model' => 'username',
				],
			],
			'email_preview' => [
				'type'              => 'note',
				'desc'              => sprintf(
					'<strong>%s:</strong> <code>{{ username }}@{{ domain }}</code>',
					__('Email Address', 'ultimate-multisite')
				),
				'wrapper_html_attr' => [
					'v-show'  => 'username && domain',
					'v-cloak' => 1,
				],
			],
			'current_site'  => [
				'type'  => 'hidden',
				'value' => wu_request('current_site'),
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Create Email Account', 'ultimate-multisite'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!provider || !domain || !username',
				],
			],
		];

		$form = new Form(
			'create_email_account',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'create_email_account',
					'data-state'  => wp_json_encode(
						[
							'provider' => '',
							'domain'   => '',
							'username' => '',
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles creation of a new email account.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function handle_create_email_form(): void {

		$current_user_id = get_current_user_id();
		$current_site_id = wu_request('current_site');
		$current_site    = wu_get_site($current_site_id);

		if ( ! $current_site) {
			wp_send_json_error(new \WP_Error('invalid_site', __('Invalid site.', 'ultimate-multisite')));
			exit;
		}

		$customer = $current_site->get_customer();

		if ( ! is_super_admin() && (! $customer || $customer->get_user_id() !== $current_user_id)) {
			wp_send_json_error(new \WP_Error('no_permissions', __('You do not have permissions to perform this action.', 'ultimate-multisite')));
			exit;
		}

		$membership = $current_site->get_membership();

		// Build email address
		$username      = sanitize_user(wu_request('username'), true);
		$domain        = sanitize_text_field(wu_request('domain'));
		$provider      = sanitize_text_field(wu_request('provider'));
		$email_address = $username . '@' . $domain;

		// Validate
		if (empty($username) || empty($domain) || empty($provider)) {
			wp_send_json_error(new \WP_Error('missing_fields', __('All fields are required.', 'ultimate-multisite')));
			exit;
		}

		// Create the account
		$manager = \WP_Ultimo\Managers\Email_Account_Manager::get_instance();

		$email_account = $manager->create_account(
			[
				'customer_id'   => $customer->get_id(),
				'membership_id' => $membership ? $membership->get_id() : null,
				'site_id'       => $current_site_id,
				'email_address' => $email_address,
				'provider'      => $provider,
				'purchase_type' => 'membership_included',
			]
		);

		if (is_wp_error($email_account)) {
			wp_send_json_error($email_account);
			exit;
		}

		wp_send_json_success(
			[
				'redirect_url' => wu_get_current_url(),
			]
		);

		exit;
	}

	/**
	 * Renders the credentials modal.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function render_credentials_modal(): void {

		$email_account_id = wu_request('email_account_id');
		$email_account    = wu_get_email_account($email_account_id);

		if ( ! $email_account) {
			echo '<p>' . esc_html__('Email account not found.', 'ultimate-multisite') . '</p>';
			return;
		}

		// Get password from token if available
		$password_token = $email_account->get_meta('password_display_token');
		$password       = '';

		if ($password_token) {
			$password = wu_get_email_password_from_token($password_token, $email_account_id);

			if ($password) {
				// Clear the token after retrieval
				$email_account->delete_meta('password_display_token');
			}
		}

		$imap_settings = $email_account->get_imap_settings();
		$smtp_settings = $email_account->get_smtp_settings();

		$fields = [
			'email_address' => [
				'type'          => 'text-display',
				'title'         => __('Email Address', 'ultimate-multisite'),
				'display_value' => $email_account->get_email_address(),
				'copy'          => true,
			],
		];

		if ($password) {
			$fields['password'] = [
				'type'          => 'text-display',
				'title'         => __('Password', 'ultimate-multisite'),
				'display_value' => $password,
				'copy'          => true,
			];

			$fields['password_note'] = [
				'type' => 'note',
				'desc' => '<span class="wu-text-yellow-600">' . __('Please save this password now. For security, it will not be shown again.', 'ultimate-multisite') . '</span>',
			];
		}

		$fields['webmail_url'] = [
			'type'          => 'text-display',
			'title'         => __('Webmail URL', 'ultimate-multisite'),
			'display_value' => sprintf('<a href="%s" target="_blank">%s</a>', esc_url($email_account->get_webmail_url()), esc_html($email_account->get_webmail_url())),
		];

		$fields['imap_header'] = [
			'type'  => 'header',
			'title' => __('IMAP Settings', 'ultimate-multisite'),
		];

		$fields['imap_server'] = [
			'type'          => 'text-display',
			'title'         => __('Server', 'ultimate-multisite'),
			'display_value' => $imap_settings['server'] ?? '',
			'copy'          => true,
		];

		$fields['imap_port'] = [
			'type'          => 'text-display',
			'title'         => __('Port', 'ultimate-multisite'),
			'display_value' => $imap_settings['port'] ?? '',
		];

		$fields['smtp_header'] = [
			'type'  => 'header',
			'title' => __('SMTP Settings', 'ultimate-multisite'),
		];

		$fields['smtp_server'] = [
			'type'          => 'text-display',
			'title'         => __('Server', 'ultimate-multisite'),
			'display_value' => $smtp_settings['server'] ?? '',
			'copy'          => true,
		];

		$fields['smtp_port'] = [
			'type'          => 'text-display',
			'title'         => __('Port', 'ultimate-multisite'),
			'display_value' => $smtp_settings['port'] ?? '',
		];

		$form = new Form(
			'view_email_credentials',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			]
		);

		$form->render();
	}

	/**
	 * Renders the delete confirmation form.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function render_delete_email_form(): void {

		$email_account_id = wu_request('email_account_id');
		$email_account    = wu_get_email_account($email_account_id);

		$fields = [
			'warning'          => [
				'type' => 'note',
				'desc' => sprintf(
					'<strong class="wu-text-red-600">%s</strong> %s',
					__('Warning:', 'ultimate-multisite'),
					__('This will permanently delete the email account and all its data from the email provider.', 'ultimate-multisite')
				),
			],
			'confirm'          => [
				'type'      => 'toggle',
				'title'     => __('Confirm Deletion', 'ultimate-multisite'),
				'desc'      => __('I understand this action cannot be undone.', 'ultimate-multisite'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'email_account_id' => [
				'type'  => 'hidden',
				'value' => $email_account_id,
			],
			'submit_button'    => [
				'type'            => 'submit',
				'title'           => __('Delete Email Account', 'ultimate-multisite'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!confirmed',
				],
			],
		];

		$form = new Form(
			'delete_email_account',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'delete_email_account',
					'data-state'  => wp_json_encode(
						[
							'confirmed' => false,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles email account deletion.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function handle_delete_email_form(): void {

		$email_account_id = wu_request('email_account_id');
		$email_account    = wu_get_email_account($email_account_id);

		if ( ! $email_account) {
			wp_send_json_error(new \WP_Error('not_found', __('Email account not found.', 'ultimate-multisite')));
			exit;
		}

		// Verify ownership
		$current_user_id = get_current_user_id();
		$customer        = $email_account->get_customer();

		if ( ! is_super_admin() && (! $customer || $customer->get_user_id() !== $current_user_id)) {
			wp_send_json_error(new \WP_Error('no_permissions', __('You do not have permissions to perform this action.', 'ultimate-multisite')));
			exit;
		}

		// Queue deletion from provider
		wu_enqueue_async_action(
			'wu_async_delete_email_account',
			[
				'email_address' => $email_account->get_email_address(),
				'provider'      => $email_account->get_provider(),
			],
			'email_account'
		);

		// Delete from database
		$email_account->delete();

		wp_send_json_success(
			[
				'redirect_url' => wu_get_current_url(),
			]
		);

		exit;
	}

	/**
	 * Renders the DNS instructions modal.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function render_dns_instructions_modal(): void {

		$provider_id = wu_request('provider');
		$domain      = wu_request('domain');

		$provider = wu_get_email_provider($provider_id);

		if ( ! $provider) {
			echo '<p>' . esc_html__('Provider not found.', 'ultimate-multisite') . '</p>';
			return;
		}

		$dns_records = $provider->get_dns_instructions($domain);

		echo '<div class="wu-p-4">';
		echo '<p>' . esc_html__('Add the following DNS records to your domain to enable email:', 'ultimate-multisite') . '</p>';
		echo '<table class="wu-w-full wu-mt-4">';
		echo '<thead><tr><th>' . esc_html__('Type', 'ultimate-multisite') . '</th><th>' . esc_html__('Name', 'ultimate-multisite') . '</th><th>' . esc_html__('Value', 'ultimate-multisite') . '</th></tr></thead>';
		echo '<tbody>';

		foreach ($dns_records as $record) {
			$priority = isset($record['priority']) ? ' (' . esc_html__('Priority:', 'ultimate-multisite') . ' ' . esc_html($record['priority']) . ')' : '';
			echo '<tr>';
			echo '<td><code>' . esc_html($record['type']) . '</code></td>';
			echo '<td><code>' . esc_html($record['name']) . '</code></td>';
			echo '<td><code>' . esc_html($record['value']) . '</code>' . esc_html($priority) . '</td>';
			echo '</tr>';

			if ( ! empty($record['description'])) {
				echo '<tr><td colspan="3" class="wu-text-gray-500 wu-text-sm">' . esc_html($record['description']) . '</td></tr>';
			}
		}

		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Runs early on the request lifecycle.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function setup(): void {

		$this->site     = WP_Ultimo()->currents->get_site();
		$this->customer = WP_Ultimo()->currents->get_customer();

		if ( ! $this->site || ! $this->customer || ! $this->site->is_customer_allowed()) {
			$this->set_display(false);
			return;
		}

		$this->membership = $this->site->get_membership();

		// Load admin.php for helper functions
		require_once wu_path('inc/functions/admin.php');
	}

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function setup_preview(): void {

		$this->site       = wu_mock_site();
		$this->customer   = wu_mock_customer();
		$this->membership = wu_mock_membership();
	}

	/**
	 * The content to be output on the screen.
	 *
	 * @since 2.3.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return void
	 */
	public function output($atts, $content = null): void {

		$current_site = $this->site;
		$customer     = $this->customer;

		if ( ! $current_site || ! $customer) {
			return;
		}

		// Get email accounts for this site
		$email_accounts = wu_get_email_accounts(
			[
				'customer_id' => $customer->get_id(),
				'site_id'     => $current_site->get_id(),
				'orderby'     => 'date_created',
				'order'       => 'DESC',
			]
		);

		$accounts = [];

		foreach ($email_accounts as $account) {
			$status = new Email_Account_Status($account->get_status());

			$url_atts = [
				'current_site'     => $current_site->get_id(),
				'email_account_id' => $account->get_id(),
			];

			$accounts[] = [
				'id'              => $account->get_id(),
				'email_object'    => $account,
				'email_address'   => $account->get_email_address(),
				'provider'        => $account->get_provider(),
				'provider_title'  => $account->get_provider_instance() ? $account->get_provider_instance()->get_title() : $account->get_provider(),
				'status'          => $status->get_label(),
				'status_class'    => $status->get_classes(),
				'webmail_url'     => $account->get_webmail_url(),
				'credentials_url' => wu_get_form_url('user_view_email_credentials', $url_atts),
				'delete_url'      => wu_get_form_url('user_delete_email_account', $url_atts),
			];
		}

		// Check if can create more
		$can_create = false;

		if ($this->membership) {
			$can_create = wu_can_create_email_account($customer->get_id(), $this->membership->get_id());
		}

		// Get enabled providers
		$providers = wu_get_enabled_email_providers();

		$url_atts = [
			'current_site' => $current_site->get_id(),
		];

		$other_atts = [
			'email_accounts' => $accounts,
			'can_create'     => $can_create && ! empty($providers),
			'modal'          => [
				'label'   => __('Add Email Account', 'ultimate-multisite'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('user_create_email_account', $url_atts),
			],
		];

		$atts = array_merge($other_atts, $atts);

		wu_get_template('dashboard-widgets/email-accounts', $atts);
	}
}
