<?php
/**
 * Adds the Checkout_Element UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use ScssPhp\ScssPhp\Compiler;
use WP_Ultimo\Database\Memberships\Membership_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Checkout Element UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Checkout_Element extends Base_Element {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The current signup.
	 *
	 * @since 2.2.0
	 * @var Mocked_Signup
	 */
	protected $signup;

	/**
	 * The current checkout form steps.
	 *
	 * @since 2.2.0
	 * @var array|false
	 */
	public $steps;

	/**
	 * The current checkout form step.
	 *
	 * @since 2.2.0
	 * @var array
	 */
	public $step;

	/**
	 * The current checkout form step name.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	public $step_name;

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
	public $id = 'checkout';

	/**
	 * Controls if this is a public element to be used in pages/shortcodes by user.
	 *
	 * @since 2.0.24
	 * @var boolean
	 */
	protected $public = true;

	/**
	 * The icon of the UI element.
	 * e.g. return fa fa-search
	 *
	 * @since 2.0.0
	 * @param string $context One of the values: block, elementor or bb.
	 */
	public function get_icon($context = 'block'): string {

		if ('elementor' === $context) {
			return 'eicon-cart-medium';
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

		return __('Checkout', 'multisite-ultimate');
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

		$fields['slug'] = [
			'title' => __('Slug', 'multisite-ultimate'),
			'desc'  => __('The checkout form slug.', 'multisite-ultimate'),
			'type'  => 'text',
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
	 *  'Checkout',
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
			'Checkout',
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
			'slug'                   => 'main-form',
			'step'                   => false,
			'display_title'          => false,
			'membership_limitations' => [],
		];
	}

	/**
	 * Checks if we are on a thank you page.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_thank_you_page() {

		return is_user_logged_in() && wu_request('payment') && wu_request('status') === 'done';
	}

	/**
	 * Triggers the setup event to allow the checkout class to hook in.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup(): void {

		if ($this->is_thank_you_page()) {
			\WP_Ultimo\UI\Thank_You_Element::get_instance()->setup();

			return;
		}

		do_action('wu_setup_checkout', $this);
	}

	/**
	 * Print the Custom CSS added on the checkout.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Checkout_Form $checkout_form The current checkout form.
	 * @return void
	 */
	public function print_custom_css($checkout_form): void {

		$scss = new Compiler();

		$slug = $checkout_form->get_slug();

		$custom_css = $checkout_form->get_custom_css();

		if ($custom_css) {
			$custom_css = $scss->compileString(
				".wu_checkout_form_{$slug} {
				{$custom_css}
			}"
			)->getCss();

			printf('<style>%s</style>', $custom_css); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Outputs thank you page.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return string
	 */
	public function output_thank_you($atts, $content = null) {

		$slug = $atts['slug'];

		$checkout_form = wu_get_checkout_form_by_slug($slug);

		$atts = $checkout_form->get_meta('wu_thank_you_settings');

		$atts['checkout_form'] = $checkout_form;

		\WP_Ultimo\UI\Thank_You_Element::get_instance()->register_scripts();

		return \WP_Ultimo\UI\Thank_You_Element::get_instance()->output($atts, $content);
	}

	/**
	 * Outputs the registration form.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return string
	 */
	public function output_form($atts, $content = null) {

		$atts['step'] = wu_request('step', $atts['step']);

		$slug = $atts['slug'];

		$customer = wu_get_current_customer();

		$membership = WP_Ultimo()->currents->get_membership();

		/**
		 * Allow developers bypass the output and set a new one
		 *
		 * @param string|bool $bypass If we should bypass the checkout form or a string to return instead of the form.
		 * @param array       $atts   Parameters of the checkout block/shortcode.
		 */
		$bypass = apply_filters('wu_bypass_checkout_form', false, $atts);

		if ($bypass) {
			return is_string($bypass) ? $bypass : '';
		}

		if ($customer && $membership && 'wu-finish-checkout' !== $slug) {
			$published_sites = $membership->get_published_sites();

			$pending_payment = $membership ? $membership->get_last_pending_payment() : false;

			if ($pending_payment && ! $membership->is_active() && $membership->get_status() !== Membership_Status::TRIALING) {
				/**
				 *  We are talking about membership with a pending payment
				 */

				// Translators: Placeholder receives the customer display name
				$message = sprintf(__('Hi %s. You have a pending payment for your membership!', 'multisite-ultimate'), $customer->get_display_name());

				$payment_url = add_query_arg(
					[
						'payment' => $pending_payment->get_hash(),
					],
					wu_get_registration_url()
				);

				// Translators: The link to registration url with payment hash
				$message .= '<br>' . sprintf(__('Click <a href="%s">here</a> to pay.', 'multisite-ultimate'), $payment_url);

				$message = '<p>' . $message . '</p>';

				/**
				 * Allow developers to change the message if membership have a pending payment
				 *
				 * @param string                      $message    The HTML message to print in screen.
				 * @param \WP_Ultimo\Models\Membership $membership The membership in use.
				 * @param \WP_Ultimo\Models\Customer   $customer   The active customer in use.
				 */
				return apply_filters('wu_checkout_pending_payment_error_message', $message, $membership, $customer);
			}

			$membership_blocked_forms = [
				'wu-add-new-site',
			];

			if ( ! $membership->is_active() && $membership->get_status() !== Membership_Status::TRIALING && in_array($atts['slug'], $membership_blocked_forms, true)) {

				// Translators: Placeholder receives the customer display name
				$message = sprintf(__('Hi %s. You cannot take action on your membership while it is not active!', 'multisite-ultimate'), $customer->get_display_name());

				if ($membership->get_status() === Membership_Status::PENDING && $customer->get_email_verification() === 'pending') {
					/**
					 * Enqueue thank you page scripts to handle resend email verification link
					 */
					wp_register_script('wu-thank-you', wu_get_asset('thank-you.js', 'js'), [], wu_get_version(), true);

					wp_localize_script(
						'wu-thank-you',
						'wu_thank_you',
						[
							'ajaxurl'         => admin_url('admin-ajax.php'),
							'resend_verification_email_nonce' => wp_create_nonce('wu_resend_verification_email_nonce'),
							'membership_hash' => $membership->get_hash(),
							'i18n'            => [
								'resending_verification_email' => __('Resending verification email...', 'multisite-ultimate'),
								'email_sent' => __('Verification email sent!', 'multisite-ultimate'),
							],
						]
					);

					wp_enqueue_script('wu-thank-you');

					$message .= '<p>' . __('Check your inbox and verify your email address.', 'multisite-ultimate') . '</p>';
					$message .= '<span class="wu-styling">';
					$message .= sprintf('<a href="#" class="wu-mr-2 wu-resend-verification-email wu-no-underline button button-primary">%s</a>', __('Resend verification email', 'multisite-ultimate'));
					$message .= '</span>';
				}

				/**
				 * Allow developers to change the message if membership have a pending payment
				 *
				 * @param string                      $message    The HTML message to print in screen.
				 * @param \WP_Ultimo\Models\Membership $membership The membership in use.
				 * @param \WP_Ultimo\Models\Customer   $customer   The active customer in use.
				 */
				return apply_filters('wu_checkout_membership_status_error_message', $message, $membership, $customer);
			}

			if ( ! wu_multiple_memberships_enabled() && $membership) {

				/**
				 * Allow developers to add new form slugs to bypass this behaviour.
				 *
				 * @param array $slugs a list of form slugs to bypass.
				 */
				$allowed_forms = apply_filters(
					'wu_get_membership_allowed_forms',
					[
						'wu-checkout',
						'wu-add-new-site',
					]
				);

				if ( ! in_array($slug, $allowed_forms, true) && ! wu_request('payment')) {
					$message = sprintf('<p>%s</p>', __('You already have a membership!', 'multisite-ultimate'));

					if (isset($published_sites[0])) {
						$account_link = get_admin_url($published_sites[0]->get_id(), 'admin.php?page=account');
						$button_text  = __('Go to my account', 'multisite-ultimate');

						$message .= "<p><a class=\"wu-no-underline button button-primary\" href=\"$account_link\">$button_text</a><p>";
					}

					/**
					 * Allow developers to change the message about the limitation of a single membership for customer.
					 *
					 * @param string                      $message    The HTML message to print in screen.
					 * @param \WP_Ultimo\Models\Customer   $customer   The active customer in use.
					 */
					return apply_filters('wu_checkout_single_membership_message', $message, $customer);
				}
			}

			if ($membership && $membership->get_customer_id() !== $customer->get_id()) {
				$message = sprintf('<p>%s</p>', __('You are not allowed to change this membership!', 'multisite-ultimate'));

				/**
				 * Allow developers to change the message if customer is not part of the membership
				 *
				 * @param string                      $message    The HTML message to print in screen.
				 * @param \WP_Ultimo\Models\Membership $membership The membership in use.
				 * @param \WP_Ultimo\Models\Customer   $customer   The active customer in use.
				 */
				return apply_filters('wu_checkout_customer_error_message', $message, $membership, $customer);
			}

			/**
			 *  Now we filter the current membership for each membership_limitations
			 *  field in element atts to check if we can show the form, if not we show
			 *  a error message informing the user about and with buttons to allow
			 *  account upgrade and/or to buy a new membership.
			 */
			if ($membership && ! empty($atts['membership_limitations'])) {
				$limits = $membership->get_limitations();

				foreach ($atts['membership_limitations'] as $limitation) {
					if ( ! method_exists($membership, "get_$limitation")) {
						continue;
					}

					$current_limit = $limits->{$limitation};

					$limit_max = $current_limit->is_enabled() ? $current_limit->get_limit() : PHP_INT_MAX;

					$limit_max = ! empty($limit_max) ? (int) $limit_max : 1;

					$used_limit = $membership->{"get_$limitation"}();

					$used_limit = is_array($used_limit) ? count($used_limit) : (int) $used_limit;

					if ($used_limit >= $limit_max) {

						// Translators: Placeholder receives the limit name
						$message = '<p>' . sprintf(__('You reached your membership %s limit!', 'multisite-ultimate'), $limitation) . '</p>';

						$message .= '<span class="wu-styling">';

						if (wu_multiple_memberships_enabled()) {
							$register_page = wu_get_registration_url();
							$button_text   = __('Buy a new membership', 'multisite-ultimate');

							$message .= "<a class=\"wu-no-underline button button-primary wu-mr-2\" href=\"$register_page\">$button_text</a>";
						}

						if ('sites' !== $limitation || wu_get_setting('enable_multiple_sites')) {
							$update_link = '';

							$checkout_pages = \WP_Ultimo\Checkout\Checkout_Pages::get_instance();

							$update_url = $checkout_pages->get_page_url('update');

							if ($update_url) {
								$update_link = add_query_arg(
									[
										'membership' => $membership->get_hash(),
									],
									$update_url
								);
							} elseif (is_admin()) {
								$update_link = admin_url('admin.php?page=wu-checkout&membership=' . $membership->get_hash());
							} elseif (isset($published_sites[0])) {
								$update_link = get_admin_url($published_sites[0]->get_id(), 'admin.php?page=wu-checkout&membership=' . $membership->get_hash());
							}

							if ( ! empty($update_link)) {
								$button_text = __('Upgrade your account', 'multisite-ultimate');

								$message .= "<a class=\"wu-no-underline button button-primary wu-mr-2\" href=\"$update_link\">$button_text</a>";
							}
						}

						$message .= '</span>';

						/**
						 * Allow developers to change the message about the membership limit
						 *
						 * @param string                      $message    The HTML message to print in screen.
						 * @param string                      $limitation The limitation name.
						 * @param int                         $limit_max  The allowed limit.
						 * @param int                         $used_limit The limit used in membership.
						 * @param \WP_Ultimo\Models\Membership $membership The membership in use.
						 * @param \WP_Ultimo\Models\Customer   $customer   The active customer in use.
						 */
						return apply_filters('wu_checkout_membership_limit_message', $message, $limitation, $limit_max, $used_limit, $membership, $customer);
					}
				}
			}
		} elseif ( ! $customer && 'wu-finish-checkout' === $slug) {
			if (is_user_logged_in()) {
				$message = __('You need to be the account owner to complete this payment.', 'multisite-ultimate');
			} else {
				$message = __('You need to be logged in to complete a payment', 'multisite-ultimate');

				// Translators: The link to login url with redirect_to url
				$message .= '<br>' . sprintf(__('Click <a href="%s">here</a> sign in.', 'multisite-ultimate'), wp_login_url(wu_get_current_url()));
			}

			$message = '<p>' . $message . '</p>';

			/**
			 * Allow developers to change the message
			 *
			 * @param string $message The HTML message to print in screen.
			 */
			return apply_filters('wu_checkout_payment_login_error_message', $message);
		}

		$checkout_form = wu_get_checkout_form_by_slug($slug);

		if ( ! $checkout_form) {

			// translators: %s is the id of the form. e.g. main-form
			return sprintf(__('Checkout form %s not found.', 'multisite-ultimate'), $slug);
		}

		if ($checkout_form->get_field_count() === 0) {

			// translators: %s is the id of the form. e.g. main-form
			return sprintf(__('Checkout form %s contains no fields.', 'multisite-ultimate'), $slug);
		}

		if ( ! $checkout_form->is_active() || ! wu_get_setting('enable_registration', true)) {
			return sprintf('<p>%s</p>', __('Registration is not available at this time.', 'multisite-ultimate'));
		}

		if ($checkout_form->has_country_lock()) {
			$geolocation = \WP_Ultimo\Geolocation::geolocate_ip('', true);

			if ( ! in_array($geolocation['country'], $checkout_form->get_allowed_countries(), true)) {
				return sprintf('<p>%s</p>', __('Registration is closed for your location.', 'multisite-ultimate'));
			}
		}

		$checkout = \WP_Ultimo\Checkout\Checkout::get_instance();

		$checkout_form->get_steps_to_show();

		$this->steps = $checkout_form->get_steps_to_show();

		$step = $checkout_form->get_step($atts['step'], true);

		$this->step = $step ?: current($this->steps);

		$this->step = wp_parse_args(
			$this->step,
			[
				'classes' => '',
				'fields'  => [],
			]
		);

		$this->step_name = $this->step['id'] ?? '';

		/*
		 * Hack-y way to make signup available on the template.
		 */
		global $signup;

		$signup = new Mocked_Signup($this->step_name, $this->steps); // phpcs:ignore

		$this->signup = $signup;

		add_action(
			'wp_print_footer_scripts',
			function () use ($checkout_form) {

				$this->print_custom_css($checkout_form);
			}
		);

		/*
		 * Load the checkout class with the parameters
		 * so we can access them inside the layouts.
		 */
		$checkout->checkout_form = $checkout_form;
		$checkout->steps         = $this->steps;
		$checkout->step          = $this->step;
		$checkout->step_name     = $this->step_name;
		$auto_submittable_field  = $checkout->contains_auto_submittable_field($this->step['fields']);

		$final_fields = wu_create_checkout_fields($this->step['fields']);

		/*
		 * Adds the product fields to keep them.
		 */
		$final_fields['products[]'] = [
			'type'      => 'hidden',
			'html_attr' => [
				'v-for'     => '(product, index) in unique_products',
				'v-model'   => 'products[index]',
				'v-bind:id' => '"products-" + index',
			],
		];

		$this->inject_inline_auto_submittable_field($auto_submittable_field);

		$final_fields = apply_filters('wu_checkout_form_final_fields', $final_fields, $this);

		return wu_get_template_contents(
			'checkout/form',
			[
				'step'               => $this->step,
				'step_name'          => $this->step_name,
				'checkout_form_name' => $atts['slug'],
				'errors'             => $checkout->errors,
				'display_title'      => $atts['display_title'],
				'final_fields'       => $final_fields,
			]
		);
	}

	/**
	 * Injects the auto-submittable field inline snippet.
	 *
	 * @since 2.0.11
	 *
	 * @param string $auto_submittable_field The auto-submittable field.
	 * @return void
	 */
	public function inject_inline_auto_submittable_field($auto_submittable_field): void {

		$callback = function () use ($auto_submittable_field) {

			wp_add_inline_script(
				'wu-checkout',
				sprintf(
					'

				/**
				 * Set the auto-submittable field, if one exists.
				 */
				window.wu_auto_submittable_field = %s;

			',
					wp_json_encode($auto_submittable_field)
				),
				'after'
			);
		};

		if (wu_is_block_theme() && ! is_admin()) {
			add_action('wu_checkout_scripts', $callback, 100);
		} else {
			call_user_func($callback);
		}
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

		if (wu_is_update_page()) {
			$atts = [
				'slug'          => apply_filters('wu_membership_update_form', 'wu-checkout'),
				'step'          => false,
				'display_title' => false,
			];
		}

		if (wu_is_new_site_page()) {
			$atts = [
				'slug'                   => apply_filters('wu_membership_new_site_form', 'wu-add-new-site'),
				'step'                   => false,
				'display_title'          => false,
				'membership_limitations' => ['sites'],
			];
		}

		if ($this->is_thank_you_page()) {
			return $this->output_thank_you($atts, $content);
		}

		/**
		 * Allow developers to add new update form slugs.
		 *
		 * @param array $slugs a list of form slugs to bypass.
		 */
		$update_forms = apply_filters(
			'wu_membership_update_forms',
			[
				'wu-checkout',
			]
		);

		if ( ! in_array($atts['slug'], $update_forms, true) && (wu_request('payment') || wu_request('payment_id'))) {
			$atts = [
				'slug'          => 'wu-finish-checkout',
				'step'          => false,
				'display_title' => false,
			];
		}

		if (wu_request('wu_form') && in_array(wu_request('wu_form'), $update_forms, true)) {
			$atts = [
				'slug'          => wu_request('wu_form'),
				'step'          => false,
				'display_title' => false,
			];
		}

		return $this->output_form($atts, $content);
	}
}

/**
 * Replacement of the old WU_Signup class for templates.
 *
 * @since 2.0.0
 */
class Mocked_Signup {
	/**
	 * @var string
	 */
	public $step;

	/**
	 * @var array
	 */
	public $steps;

	/**
	 * Constructs the class.
	 *
	 * @since 2.0.0
	 *
	 * @param string $step Current step.
	 * @param array  $steps List of all steps.
	 */
	public function __construct($step, $steps) {
		$this->step  = $step;
		$this->steps = $steps;
	}

	/**
	 * Get the value of steps.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_steps() {

		return $this->steps;
	}

	/**
	 * Deprecated: returns the prev step link.
	 *
	 * @since 2.0.0
	 */
	public function get_prev_step_link(): string {

		return '';
	}
}
