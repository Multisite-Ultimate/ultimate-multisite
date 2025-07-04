<?php
/**
 * Multisite Ultimate settings helper class.
 *
 * @package WP_Ultimo
 * @subpackage Settings
 * @since 2.0.0
 */

namespace WP_Ultimo;

use WP_Ultimo\Checkout\Checkout_Pages;
use WP_Ultimo\UI\Field;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate settings helper class.
 *
 * @since 2.0.0
 */
class Settings {

	use \WP_Ultimo\Traits\Singleton;
	use \WP_Ultimo\Traits\WP_Ultimo_Settings_Deprecated;

	/**
	 * Keeps the key used to access settings.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const KEY = 'v2_settings';

	/**
	 * Holds the array containing all the saved settings.
	 *
	 * @since 2.0.0
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * Holds the sections of the settings page.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	private $sections = null;

	/**
	 * @var bool
	 */
	private bool $saving = false;

	/**
	 * Runs on singleton instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_action('init', [$this, 'handle_legacy_filters'], 2);

		add_action('wu_render_settings', [$this, 'handle_legacy_scripts']);

		add_filter('pre_site_option_registration', [$this, 'force_registration_status'], 10, 3);

		add_filter('pre_site_option_add_new_users', [$this, 'force_add_new_users'], 10, 3);

		add_filter('pre_site_option_menu_items', [$this, 'force_plugins_menu'], 10, 3);
	}

	/**
	 * Change the current status of the registration on WordPress MS.
	 *
	 * @since 2.0.0
	 *
	 * @param string $status The registration status.
	 * @param string $option Option name, in this case, 'registration'.
	 * @param int    $network_id The id of the network being accessed.
	 * @return string
	 */
	public function force_registration_status($status, $option, $network_id) {

		global $current_site;

		if ($current_site->id !== $network_id) {
			return $status;
		}

		$status = wu_get_setting('enable_registration', true) ? 'all' : $status;

		return $status;
	}

	/**
	 * Change the current status of the add_new network option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $status The add_new_users status.
	 * @param string $option Option name, in this case, 'add_new_user'.
	 * @param int    $network_id The id of the network being accessed.
	 * @return string
	 */
	public function force_add_new_users($status, $option, $network_id) {

		global $current_site;

		if ($current_site->id !== $network_id) {
			return $status;
		}

		return wu_get_setting('add_new_users', true);
	}

	/**
	 * Change the current status of the add_new network option.
	 *
	 * @since 2.0.0
	 *
	 * @param array|bool $status The add_new_users status.
	 * @param string     $option Option name, in this case, 'add_new_user'.
	 * @param int        $network_id The id of the network being accessed.
	 * @return string
	 */
	public function force_plugins_menu($status, $option, $network_id) {

		global $current_site;

		if ($current_site->id !== $network_id || is_bool($status)) {
			return $status;
		}

		$status['plugins'] = wu_get_setting('menu_items_plugin', true);

		return $status;
	}

	/**
	 * Get all the settings from Multisite Ultimate
	 *
	 * @param bool $check_caps If we should remove the settings the user does not have rights to see.
	 * @return array Array containing all the settings
	 */
	public function get_all($check_caps = false) {

		// Get all the settings
		if (null === $this->settings) {
			$this->settings = wu_get_option(self::KEY);
		}

		if (empty($this->settings)) {
			return [];
		}

		if ($check_caps) {} // phpcs:ignore;

		return $this->settings;
	}

	/**
	 * Get a specific settings from the plugin
	 *
	 * @since  1.1.5 Let's we pass default values in case nothing is found.
	 * @since  1.4.0 Now we can filter settings we get.
	 *
	 * @param  string $setting Settings name to return.
	 * @param  mixed  $default_value Default value for the setting if it doesn't exist.
	 *
	 * @return mixed The value of that setting
	 */
	public function get_setting($setting, $default_value = false) {

		$settings = $this->get_all();

		if (str_contains($setting, '-')) {
			_doing_it_wrong(esc_html($setting), esc_html__('Dashes are no longer supported when registering a setting. You should change it to underscores in later versions.', 'multisite-ultimate'), '2.0.0');
		}

		$setting_value = $settings[ $setting ] ?? $default_value;

		return apply_filters('wu_get_setting', $setting_value, $setting, $default_value, $settings);
	}

	/**
	 * Saves a specific setting into the database
	 *
	 * @param string $setting Option key to save.
	 * @param mixed  $value   New value of the option.
	 * @return boolean
	 */
	public function save_setting($setting, $value) {

		$settings = $this->get_all();

		$value = apply_filters('wu_save_setting', $value, $setting, $settings);

		if (is_callable($value)) {
			$value = call_user_func($value);
		}

		$settings[ $setting ] = $value;

		$status = wu_save_option(self::KEY, $settings);

		$this->settings = $settings;

		return $status;
	}

	/**
	 * Save Multisite Ultimate Settings
	 *
	 * This function loops through the settings sections and saves the settings
	 * after validating them.
	 *
	 * @since 2.0.0
	 *
	 * @param array   $settings_to_save Array containing the settings to save.
	 * @param boolean $reset If true, Multisite Ultimate will override the saved settings with the default values.
	 * @return array
	 */
	public function save_settings($settings_to_save = [], $reset = false) {

		$settings = [];

		$sections = $this->get_sections();

		$saved_settings = $this->get_all();

		do_action('wu_before_save_settings', $settings_to_save);

		foreach ($sections as $section_slug => $section) {
			foreach ($section['fields'] as $field_slug => $field_atts) {
				$existing_value = $saved_settings[ $field_slug ] ?? false;

				$field = new Field($field_slug, $field_atts);

				$new_value = $settings_to_save[ $field_slug ] ?? $existing_value;

				/**
				 * For the current tab, we need to assume toggle fields.
				 */
				if (wu_request('tab', 'general') === $section_slug && 'toggle' === $field->type && ! isset($settings_to_save[ $field_slug ])) {
					$new_value = false;
				}

				$value = $new_value;

				$field->set_value($value);

				if ($field->get_value() !== null) {
					$settings[ $field_slug ] = $field->get_value();
				}

				do_action('wu_saving_setting', $field_slug, $field, $settings_to_save);
			}
		}

		/**
		 * Allow developers to filter settings before save by Multisite Ultimate.
		 *
		 * @since 2.0.18
		 *
		 * @param array  $settings         The settings to be saved.
		 * @param array  $settings_to_save The new settings to add.
		 * @param string $saved_settings   The current settings saved.
		 */
		$settings = apply_filters('wu_pre_save_settings', $settings, $settings_to_save, $saved_settings);

		wu_save_option(self::KEY, $settings);

		$this->settings = $settings;

		do_action('wu_after_save_settings', $settings, $settings_to_save, $saved_settings);

		return $settings;
	}

	/**
	 * Returns the list of sections and their respective fields.
	 *
	 * @since 1.1.0
	 * @todo Order sections by the order parameter.
	 * @todo Order fields by the order parameter.
	 * @return array
	 */
	public function get_sections() {

		if ( $this->sections ) {
			return $this->sections;
		}

		$this->default_sections();
		$this->sections = apply_filters(
			'wu_settings_get_sections',
			[

				/*
				 * Add a default invisible section that we can use
				 * to register settings that will not have a control.
				 */
				'core' => [
					'invisible' => true,
					'order'     => 1_000_000,
					'fields'    => apply_filters('wu_settings_section_core_fields', []),
				],
			]
		);

		uasort($this->sections, 'wu_sort_by_order');

		return $this->sections;
	}

	/**
	 * Returns a particular settings section.
	 *
	 * @since 2.0.0
	 *
	 * @param string $section_name The slug of the section to return.
	 * @return array
	 */
	public function get_section($section_name = 'general') {

		$sections = $this->get_sections();

		return wu_get_isset(
			$sections,
			$section_name,
			[
				'fields' => [],
			]
		);
	}

	/**
	 * Adds a new settings section.
	 *
	 * Sections are a way to organize correlated settings into one cohesive unit.
	 * Developers should be able to add their own sections, if they need to.
	 * This is the purpose of this APIs.
	 *
	 * @since 2.0.0
	 *
	 * @param string $section_slug ID of the Section. This is used to register fields to this section later.
	 * @param array  $atts Section attributes such as title, description and so on.
	 * @return void
	 */
	public function add_section($section_slug, $atts): void {

		add_filter(
			'wu_settings_get_sections',
			function ($sections) use ($section_slug, $atts) {

				$default_order = (count($sections) + 1) * 10;

				$atts = wp_parse_args(
					$atts,
					[
						'icon'       => 'dashicons-wu-cog',
						'order'      => $default_order,
						'capability' => 'manage_network',
					]
				);

				$atts['fields'] = apply_filters("wu_settings_section_{$section_slug}_fields", []);

				$sections[ $section_slug ] = $atts;

				return $sections;
			}
		);
	}

	/**
	 * Adds a new field to a settings section.
	 *
	 * Fields are settings that admins can actually change.
	 * This API allows developers to add new fields to a given settings section.
	 *
	 * @since 2.0.0
	 *
	 * @param string $section_slug Section to which this field will be added to.
	 * @param string $field_slug ID of the field. This is used to later retrieve the value saved on this setting.
	 * @param array  $atts Field attributes such as title, description, tooltip, default value, etc.
	 * @param int    $priority Priority of the field. This is used to order the fields.
	 * @return void
	 */
	public function add_field($section_slug, $field_slug, $atts, $priority = 10): void {
		/*
		 * Adds the field to the desired fields array.
		 */
		add_filter(
			"wu_settings_section_{$section_slug}_fields",
			function ($fields) use ($field_slug, $atts) {
				/*
				* We no longer support settings with hyphens.
				*/
				if (str_contains($field_slug, '-')) {
					_doing_it_wrong(esc_html($field_slug), esc_html__('Dashes are no longer supported when registering a setting. You should change it to underscores in later versions.', 'multisite-ultimate'), '2.0.0');
				}

				$default_order = (count($fields) + 1) * 10;

				$atts = wp_parse_args(
					$atts,
					[
						'setting_id'        => $field_slug,
						'title'             => '',
						'desc'              => '',
						'order'             => $default_order,
						'default'           => null,
						'capability'        => 'manage_network',
						'wrapper_html_attr' => [],
						'require'           => [],
						'html_attr'         => [],
						'value'             => fn() => wu_get_setting($field_slug),
						'display_value'     => fn() => wu_get_setting($field_slug),
						'img'               => function () use ($field_slug) {

							$img_id = wu_get_setting($field_slug);

							if ( ! $img_id) {
								return '';
							}

							$custom_logo_args = wp_get_attachment_image_src($img_id, 'full');

							return $custom_logo_args ? $custom_logo_args[0] : '';
						},
					]
				);

				/**
				 * Adds v-model
				 */
				if (wu_get_isset($atts, 'type') !== 'submit') {
					$atts['html_attr']['v-model']     = wu_replace_dashes($field_slug);
					$atts['html_attr']['true-value']  = '1';
					$atts['html_attr']['false-value'] = '0';
				}

				$atts['html_attr']['id'] = $field_slug;

				/**
				 * Handle selectize.
				 */
				$model_name = wu_get_isset($atts['html_attr'], 'data-model');

				if ($model_name) {
					if (function_exists("wu_get_{$model_name}") || 'page' === $model_name) {
						$original_html_attr = $atts['html_attr'];

						$atts['html_attr'] = function () use ($field_slug, $model_name, $atts, $original_html_attr) {

							$value = wu_get_setting($field_slug);

							if ('page' === $model_name) {
								$new_attrs['data-selected'] = get_post($value);
							} else {
								$data_selected              = call_user_func("wu_get_{$model_name}", $value);
								$new_attrs['data-selected'] = $data_selected->to_search_results();
							}

							$new_attrs['data-selected'] = wp_json_encode($new_attrs['data-selected']);

							return array_merge($original_html_attr, $new_attrs);
						};
					}
				}

				if ( ! empty($atts['require'])) {
					$require_rules = [];

					foreach ($atts['require'] as $attr => $value) {
						$attr = str_replace('-', '_', $attr);

						$value = wp_json_encode($value);

						$require_rules[] = "require('{$attr}', {$value})";
					}

					$atts['wrapper_html_attr']['v-show']  = implode(' && ', $require_rules);
					$atts['wrapper_html_attr']['v-cloak'] = 'v-cloak';
				}

				$fields[ $field_slug ] = $atts;

				return $fields;
			},
			$priority
		);

		$settings = $this->get_all();

		/*
		 * Makes sure we install the default value if it is not set yet.
		 */
		if (isset($atts['default']) && null !== $atts['default'] && ! isset($settings[ $field_slug ])) {
			$this->save_setting($field_slug, $atts['default']);
		}
	}

	/**
	 * Register the Multisite Ultimate default sections and fields.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function default_sections(): void {
		/*
		 * General Settings
		 * This section holds the General settings of the Multisite Ultimate Plugin.
		 */

		// Comma separated string of page ids that are already being used as default option
		$filter_default_signup_pages = implode(',', array_filter(Checkout_Pages::get_instance()->get_signup_pages()));

		$this->add_section(
			'general',
			[
				'title' => __('General', 'multisite-ultimate'),
				'desc'  => __('General', 'multisite-ultimate'),
			]
		);

		$this->add_field(
			'general',
			'company_header',
			[
				'title' => __('Your Business', 'multisite-ultimate'),
				'desc'  => __('General information about your business..', 'multisite-ultimate'),
				'type'  => 'header',
			],
			10
		);

		$this->add_field(
			'general',
			'company_name',
			[
				'title'   => __('Company Name', 'multisite-ultimate'),
				'desc'    => __('This name is used when generating invoices, for example.', 'multisite-ultimate'),
				'type'    => 'text',
				'default' => get_network_option(null, 'site_name'),
			],
			20
		);

		$this->add_field(
			'general',
			'company_logo',
			[
				'title'   => __('Upload Company Logo', 'multisite-ultimate'),
				'desc'    => __('Add your company logo to be used on the login page and other places.', 'multisite-ultimate'),
				'type'    => 'image',
				'default' => '',
			],
			30
		);

		$this->add_field(
			'general',
			'company_email',
			[
				'title'   => __('Company Email Address', 'multisite-ultimate'),
				'desc'    => __('This email is used when generating invoices, for example.', 'multisite-ultimate'),
				'type'    => 'text',
				'default' => get_network_option(null, 'admin_email'),
			],
			40
		);

		$this->add_field(
			'general',
			'company_address',
			[
				'title'       => __('Company Address', 'multisite-ultimate'),
				'desc'        => __('This address is used when generating invoices.', 'multisite-ultimate'),
				'type'        => 'textarea',
				'placeholder' => "350 Fifth Avenue\nManhattan, \nNew York City, NY \n10118",
				'default'     => '',
				'html_attr'   => [
					'rows' => 5,
				],
			],
			50
		);

		$this->add_field(
			'general',
			'company_country',
			[
				'title'   => __('Company Country', 'multisite-ultimate'),
				'desc'    => __('This info is used when generating invoices, as well as for calculating when taxes apply in some contexts.', 'multisite-ultimate'),
				'type'    => 'select',
				'options' => 'wu_get_countries',
				'default' => [$this, 'get_default_company_country'],
			],
			60
		);

		$this->add_field(
			'general',
			'currency_header',
			[
				'title' => __('Currency Options', 'multisite-ultimate'),
				'desc'  => __('The following options affect how prices are displayed on the frontend, the backend and in reports.', 'multisite-ultimate'),
				'type'  => 'header',
			],
			70
		);

		$this->add_field(
			'general',
			'currency_symbol',
			[
				'title'   => __('Currency', 'multisite-ultimate'),
				'desc'    => __('Select the currency to be used in Multisite Ultimate.', 'multisite-ultimate'),
				'type'    => 'select',
				'default' => 'USD',
				'options' => 'wu_get_currencies',
			],
			80
		);

		$this->add_field(
			'general',
			'currency_position',
			[
				'title'   => __('Currency Position', 'multisite-ultimate'),
				'desc'    => __('This setting affects all prices displayed across the plugin elements.', 'multisite-ultimate'),
				'type'    => 'select',
				'default' => '%s %v',
				'options' => [
					'%s%v'  => __('Left ($99.99)', 'multisite-ultimate'),
					'%v%s'  => __('Right (99.99$)', 'multisite-ultimate'),
					'%s %v' => __('Left with space ($ 99.99)', 'multisite-ultimate'),
					'%v %s' => __('Right with space (99.99 $)', 'multisite-ultimate'),
				],
			],
			90
		);

		$this->add_field(
			'general',
			'decimal_separator',
			[
				'title'   => __('Decimal Separator', 'multisite-ultimate'),
				'desc'    => __('This setting affects all prices displayed across the plugin elements.', 'multisite-ultimate'),
				'type'    => 'text',
				'default' => '.',
			],
			100
		);

		$this->add_field(
			'general',
			'thousand_separator',
			[
				'title'   => __('Thousand Separator', 'multisite-ultimate'),
				'desc'    => __('This setting affects all prices displayed across the plugin elements.', 'multisite-ultimate'),
				'type'    => 'text',
				'default' => ',',
				'raw'     => true,
			],
			110
		);

		$this->add_field(
			'general',
			'precision',
			[
				'title'   => __('Number of Decimals', 'multisite-ultimate'),
				'desc'    => __('This setting affects all prices displayed across the plugin elements.', 'multisite-ultimate'),
				'type'    => 'number',
				'default' => '2',
				'min'     => 0,
			],
			120
		);

		/*
		 * Login & Registration
		 * This section holds the Login & Registration settings of the Multisite Ultimate Plugin.
		 */

		$this->add_section(
			'login-and-registration',
			[
				'title' => __('Login & Registration', 'multisite-ultimate'),
				'desc'  => __('Login & Registration', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-key',
			]
		);

		$this->add_field(
			'login-and-registration',
			'registration_header',
			[
				'title' => __('Login and Registration Options', 'multisite-ultimate'),
				'desc'  => __('Options related to registration and login behavior.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$this->add_field(
			'login-and-registration',
			'enable_registration',
			[
				'title'   => __('Enable Registration', 'multisite-ultimate'),
				'desc'    => __('Turning this toggle off will disable registration in all checkout forms across the network.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'login-and-registration',
			'enable_email_verification',
			[
				'title'   => __('Enable email verification', 'multisite-ultimate'),
				'desc'    => __('Enabling this option will require the customer to verify their email address when subscribing to a free plan or a plan with a trial period. Sites will not be created until the customer email verification status is changed to verified.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'login-and-registration',
			'default_registration_page',
			[
				'type'        => 'model',
				'title'       => __('Default Registration Page', 'multisite-ultimate'),
				'placeholder' => __('Search pages on the main site...', 'multisite-ultimate'),
				'desc'        => __('Only published pages on the main site are available for selection, and you need to make sure they contain a [wu_checkout] shortcode.', 'multisite-ultimate'),
				'tooltip'     => '',
				'html_attr'   => [
					'data-base-link'    => get_admin_url(wu_get_main_site_id(), 'post.php?action=edit&post'),
					'data-model'        => 'page',
					'data-value-field'  => 'ID',
					'data-label-field'  => 'post_title',
					'data-search-field' => 'post_title',
					'data-max-items'    => 1,
					'data-exclude'      => $filter_default_signup_pages,
				],
			]
		);

		$this->add_field(
			'login-and-registration',
			'enable_custom_login_page',
			[
				'title'   => __('Use Custom Login Page', 'multisite-ultimate'),
				'desc'    => __('Turn this toggle on to select a custom page to be used as the login page.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$this->add_field(
			'login-and-registration',
			'default_login_page',
			[
				'type'        => 'model',
				'title'       => __('Default Login Page', 'multisite-ultimate'),
				'placeholder' => __('Search pages on the main site...', 'multisite-ultimate'),
				'desc'        => __('Only published pages on the main site are available for selection, and you need to make sure they contain a [wu_login_form] shortcode.', 'multisite-ultimate'),
				'tooltip'     => '',
				'html_attr'   => [
					'data-base-link'    => get_admin_url(wu_get_main_site_id(), 'post.php?action=edit&post'),
					'data-model'        => 'page',
					'data-value-field'  => 'ID',
					'data-label-field'  => 'post_title',
					'data-search-field' => 'post_title',
					'data-max-items'    => 1,
				],
				'require'     => [
					'enable_custom_login_page' => true,
				],
			]
		);

		$this->add_field(
			'login-and-registration',
			'obfuscate_original_login_url',
			[
				'title'   => __('Obfuscate the Original Login URL (wp-login.php)', 'multisite-ultimate'),
				'desc'    => __('If this option is enabled, we will display a 404 error when a user tries to access the original wp-login.php link. This is useful to prevent brute-force attacks.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
				'require' => [
					'enable_custom_login_page' => 1,
				],
			]
		);

		$this->add_field(
			'login-and-registration',
			'subsite_custom_login_logo',
			[
				'title'   => __('Use Sub-site logo on Login Page', 'multisite-ultimate'),
				'desc'    => __('Toggle this option to replace the WordPress logo on the sub-site login page with the logo set for that sub-site. If unchecked, the network logo will be used instead.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
				'require' => [
					'enable_custom_login_page' => 0,
				],
			]
		);

		$this->add_field(
			'login-and-registration',
			'force_publish_sites_sync',
			[
				'title'   => __('Force Synchronous Site Publication ', 'multisite-ultimate'),
				'desc'    => __('By default, when a new pending site needs to be converted into a real network site, the publishing process happens via Job Queue, asynchronously. Enable this option to force the publication to happen in the same request as the signup. Be careful, as this can cause timeouts depending on the size of the site templates being copied.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$this->add_field(
			'login-and-registration',
			'other_header',
			[
				'title' => __('Other Options', 'multisite-ultimate'),
				'desc'  => __('Other registration-related options.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$this->add_field(
			'login-and-registration',
			'default_role',
			[
				'title'   => __('Default Role', 'multisite-ultimate'),
				'desc'    => __('Set the role to be applied to the user during the signup process.', 'multisite-ultimate'),
				'type'    => 'select',
				'default' => 'administrator',
				'options' => 'wu_get_roles_as_options',
			]
		);

		$this->add_field(
			'login-and-registration',
			'add_users_to_main_site',
			[
				'title'   => __('Add Users to the Main Site as well?', 'multisite-ultimate'),
				'desc'    => __('Enabling this option will also add the user to the main site of your network.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$this->add_field(
			'login-and-registration',
			'main_site_default_role',
			[
				'title'   => __('Add to Main Site with Role...', 'multisite-ultimate'),
				'desc'    => __('Select the role Multisite Ultimate should use when adding the user to the main site of your network. Be careful.', 'multisite-ultimate'),
				'type'    => 'select',
				'default' => 'subscriber',
				'options' => 'wu_get_roles_as_options',
				'require' => [
					'add_users_to_main_site' => 1,
				],
			]
		);

		do_action('wu_settings_login');

		/*
		 * Memberships
		 * This section holds the Membership  settings of the Multisite Ultimate Plugin.
		 */

		$this->add_section(
			'memberships',
			[
				'title' => __('Memberships', 'multisite-ultimate'),
				'desc'  => __('Memberships', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-infinity',
			]
		);

		$this->add_field(
			'memberships',
			'default_update_page',
			[
				'type'        => 'model',
				'title'       => __('Default Membership Update Page', 'multisite-ultimate'),
				'placeholder' => __('Search pages on the main site...', 'multisite-ultimate'),
				'desc'        => __('Only published pages on the main site are available for selection, and you need to make sure they contain a [wu_checkout] shortcode.', 'multisite-ultimate'),
				'tooltip'     => '',
				'html_attr'   => [
					'data-base-link'    => get_admin_url(wu_get_main_site_id(), 'post.php?action=edit&post'),
					'data-model'        => 'page',
					'data-value-field'  => 'ID',
					'data-label-field'  => 'post_title',
					'data-search-field' => 'post_title',
					'data-max-items'    => 1,
					'data-exclude'      => $filter_default_signup_pages,
				],
			]
		);

		$this->add_field(
			'memberships',
			'block_frontend',
			[
				'title'   => __('Block Frontend Access', 'multisite-ultimate'),
				'desc'    => __('Block the frontend access of network sites after a membership is no longer active.', 'multisite-ultimate'),
				'tooltip' => __('By default, if a user does not pay and the account goes inactive, only the admin panel will be blocked, but the user\'s site will still be accessible on the frontend. If enabled, this option will also block frontend access in those cases.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$this->add_field(
			'memberships',
			'block_frontend_grace_period',
			[
				'title'   => __('Frontend Block Grace Period', 'multisite-ultimate'),
				'desc'    => __('Select the number of days Multisite Ultimate should wait after the membership goes inactive before blocking the frontend access. Leave 0 to block immediately after the membership becomes inactive.', 'multisite-ultimate'),
				'type'    => 'number',
				'default' => 0,
				'min'     => 0,
				'require' => [
					'block_frontend' => 1,
				],
			]
		);

		$this->add_field(
			'memberships',
			'default_block_frontend_page',
			[
				'title'     => __('Frontend Block Page', 'multisite-ultimate'),
				'desc'      => __('Select a page on the main site to redirect user if access is blocked', 'multisite-ultimate'),
				'tooltip'   => '',
				'html_attr' => [
					'data-base-link'    => get_admin_url(wu_get_main_site_id(), 'post.php?action=edit&post'),
					'data-model'        => 'page',
					'data-value-field'  => 'ID',
					'data-label-field'  => 'post_title',
					'data-search-field' => 'post_title',
					'data-max-items'    => 1,
				],
				'require'   => [
					'block_frontend' => 1,
				],
			]
		);

		$this->add_field(
			'memberships',
			'enable_multiple_memberships',
			[
				'title'   => __('Enable Multiple Memberships per Customer', 'multisite-ultimate'),
				'desc'    => __('Enabling this option will allow your users to create more than one membership.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$this->add_field(
			'memberships',
			'enable_multiple_sites',
			[
				'title'   => __('Enable Multiple Sites per Membership', 'multisite-ultimate'),
				'desc'    => __('Enabling this option will allow your customers to create more than one site. You can limit how many sites your users can create in a per plan basis.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$this->add_field(
			'memberships',
			'block_sites_on_downgrade',
			[
				'title'   => __('Block Sites on Downgrade', 'multisite-ultimate'),
				'desc'    => __('Choose how Multisite Ultimate should handle client sites above their plan quota on downgrade.', 'multisite-ultimate'),
				'type'    => 'select',
				'default' => 'none',
				'options' => [
					'none'           => __('Keep sites as is (do nothing)', 'multisite-ultimate'),
					'block-frontend' => __('Block only frontend access', 'multisite-ultimate'),
					'block-backend'  => __('Block only backend access', 'multisite-ultimate'),
					'block-both'     => __('Block both frontend and backend access', 'multisite-ultimate'),
				],
				'require' => [
					'enable_multiple_sites' => true,
				],
			]
		);

		$this->add_field(
			'memberships',
			'move_posts_on_downgrade',
			[
				'title'   => __('Move Posts on Downgrade', 'multisite-ultimate'),
				'desc'    => __('Select how you want to handle the posts above the quota on downgrade. This will apply to all post types with quotas set.', 'multisite-ultimate'),
				'type'    => 'select',
				'default' => 'none',
				'options' => [
					'none'  => __('Keep posts as is (do nothing)', 'multisite-ultimate'),
					'trash' => __('Move posts above the new quota to the Trash', 'multisite-ultimate'),
					'draft' => __('Mark posts above the new quota as Drafts', 'multisite-ultimate'),
				],
			]
		);

		$this->add_field(
			'memberships',
			'emulated_post_types_header',
			[
				'type'  => 'header',
				'title' => __('Emulated Post Types', 'multisite-ultimate'),
				'desc'  => __('Emulates the registering of a custom post type to be able to create limits for it without having to activate plugins on the main site.', 'multisite-ultimate'),
			]
		);

		$this->add_field(
			'memberships',
			'emulated_post_types_explanation',
			[
				'type'            => 'note',
				'desc'            => __('By default, Multisite Ultimate only allows super admins to limit post types that are registered on the main site. This makes sense from a technical stand-point but it also forces you to have plugins network-activated in order to be able to set limitations for their custom post types. Using this option, you can emulate the registering of a post type. This will register them on the main site and allow you to create limits for them on your products.', 'multisite-ultimate'),
				'classes'         => '',
				'wrapper_classes' => '',
			]
		);

		$this->add_field(
			'memberships',
			'emulated_post_types_empty',
			[
				'type'              => 'note',
				'desc'              => __('Add the first post type using the button below.', 'multisite-ultimate'),
				'classes'           => 'wu-text-gray-600 wu-text-xs wu-text-center wu-w-full',
				'wrapper_classes'   => 'wu-bg-gray-100 wu-items-end',
				'wrapper_html_attr' => [
					'v-if'    => 'emulated_post_types.length === 0',
					'v-cloak' => '1',
				],
			]
		);

		$this->add_field(
			'memberships',
			'emulated_post_types',
			[
				'type'              => 'group',
				'tooltip'           => '',
				'raw'               => true,
				'default'           => [],
				'wrapper_classes'   => 'wu-relative wu-bg-gray-100 wu-pb-2',
				'wrapper_html_attr' => [
					'v-if'    => 'emulated_post_types.length',
					'v-for'   => '(emulated_post_type, index) in emulated_post_types',
					'v-cloak' => '1',
				],
				'fields'            => [
					'emulated_post_types_remove' => [
						'type'            => 'note',
						'desc'            => sprintf('<a title="%s" class="wu-no-underline wu-inline-block wu-text-gray-600 wu-mt-2 wu-mr-2" href="#" @click.prevent="() => emulated_post_types.splice(index, 1)"><span class="dashicons-wu-squared-cross"></span></a>', __('Remove', 'multisite-ultimate')),
						'wrapper_classes' => 'wu-absolute wu-top-0 wu-right-0',
					],
					'emulated_post_types_slug'   => [
						'type'            => 'text',
						'title'           => __('Post Type Slug', 'multisite-ultimate'),
						'placeholder'     => __('e.g. product', 'multisite-ultimate'),
						'wrapper_classes' => 'wu-w-5/12',
						'html_attr'       => [
							'v-model'     => 'emulated_post_type.post_type',
							'v-bind:name' => '"emulated_post_types[" + index + "][post_type]"',
						],
					],
					'emulated_post_types_label'  => [
						'type'            => 'text',
						'title'           => __('Post Type Label', 'multisite-ultimate'),
						'placeholder'     => __('e.g. Products', 'multisite-ultimate'),
						'wrapper_classes' => 'wu-w-7/12 wu-ml-2',
						'html_attr'       => [
							'v-model'     => 'emulated_post_type.label',
							'v-bind:name' => '"emulated_post_types[" + index + "][label]"',
						],
					],
				],
			]
		);

		$this->add_field(
			'memberships',
			'emulated_post_types_repeat',
			[
				'type'              => 'submit',
				'title'             => __('+ Add Post Type', 'multisite-ultimate'),
				'classes'           => 'wu-uppercase wu-text-2xs wu-text-blue-700 wu-border-none wu-bg-transparent wu-font-bold wu-text-right wu-w-full wu-cursor-pointer',
				'wrapper_classes'   => 'wu-bg-gray-100 wu-items-end',
				'wrapper_html_attr' => [
					'v-cloak' => '1',
				],
				'html_attr'         => [
					'v-on:click.prevent' => '() => {
					emulated_post_types = Array.isArray(emulated_post_types) ? emulated_post_types : [];  emulated_post_types.push({
						post_type: "",
						label: "",
					})
				}',
				],
			]
		);

		do_action('wu_settings_memberships');

		/*
		 * Site Templates
		 * This section holds the Site Templates settings of the Multisite Ultimate Plugin.
		 */

		$this->add_section(
			'sites',
			[
				'title' => __('Sites', 'multisite-ultimate'),
				'desc'  => __('Sites', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-browser',
			]
		);

		$this->add_field(
			'sites',
			'sites_features_heading',
			[
				'title' => __('Site Options', 'multisite-ultimate'),
				'desc'  => __('Configure certain aspects of how network Sites behave.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$this->add_field(
			'sites',
			'default_new_site_page',
			[
				'type'        => 'model',
				'title'       => __('Default New Site Page', 'multisite-ultimate'),
				'placeholder' => __('Search pages on the main site...', 'multisite-ultimate'),
				'desc'        => __('Only published pages on the main site are available for selection, and you need to make sure they contain a [wu_checkout] shortcode.', 'multisite-ultimate'),
				'tooltip'     => '',
				'html_attr'   => [
					'data-base-link'    => get_admin_url(wu_get_main_site_id(), 'post.php?action=edit&post'),
					'data-model'        => 'page',
					'data-value-field'  => 'ID',
					'data-label-field'  => 'post_title',
					'data-search-field' => 'post_title',
					'data-max-items'    => 1,
					'data-exclude'      => $filter_default_signup_pages,
				],
			]
		);

		$this->add_field(
			'sites',
			'enable_visits_limiting',
			[
				'title'   => __('Enable Visits Limitation & Counting', 'multisite-ultimate'),
				'desc'    => __('Enabling this option will add visits limitation settings to the plans and add the functionality necessary to count site visits on the front-end.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'sites',
			'enable_screenshot_generator',
			[
				'title'   => __('Enable Screenshot Generator', 'multisite-ultimate'),
				'desc'    => __('With this option is enabled, Multisite Ultimate will take a screenshot for every newly created site on your network and set the resulting image as that site\'s featured image. This features requires a valid license key to work and it is not supported for local sites.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'sites',
			'wordpress_features_heading',
			[
				'title' => __('WordPress Features', 'multisite-ultimate'),
				'desc'  => __('Override default WordPress settings for network Sites.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$this->add_field(
			'sites',
			'menu_items_plugin',
			[
				'title'   => __('Enable Plugins Menu', 'multisite-ultimate'),
				'desc'    => __('Do you want to let users on the network to have access to the Plugins page, activating plugins for their sites? If this option is disabled, the customer will not be able to manage the site plugins.', 'multisite-ultimate'),
				'tooltip' => __('You can select which plugins the user will be able to use for each plan.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'sites',
			'add_new_users',
			[
				'title'   => __('Add New Users', 'multisite-ultimate'),
				'desc'    => __('Allow site administrators to add new users to their site via the "Users → Add New" page.', 'multisite-ultimate'),
				'tooltip' => __('You can limit the number of users allowed for each plan.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'sites',
			'site_template_features_heading',
			[
				'title' => __('Site Template Options', 'multisite-ultimate'),
				'desc'  => __('Configure certain aspects of how Site Templates behave.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$this->add_field(
			'sites',
			'allow_template_switching',
			[
				'title'   => __('Allow Template Switching', 'multisite-ultimate'),
				'desc'    => __("Enabling this option will add an option on your client's dashboard to switch their site template to another one available on the catalog of available templates. The data is lost after a switch as the data from the new template is copied over.", 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'sites',
			'allow_own_site_as_template',
			[
				'title'   => __('Allow Users to use their own Sites as Templates', 'multisite-ultimate'),
				'desc'    => __('Enabling this option will add the user own sites to the template screen, allowing them to create a new site based on the content and customizations they made previously.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
				'require' => [
					'allow_template_switching' => true,
				],
			]
		);

		$this->add_field(
			'sites',
			'copy_media',
			[
				'title'   => __('Copy Media on Template Duplication?', 'multisite-ultimate'),
				'desc'    => __('Checking this option will copy the media uploaded on the template site to the newly created site. This can be overridden on each of the plans.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'sites',
			'stop_template_indexing',
			[
				'title'   => __('Prevent Search Engines from indexing Site Templates', 'multisite-ultimate'),
				'desc'    => __('Checking this option will discourage search engines from indexing all the Site Templates on your network.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		do_action('wu_settings_site_templates');

		/*
		 * Payment Gateways
		 * This section holds the Payment Gateways settings of the Multisite Ultimate Plugin.
		 */

		$this->add_section(
			'payment-gateways',
			[
				'title' => __('Payments', 'multisite-ultimate'),
				'desc'  => __('Payments', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-credit-card',
			]
		);

		$this->add_field(
			'payment-gateways',
			'main_header',
			[
				'title'           => __('Payment Settings', 'multisite-ultimate'),
				'desc'            => __('The following options affect how prices are displayed on the frontend, the backend and in reports.', 'multisite-ultimate'),
				'type'            => 'header',
				'show_as_submenu' => true,
			]
		);

		$this->add_field(
			'payment-gateways',
			'force_auto_renew',
			[
				'title'   => __('Force Auto-Renew', 'multisite-ultimate'),
				'desc'    => __('Enable this option if you want to make sure memberships are created with auto-renew activated whenever the selected gateway supports it. Disabling this option will show an auto-renew option during checkout.', 'multisite-ultimate'),
				'tooltip' => '',
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'payment-gateways',
			'allow_trial_without_payment_method',
			[
				'title'   => __('Allow Trials without Payment Method', 'multisite-ultimate'),
				'desc'    => __('By default, Multisite Ultimate asks customers to add a payment method on sign-up even if a trial period is present. Enable this option to only ask for a payment method when the trial period is over.', 'multisite-ultimate'),
				'tooltip' => '',
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$this->add_field(
			'payment-gateways',
			'attach_invoice_pdf',
			[
				'title'   => __('Send Invoice on Payment Confirmation', 'multisite-ultimate'),
				'desc'    => __('Enabling this option will attach a PDF invoice (marked paid) with the payment confirmation email. This option does not apply to the Manual Gateway, which sends invoices regardless of this option.', 'multisite-ultimate'),
				'tooltip' => __('The invoice files will be saved on the wp-content/uploads/wu-invoices folder.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'payment-gateways',
			'invoice_numbering_scheme',
			[
				'title'   => __('Invoice Numbering Scheme', 'multisite-ultimate'),
				'desc'    => __('What should Multisite Ultimate use as the invoice number?', 'multisite-ultimate'),
				'type'    => 'select',
				'default' => 'reference_code',
				'tooltip' => '',
				'options' => [
					'reference_code'    => __('Payment Reference Code', 'multisite-ultimate'),
					'sequential_number' => __('Sequential Number', 'multisite-ultimate'),
				],
			]
		);

		$this->add_field(
			'payment-gateways',
			'next_invoice_number',
			[
				'title'   => __('Next Invoice Number', 'multisite-ultimate'),
				'desc'    => __('This number will be used as the invoice number for the next invoice generated on the system. It is incremented by one every time a new invoice is created. You can change it and save it to reset the invoice sequential number to a specific value.', 'multisite-ultimate'),
				'type'    => 'number',
				'default' => '1',
				'min'     => 0,
				'require' => [
					'invoice_numbering_scheme' => 'sequential_number',
				],
			]
		);

		$this->add_field(
			'payment-gateways',
			'invoice_prefix',
			[
				'title'       => __('Invoice Number Prefix', 'multisite-ultimate'),
				'placeholder' => __('INV00', 'multisite-ultimate'),
			'desc'        => sprintf(__('Use %%YEAR%%, %%MONTH%%, and %%DAY%% to create a dynamic placeholder. E.g. %%YEAR%%-%%MONTH%%-INV will become %s.', 'multisite-ultimate'), date('Y') . '-' . date('m') . '-INV'), // phpcs:ignore
			'default'         => '',
			'type'            => 'text',
			'raw'             => true, // Necessary to prevent the removal of the %% tags.
			'require'         => [
				'invoice_numbering_scheme' => 'sequential_number',
				],
			]
		);

		$this->add_field(
			'payment-gateways',
			'gateways_header',
			[
				'title'           => __('Payment Gateways', 'multisite-ultimate'),
				'desc'            => __('Activate and configure the installed payment gateways in this section.', 'multisite-ultimate'),
				'type'            => 'header',
				'show_as_submenu' => true,
			]
		);

		do_action('wu_settings_payment_gateways');

		/*
		 * Emails
		 * This section holds the Email settings of the Multisite Ultimate Plugin.
		 */
		$this->add_section(
			'emails',
			[
				'title' => __('Emails', 'multisite-ultimate'),
				'desc'  => __('Emails', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-email',
			]
		);

		do_action('wu_settings_emails');

		/*
		 * Domain Mapping
		 * This section holds the Domain Mapping settings of the Multisite Ultimate Plugin.
		 */

		$this->add_section(
			'domain-mapping',
			[
				'title' => __('Domain Mapping', 'multisite-ultimate'),
				'desc'  => __('Domain Mapping', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-link',
			]
		);

		do_action('wu_settings_domain_mapping');

		/*
		 * Single Sign-on
		 * This section includes settings related to the single sign-on functionality
		 */

		$this->add_section(
			'sso',
			[
				'title' => __('Single Sign-On', 'multisite-ultimate'),
				'desc'  => __('Single Sign-On', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-add-user',
			]
		);

		do_action('wu_settings_sso');

		/*
		 * Integrations
		 * This section holds the Integrations settings of the Multisite Ultimate Plugin.
		 */

		$this->add_section(
			'integrations',
			[
				'title' => __('Integrations', 'multisite-ultimate'),
				'desc'  => __('Integrations', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-power-plug',
			]
		);

		$this->add_field(
			'integrations',
			'hosting_providers_header',
			[
				'title'           => __('Hosting or Panel Providers', 'multisite-ultimate'),
				'desc'            => __('Configure and manage the integration with your Hosting or Panel Provider.', 'multisite-ultimate'),
				'type'            => 'header',
				'show_as_submenu' => true,
			]
		);

		do_action('wu_settings_integrations');

		/*
		 * Other Options
		 * This section holds the Other Options settings of the Multisite Ultimate Plugin.
		 */

		$this->add_section(
			'other',
			[
				'title' => __('Other Options', 'multisite-ultimate'),
				'desc'  => __('Other Options', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-switch',
				'order' => 1000,
			]
		);

		$this->add_field(
			'other',
			'Other_header',
			[
				'title' => __('Miscellaneous', 'multisite-ultimate'),
				'desc'  => __('Other options that do not fit anywhere else.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$preview_image = wu_preview_image(wu_get_asset('settings/settings-hide-ui-tours.webp'));

		$this->add_field(
			'other',
			'hide_tours',
			[
				'title'   => __('Hide UI Tours', 'multisite-ultimate') . $preview_image,
				'desc'    => __('The UI tours showed by Multisite Ultimate should permanently hide themselves after being seen but if they persist for whatever reason, toggle this option to force them into their viewed state - which will prevent them from showing up again.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$preview_image_2 = wu_preview_image(wu_get_asset('settings/settings-disable-hover-to-zoom.webp'));

		$this->add_field(
			'other',
			'disable_image_zoom',
			[
				'title'   => __('Disable "Hover to Zoom"', 'multisite-ultimate') . $preview_image_2,
				'desc'    => __('By default, Multisite Ultimate adds a "hover to zoom" feature, allowing network admins to see larger version of site screenshots and other images across the UI in full-size when hovering over them. You can disable that feature here. Preview tags like the above are not affected.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		$this->add_field(
			'other',
			'error_reporting_header',
			[
				'title' => __('Logging', 'multisite-ultimate'),
				'desc'  => __('Log Multisite Ultimate data. This is useful for debugging purposes.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$this->add_field(
			'other',
			'error_logging_level',
			[
				'title'   => __('Logging Level', 'multisite-ultimate'),
				'desc'    => __('Select the level of logging you want to use.', 'multisite-ultimate'),
				'type'    => 'select',
				'default' => 'default',
				'options' => [
					'default'  => __('PHP Default', 'multisite-ultimate'),
					'disabled' => __('Disabled', 'multisite-ultimate'),
					'errors'   => __('Errors Only', 'multisite-ultimate'),
					'all'      => __('Everything', 'multisite-ultimate'),
				],
			]
		);

		$this->add_field(
			'other',
			'enable_error_reporting',
			[
				'title'   => __('Send Error Data to Multisite Ultimate Developers', 'multisite-ultimate'),
				'desc'    => __('With this option enabled, every time your installation runs into an error related to Multisite Ultimate, that error data will be sent to us. No sensitive data gets collected, only environmental stuff (e.g. if this is this is a subdomain network, etc).', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		$this->add_field(
			'other',
			'advanced_header',
			[
				'title' => __('Advanced Options', 'multisite-ultimate'),
				'desc'  => __('Change the plugin and wordpress behavior.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$plans = get_posts(
			[
				'post_type'   => 'wpultimo_plan',
				'numberposts' => 1,
			]
		);

		if ( ! empty($plans)) {
			$url = wu_network_admin_url('wp-ultimo-migration-alert');

			$title = __('Run Migration Again', 'multisite-ultimate') . sprintf(
				"<span class='wu-normal-case wu-block wu-text-xs wu-font-normal wu-mt-1'>%s</span>",
				__('Rerun the Migration Wizard if you experience data-loss after migrate.', 'multisite-ultimate')
			) . sprintf(
				"<span class='wu-normal-case wu-block wu-text-xs wu-font-normal wu-mt-2'>%s</span>",
				__('<b>Important:</b> This process can have unexpected behavior with your current Ultimo models.<br>We recommend that you create a backup before continue.', 'multisite-ultimate')
			);

			$html = sprintf('<a href="%s" class="button-primary">%s</a>', $url, __('Migrate', 'multisite-ultimate'));

			$this->add_field(
				'other',
				'run_migration',
				[
					'title' => $title,
					'type'  => 'note',
					'desc'  => $html,
				]
			);
		}

		if (function_exists('wu_get_security_mode_key')) {
			/**
			 *  Only allow security mode if we added sunrise.php functions
			 */
			$security_mode_key = '?wu_secure=' . wu_get_security_mode_key();

			$this->add_field(
				'other',
				'security_mode',
				[
					'title'   => __('Security Mode', 'multisite-ultimate'),
					// Translators: Placeholder adds the security mode key and current site url with query string
					'desc'    => sprintf(__('Only Multisite Ultimate and other must-use plugins will run on your WordPress install while this option is enabled.<div class="wu-mt-2"><b>Important:</b> Copy the following URL to disable security mode if something goes wrong and this page becomes unavailable:<code>%2$s</code></div>', 'multisite-ultimate'), $security_mode_key, get_site_url() . $security_mode_key),
					'type'    => 'toggle',
					'default' => 0,
				]
			);
		}

		$this->add_field(
			'other',
			'uninstall_wipe_tables',
			[
				'title'   => __('Remove Data on Uninstall', 'multisite-ultimate'),
				'desc'    => __('Remove all saved data for Multisite Ultimate when the plugin is uninstalled.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);
	}

	/**
	 * Tries to determine the location of the company based on the admin IP.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_default_company_country() {

		$geolocation = \WP_Ultimo\Geolocation::geolocate_ip('', true);

		return $geolocation['country'];
	}
}
