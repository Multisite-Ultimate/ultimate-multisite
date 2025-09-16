<?php
/**
 * Handles the processing of new membership purchases.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */


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
			'signup-main.php' => __('Multisite Ultimate Legacy Signup', 'ultimate-multisite'),
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

		wp_register_script('wu-legacy-signup', wu_get_asset('legacy-signup.js', 'js'), ['wu-functions'], \WP_Ultimo::VERSION, true);

		wp_localize_script('wu-legacy-signup', 'wpu', [
			'default_pricing_option' => 1,
		]);

		wp_enqueue_script('wu-legacy-signup');

		// Register coupon code script
		wp_register_script('wu-coupon-code', wu_get_asset('coupon-code.js', 'js'), ['wu-vue', 'wu-functions', 'wu-block-ui', 'wu-accounting'], \WP_Ultimo::VERSION, true);

		// Check if coupon is present and enqueue script
		if (isset($_GET['coupon']) && wu_get_coupon(sanitize_text_field(wp_unslash($_GET['coupon']))) !== false && isset($_GET['step']) && 'plan' === $_GET['step']) { // phpcs:ignore WordPress.Security.NonceVerification
			$coupon = wu_get_coupon(sanitize_text_field(wp_unslash($_GET['coupon']))); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			wp_localize_script('wu-coupon-code', 'wu_coupon_data', [
				'coupon' => $coupon,
				'type' => get_post_meta($coupon->id, 'wpu_type', true),
				'value' => get_post_meta($coupon->id, 'wpu_value', true),
				'applies_to_setup_fee' => get_post_meta($coupon->id, 'wpu_applies_to_setup_fee', true),
				'setup_fee_discount_value' => get_post_meta($coupon->id, 'wpu_setup_fee_discount_value', true),
				'setup_fee_discount_type' => get_post_meta($coupon->id, 'wpu_setup_fee_discount_type', true),
				'allowed_plans' => get_post_meta($coupon->id, 'wpu_allowed_plans', true),
				'allowed_freqs' => get_post_meta($coupon->id, 'wpu_allowed_freqs', true),
				'off_text' => __('OFF', 'ultimate-multisite'),
				'free_text' => __('Free!', 'ultimate-multisite'),
				'no_setup_fee_text' => __('No Setup Fee', 'ultimate-multisite'),
			]);

			wp_enqueue_script('wu-coupon-code');
		}

		wp_enqueue_style('legacy-signup', wu_get_asset('legacy-signup.css', 'css'), ['dashicons', 'install', 'admin-bar'], \WP_Ultimo::VERSION);

		wp_enqueue_style('legacy-shortcodes', wu_get_asset('legacy-shortcodes.css', 'css'), ['dashicons', 'install'], \WP_Ultimo::VERSION);

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

		?>

			.wu-content-plan .plan-tier h4 {
				background-color: #<?php echo esc_html($primary_color->getHex()); ?>;
				color: <?php echo $primary_color->isDark() ? "white" : "#333"; ?> !important;
			}

			.wu-content-plan .plan-tier.callout h6 {
				background-color: #<?php echo esc_html($accent_color->getHex()); ?>;
				color: <?php echo $accent_color->isDark() ? "#f9f9f9" : "rgba(39,65,90,.5)"; ?> !important;
			}

			.wu-content-plan .plan-tier.callout h4 {
				background-color: #<?php echo esc_html($accent_color_2->getHex()); ?>;
				color: <?php echo $accent_color->isDark() ? "white" : "#333"; ?> !important;
			}

		<?php

		return ob_get_clean();
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
				wp_die(esc_html__('Sorry. Our service is not allowed in your country.', 'ultimate-multisite'));
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
			'name'    => __('Pick a Plan', 'ultimate-multisite'),
			'desc'    => __('Which one of our amazing plans you want to get?', 'ultimate-multisite'),
			'view'    => 'step-plans',
//			'handler' => [$this, 'plans_save'],
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
				'name'    => __('Template Selection', 'ultimate-multisite'),
				'desc'    => __('Select the base template of your new site.', 'ultimate-multisite'),
				'view'    => 'step-template',
				'order'   => 20,
				'handler' => false,
				'core'    => true,
			];
		}

		// Domain registering
		$steps['domain'] = [
			'name'    => __('Site Details', 'ultimate-multisite'),
			'desc'    => __('Ok, now it\'s time to pick your site url and title!', 'ultimate-multisite'),
//			'handler' => [$this, 'domain_save'],
			'view'    => false,
			'order'   => 30,
			'core'    => true,
			'fields'  => apply_filters(
				'wu_signup_fields_domain',
				[
					'blog_title'  => [
						'order'       => 10,
						'name'        => apply_filters('wu_signup_site_title_label', __('Site Title', 'ultimate-multisite')),
						'type'        => 'text',
						'default'     => '',
						'placeholder' => '',
						'tooltip'     => apply_filters('wu_signup_site_title_tooltip', __('Select the title your site is going to have.', 'ultimate-multisite')),
						'required'    => true,
						'core'        => true,
					],
					'blogname'    => [
						'order'       => 20,
						'name'        => apply_filters('wu_signup_site_url_label', __('URL', 'ultimate-multisite')),
						'type'        => 'text',
						'default'     => '',
						'placeholder' => '',
						'tooltip'     => apply_filters('wu_signup_site_url_tooltip', __('Site urls can only contain lowercase letters (a-z) and numbers and must be at least 4 characters. .', 'ultimate-multisite')),
						'required'    => true,
						'core'        => true,
					],
					'url_preview' => [
						'order'   => 30,
						'name'    => __('Site URL Preview', 'ultimate-multisite'),
						'type'    => 'html',
						'content' => wu_get_template_contents('legacy/signup/steps/step-domain-url-preview'),
					],
					'submit'      => [
						'order' => 100,
						'type'  => 'submit',
						'name'  => __('Continue to the next step', 'ultimate-multisite'),
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
				'name'        => apply_filters('wu_signup_username_label', __('Username', 'ultimate-multisite')),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => '',
				'tooltip'     => apply_filters('wu_signup_username_tooltip', __('Username must be at least 4 characters.', 'ultimate-multisite')),
				'required'    => true,
				'core'        => true,
			],

			'user_email'     => [
				'order'       => 20,
				'name'        => apply_filters('wu_signup_email_label', __('Email', 'ultimate-multisite')),
				'type'        => 'email',
				'default'     => '',
				'placeholder' => '',
				'tooltip'     => apply_filters('wu_signup_email_tooltip', ''),
				'required'    => true,
				'core'        => true,
			],

			'user_pass'      => [
				'order'       => 30,
				'name'        => apply_filters('wu_signup_password_label', __('Password', 'ultimate-multisite')),
				'type'        => 'password',
				'default'     => '',
				'placeholder' => '',
				'tooltip'     => apply_filters('wu_signup_password_tooltip', __('Your password should be at least 6 characters long.', 'ultimate-multisite')),
				'required'    => true,
				'core'        => true,
			],

			'user_pass_conf' => [
				'order'       => 40,
				'name'        => apply_filters('wu_signup_password_conf_label', __('Confirm Password', 'ultimate-multisite')),
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
				'name'               => __('Site URL', 'ultimate-multisite'),
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
		// 'name'         => __('Have a coupon code?', 'ultimate-multisite'),
		// 'core'          => true,
		// 'check_if'      => 'coupon', // Check if the input with this name is selected
		// 'checked'       => $coupon ? true : false,
		// );
		// $account_fields['coupon'] = array(
		// 'order'         => 60,
		// 'name'         => __('Coupon Code', 'ultimate-multisite'),
		// 'type'          => 'text',
		// 'default'       => '',
		// 'placeholder'   => '',
		// 'tooltip'       => __('The code should be an exact match. This field is case-sensitive.', 'ultimate-multisite'),
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
		// 'name'         => sprintf(__('I agree with the <a href="%s" target="_blank">Terms of Service</a>', 'ultimate-multisite'), $this->get_terms_url()),
		// 'core'          => true,
		// );
		// }

		/**
		 * Submit Field
		 */
		$account_fields['submit'] = [
			'order' => 100,
			'type'  => 'submit',
			'name'  => __('Create Account', 'ultimate-multisite'),
			'core'  => true,
		];

		// Account registering
		$steps['account'] = [
			'name'    => __('Account Details', 'ultimate-multisite'),
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
				'name'    => __('Begin Signup Process', 'ultimate-multisite'),
//				'handler' => [$this, 'begin_signup'],
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
				'name'    => __('Creating Account', 'ultimate-multisite'),
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

			// wp_die(__('Try again', 'ultimate-multisite'));
		}

		if (is_null($transient)) {
			return [];
		}

		return $transient;
	}

	/**
	 * Checks transient data to see if the plan step is necessary
	 */
	public function has_plan_step(): bool {

		$transient = static::get_transient();
		return ! (isset($transient['skip_plan']) && isset($transient['plan_id']) && isset($transient['plan_freq']));
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
					wp_die(esc_html__('Please, do not use the "site_url" as one of your custom fields\' ids. We use it as a honeytrap field to prevent spam registration. Consider alternatives such as "url" or "website".', 'ultimate-multisite'));
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
