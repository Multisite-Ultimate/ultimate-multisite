<?php
/**
 * Adds the Site_Actions_Element UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\Database\Memberships\Membership_Status;
use WP_Ultimo\Models\Site;
use WP_Ultimo\Models\Membership;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Checkout Element UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Site_Actions_Element extends Base_Element {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The id of the element.
	 *
	 * Something simple, without prefixes, like 'checkout', or 'pricing-tables'.
	 *
	 * This is used to construct shortcodes by prefixing the id with 'wu_'
	 * e.g. an id checkout becomes the shortcode 'wu_checkout' and
	 * to generate the Gutenberg block by prefixing it with 'wp-ultimo/'
	 * e.g. checkout would become the block 'wp-ultimo/checkout'.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $id = 'site-actions';

	/**
	 * Controls if this is a public element to be used in pages/shortcodes by user.
	 *
	 * @since 2.0.24
	 * @var boolean
	 */
	protected $public = true;

	/**
	 * The current site.
	 *
	 * @since 2.2.0
	 * @var Site
	 */
	public $site;

	/**
	 * The current membership.
	 *
	 * @since 2.2.0
	 * @var Membership
	 */
	public $membership;

	/**
	 * Loads the required scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		add_wubox();
	}

	/**
	 * The icon of the UI element.
	 * e.g. return fa fa-search
	 *
	 * @since 2.0.0
	 * @param string $context One of the values: block, elementor or bb.
	 */
	public function get_icon($context = 'block'): string {

		if ('elementor' === $context) {
			return 'eicon-info-circle-o';
		}

		return 'fa fa-search';
	}

	/**
	 * The title of the UI element.
	 *
	 * This is used on the Blocks list of Gutenberg.
	 * You should return a string with the localized title.
	 * e.g. return __('My Element', 'multisite-ultimate').
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Actions', 'multisite-ultimate');
	}

	/**
	 * The description of the UI element.
	 *
	 * This is also used on the Gutenberg block list
	 * to explain what this block is about.
	 * You should return a string with the localized title.
	 * e.g. return __('Adds a checkout form to the page', 'multisite-ultimate').
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Adds a checkout form block to the page.', 'multisite-ultimate');
	}

	/**
	 * The list of fields to be added to Gutenberg.
	 *
	 * If you plan to add Gutenberg controls to this block,
	 * you'll need to return an array of fields, following
	 * our fields interface (@see inc/ui/class-field.php).
	 *
	 * You can create new Gutenberg panels by adding fields
	 * with the type 'header'. See the Checkout Elements for reference.
	 *
	 * @see inc/ui/class-checkout-element.php
	 *
	 * Return an empty array if you don't have controls to add.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function fields() {

		$fields = [];

		$fields['header'] = [
			'title' => __('General', 'multisite-ultimate'),
			'desc'  => __('General', 'multisite-ultimate'),
			'type'  => 'header',
		];

		$fields['show_change_password'] = [
			'type'    => 'toggle',
			'title'   => __('Show Change Password', 'multisite-ultimate'),
			'desc'    => __('Toggle to show/hide the password link.', 'multisite-ultimate'),
			'tooltip' => '',
			'value'   => 1,
		];

		$fields['show_change_default_site'] = [
			'type'    => 'toggle',
			'title'   => __('Show Change Default Site', 'multisite-ultimate'),
			'desc'    => __('Toggle to show/hide the change default site link.', 'multisite-ultimate'),
			'tooltip' => '',
			'value'   => 1,
		];

		$fields['show_change_payment_method'] = [
			'type'    => 'toggle',
			'title'   => __('Show Change Payment Method', 'multisite-ultimate'),
			'desc'    => __('Toggle to show/hide the option to cancel the current payment method.', 'multisite-ultimate'),
			'tooltip' => '',
			'value'   => 1,
		];

		$pages = get_pages(
			[
				'exclude' => [get_the_ID()],
			]
		);

		$pages = $pages ?: [];

		$pages_list = [0 => __('Default', 'multisite-ultimate')];

		foreach ($pages as $page) {
			$pages_list[ $page->ID ] = $page->post_title;
		}

		$fields['redirect_after_delete'] = [
			'type'    => 'select',
			'title'   => __('Redirect After Delete', 'multisite-ultimate'),
			'value'   => 0,
			'desc'    => __('The page to redirect user after delete current site.', 'multisite-ultimate'),
			'tooltip' => '',
			'options' => $pages_list,
		];

		return $fields;
	}

	/**
	 * The list of keywords for this element.
	 *
	 * Return an array of strings with keywords describing this
	 * element. Gutenberg uses this to help customers find blocks.
	 *
	 * e.g.:
	 * return array(
	 *  'Multisite Ultimate',
	 *  'Actions',
	 *  'Form',
	 *  'Cart',
	 * );
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function keywords() {

		return [
			'WP Ultimo',
			'Multisite Ultimate',
			'Actions',
			'Form',
			'Cart',
		];
	}

	/**
	 * List of default parameters for the element.
	 *
	 * If you are planning to add controls using the fields,
	 * it might be a good idea to use this method to set defaults
	 * for the parameters you are expecting.
	 *
	 * These defaults will be used inside a 'wp_parse_args' call
	 * before passing the parameters down to the block render
	 * function and the shortcode render function.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		return [
			'show_change_password'       => 1,
			'show_change_default_site'   => 1,
			'show_change_payment_method' => 1,
			'redirect_after_delete'      => 0,
		];
	}

	/**
	 * Runs early on the request lifecycle as soon as we detect the shortcode is present.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup(): void {

		$this->site = WP_Ultimo()->currents->get_site();

		if ( ! $this->site || ! $this->site->is_customer_allowed()) {
			$this->site = false;
		}

		$this->membership = WP_Ultimo()->currents->get_membership();

		if ( ! $this->membership) {
			$this->set_display(false);

			return;
		}
	}

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_preview(): void {

		$this->site = wu_mock_site();

		$this->membership = wu_mock_membership();
	}

	/**
	 * Overload the init to add site-related forms.
	 *
	 * @since 2.0.21
	 * @return void
	 */
	public function init(): void {

		parent::init();

		$this->register_forms();
	}

	/**
	 * Register forms
	 *
	 * @since 2.0.21
	 * @return void
	 */
	public function register_forms(): void {

		wu_register_form(
			'change_password',
			[
				'render'     => [$this, 'render_change_password'],
				'handler'    => [$this, 'handle_change_password'],
				'capability' => 'exist',
			]
		);

		wu_register_form(
			'delete_site',
			[
				'render'     => [$this, 'render_delete_site'],
				'handler'    => [$this, 'handle_delete_site'],
				'capability' => 'exist',
			]
		);

		wu_register_form(
			'change_default_site',
			[
				'render'     => [$this, 'render_change_default_site'],
				'handler'    => [$this, 'handle_change_default_site'],
				'capability' => 'exist',
			]
		);

		wu_register_form(
			'cancel_payment_method',
			[
				'render'     => [$this, 'render_cancel_payment_method'],
				'handler'    => [$this, 'handle_cancel_payment_method'],
				'capability' => 'exist',
			]
		);

		wu_register_form(
			'cancel_membership',
			[
				'render'     => [$this, 'render_cancel_membership'],
				'handler'    => [$this, 'handle_cancel_membership'],
				'capability' => 'exist',
			]
		);
	}

	/**
	 * Returns the actions for the element. These can be filtered.
	 *
	 * @since 2.0.0
	 * @param array $atts Parameters of the block/shortcode.
	 * @return array
	 */
	public function get_actions($atts) {

		$actions = [];

		$all_blogs = get_blogs_of_user(get_current_user_id());

		$is_template_switching_enabled = wu_get_setting('allow_template_switching', true);

		if ($is_template_switching_enabled && $this->site) {
			$actions['template_switching'] = [
				'label'        => __('Change Site Template', 'multisite-ultimate'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'href'         => add_query_arg(
					[
						'page' => 'wu-template-switching',
					],
					get_admin_url($this->site->get_id())
				),
			];
		}

		if (count($all_blogs) > 1 && wu_get_isset($atts, 'show_change_default_site')) {
			$actions['default_site'] = [
				'label'        => __('Change Default Site', 'multisite-ultimate'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'classes'      => 'wubox',
				'href'         => wu_get_form_url('change_default_site'),
			];
		}

		if (wu_get_isset($atts, 'show_change_password')) {
			$actions['change_password'] = [
				'label'        => __('Change Password', 'multisite-ultimate'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'classes'      => 'wubox',
				'href'         => wu_get_form_url('change_password'),
			];
		}

		$payment_gateway = $this->membership ? $this->membership->get_gateway() : false;

		if (wu_get_isset($atts, 'show_change_payment_method') && $payment_gateway) {
			$actions['cancel_payment_method'] = [
				'label'        => __('Cancel Current Payment Method', 'multisite-ultimate'),
				'icon_classes' => 'dashicons-wu-edit wu-align-middle',
				'classes'      => 'wubox',
				'href'         => wu_get_form_url(
					'cancel_payment_method',
					[
						'membership'   => $this->membership->get_hash(),
						'redirect_url' => wu_get_current_url(),
					]
				),
			];
		}

		return apply_filters('wu_element_get_site_actions', $actions, $atts, $this->site, $this->membership);
	}

	/**
	 * Returns the danger actions actions for the element. These can be filtered.
	 *
	 * @since 2.0.21
	 * @param array $atts Parameters of the block/shortcode.
	 * @return array
	 */
	public function get_danger_zone_actions($atts) {

		$actions = [];

		if ($this->site) {
			$actions = array_merge(
				[
					'delete_site' => [
						'label'        => __('Delete Site', 'multisite-ultimate'),
						'icon_classes' => 'dashicons-wu-edit wu-align-middle',
						'classes'      => 'wubox wu-text-red-500',
						'href'         => wu_get_form_url(
							'delete_site',
							[
								'site'         => $this->site->get_hash(),
								'redirect_url' => ! $atts['redirect_after_delete'] ? false : get_page_link($atts['redirect_after_delete']),
							]
						),
					],
				],
				$actions
			);
		}

		if ($this->membership && $this->membership->is_recurring() && $this->membership->get_status() !== Membership_Status::CANCELLED) {
			$actions = array_merge(
				[
					'cancel_membership' => [
						'label'        => __('Cancel Membership', 'multisite-ultimate'),
						'icon_classes' => 'dashicons-wu-edit wu-align-middle',
						'classes'      => 'wubox wu-text-red-500',
						'href'         => wu_get_form_url(
							'cancel_membership',
							[
								'membership'   => $this->membership->get_hash(),
								'redirect_url' => wu_get_current_url(),
							]
						),
					],
				],
				$actions
			);
		}

		return apply_filters('wu_element_get_danger_zone_site_actions', $actions);
	}

	/**
	 * Renders the delete site modal.
	 *
	 * @since 2.0.21
	 * @return void
	 */
	public function render_delete_site(): void {

		$site = wu_get_site_by_hash(wu_request('site'));

		$error = '';

		if ( ! $site) {
			$error = __('Site not selected.', 'multisite-ultimate');
		}

		$customer = wu_get_current_customer();

		if ( ! $customer || $customer->get_id() !== $site->get_customer_id()) {
			$error = __('You are not allowed to do this.', 'multisite-ultimate');
		}

		if ( ! empty($error)) {
			$error_field = [
				'error_message' => [
					'type' => 'note',
					'desc' => $error,
				],
			];

			$form = new \WP_Ultimo\UI\Form(
				'change_password',
				$error_field,
				[
					'views'                 => 'admin-pages/fields',
					'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
					'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				]
			);

			$form->render();

			return;
		}

		$fields = [
			'site'          => [
				'type'  => 'hidden',
				'value' => wu_request('site'),
			],
			'redirect_url'  => [
				'type'  => 'hidden',
				'value' => wu_request('redirect_url'),
			],
			'confirm'       => [
				'type'      => 'toggle',
				'title'     => __('Confirm Site Deletion', 'multisite-ultimate'),
				'desc'      => __('This action can not be undone.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Delete Site', 'multisite-ultimate'),
				'placeholder'     => __('Delete Site', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!confirmed',
				],
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'change_password',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'change_password',
					'data-state'  => wu_convert_to_state(
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
	 * Handles the delete site modal.
	 *
	 * @since 2.0.21
	 *
	 * @return void|\WP_Error Void or WP_Error.
	 */
	public function handle_delete_site() {

		global $wpdb;

		$site = wu_get_site_by_hash(wu_request('site'));

		if ( ! $site || ! $site->is_customer_allowed()) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$customer = wu_get_current_customer();

		if ( ! $customer || $customer->get_id() !== $site->get_customer_id()) {
			return new \WP_Error('error', __('You are not allowed to do this.', 'multisite-ultimate'));
		}

		$wpdb->query('START TRANSACTION'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		try {
			$saved = $site->delete();

			if (is_wp_error($saved)) {
				return $saved;
			}
		} catch (\Throwable $e) {
			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return new \WP_Error('exception', $e->getMessage());
		}

		$wpdb->query('COMMIT'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		$redirect_url = wu_request('redirect_url');

		$redirect_url = add_query_arg(
			[
				'site_deleted' => true,
			],
			wu_request('redirect_url') ?? user_admin_url()
		);

		wp_send_json_success(
			[
				'redirect_url' => $redirect_url,
			]
		);
	}

	/**
	 * Renders the change password modal.
	 *
	 * @since 2.0.21
	 * @return void
	 */
	public function render_change_password(): void {

		$fields = [
			'password'          => [
				'type'        => 'password',
				'title'       => __('Current Password', 'multisite-ultimate'),
				'placeholder' => __('******', 'multisite-ultimate'),
			],
			'new_password'      => [
				'type'        => 'password',
				'title'       => __('New Password', 'multisite-ultimate'),
				'placeholder' => __('******', 'multisite-ultimate'),
				'meter'       => true,
			],
			'new_password_conf' => [
				'type'        => 'password',
				'placeholder' => __('******', 'multisite-ultimate'),
				'title'       => __('Confirm New Password', 'multisite-ultimate'),
			],
			'submit_button'     => [
				'type'            => 'submit',
				'title'           => __('Reset Password', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [],
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'change_password',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'change_password',
					'data-state'  => wu_convert_to_state(),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the password reset form.
	 *
	 * @since 2.0.21
	 * @return void
	 */
	public function handle_change_password(): void {

		$user = wp_get_current_user();

		if ( ! $user) {
			$error = new \WP_Error('user-dont-exist', __('Something went wrong.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		$current_password = wu_request('password');

		if ( ! wp_check_password($current_password, $user->user_pass, $user->ID)) {
			$error = new \WP_Error('wrong-password', __('Your current password is wrong.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		$new_password      = wu_request('new_password');
		$new_password_conf = wu_request('new_password_conf');

		if ( ! $new_password || strlen((string) $new_password) < 6) {
			$error = new \WP_Error('password-min-length', __('The new password must be at least 6 characters long.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		if ($new_password !== $new_password_conf) {
			$error = new \WP_Error('passwords-dont-match', __('New passwords do not match.', 'multisite-ultimate'));

			wp_send_json_error($error);
		}

		reset_password($user, $new_password);

		// Log-in again.
		wp_set_auth_cookie($user->ID);
		wp_set_current_user($user->ID);
		do_action('wp_login', $user->user_login, $user); // PHPCS:ignore WordPress.NamingConventions
		$referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_url(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

		wp_send_json_success(
			[
				'redirect_url' => add_query_arg('updated', 1, $referer),
			]
		);
	}

	/**
	 * Renders the change current site modal.
	 *
	 * @since 2.0.21
	 * @return void
	 */
	public function render_change_default_site(): void {

		$all_blogs = get_blogs_of_user(get_current_user_id());

		$option_blogs = [];

		foreach ($all_blogs as $key => $blog) {
			$option_blogs[ $blog->userblog_id ] = get_home_url($blog->userblog_id);
		}

		$primary_blog = get_user_meta(get_current_user_id(), 'primary_blog', true);

		$fields = [
			'new_primary_site' => [
				'type'      => 'select',
				'title'     => __('Primary Site', 'multisite-ultimate'),
				'desc'      => __('Change the primary site of your network.', 'multisite-ultimate'),
				'options'   => $option_blogs,
				'value'     => $primary_blog,
				'html_attr' => [
					'v-model' => 'new_primary_site',
				],
			],
			'submit_button'    => [
				'type'            => 'submit',
				'title'           => __('Change Default Site', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => 'new_primary_site === "' . $primary_blog . '"',
				],
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'change_default_site',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'change_default_site',
					'data-state'  => wu_convert_to_state(
						[
							'new_primary_site' => $primary_blog,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the change default site form.
	 *
	 * @since 2.0.21
	 * @return void
	 */
	public function handle_change_default_site(): void {

		$new_primary_site = wu_request('new_primary_site');

		if ($new_primary_site) {
			update_user_meta(get_current_user_id(), 'primary_blog', $new_primary_site);
			$referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_url(wp_unslash($_SERVER['HTTP_REFERER'])) : '';

			wp_send_json_success(
				[
					'redirect_url' => add_query_arg('updated', 1, $referer),
				]
			);
		}

		$error = new \WP_Error('no-site-selected', __('You need to select a new primary site.', 'multisite-ultimate'));

		wp_send_json_error($error);
	}

	/**
	 * Renders the cancel payment method modal.
	 *
	 * @since 2.1.2
	 * @return void
	 */
	public function render_cancel_payment_method(): void {

		$membership = wu_get_membership_by_hash(wu_request('membership'));

		$error = '';

		if ( ! $membership) {
			$error = __('Membership not selected.', 'multisite-ultimate');
		}

		$customer = wu_get_current_customer();

		if ( ! is_super_admin() && (! $customer || $customer->get_id() !== $membership->get_customer_id())) {
			$error = __('You are not allowed to do this.', 'multisite-ultimate');
		}

		if ( ! empty($error)) {
			$error_field = [
				'error_message' => [
					'type' => 'note',
					'desc' => $error,
				],
			];

			$form = new \WP_Ultimo\UI\Form(
				'cancel_payment_method',
				$error_field,
				[
					'views'                 => 'admin-pages/fields',
					'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
					'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				]
			);

			$form->render();

			return;
		}

		$fields = [
			'membership'    => [
				'type'  => 'hidden',
				'value' => wu_request('membership'),
			],
			'redirect_url'  => [
				'type'  => 'hidden',
				'value' => wu_request('redirect_url'),
			],
			'confirm'       => [
				'type'      => 'toggle',
				'title'     => __('Confirm Payment Method Cancellation', 'multisite-ultimate'),
				'desc'      => __('This action can not be undone.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Cancel Payment Method', 'multisite-ultimate'),
				'placeholder'     => __('Cancel Payment Method', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!confirmed',
				],
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'cancel_payment_method',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'cancel_payment_method',
					'data-state'  => wu_convert_to_state(
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
	 * Handles the payment method cancellation.
	 *
	 * @since 2.1.2
	 * @return void
	 */
	public function handle_cancel_payment_method(): void {

		$membership = wu_get_membership_by_hash(wu_request('membership'));

		if ( ! $membership) {
			$error = new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));

			wp_send_json_error($error);

			return;
		}

		$customer = wu_get_current_customer();

		if ( ! is_super_admin() && (! $customer || $customer->get_id() !== $membership->get_customer_id())) {
			$error = new \WP_Error('error', __('You are not allowed to do this.', 'multisite-ultimate'));

			wp_send_json_error($error);

			return;
		}

		$membership->set_gateway('');
		$membership->set_gateway_subscription_id('');
		$membership->set_gateway_customer_id('');
		$membership->set_auto_renew(false);

		$membership->save();

		$redirect_url = wu_request('redirect_url');

		$redirect_url = add_query_arg(
			[
				'payment_gateway_cancelled' => true,
			],
			$redirect_url ?? user_admin_url()
		);

		wp_send_json_success(
			[
				'redirect_url' => $redirect_url,
			]
		);
	}

	/**
	 * Renders the cancel payment method modal.
	 *
	 * @since 2.1.2
	 * @return void
	 */
	public function render_cancel_membership(): void {

		$membership = wu_get_membership_by_hash(wu_request('membership'));

		$error = '';

		if ( ! $membership) {
			$error = __('Membership not selected.', 'multisite-ultimate');
		}

		$customer = wu_get_current_customer();

		if ( ! is_super_admin() && (! $customer || $customer->get_id() !== $membership->get_customer_id())) {
			$error = __('You are not allowed to do this.', 'multisite-ultimate');
		}

		if ( ! empty($error)) {
			$error_field = [
				'error_message' => [
					'type' => 'note',
					'desc' => $error,
				],
			];

			$form = new \WP_Ultimo\UI\Form(
				'cancel_membership',
				$error_field,
				[
					'views'                 => 'admin-pages/fields',
					'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
					'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				]
			);

			$form->render();

			return;
		}

		$fields = [
			'membership'               => [
				'type'  => 'hidden',
				'value' => wu_request('membership'),
			],
			'redirect_url'             => [
				'type'  => 'hidden',
				'value' => wu_request('redirect_url'),
			],
			'cancellation_reason'      => [
				'type'      => 'select',
				'title'     => __('Please tell us why you are cancelling.', 'multisite-ultimate'),
				'desc'      => __('We would love your feedback.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'cancellation_reason',
				],
				'default'   => '',
				'options'   => [
					''                 => __('Select a reason', 'multisite-ultimate'),
					'unused'           => __('I no longer need it', 'multisite-ultimate'),
					'too_expensive'    => __('It\'s too expensive', 'multisite-ultimate'),
					'missing_features' => __('I need more features', 'multisite-ultimate'),
					'switched_service' => __('Switched to another service', 'multisite-ultimate'),
					'customer_service' => __('Customer support is less than expected', 'multisite-ultimate'),
					'too_complex'      => __('Too complex', 'multisite-ultimate'),
					'other'            => __('Other', 'multisite-ultimate'),
				],
			],
			'cancellation_explanation' => [
				'type'              => 'textarea',
				'title'             => __('Please provide additional details.', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-show' => 'cancellation_reason === "other"',
				],
			],
			'confirm'                  => [
				'type'      => 'text',
				'title'     => __('Type <code class="wu-text-red-600">CANCEL</code> to confirm this membership cancellation.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmation',
				],
			],
		];

		$next_charge = false;

		if ($membership->is_recurring() && ($membership->is_active() || $membership->get_status() === Membership_Status::TRIALING)) {
			$next_charge = strtotime($membership->get_date_expiration());
		}

		if ($next_charge && $next_charge > time()) {
			$fields['next_charge'] = [
				'type' => 'note',
				// translators: %s: Next charge date.
				'desc' => sprintf(__('Your sites will stay working until %s.', 'multisite-ultimate'), date_i18n(get_option('date_format'), $next_charge)),
			];
		}

		$fields['submit_button'] = [
			'type'            => 'submit',
			'title'           => __('Cancel Membership', 'multisite-ultimate'),
			'placeholder'     => __('Cancel Membership', 'multisite-ultimate'),
			'value'           => 'save',
			'classes'         => 'button button-primary wu-w-full',
			'wrapper_classes' => 'wu-items-end',
			'html_attr'       => [
				'v-bind:disabled' => 'confirmation !== "' . __('CANCEL', 'multisite-ultimate') . '" || cancellation_reason === ""',
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'cancel_membership',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'cancel_membership',
					'data-state'  => wu_convert_to_state(
						[
							'confirmation'        => '',
							'cancellation_reason' => '',
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Handles the payment method cancellation.
	 *
	 * @since 2.1.2
	 * @return void
	 */
	public function handle_cancel_membership(): void {

		$membership = wu_get_membership_by_hash(wu_request('membership'));

		if ( ! $membership) {
			$error = new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));

			wp_send_json_error($error);

			return;
		}

		$customer = wu_get_current_customer();

		if ( ! is_super_admin() && (! $customer || $customer->get_id() !== $membership->get_customer_id())) {
			$error = new \WP_Error('error', __('You are not allowed to do this.', 'multisite-ultimate'));

			wp_send_json_error($error);

			return;
		}

		$cancellation_options = [
			'unused'           => __('I no longer need it', 'multisite-ultimate'),
			'too_expensive'    => __('It\'s too expensive', 'multisite-ultimate'),
			'missing_features' => __('I need more features', 'multisite-ultimate'),
			'switched_service' => __('Switched to another service', 'multisite-ultimate'),
			'customer_service' => __('Customer support is less than expected', 'multisite-ultimate'),
			'too_complex'      => __('Too complex', 'multisite-ultimate'),
			'other'            => wu_request('cancellation_explanation'),
		];

		$reason = wu_get_isset($cancellation_options, wu_request('cancellation_reason'), '');
		try {
			$membership->cancel($reason);
		} catch (\Exception $e) {
			wp_send_json_error(
				array(
					'code'    => 'error',
					'message' => $e->getMessage(),
				)
			);
		}

		$redirect_url = wu_request('redirect_url');

		$redirect_url = add_query_arg(
			[
				'payment_gateway_cancelled' => true,
			],
			! empty($redirect_url) ? $redirect_url : user_admin_url()
		);

		wp_send_json_success(
			[
				'redirect_url' => $redirect_url,
			]
		);
	}

	/**
	 * The content to be output on the screen.
	 *
	 * Should return HTML markup to be used to display the block.
	 * This method is shared between the block render method and
	 * the shortcode implementation.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return string
	 */
	public function output($atts, $content = null) {

		$atts['actions'] = $this->get_actions($atts);

		$atts['danger_zone_actions'] = $this->get_danger_zone_actions($atts);

		return wu_get_template_contents('dashboard-widgets/site-actions', $atts);
	}
}
