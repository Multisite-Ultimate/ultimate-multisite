<?php
/**
 * Handles the processing of new membership purchases.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */

// phpcs:disable

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Checkout\Cart;
use WU_Gateway;
use WU_Site_Template;

/**
 * Handles the processing of new membership purchases.
 *
 * @since 2.0.0
 */
class Legacy_Checkout {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds checkout errors.
	 *
	 * @since 2.0.0
	 * @var \WP_Error|null
	 */
	public $errors;

	/**
	 * Holds checkout errors.
	 *
	 * @since 2.0.0
	 * @var \WP_Error|null
	 */
	public $results;

	/**
	 * Current step of the signup flow.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $step;

	/**
	 * List of steps for the signup flow.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $steps;

	/**
	 * Product being purchased, if that exists.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Ultimo\Models\Product
	 */
	public $product;

	/**
	 * Session object.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Session.
	 */
	protected $session;

	/**
	 * Page templates to add.
	 *
	 * We use this to inject the legacy-signup.php page template option
	 * onto the post/page edit page on the main site.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $templates;

	/**
	 * Initializes the Checkout singleton and adds hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		$this->session = wu_get_session('signup');

		$this->templates = [
			'signup-main.php' => __('Multisite Ultimate Legacy Signup', 'multisite-ultimate'),
		];

		// add_filter('request', array($this, 'maybe_render_legacy_signup'));

		add_action('wu_signup_enqueue_scripts', [$this, 'register_scripts']);

		add_filter('theme_page_templates', [$this, 'add_new_template']);

		// Add a filter to the save post to inject out template into the page cache
		add_filter('wp_insert_post_data', [$this, 'register_legacy_templates']);

		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path
		add_filter('template_include', [$this, 'view_legacy_template']);

	}

	/**
	 * Adds our page templates to the page template dropdown.
	 *
	 * @since 2.0.0
	 *
	 * @param array $posts_templates Existing page templates.
	 * @return array
	 */
	public function add_new_template($posts_templates) {

		if (is_main_site()) {

			$posts_templates = array_merge($posts_templates, $this->templates);

		}

		return $posts_templates;

	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doesn't really exist.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Post data.
	 * @return array
	 */
	public function register_legacy_templates($atts) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();

		if (empty($templates)) {

			$templates = [];

		}

		// New cache, therefore remove the old one
		wp_cache_delete($cache_key, 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge($templates, $this->templates);

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add($cache_key, $templates, 'themes', 1800);

		return $atts;

	}

	/**
	 * Checks if our custom template is assigned to the page and display it.
	 *
	 * @since 2.0.0
	 *
	 * @param string $template The template set to a given page.
	 * @return string
	 */
	public function view_legacy_template($template) {

		// Return the search template if we're searching (instead of the template for the first result)
		if (is_search()) {

			return $template;

		}

		// Get global post
		global $post, $signup;

		// Return template if post is empty
		if (!$post) {

			return $template;

		}

		$template_slug = get_post_meta($post->ID, '_wp_page_template', true);

		// Return default template if we don't have a custom one defined
		if (!isset($this->templates[$template_slug])) {

			return $template;

		}

		$file = wu_path("views/legacy/signup/$template_slug");

		// Just to be safe, we check if the file exist first
		if (file_exists($file)) {

			return $file;

		}

		// Return template
		return $template;

	}

	/**
	 * Loads the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		wp_enqueue_script('wu-block-ui');

		wp_register_script('wu-legacy-signup', wu_get_asset('legacy-signup.js', 'js'), ['wu-functions']);

		wp_localize_script('wu-legacy-signup', 'wpu', [
			'default_pricing_option' => 1,
		]);

		wp_enqueue_script('wu-legacy-signup');

		wp_enqueue_style('legacy-signup', wu_get_asset('legacy-signup.css', 'css'), ['dashicons', 'install', 'admin-bar']);

		wp_enqueue_style('legacy-shortcodes', wu_get_asset('legacy-shortcodes.css', 'css'), ['dashicons', 'install']);

		wp_add_inline_style('legacy-signup', $this->get_legacy_dynamic_styles());

		// Do not get the login if the first step
		if ('plan' != $this->step) {

			wp_enqueue_style('login');

		}

		wp_enqueue_style('common');

	}

	/**
	 * Adds the additional dynamic styles.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_legacy_dynamic_styles() {

		/**
		 * Get the Colors to be using.
		 */
		$primary_color  = wu_color(wu_get_setting('primary_color', '#00a1ff'));
		$accent_color   = wu_color(wu_get_setting('accent_color', '#78b336'));
		$accent_color_2 = wu_color($accent_color->darken(4));

		ob_start();

		// phpcs:disable

		?>

			.wu-content-plan .plan-tier h4 {
				background-color: #<?php echo $primary_color->getHex(); ?>;
				color: <?php echo $primary_color->isDark() ? "white" : "#333"; ?> !important;
			}

			.wu-content-plan .plan-tier.callout h6 {
				background-color: #<?php echo $accent_color->getHex(); ?>;
				color: <?php echo $accent_color->isDark() ? "#f9f9f9" : "rgba(39,65,90,.5)"; ?> !important;
			}

			.wu-content-plan .plan-tier.callout h4 {
				background-color: #<?php echo $accent_color_2->getHex(); ?>;
				color: <?php echo $accent_color->isDark() ? "white" : "#333"; ?> !important;
			}

		<?php

		// phpcs:enable

		return ob_get_clean();
	}

	/**
	 * Checks if we should pre-fill checkout fields based on the request.
	 *
	 * We do a couple of clever things here:
	 * 1. We check for a plan slug right after the checkout/slug of the main page.
	 *
	 * @since 2.0.0
	 *
	 * @param array $request WordPress request.
	 * @return array
	 */
	public function maybe_render_legacy_signup($request) {

		$checkout_page_slug = 'register';

		$page_name = $request['pagename'] ?? '';

		if (str_starts_with((string) $page_name, $checkout_page_slug)) {
			$page = explode('/', (string) $page_name);

			/**
			 * Product passed
			 *
			 * @todo needs to check for frequency and unit.
			 */
			if (isset($page[1])) {
				$product_slug = $page[1];

				$product = wu_get_product_by_slug($product_slug);

				$this->product = $product;
			}

			$request['pagename'] = $checkout_page_slug;

			return $this->legacy_signup();
		}

		return $request;
	}

	/**
	 * Renders the legacy checkout.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function legacy_signup(): void {

		status_header(200);

		$this->session = wu_get_session('signup');

		$this->session->set('form', ['not-empty']);

		// Apply a filter so we can add steps in the future
		$this->steps = $this->get_steps();

		// Set the current step based on the get
		$this->step = $this->get_current_step();

		$this->handle_post();

		wu_get_template(
			'legacy/signup/signup-main',
			[
				'signup' => $this,
			]
		);

		exit;
	}

	/**
	 * Check Geolocation
	 *
	 * @return void
	 */
	public function check_geolocation(): void {

		$location = \WP_Ultimo\Geolocation::geolocate_ip();

		$this->session->set('geolocation', $location);

		$allowed_countries = wu_get_setting('allowed_countries');

		if (isset($location['country']) && $location['country'] && $allowed_countries) {
			if ( ! in_array($location['country'], $allowed_countries, true)) {
				wp_die(apply_filters('wu_geolocation_error_message', esc_html__('Sorry. Our service is not allowed in your country.', 'multisite-ultimate'))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Gets the info for the current step.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_current_step_info() {

		return $this->steps[ $this->step ];
	}

	/**
	 * Handles a post submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function handle_post() {

		$is_save = wu_request('save_step');

		$current_step = $this->get_current_step_info();

		/**
		 * If we are in the middle of a saving request, we need to call the handler
		 */
		if ($is_save || $current_step['hidden']) {

			/** Checks if the view has a handler of its own */
			if (isset($current_step['handler']) && $current_step['handler']) {
				$handler_function = $current_step['handler'];
			} else {
				$handler_function = [$this, 'default_save'];
			}

			/** Allows for handler rewrite */
			$handler_function = apply_filters("wu_signup_step_handler_$this->step", $handler_function);

			call_user_func($handler_function);
		}
	}

	/**
	 * The first invisible step handles the creation of the transient saver
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function begin_signup(): void {

		/**
		 * Check Geo-location
		 */
		$this->check_geolocation();

		/** Create the unique ID we well use from now on */
		$uniqid = uniqid('', true);

		/** Initializes the content holder with the honeypot unique id */
		$content = [
			'honeypot_id' => uniqid(''),
		];

		/**
		 * Saves the coupon code in the request only if that option is available
		 */
		if (! empty($_REQUEST['coupon']) && wu_get_setting('enable_coupon_codes', 'url_and_field') !== 'disabled') { // phpcs:ignore WordPress.Security.NonceVerification

			// Adds to the payload
			$content['coupon'] = sanitize_text_field(wp_unslash($_REQUEST['coupon'])); // phpcs:ignore WordPress.Security.NonceVerification
		}

		/**
		 * Check if we only have one plan and the skip_plan enabled
		 */

		$plans = wu_get_plans();

		if (wu_get_setting('skip_plan', false) && count($plans) === 1) {
			$billing_frequency = wu_get_setting('default_pricing_option', 1);

			$plan = reset($plans);

			// Append that to the content
			$content['plan_id']   = $plan->id;
			$content['plan_freq'] = $billing_frequency;
			$content['skip_plan'] = true;

			$_REQUEST['skip_plan'] = 1;
		}

		/**
		 * Check if we are settings the template via the URL
		*
		 * @since 1.7.3
		 */
		if (isset($_REQUEST['template_id']) && wu_get_setting('allow_template')) { // phpcs:ignore WordPress.Security.NonceVerification

			// Check if the template is valid
			$site = new WU_Site_Template($_REQUEST['template_id']); // phpcs:ignore

			if ($site->is_template) {
				$content['template']                = $_REQUEST['template_id']; // phpcs:ignore
				$content['skip_template_selection'] = true;
			}
		}

		$this->session->set('form', $content);

		/** Go to the next step */
		$this->next_step();
	}
	/**
	 * Check if the current page is a customizer page.
	 */
	public static function is_customizer(): bool {

		$exclude_list = apply_filters('wu_replace_signup_urls_exclude', ['wu-signup-customizer-preview']);

		foreach ($exclude_list as $replace_word) {
			if (isset($_GET[ $replace_word ])) { // phpcs:ignore WordPress.Security.NonceVerification
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the first step of the signup process
	 *
	 * @return string
	 */
	public function get_first_step() {

		$keys = array_keys($this->get_steps());

		if (isset($keys[1])) {
			return $keys[1];
		} else {
			return false;
		}
	}

	/**
	 * Get the current step
	 *
	 * @return string
	 */
	public function get_current_step() {

		$current_step = wu_request('step', current(array_keys($this->steps)));

		// Always get the first step for the customizer //
		if (static::is_customizer()) {
			$current_step = $this->get_first_step();
		}

		return apply_filters('wu_current_step', $current_step);
	}

	/**
	 * Includes the template for that particular step; If none is set (false), includes the default template
	 *
	 * @param string $step The current step.
	 * @return void
	 */
	public function get_step_view($step): void {

		$transient = $this->session->get('form');
		$geo       = $this->session->get('geolocation');

		/**
		 * Set the errors
		 */
		if (null === $this->results) {
			$this->results = ['errors' => new \WP_Error()];
		}

		if (empty($_POST)) {  // phpcs:ignore WordPress.Security.NonceVerification
			$this->results = array_merge($this->results, $transient);
		}

		/**
		 * Builds the array containing the available elements inside the template
		 */
		$args = [
			'signup'    => $this,
			'transient' => $transient,
			'fields'    => $this->steps[ $step ]['fields'] ?? [],
			'results'   => $this->results,
		];

		/**
		 * Checks if anything is passed to the view element
		 */
		if (isset($this->steps[ $step ]['view']) && $this->steps[ $step ]['view']) {
			wu_get_template('legacy/signup/steps/' . $this->steps[ $step ]['view'], $args);
		} else {
			$found = locate_template("wp-ultimo/signup/steps/step-$step.php");

			/**
			 * Let's try to locate a custom template on the user's theme. If it's there, we use it instead
			 */
			if ($found) {
				wu_get_template("legacy/signup/steps/step-$step", $args);
			} else {
				wu_get_template('legacy/signup/steps/step-default', $args);
			}
		}
	}

	/**
	 * Set and return the steps and fields of each step.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $include_hidden If we should return hidden steps as well.
	 * @param boolean $filtered If we should apply filters.
	 * @return array
	 */
	public function get_steps($include_hidden = true, $filtered = true) {

		// Set the Steps
		$steps = [];

		// Plan Selector
		$steps['plan'] = [
			'name'    => __('Pick a Plan', 'multisite-ultimate'),
			'desc'    => __('Which one of our amazing plans you want to get?', 'multisite-ultimate'),
			'view'    => 'step-plans',
			'handler' => [$this, 'plans_save'],
			'order'   => 10,
			'fields'  => false,
			'core'    => true,
		];

		$site_templates = [
			2,
		];

		// We add template selection if this has template
		if ($site_templates) {
			$steps['template'] = [
				'name'    => __('Template Selection', 'multisite-ultimate'),
				'desc'    => __('Select the base template of your new site.', 'multisite-ultimate'),
				'view'    => 'step-template',
				'order'   => 20,
				'handler' => false,
				'core'    => true,
			];
		}

		// Domain registering
		$steps['domain'] = [
			'name'    => __('Site Details', 'multisite-ultimate'),
			'desc'    => __('Ok, now it\'s time to pick your site url and title!', 'multisite-ultimate'),
			'handler' => [$this, 'domain_save'],
			'view'    => false,
			'order'   => 30,
			'core'    => true,
			'fields'  => apply_filters(
				'wu_signup_fields_domain',
				[
					'blog_title'  => [
						'order'       => 10,
						'name'        => apply_filters('wu_signup_site_title_label', __('Site Title', 'multisite-ultimate')),
						'type'        => 'text',
						'default'     => '',
						'placeholder' => '',
						'tooltip'     => apply_filters('wu_signup_site_title_tooltip', __('Select the title your site is going to have.', 'multisite-ultimate')),
						'required'    => true,
						'core'        => true,
					],
					'blogname'    => [
						'order'       => 20,
						'name'        => apply_filters('wu_signup_site_url_label', __('URL', 'multisite-ultimate')),
						'type'        => 'text',
						'default'     => '',
						'placeholder' => '',
						'tooltip'     => apply_filters('wu_signup_site_url_tooltip', __('Site urls can only contain lowercase letters (a-z) and numbers and must be at least 4 characters. .', 'multisite-ultimate')),
						'required'    => true,
						'core'        => true,
					],
					'url_preview' => [
						'order'   => 30,
						'name'    => __('Site URL Preview', 'multisite-ultimate'),
						'type'    => 'html',
						'content' => wu_get_template_contents('legacy/signup/steps/step-domain-url-preview'),
					],
					'submit'      => [
						'order' => 100,
						'type'  => 'submit',
						'name'  => __('Continue to the next step', 'multisite-ultimate'),
						'core'  => true,
					],
				]
			),
		];

		/**
		 * Since there are some conditional fields on the accounts step, we need to declare the variable before
		 * so we can append items and filter it later
		 */
		$account_fields = [

			'user_name'      => [
				'order'       => 10,
				'name'        => apply_filters('wu_signup_username_label', __('Username', 'multisite-ultimate')),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => '',
				'tooltip'     => apply_filters('wu_signup_username_tooltip', __('Username must be at least 4 characters.', 'multisite-ultimate')),
				'required'    => true,
				'core'        => true,
			],

			'user_email'     => [
				'order'       => 20,
				'name'        => apply_filters('wu_signup_email_label', __('Email', 'multisite-ultimate')),
				'type'        => 'email',
				'default'     => '',
				'placeholder' => '',
				'tooltip'     => apply_filters('wu_signup_email_tooltip', ''),
				'required'    => true,
				'core'        => true,
			],

			'user_pass'      => [
				'order'       => 30,
				'name'        => apply_filters('wu_signup_password_label', __('Password', 'multisite-ultimate')),
				'type'        => 'password',
				'default'     => '',
				'placeholder' => '',
				'tooltip'     => apply_filters('wu_signup_password_tooltip', __('Your password should be at least 6 characters long.', 'multisite-ultimate')),
				'required'    => true,
				'core'        => true,
			],

			'user_pass_conf' => [
				'order'       => 40,
				'name'        => apply_filters('wu_signup_password_conf_label', __('Confirm Password', 'multisite-ultimate')),
				'type'        => 'password',
				'default'     => '',
				'placeholder' => '',
				'tooltip'     => apply_filters('wu_signup_password_conf_tooltip', ''),
				'required'    => true,
				'core'        => true,
			],

			/**
			 * HoneyPot Field
			 */
			'site_url'       => [
				'order'              => random_int(1, 59), // Use random order for Honeypot
				'name'               => __('Site URL', 'multisite-ultimate'),
				'type'               => 'text',
				'default'            => '',
				'placeholder'        => '',
				'tooltip'            => '',
				'core'               => true,
				'wrapper_attributes' => [
					'style' => 'display: none;',
				],
				'attributes'         => [
					'autocomplete' => 'nope',
				],
			],

		];

		/**
		 * Check and Add Coupon Code Fields
		*
		 * @since 1.4.0
		 */
		// if (wu_get_setting('enable_coupon_codes', 'url_and_field') == 'url_and_field') {
		// **
		// * Test default state, if we have a coupon saved
		// */
		// $coupon = $this->has_coupon_code();
		// $account_fields['has_coupon'] = array(
		// 'order'         => 50,
		// 'type'          => 'checkbox',
		// 'name'         => __('Have a coupon code?', 'multisite-ultimate'),
		// 'core'          => true,
		// 'check_if'      => 'coupon', // Check if the input with this name is selected
		// 'checked'       => $coupon ? true : false,
		// );
		// $account_fields['coupon'] = array(
		// 'order'         => 60,
		// 'name'         => __('Coupon Code', 'multisite-ultimate'),
		// 'type'          => 'text',
		// 'default'       => '',
		// 'placeholder'   => '',
		// 'tooltip'       => __('The code should be an exact match. This field is case-sensitive.', 'multisite-ultimate'),
		// 'requires'      => array('has_coupon' => true),
		// 'core'          => true,
		// );
		// }
		// /**
		// * Check and Add the Terms field
		// * @since 1.0.4
		// */
		// if (wu_get_setting('enable_terms')) {
		// $account_fields['agree_terms'] = array(
		// 'order'         => 70,
		// 'type'          => 'checkbox',
		// 'checked'       => false,
		// 'name'         => sprintf(__('I agree with the <a href="%s" target="_blank">Terms of Service</a>', 'multisite-ultimate'), $this->get_terms_url()),
		// 'core'          => true,
		// );
		// }

		/**
		 * Submit Field
		 */
		$account_fields['submit'] = [
			'order' => 100,
			'type'  => 'submit',
			'name'  => __('Create Account', 'multisite-ultimate'),
			'core'  => true,
		];

		// Account registering
		$steps['account'] = [
			'name'    => __('Account Details', 'multisite-ultimate'),
			'view'    => false,
			'handler' => [$this, 'account_save'],
			'order'   => 40,
			'core'    => true,
			'fields'  => apply_filters('wu_signup_fields_account', $account_fields),
		];

		/**
		 * Add additional steps via filters
		 */
		$steps = $filtered ? apply_filters('wp_ultimo_registration_steps', $steps) : $steps;

		// Sort elements based on their order
		uasort($steps, [$this, 'sort_steps_and_fields']);

		// Sorts each of the fields block
		foreach ($steps as &$step) {
			$step = wp_parse_args(
				$step,
				[
					'hidden' => false,
				]
			);

			if (isset($step['fields']) && is_array($step['fields'])) {

				// Sort elements based on their order
				uasort($step['fields'], [$this, 'sort_steps_and_fields']);
			}
		}

		/**
		 * Adds the hidden step now responsible for validating data entry and the actual account creation
		*
		 * @since  1.4.0
		 */
		$begin_signup = [
			'begin-signup' => [
				'name'    => __('Begin Signup Process', 'multisite-ultimate'),
				'handler' => [$this, 'begin_signup'],
				'view'    => false,
				'hidden'  => true,
				'order'   => 0,
				'core'    => true,
			],
		];

		/**
		 * Adds the hidden step now responsible for validating data entry and the actual account creation
		*
		 * @since  1.4.0
		 */
		$create_account = [
			'create-account' => [
				'name'    => __('Creating Account', 'multisite-ultimate'),
				'handler' => [$this, 'create_account'],
				'view'    => false,
				'hidden'  => true,
				'core'    => true,
				'order'   => 1_000_000_000,
			],
		];

		/**
		 * Glue the required steps together with the filterable ones
		 */
		$steps = array_merge($begin_signup, $steps, $create_account);

		/**
		 * Filter the hidden ones, if we need to...
		*
		 * @var array
		 */
		if ( ! $include_hidden) {
			$steps = array_filter($steps, fn($step) => ! (isset($step['hidden']) && $step['hidden']));
		}

		// If we need to add that
		if ( ! $this->has_plan_step()) {
			unset($steps['plan']);
		}

		return $steps;
	}

	/**
	 * Check the transient, and if it does not exists, throw fatal
	 *
	 * @param bool $die If we should die when there's no transient set.
	 * @return array The transient information
	 */
	public static function get_transient($die = true) {

		if (self::is_customizer()) {
			$transient = [
				'not-empty' => '',
			];
		} else {
			$transient = wu_get_session('signup')->get('form');
		}

		if ($die && empty($transient)) {

			// wp_die(__('Try again', 'multisite-ultimate'));
		}

		if (is_null($transient)) {
			return [];
		}

		return $transient;
	}

	/**
	 * Update the transient data in out database
	 *
	 * @param array $transient Array containing the transient data.
	 */
	public function update_transient($transient): void {

		$this->session->set('form', $transient);

		$this->session->commit();
	}
	/**
	 * Checks transient data to see if the plan step is necessary
	 */
	public function has_plan_step(): bool {

		$transient = static::get_transient();
		return ! (isset($transient['skip_plan']) && isset($transient['plan_id']) && isset($transient['plan_freq']));
	}

	/**
	 * Get the link for the next step
	 *
	 * @param array $params The params.
	 * @return string The link for the next step
	 */
	public function get_next_step_link($params = []) {

		// Add CS
		if (isset($_GET['cs'])) { // phpcs:ignore WordPress.Security.NonceVerification
			$params['cs'] = sanitize_text_field(wp_unslash($_GET['cs'])); // phpcs:ignore WordPress.Security.NonceVerification
		}

		if (isset($_REQUEST['customized'])) { // phpcs:ignore WordPress.Security.NonceVerification
			$params['customized'] = sanitize_text_field(wp_unslash($_REQUEST['customized'])); // phpcs:ignore WordPress.Security.NonceVerification
		}

		if (isset($_REQUEST['skip_plan']) && 1 === (int) $_REQUEST['skip_plan']) { // phpcs:ignore WordPress.Security.NonceVerification
			unset($this->steps['plan']);
			unset($params['skip_plan']);
		}

		if (isset($_REQUEST['template_id'])) { // phpcs:ignore WordPress.Security.NonceVerification
			$plan = false;

			if (isset($_REQUEST['plan_id'])) { // phpcs:ignore WordPress.Security.NonceVerification
				$plan = wu_get_plan((int) $_REQUEST['plan_id']); // phpcs:ignore WordPress.Security.NonceVerification
			}

			$templates = array_keys((array) wu_get_setting('templates'));

			if ( ($plan && $plan->is_template_available($_REQUEST['template_id'])) || in_array($_REQUEST['template_id'], $templates)) { // phpcs:ignore WordPress.Security.NonceVerification
				unset($this->steps['template']);
				unset($params['skip_template_selection']);
			}
		}

		$keys = array_keys($this->steps);
		$url  = add_query_arg('step', $keys[ array_search($this->step, array_keys($this->steps)) + 1 ]);

		foreach ($params as $param => $value) {
			$url = add_query_arg($param, $value, $url);
		}

		return $url;
	}

	/**
	 * Redirects the user to the next step on the signup flow
	 *
	 * @param array $args Arguments to build the URL.
	 * @return void
	 */
	public function next_step($args = []): void {

		/** Redirect the user to the next step */
		wp_safe_redirect(esc_url_raw($this->get_next_step_link($args)));

		/** Kill the execution after the redirect */
		exit;
	}

	/**
	 * Get the link for the previous step
	 *
	 * @param array $params The params.
	 * @return string The link for the previous step
	 */
	public function get_prev_step_link($params = []) {

		// Add CS
		if (isset($_GET['cs'])) { // phpcs:ignore WordPress.Security.NonceVerification
			$params['cs'] = sanitize_text_field(wp_unslash($_GET['cs'])); // phpcs:ignore WordPress.Security.NonceVerification
		}

		if (isset($_REQUEST['customized'])) { // phpcs:ignore WordPress.Security.NonceVerification
			$params['customized'] = sanitize_text_field(wp_unslash($_REQUEST['customized'])); // phpcs:ignore WordPress.Security.NonceVerification
		}

		$keys       = array_keys($this->steps);
		$search_key = array_search($this->step, array_keys($this->steps)) - 1 >= 0 ? array_search($this->step, array_keys($this->steps)) - 1 : false;
		$key        = false === $search_key ? '' : $keys[ $search_key ];

		if ( ! $key || 'begin-signup' == $key) {
			return false;
		}

		$url = add_query_arg('step', $key);

		foreach ($params as $param => $value) {
			$url = add_query_arg($param, $value, $url);
		}

		return $url;
	}

	/**
	 * Sorts the steps.
	 *
	 * @param array $a Value 1.
	 * @param array $b Value to compare against.
	 * @return boolean
	 */
	public function sort_steps_and_fields($a, $b) {

		$a['order'] = isset($a['order']) ? (int) $a['order'] : 50;

		$b['order'] = isset($b['order']) ? (int) $b['order'] : 50;

		return $a['order'] - $b['order'];
	}

	/**
	 * Display the necessary fields for the plan template
	 *
	 * @since 1.5.0 Takes the frequency parameter
	 *
	 * @param boolean $current_plan The current plan.
	 * @param string  $step The step.
	 * @param integer $freq The freq.
	 * @return void
	 */
	public function form_fields($current_plan = false, $step = 'plan', $freq = false): void {

		/** Select the default frequency */
		$freq = $freq ?: wu_get_setting('default_pricing_option');

		?>

		<?php if ('plan' == $step) { ?>

		<input type="hidden" name="wu_action" value="wu_new_user">
		<input type="hidden" id="wu_plan_freq" name="plan_freq" value="<?php echo esc_attr($freq); ?>">

			<?php
		}
		?>

	<input type="hidden" name="save_step" value="1">

		<?php wp_nonce_field('signup_form_1', '_signup_form'); ?>

		<!-- if this is a change plan, let us know -->
		<?php if ($current_plan) : ?>

		<input type="hidden" name="changing-plan" value="1">

		<?php endif; ?>

		<?php
	}

	/**
	 * Get the primary site URL we will use on the URL previewer, during sign-up
	 *
	 * @since 1.7.2
	 * @return string
	 */
	public function get_site_url_for_previewer() {

		$domain_options = [];

		$site = get_current_site();

		$domain = $site->domain;

		if (wu_get_setting('enable_multiple_domains', false) && $domain_options) {
			$domain = array_shift($domain_options);
		}

		$domain = rtrim($domain . $site->path, '/');

		/**
		 * Allow plugin developers to filter the URL used in the previewer
		 *
		 * @since 1.7.2
		 * @param string  Default domain being used right now, useful for manipulations
		 * @param array   List of all the domain options entered in the Multisite Ultimate Settings -> Network Settings -> Domain Options
		 * @return string New domain to be used
		 */
		return apply_filters('get_site_url_for_previewer', $domain, $domain_options); // phpcs:ignore
	}

	/**
	 * We pass the following info
	 */
	public function plans_save(): void {

		// Get transient
		$transient = static::get_transient();

		// Check referer
		check_admin_referer('signup_form_1', '_signup_form');

		// Errors
		$this->results['errors'] = new \WP_Error();

		// We need now to check for plan
		if ( ! isset($_POST['plan_id'])) {
			$this->results['errors']->add('plan_id', __('You don\'t have any plan selected.', 'multisite-ultimate'));
		} else {
			// We need now to check if the plan exists
			$plan = wu_get_product((int) $_POST['plan_id']); // phpcs:ignore WordPress.Security.NonceVerification

			if ( ! $plan->exists()) {
				$this->results['errors']->add('plan_id', __('The plan you\'ve selected doesn\'t exist.', 'multisite-ultimate'));
			}
		}

		$transient = apply_filters('wp_ultimo_registration_step_plans_save_transient', $transient);

		// Action hook for users
		do_action('wp_ultimo_registration_step_plans_save', $transient);

		// Stay on the form if we get any errors
		if ($this->results['errors']->get_error_code()) {
			return;
		}

		/** Update Transient Content */
		$transient['plan_freq'] = sanitize_text_field(wp_unslash($_POST['plan_freq'])); // phpcs:ignore WordPress.Security.NonceVerification
		$transient['plan_id']   = (int) $_POST['plan_id']; // phpcs:ignore WordPress.Security.NonceVerification

		/** Update Data */
		$this->update_transient($transient);

		/** Go to the next step */
		$this->next_step();
	}

	/**
	 * Personal Info Settings.
	 */
	public function domain_save(): void {

		// Get transient
		$transient = static::get_transient();

		// Check referer
		check_admin_referer('signup_form_1', '_signup_form');

		/**
		 * Make sure we trim() the contents of the form.
		 *
		 * @since 1.9.0
		 */
		$_POST = array_map('trim', $_POST);

		// Get validation errors
		$this->results = validate_blog_form();

		/** Sanitizes Input */
		$transient = array_merge($transient, $this->filter_post_array($_POST));

		// Action hook for users
		do_action('wp_ultimo_registration_step_domain_save', $transient);

		// Stay on the form if we get any errors
		if ($this->results['errors']->get_error_code()) {
			$this->results = array_merge($this->results, $_POST);

			return;
		}

		// Re-saves the transient
		$this->update_transient($transient);

		/** Go to the next step */
		$this->next_step();
	}

	/**
	 * Filters the input variables and sanitizes its contents
	 *
	 * @param array $post The post.
	 * @param array $exclude_list The exclude list.
	 * @return array
	 */
	public function filter_post_array($post, $exclude_list = false) {

		$exclude_list = $exclude_list ?: ['_signup_form', '_wp_http_referer'];

		/** Filter Array */
		$post = $this->array_filter_key($post, fn($element_key) => ! in_array($element_key, $exclude_list, true));

		/** Sanitizes the input */
		$post = array_map(fn($element) => sanitize_text_field($element), $post);

		return $post;
	}
	/**
	 * Helper function to filter based on key.
	 *
	 * @since 2.0.0
	 *
	 * @param array    $array The array.
	 * @param callable $callback The callback.
	 */
	public function array_filter_key(array $array, $callback): array {

		$matched_keys = array_filter(array_keys($array), $callback ?? fn($v, $k): bool => ! empty($v), null === $callback ? ARRAY_FILTER_USE_BOTH : 0);

		return array_intersect_key($array, array_flip($matched_keys));
	}
	/**
	 * Get the active until + trial days, to allow for putting subscription on hold
	 *
	 * @since 1.5.5
	 * @param string  $now Time now.
	 * @param integer $trial_days Trial days.
	 */
	public static function get_active_until_with_trial($now, $trial_days): string {

		$active_until = new \DateTime($now);

		$active_until->add(new \DateInterval('P' . $trial_days . 'D'));

		return $active_until->format('Y-m-d H:i:s');
	}

	/**
	 * Adds a new Step to the sign-up flow
	 *
	 * @since 1.4.0
	 * @param string  $id The field id.
	 * @param integer $order The field order.
	 * @param array   $step The step info.
	 * @return void
	 */
	public function add_signup_step($id, $order, $step): void {

		add_filter(
			'wp_ultimo_registration_steps',
			function ($steps) use ($id, $order, $step) {

				// Save new order
				$step['order'] = $order;

				// mark as not core
				$step['core'] = false;

				$steps[ $id ] = $step;

				return $steps;
			}
		);
	}

	/**
	 * Adds a new field to a step the sign-up flow
	 *
	 * @since 1.4.0
	 * @param string  $step The step name.
	 * @param string  $id The field id.
	 * @param integer $order The field order.
	 * @param array   $field The field.
	 * @return void
	 */
	public function add_signup_field($step, $id, $order, $field): void {

		add_filter(
			'wp_ultimo_registration_steps',
			function ($steps) use ($step, $id, $order, $field) {

				// Checks for honey-trap id
				if ('site_url' === $id) {
					wp_die(esc_html__('Please, do not use the "site_url" as one of your custom fields\' ids. We use it as a honeytrap field to prevent spam registration. Consider alternatives such as "url" or "website".', 'multisite-ultimate'));
				}

				// Saves the order
				$field['order'] = $order;

				// mark as not core
				$field['core'] = false;

				$steps[ $step ]['fields'][ $id ] = $field;

				return $steps;
			}
		);
	}
}
