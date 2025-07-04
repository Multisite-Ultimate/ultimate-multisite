<?php
/**
 * Legacy Shortcodes
 *
 * Handles Multisite Ultimate 1.X Legacy Shortcodes
 *
 * @package WP_Ultimo
 * @subpackage Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Memberships\Membership_Status;

/**
 * Handles Multisite Ultimate 1.X Legacy Shortcodes
 *
 * @since 2.0.0
 */
class Legacy_Shortcodes {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Control array for the template list shortcode
	 *
	 * @since 1.7.4
	 * @var array|boolean
	 */
	public $templates = false;

	/**
	 * Defines all the legacy shortcodes.
	 *
	 * @since 1.0.0 Adds Pricing Table and Paying customers.
	 * @since 1.2.1 Adds Plan Link Shortcode.
	 * @since 1.2.2 Adds Template Display.
	 * @since 1.4.0 Adds User meta getter.
	 * @since 1.5.0 Adds Restricted content.
	 *
	 * @return void
	 */
	public function init(): void {

		add_shortcode('wu_paying_users', [$this, 'paying_users']);

		add_shortcode('wu_user_meta', [$this, 'user_meta']);

		add_shortcode('wu_plan_link', [$this, 'plan_link']);

		add_shortcode('wu_restricted_content', [$this, 'restricted_content']);

		add_shortcode('wu_pricing_table', [$this, 'pricing_table']);

		add_shortcode('wu_templates_list', [$this, 'templates_list']);
	}

	/**
	 * Return the value of a user meta on the database.
	 * This is useful to fetch data saved from custom sign-up fields during sign-up.
	 *
	 * @since 1.4.0
	 * @since 2.0.0 Search customer meta first before trying to fetch the info from the user table.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function user_meta($atts) {

		$customer_id = 0;

		$user_id = get_current_user_id();

		$site = wu_get_current_site();

		$customer = $site->get_customer();

		if ($customer) {
			$customer_id = $customer->get_id();

			$user_id = $customer->get_user_id();
		}

		$atts = shortcode_atts(
			[
				'user_id'   => $user_id,
				'meta_name' => 'first_name',
				'default'   => false,
				'unique'    => true,
			],
			$atts,
			'wu_user_meta'
		);

		if ($customer_id) {
			$value = $customer->get_meta($atts['meta_name']);
		} else {
			$value = get_user_meta($atts['user_id'], $atts['meta_name'], $atts['unique']);
		}

		if (is_array($value)) {
			$value = implode(', ', $value);
		}

		return $value ?: '--';
	}

	/**
	 * Returns the number of paying users on the platform.
	 *
	 * @since 1.X
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function paying_users($atts) {

		global $wpdb;

		$atts = shortcode_atts([], $atts, 'wu_paying_users');

		$paying_customers = wu_get_customers(
			[
				'count' => true,
			]
		);

		return $paying_customers;
	}

	/**
	 * Plan Link shortcode.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function plan_link($atts) {

		$atts = shortcode_atts(
			[
				'plan_id'   => 0,
				'plan_freq' => 1,
				'skip_plan' => 1,
			],
			$atts,
			'wu_plan_link'
		);

		/**
		 * Treat the results to make sure we are getting numbers out of it
		 *
		 * @since 1.5.1
		 */
		foreach (['plan_id', 'plan_freq'] as $att) {
			$atts[ $att ] = wu_extract_number($atts[ $att ]);
		}

		$path = '';

		/*
		 * First pass: try to get via the migrated_from_id property
		 * to make sure we don't break links for legacy customers.
		 */
		$plan = wu_get_product_by('migrated_from_id', $atts['plan_id']);

		if ($plan) {
			$path = $plan->get_slug();
		}

		/**
		 * Second pass: try via the real ID, if new customers
		 * decide to use the old shortcode.
		 */
		if (empty($path)) {
			$plan = wu_get_product($atts['plan_id']);

			if ($plan) {
				$path = $plan->get_slug();
			}
		}

		return wu_get_registration_url($path);
	}

	/**
	 * Renders the restrict content shortcode.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content The content inside the shortcode.
	 * @return string
	 */
	public function restricted_content($atts, $content) {

		$atts = shortcode_atts(
			[
				'plan_id'        => false,
				'product_id'     => false,
				'only_active'    => true,
				'only_logged'    => false,
				'exclude_trials' => false,
				'from_request'   => false,
			],
			$atts,
			'wu_restricted_content'
		);

		$atts['plan_id'] = ! empty($atts['product_id']) ? $atts['product_id'] : $atts['plan_id'];

		if (empty($atts) || ! $atts['plan_id']) {
			return __('You need to pass a valid plan ID.', 'multisite-ultimate');
		}

		$query_products = get_query_var('products', []);

		$request_products = [];

		foreach ($query_products as $product) {
			$product = wu_get_product($product);

			if ( ! $product) {
				continue;
			}

			$request_products[] = $product->get_id();
		}

		$plan_ids = explode(',', $atts['plan_id']);
		$plan_ids = array_map('trim', $plan_ids);
		$plan_ids = array_map('intval', $plan_ids);

		$else = '[wu_default_content]';

		if (str_contains($content, $else)) {
			[$if, $else] = explode($else, $content, 2);
		} else {
			$if = $content;

			$else = '';
		}

		$condition = false;

		$membership = WP_Ultimo()->currents->get_membership();

		$user_logged_in = is_user_logged_in();

		$should_check = ! (bool) $atts['only_logged'] || $user_logged_in;

		$from_request = $atts['from_request'] && count(array_intersect($plan_ids, $request_products)) > 0;

		if ($membership && $should_check && ! $from_request) {
			$membership_products = array_merge(
				[$membership->get_plan_id()],
				$membership->get_addon_ids()
			);

			$condition = in_array('all', $plan_ids, true) || count(array_intersect($membership_products, $plan_ids)) > 0;

			if ( (bool) $atts['only_active']) {
				$condition = $condition && ($membership->is_active() || $membership->get_status() === 'trialing');
			}

			if ( (bool) $atts['exclude_trials']) {
				$condition = $condition && ! $membership->is_trialing();
			}
		} else {
			$condition = $from_request && $should_check;
		}

		$final_content = wpautop($condition ? $if : $else);

		return do_shortcode($final_content);
	}

	/**
	 * Adds the pricing table shortcode.
	 *
	 * This method is intended to be able to support both legacy shortcodes, as well
	 * as display new layouts.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $atts Parsed shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string
	 */
	public function pricing_table($atts, $content) {

		global $post;

		$atts = shortcode_atts(
			[
				'primary_color'          => wu_get_setting('primary_color', '#00a1ff'),
				'accent_color'           => wu_get_setting('accent_color', '#78b336'),
				'default_pricing_option' => wu_get_setting('default_pricing_option', 1),
				'plan_id'                => false,
				'show_selector'          => true,
				// New Options
				'layout'                 => 'legacy',
				'period_selector_layout' => 'legacy',
			],
			$atts,
			'wu_pricing_table'
		);

		/**
		 * In the case of the legacy layout, we need to load extra styles.
		 */
		if ('legacy' === $atts['layout']) {
			wp_enqueue_style('legacy-signup', wu_get_asset('legacy-signup.css', 'css'), [], \WP_Ultimo::VERSION);

			wp_add_inline_style('legacy-signup', \WP_Ultimo\Checkout\Legacy_Checkout::get_instance()->get_legacy_dynamic_styles());
		}

		do_action('wu_checkout_scripts', $post);

		do_action('wu_setup_checkout');

		$atts['plan_id'] = is_string($atts['plan_id']) && 'all' !== $atts['plan_id'] ? explode(',', $atts['plan_id']) : false;

		$checkout_form = new \WP_Ultimo\Models\Checkout_Form();

		$fields = [];

		$search_arguments = [
			'fields' => 'ids',
		];

		if ($atts['plan_id']) {
			$search_arguments['id__in'] = $atts['plan_id'];
		}

		if ($atts['show_selector']) {
			$fields[] = [
				'step'                      => 'checkout',
				'name'                      => '',
				'type'                      => 'period_selection',
				'id'                        => 'period_selection',
				'period_selection_template' => $atts['period_selector_layout'],
				'period_options'            => [
					[
						'duration'      => 1,
						'duration_unit' => 'month',
						'label'         => __('Monthly', 'multisite-ultimate'),
					],
					[
						'duration'      => 3,
						'duration_unit' => 'month',
						'label'         => __('Quarterly', 'multisite-ultimate'),
					],
					[
						'duration'      => 1,
						'duration_unit' => 'year',
						'label'         => __('Yearly', 'multisite-ultimate'),
					],
				],
			];
		}

		$layout = $atts['layout'];

		$fields[] = [
			'step'                   => 'checkout',
			'name'                   => __('Plans', 'multisite-ultimate'),
			'type'                   => 'pricing_table',
			'id'                     => 'pricing_table',
			'required'               => true,
			'pricing_table_products' => implode(',', wu_get_plans($search_arguments)),
			'pricing_table_template' => $layout,
			'element_classes'        => 'legacy' === $layout ? 'wu-content-plan' : '',
		];

		/**
		 * If not using the legacy checkout,
		 * we'll need a submit field.
		 */
		if ('legacy' !== $layout) {
			$fields[] = [
				'step' => 'checkout',
				'name' => __('Get Started &rarr;', 'multisite-ultimate'),
				'type' => 'submit_button',
				'id'   => 'checkout',
			];
		}

		$steps = [
			[
				'id'     => 'checkout',
				'name'   => __('Checkout', 'multisite-ultimate'),
				'desc'   => '',
				'fields' => $fields,
			],
		];

		$checkout = \WP_Ultimo\Checkout\Checkout::get_instance();

		$steps = apply_filters('wu_checkout_form_shortcode_pricing_table_fields', $steps);

		$checkout_form->set_settings($steps);

		$auto_submittable_field = $checkout->contains_auto_submittable_field($checkout_form->get_step('checkout', true)['fields']);

		do_action('wu_checkout_scripts', $post);

		do_action('wu_setup_checkout');

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

		$final_fields = wu_create_checkout_fields($checkout_form->get_step('checkout', true)['fields']);

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

		$final_fields['pre-flight'] = [
			'type'  => 'hidden',
			'value' => 1,
		];

		return wu_get_template_contents(
			'checkout/form',
			[
				'step'                 => $checkout_form->get_step('checkout', true),
				'step_name'            => 'checkout',
				'checkout_form_name'   => 'wu_pricing_table',
				'checkout_form_action' => add_query_arg('pre-flight', 1, wu_get_registration_url()),
				'display_title'        => '',
				'final_fields'         => $final_fields,
			]
		);
	}

	/**
	 * Adds the template sites shortcode.
	 *
	 * This method is intended to be able to support both legacy shortcodes, as well
	 * as display new layouts.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $atts Parsed shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string
	 */
	public function templates_list($atts, $content) {

		global $post;

		$atts = shortcode_atts(
			[
				'show_filters'  => true,
				'show_title'    => true,
				'templates'     => false,
				'cols'          => 3,
				'layout'        => 'legacy',
				'checkout_page' => wu_guess_registration_page(),
			],
			$atts,
			'wu_templates_list'
		);

		/**
		 * Hide header, if necessary
		 */
		add_filter('wu_step_template_display_header', $atts['show_title'] ? '__return_true' : '__return_false');

		/**
		 * Filters the template list to be used
		 *
		 * @since 1.7.4
		 */
		$templates = $atts['templates'] ? $this->treat_template_list($atts['templates']) : false;

		/**
		 * In the case of the legacy layout, we need to load extra styles.
		 */
		if ('legacy' === $atts['layout']) {
			wp_enqueue_style('legacy-signup', wu_get_asset('legacy-signup.css', 'css'), [], \WP_Ultimo::VERSION);

			wp_add_inline_style('legacy-signup', \WP_Ultimo\Checkout\Legacy_Checkout::get_instance()->get_legacy_dynamic_styles());
		}

		$checkout_form = new \WP_Ultimo\Models\Checkout_Form();

		$fields = [];

		$search_arguments = [
			'fields' => 'ids',
		];

		$layout = $atts['layout'];

		$fields[] = [
			'step'                        => 'checkout',
			'name'                        => __('Templates', 'multisite-ultimate'),
			'type'                        => 'template_selection',
			'id'                          => 'template_selection',
			'template_selection_sites'    => implode(',', $templates ?: wu_get_site_templates($search_arguments)),
			'template_selection_template' => $layout,
			'cols'                        => $atts['cols'],
			'element_classes'             => 'legacy' === $layout ? 'wu-content-templates' : '',
		];

		$steps = [
			[
				'id'     => 'checkout',
				'name'   => __('Checkout', 'multisite-ultimate'),
				'desc'   => '',
				'fields' => $fields,
			],
		];

		$checkout = \WP_Ultimo\Checkout\Checkout::get_instance();

		$steps = apply_filters('wu_checkout_form_shortcode_templates_list_fields', $steps);

		$checkout_form->set_settings($steps);

		$final_fields = wu_create_checkout_fields($checkout_form->get_step('checkout', true)['fields']);

		$auto_submittable_field = $checkout->contains_auto_submittable_field($checkout_form->get_step('checkout', true)['fields']);

		do_action('wu_checkout_scripts', $post);

		do_action('wu_setup_checkout');

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

		$final_fields['pre-flight'] = [
			'type'  => 'hidden',
			'value' => 1,
		];

		$page_url = wu_switch_blog_and_run(fn() => get_permalink($atts['checkout_page']));

		return wu_get_template_contents(
			'checkout/form',
			[
				'step'                 => $checkout_form->get_step('checkout', true),
				'step_name'            => 'checkout',
				'checkout_form_name'   => 'wu_templates_list',
				'checkout_form_action' => add_query_arg('pre-flight', 1, $page_url),
				'display_title'        => '',
				'final_fields'         => $final_fields,
			]
		);
	}

	/**
	 * Makes sure we don't return any invalid values.
	 *
	 * @since  1.7.4
	 * @param  string $templates The template list as a string.
	 */
	protected function treat_template_list($templates): array {

		$list = array_map('trim', explode(',', $templates));

		return array_filter($list);
	}
}
