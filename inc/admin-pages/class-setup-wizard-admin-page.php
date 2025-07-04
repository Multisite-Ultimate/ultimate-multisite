<?php
/**
 * Multisite Ultimate Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Installers\Migrator;
use WP_Ultimo\Installers\Core_Installer;
use WP_Ultimo\Installers\Default_Content_Installer;
use WP_Ultimo\Logger;
use WP_Ultimo\Requirements;

/**
 * Multisite Ultimate Dashboard Admin Page.
 */
class Setup_Wizard_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-setup';

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
		'network_admin_menu' => 'manage_network',
	];

	/**
	 * Is this an old install migrating.
	 *
	 * @since 2.0.0
	 * @var bool|null
	 */
	private ?bool $is_migration = null;

	/**
	 * The integration object, if it exists.
	 *
	 * @since 2.2.0
	 * @var mixed
	 */
	protected $integration;

	/**
	 * Overrides original construct method.
	 *
	 * We need to override the construct method to make sure
	 * we make the necessary changes to the Wizard page when it's
	 * being run for the first time.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function __construct() {

		if ( ! $this->is_core_loaded()) {
			require_once wu_path('inc/functions/documentation.php');

			/**
			 * Loads the necessary apis.
			 */
			WP_Ultimo()->load_public_apis();

			$this->highlight_menu_slug = false;

			$this->type = 'menu';

			$this->position = 10_101_010;

			$this->menu_icon = 'dashicons-wu-wp-ultimo';

			add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
		}

		parent::__construct();

		add_action('admin_action_download_migration_logs', [$this, 'download_migration_logs']);

		/*
		 * Serve the installers
		 */
		add_action('wp_ajax_wu_setup_install', [$this, 'setup_install']);

		/*
		 * Load installers
		 */
		add_action('wu_handle_ajax_installers', [Core_Installer::get_instance(), 'handle'], 10, 3);
		add_action('wu_handle_ajax_installers', [Default_Content_Installer::get_instance(), 'handle'], 10, 3);
		add_action('wu_handle_ajax_installers', [Migrator::get_instance(), 'handle'], 10, 3);

		/*
		 * Redirect on activation
		 */
		add_action('wu_activation', [$this, 'redirect_to_wizard']);
	}

	/**
	 * Download the migration logs.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function download_migration_logs(): void {

		check_admin_referer('download_migration_logs', 'nonce');

		$path = Logger::get_logs_folder();

		$file = $path . Migrator::LOG_FILE_NAME . '.log';

		$file_name = str_replace($path, '', $file);

		header('Content-Type: application/octet-stream');

		header("Content-Disposition: attachment; filename=$file_name");

		header('Pragma: no-cache');

		readfile($file);

		exit;
	}

	/**
	 * Loads the extra elements we need on the wizard itself.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function page_loaded(): void {

		parent::page_loaded();

		$this->set_settings();
	}

	/**
	 * Checks if this is a migration or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_migration() {

		if (null === $this->is_migration) {
			$this->is_migration = Migrator::is_legacy_network();
		}

		return $this->is_migration;
	}

	/**
	 * Adds missing setup from settings when Multisite Ultimate is not fully loaded.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function set_settings(): void {

		WP_Ultimo()->settings->default_sections();
	}

	/**
	 * Redirects to the wizard, if we need to.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function redirect_to_wizard(): void {

		if ( ! Requirements::run_setup() && wu_request('page') !== 'wp-ultimo-setup') {
			wp_safe_redirect(wu_network_admin_url('wp-ultimo-setup'));

			exit;
		}
	}

	/**
	 * Handles the ajax actions for installers and migrators.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_install(): void {

		global $wpdb;

		if ( ! current_user_can('manage_network')) {
			wp_send_json_error(new \WP_Error('not-allowed', __('Permission denied.', 'multisite-ultimate')));

			exit;
		}

		/*
		 * Load tables.
		 */
		WP_Ultimo()->tables = \WP_Ultimo\Loaders\Table_Loader::get_instance();

		$installer = wu_request('installer', '');

		/*
		 * Installers should hook into this filter
		 */
		$status = apply_filters('wu_handle_ajax_installers', true, $installer, $this);

		if (is_wp_error($status)) {
			wp_send_json_error($status);
		}

		wp_send_json_success();
	}

	/**
	 * Check if the core was loaded.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_core_loaded() {

		return Requirements::met() && Requirements::run_setup();
	}

	/**
	 * Returns the logo for the wizard.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('logo.webp', 'img');
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title(): string {

		return sprintf(__('Installation', 'multisite-ultimate'));
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return WP_Ultimo()->is_loaded() ? __('Multisite Ultimate Install', 'multisite-ultimate') : __('Multisite Ultimate', 'multisite-ultimate');
	}

	/**
	 * Returns the sections for this Wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		$sections = [
			'welcome'      => [
				'title'       => __('Welcome', 'multisite-ultimate'),
				'description' => implode(
					'<br><br>',
					[
						__('...and thanks for choosing Multisite Ultimate!', 'multisite-ultimate'),
						__('This quick setup wizard will make sure your server is correctly setup, help you configure your new network, and migrate data from previous Multisite Ultimate versions if necessary.', 'multisite-ultimate'),
						__('You will also have the option of importing default content. It should take 10 minutes or less!', 'multisite-ultimate'),
					]
				),
				'next_label'  => __('Get Started &rarr;', 'multisite-ultimate'),
				'back'        => false,
			],
			'checks'       => [
				'title'       => __('Pre-install Checks', 'multisite-ultimate'),
				'description' => __('Now it is time to see if this machine has what it takes to run Multisite Ultimate well!', 'multisite-ultimate'),
				'next_label'  => Requirements::met() ? __('Go to the Next Step &rarr;', 'multisite-ultimate') : __('Check Again', 'multisite-ultimate'),
				'handler'     => [$this, 'handle_checks'],
				'back'        => false,
				'fields'      => [
					'requirements' => [
						'type' => 'note',
						'desc' => [$this, 'renders_requirements_table'],
					],
				],
			],
			'installation' => [
				'title'       => __('Installation', 'multisite-ultimate'),
				'description' => __('Now, let\'s update your database and install the Sunrise.php file, which are necessary for the correct functioning of Multisite Ultimate.', 'multisite-ultimate'),
				'next_label'  => Core_Installer::get_instance()->all_done() ? __('Go to the Next Step &rarr;', 'multisite-ultimate') : __('Install', 'multisite-ultimate'),
				'fields'      => [
					'terms' => [
						'type' => 'note',
						'desc' => fn() => $this->render_installation_steps(Core_Installer::get_instance()->get_steps(), false),
					],
				],
			],
		];

		/*
		 * In case of migrations, add different sections.
		 */
		if ($this->is_migration()) {
			$dry_run = wu_request('dry-run', true);

			$next = true;

			$errors = Migrator::get_instance()->get_errors();

			$back_traces = Migrator::get_instance()->get_back_traces();

			$next_label = __('Migrate!', 'multisite-ultimate');

			$description = __('No errors found during dry run! Now it is time to actually migrate! <br><br><strong>We strongly recommend creating a backup of your database before moving forward with the migration.</strong>', 'multisite-ultimate');

			if ($dry_run) {
				$next_label = __('Run Check', 'multisite-ultimate');

				$description = __('It seems that you were running Multisite Ultimate 1.X on this network. This migrator will convert the data from the old version to the new one.', 'multisite-ultimate') . '<br><br>' . __('First, let\'s run a test migration to see if we can spot any potential errors.', 'multisite-ultimate');
			}

			$fields = [
				'migration' => [
					'type' => 'note',
					'desc' => fn() => $this->render_installation_steps(Migrator::get_instance()->get_steps(), false),
				],
			];

			if ($errors) {
				$subject = 'Errors on migrating my network';

				$user = wp_get_current_user();

				$message_lines = [
					'Hi there,',
					sprintf('My name is %s.', $user->display_name),
					'I tried to migrate my network from version 1 to version 2, but was not able to do it successfully...',
					'Here are the error messages I got:',
					sprintf('```%s%s%s```', PHP_EOL, implode(PHP_EOL, $errors), PHP_EOL),
					sprintf('```%s%s%s```', PHP_EOL, $back_traces ? implode(PHP_EOL, $back_traces) : 'No backtraces found.', PHP_EOL),
					'Kind regards.',
				];

				$message = implode(PHP_EOL . PHP_EOL, $message_lines);

				$description = __('The dry run test detected issues during the test migration. Please, <a class="wu-trigger-support" href="#">contact our support team</a> to get help migrating from 1.X to version 2.', 'multisite-ultimate');

				$next = true;

				$next_label = __('Try Again!', 'multisite-ultimate');

				$error_list = '<strong>' . __('List of errors detected:', 'multisite-ultimate') . '</strong><br><br>';

				$errors[] = sprintf(
					'<br><a href="%2$s" class="wu-no-underline wu-text-red-500 wu-font-bold"><span class="dashicons-wu-download wu-mr-2"></span>%1$s</a>',
					__('Download migration error log', 'multisite-ultimate'),
					add_query_arg(
						[
							'action' => 'download_migration_logs',
							'nonce'  => wp_create_nonce('download_migration_logs'),
						],
						network_admin_url('admin.php')
					)
				);

				$errors[] = sprintf(
					'<br><a href="%2$s" class="wu-no-underline wu-text-red-500 wu-font-bold"><span class="dashicons-wu-back-in-time wu-mr-2"></span>%1$s</a>',
					__('Rollback to version 1.10.13', 'multisite-ultimate'),
					add_query_arg(
						[
							'page'    => 'wp-ultimo-rollback',
							'version' => '1.10.13',
							'type'    => 'select-version',
						],
						network_admin_url('admin.php')
					)
				);

				$error_list .= implode('<br>', $errors);

				$fields = array_merge(
					[
						'errors' => [
							'type'    => 'note',
							'classes' => 'wu-flex-grow',
							'desc'    => function () use ($error_list) {

								/** Reset errors */
								Migrator::get_instance()->session->set('errors', []);

								return sprintf('<div class="wu-mt-0 wu-p-4 wu-bg-red-100 wu-border wu-border-solid wu-border-red-200 wu-rounded-sm wu-text-red-500">%s</div>', $error_list);
							},
						],
					],
					$fields
				);
			}

			$sections['migration'] = [
				'title'       => __('Migration', 'multisite-ultimate'),
				'description' => $description,
				'next_label'  => $next_label,
				'skip'        => false,
				'next'        => $next,
				'handler'     => [$this, 'handle_migration'],
				'fields'      => $fields,
			];
		} else {
			$sections['your-company'] = [
				'title'       => __('Your Company', 'multisite-ultimate'),
				'description' => __('Before we move on, let\'s configure the basic settings of your network, shall we?', 'multisite-ultimate'),
				'handler'     => [$this, 'handle_save_settings'],
				'fields'      => [$this, 'get_general_settings'],
			];

			$sections['defaults'] = [
				'title'       => __('Default Content', 'multisite-ultimate'),
				'description' => __('Starting from scratch can be scarry, specially when first starting out. In this step, you can create default content to have a starting point for your network. Everything can be customized later.', 'multisite-ultimate'),
				'next_label'  => Default_Content_Installer::get_instance()->all_done() ? __('Go to the Next Step &rarr;', 'multisite-ultimate') : __('Install', 'multisite-ultimate'),
				'fields'      => [
					'terms' => [
						'type' => 'note',
						'desc' => fn() => $this->render_installation_steps(Default_Content_Installer::get_instance()->get_steps()),
					],
				],
			];
		}

		$sections['done'] = [
			'title' => __('Ready!', 'multisite-ultimate'),
			'view'  => [$this, 'section_ready'],
		];

		/**
		 * Allow developers to add additional setup wizard steps.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $sections Current sections.
		 * @param bool   $is_migration If this is a migration or not.
		 * @param object $this The current instance.
		 * @return array
		 */
		return apply_filters('wu_setup_wizard', $sections, $this->is_migration(), $this);
	}

	/**
	 * Returns the general settings to add to the wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_general_settings() {
		/*
		 * Get the general fields for company and currency.
		 */
		$general_fields = \WP_Ultimo\Settings::get_instance()->get_section('general')['fields'];

		/*
		 * Unset a couple of undesired settings
		 */
		$fields_to_unset = [
			'error_reporting_header',
			'enable_error_reporting',
			'advanced_header',
			'uninstall_wipe_tables',
		];

		foreach ($fields_to_unset as $field_to_unset) {
			unset($general_fields[ $field_to_unset ]);
		}

		// Adds a fake first field to bypass some styling issues with the top-border
		$fake_field = [
			[
				'type' => 'hidden',
			],
		];

		$fields = array_merge($fake_field, $general_fields);

		return apply_filters('wu_setup_get_general_settings', $fields);
	}

	/**
	 * Returns the payment settings to add to the setup wizard.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_payment_settings() {
		/*
		 * Get the general fields for company and currency.
		 */
		$payment_fields = \WP_Ultimo\Settings::get_instance()->get_section('payment-gateways')['fields'];

		$fields_to_unset = [
			'main_header',
		];

		foreach ($fields_to_unset as $field_to_unset) {
			unset($payment_fields[ $field_to_unset ]);
		}

		$fields = array_merge($payment_fields);

		return apply_filters('wu_setup_get_payment_settings', $fields);
	}

	/**
	 * Render the installation steps table.
	 *
	 * @since 2.0.0
	 *
	 * @param array   $steps The list of steps.
	 * @param boolean $checks If we should add the checkbox for selection or not.
	 * @return string
	 */
	public function render_installation_steps($steps, $checks = true) {

		wp_localize_script('wu-setup-wizard', 'wu_setup', $steps);

		wp_localize_script(
			'wu-setup-wizard',
			'wu_setup_settings',
			[
				'dry_run'               => wu_request('dry-run', true),
				'generic_error_message' => __('A server error happened while processing this item.', 'multisite-ultimate'),
			]
		);

		wp_enqueue_script('wu-setup-wizard');

		return wu_get_template_contents(
			'wizards/setup/installation_steps',
			[
				'page'   => $this,
				'steps'  => $steps,
				'checks' => $checks,
			]
		);
	}

	/**
	 * Renders the terms of support.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function _terms_of_support() {

		return wu_get_template_contents('wizards/setup/support_terms');
	}

	/**
	 * Renders the requirements tables.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function renders_requirements_table() {

		global $wp_version;

		$requirements = [
			'php'       => [
				'name'                => __('PHP', 'multisite-ultimate'),
				'help'                => wu_get_documentation_url('wp-ultimo-requirements'),
				'required_version'    => Requirements::$php_version,
				'recommended_version' => Requirements::$php_recommended_version,
				'installed_version'   => phpversion(),
				'pass_requirements'   => version_compare(phpversion(), Requirements::$php_version, '>='),
				'pass_recommendation' => version_compare(phpversion(), Requirements::$php_recommended_version, '>='),
			],
			'wordpress' => [
				'name'                => __('WordPress', 'multisite-ultimate'),
				'help'                => wu_get_documentation_url('wp-ultimo-requirements'),
				'required_version'    => Requirements::$wp_version,
				'recommended_version' => Requirements::$wp_recommended_version,
				'installed_version'   => $wp_version,
				'pass_requirements'   => version_compare(phpversion(), Requirements::$wp_version, '>='),
				'pass_recommendation' => version_compare(phpversion(), Requirements::$wp_recommended_version, '>='),
			],
		];

		$plugin_requirements = [
			'multisite' => [
				'name'              => __('WordPress Multisite', 'multisite-ultimate'),
				'help'              => wu_get_documentation_url('wp-ultimo-requirements'),
				'condition'         => __('Installed & Activated', 'multisite-ultimate'),
				'pass_requirements' => is_multisite(),
			],
			'wp-ultimo' => [
				'name'              => __('Multisite Ultimate', 'multisite-ultimate'),
				'help'              => wu_get_documentation_url('wp-ultimo-requirements'),
				'condition'         => apply_filters('wp_ultimo_skip_network_active_check', false) ? __('Bypassed via filter', 'multisite-ultimate') : __('Network Activated', 'multisite-ultimate'),
				'pass_requirements' => Requirements::is_network_active(),
			],
			'wp-cron'   => [
				'name'              => __('WordPress Cron', 'multisite-ultimate'),
				'help'              => wu_get_documentation_url('wp-ultimo-requirements'),
				'condition'         => __('Activated', 'multisite-ultimate'),
				'pass_requirements' => Requirements::check_wp_cron(),
			],
		];

		return wu_get_template_contents(
			'wizards/setup/requirements_table',
			[
				'requirements'        => $requirements,
				'plugin_requirements' => $plugin_requirements,
			]
		);
	}

	/**
	 * Displays the content of the final section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function section_ready(): void {

		update_network_option(null, 'wu_setup_finished', true);

		/**
		 * Mark the migration as done, if this was a migration.
		 *
		 * @since 2.0.7
		 */
		if (Migrator::is_legacy_network()) {
			update_network_option(null, 'wu_is_migration_done', true);
		}

		wu_enqueue_async_action(
			'wu_async_take_screenshot',
			[
				'site_id' => wu_get_main_site_id(),
			],
			'site'
		);

		wu_get_template(
			'wizards/setup/ready',
			[
				'screen' => get_current_screen(),
				'page'   => $this,
			]
		);
	}

	/**
	 * Handles the requirements check.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_checks(): void {

		if ( Requirements::met() === false) {
			wp_safe_redirect(add_query_arg());

			exit;
		}

		wp_safe_redirect($this->get_next_section_link());

		exit;
	}

	/**
	 * Handles the saving of setting steps.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save_settings(): void {

		$this->set_settings();

		$step = wu_request('step');

		if ('your-company' === $step) {
			$fields_to_save = $this->get_general_settings();
		} elseif ('payment-gateways' === $step) {
			$fields_to_save = $this->get_payment_settings();
		} else {
			return;
		}
		// Nonce check handled in calling method.
		$settings_to_save = array_intersect_key($_POST, $fields_to_save); // phpcs:ignore WordPress.Security.NonceVerification

		\WP_Ultimo\Settings::get_instance()->save_settings($settings_to_save);

		wp_safe_redirect($this->get_next_section_link());

		exit;
	}

	/**
	 * Handles the migration step and checks for a test run.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_migration(): void {

		$dry_run = wu_request('dry-run', true);

		$errors = Migrator::get_instance()->get_errors();

		if ($dry_run) {
			$url = add_query_arg('dry-run', empty($errors) ? 0 : 1);
		} elseif (empty($errors)) {
				$url = remove_query_arg('dry-run', $this->get_next_section_link());
		} else {
			$url = add_query_arg('dry-run', 0);
		}

		wp_safe_redirect($url);

		exit;
	}

	/**
	 * Handles the testing of a given configuration.
	 *
	 * @todo Move Vue to a scripts management class.
	 * @since 2.0.0
	 * @return void
	 */
	public function section_test(): void {

		wp_enqueue_script('wu-vue');

		wu_get_template(
			'wizards/host-integrations/test',
			[
				'screen'      => get_current_screen(),
				'page'        => $this,
				'integration' => $this->integration,
			]
		);
	}

	/**
	 * Adds the necessary missing scripts if Multisite Ultimate was not loaded.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		if (WP_Ultimo()->is_loaded() === false) {
			wp_enqueue_style('wu-styling', wu_get_asset('framework.css', 'css'), false, wu_get_version());

			wp_enqueue_style('wu-admin', wu_get_asset('admin.css', 'css'), ['wu-styling'], wu_get_version());

			/*
			* Adds tipTip
			*/
			wp_enqueue_script('wu-tiptip', wu_get_asset('lib/tiptip.js', 'js'), [], \WP_Ultimo::VERSION, true);

			/*
			* Adds jQueryBlockUI
			*/
			wp_enqueue_script('wu-block-ui', wu_get_asset('lib/jquery.blockUI.js', 'js'), ['jquery'], \WP_Ultimo::VERSION, true);

			wp_register_script('wu-fields', wu_get_asset('fields.js', 'js'), ['jquery'], \WP_Ultimo::VERSION, true);

			/*
			* Localize components
			*/
			wp_localize_script(
				'wu-fields',
				'wu_fields',
				[
					'l10n' => [
						'image_picker_title'       => __('Select an Image.', 'multisite-ultimate'),
						'image_picker_button_text' => __('Use this image', 'multisite-ultimate'),
					],
				]
			);

			wp_register_script('wu-functions', wu_get_asset('functions.js', 'js'), ['jquery'], \WP_Ultimo::VERSION, true);

			wp_register_script('wubox', wu_get_asset('wubox.js', 'js'), ['wu-functions'], \WP_Ultimo::VERSION, true);

			wp_localize_script(
				'wubox',
				'wuboxL10n',
				[
					'next'             => __('Next &gt;'),
					'prev'             => __('&lt; Prev'),
					'image'            => __('Image'),
					'of'               => __('of'),
					'close'            => __('Close'),
					'noiframes'        => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
					'loadingAnimation' => includes_url('js/thickbox/loadingAnimation.gif'),
				]
			);

			wp_add_inline_script('wu-setup-wizard-extra', 'document.addEventListener("DOMContentLoaded", () => wu_initialize_imagepicker());', 'after');
		}

		wp_enqueue_script('wu-setup-wizard-extra', wu_get_asset('setup-wizard-extra.js', 'js'), ['jquery', 'wu-fields', 'wu-functions', 'wubox'], wu_get_version(), true);

		wp_enqueue_media();

		wp_register_script('wu-setup-wizard', wu_get_asset('setup-wizard.js', 'js'), ['jquery'], wu_get_version(), true);

		wp_add_inline_style(
			'wu-admin',
			sprintf(
				'
		body.wu-page-wp-ultimo-setup #wpwrap {
			background: url("%s") right bottom no-repeat;
			background-size: 90%%;
		}',
				wu_get_asset('bg-setup.webp', 'img')
			)
		);
	}
}
