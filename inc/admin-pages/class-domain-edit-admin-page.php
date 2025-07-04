<?php
/**
 * Multisite Ultimate Domain Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Domains\Domain_Stage;

/**
 * Multisite Ultimate Domain Edit/Add New Admin Page.
 */
class Domain_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-domain';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Object ID being edited.
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id = 'domain';

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
	protected $highlight_menu_slug = 'wp-ultimo-domains';

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
		'network_admin_menu' => 'wu_edit_domains',
	];

	/**
	 * Register ajax forms.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {
		/*
		 * Adds the hooks to handle deletion.
		 */
		add_filter('wu_form_fields_delete_domain_modal', [$this, 'domain_extra_delete_fields'], 10, 2);

		add_action('wu_after_delete_domain_modal', [$this, 'domain_after_delete_actions']);
	}
	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {
		parent::register_scripts();

		wp_enqueue_script(
			'wu-dns-table',
			wu_get_asset('dns-table.js', 'js'),
			['jquery', 'wu-vue'],
			\WP_Ultimo::VERSION,
			[
				'in_footer' => true,
			]
		);
	}

	/**
	 * Adds the extra delete fields to the delete form.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $fields The original fields.
	 * @param object $domain The domain object.
	 * @return array
	 */
	public function domain_extra_delete_fields($fields, $domain) {

		$is_primary_domain = $domain->is_primary_domain();

		$has_other_domains = false;

		if ($is_primary_domain) {
			$other_domains = \WP_Ultimo\Models\Domain::get_by_site($domain->get_blog_id());

			$has_other_domains = is_countable($other_domains) ? count($other_domains) - 1 : false;
		}

		$custom_fields = [
			'set_domain_as_primary' => [
				'type'              => 'model',
				'title'             => __('Set another domain as primary', 'multisite-ultimate'),
				'html_attr'         => [
					'data-model'        => 'domain',
					'data-value-field'  => 'id',
					'data-label-field'  => 'domain',
					'data-search-field' => 'domain',
					'data-max-items'    => 1,
					'data-exclude'      => wp_json_encode([$domain->get_id()]),
					'data-include'      => wp_json_encode($domain->get_blog_id()),
				],
				'wrapper_html_attr' => [
					'v-if' => $is_primary_domain && $has_other_domains ? 'true' : 'false',
				],
			],
			'confirm'               => [
				'type'      => 'toggle',
				'title'     => __('Confirm Deletion', 'multisite-ultimate'),
				'desc'      => __('This action can not be undone.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'submit_button'         => [
				'type'            => 'submit',
				'title'           => __('Delete', 'multisite-ultimate'),
				'placeholder'     => __('Delete', 'multisite-ultimate'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!confirmed',
				],
			],
			'id'                    => [
				'type'  => 'hidden',
				'value' => $domain->get_id(),
			],
		];

		return array_merge($custom_fields, $fields);
	}

	/**
	 * Adds the primary domain handling to the domain deletion.
	 *
	 * @since 2.0.0
	 *
	 * @param object $domain The domain object.
	 * @return void
	 */
	public function domain_after_delete_actions($domain): void {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification happens in the form handler
		$new_primary_domain_name = wu_request('set_domain_as_primary');

		$new_primary_domain = wu_get_domain($new_primary_domain_name);

		if ($new_primary_domain) {
			$new_primary_domain->set_primary_domain(true);

			$new_primary_domain->save();
		}
	}

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets(): void {

		parent::register_widgets();

		$this->add_fields_widget(
			'domain-url',
			[
				'title'    => __('Domain URL', 'multisite-ultimate'),
				'position' => 'normal',
				'after'    => [$this, 'render_dns_widget'],
				'fields'   => [
					'domain' => [
						'type'          => 'text-display',
						'title'         => __('Domain', 'multisite-ultimate'),
						'tooltip'       => __('Editing an existing domain is not possible. If you want to make changes to this domain, first delete it, and then re-add the right domain.', 'multisite-ultimate'),
						'display_value' => '<span class="wu-text-sm wu-uppercase wu-font-mono">' . $this->get_object()->get_domain() . '</span> <a target="_blank" class="wu-no-underline" href="' . esc_url($this->get_object()->get_url()) . '"><span class="dashicons-wu-link1	"></span></a>',
					],
				],
			]
		);

		$this->add_tabs_widget(
			'options',
			[
				'title'    => __('Domain Options', 'multisite-ultimate'),
				'position' => 'normal',
				'sections' => [
					'general' => [
						'title'  => __('General', 'multisite-ultimate'),
						'desc'   => __('General options for the domain.', 'multisite-ultimate'),
						'icon'   => 'dashicons-wu-globe',
						'state'  => [
							'primary_domain' => $this->get_object()->is_primary_domain(),
						],
						'fields' => [
							'primary_domain' => [
								'type'      => 'toggle',
								'title'     => __('Is Primary Domain?', 'multisite-ultimate'),
								'desc'      => __('Set as the primary domain.', 'multisite-ultimate'),
								'tooltip'   => __('Setting this as the primary domain will remove any other domain mapping marked as the primary domain for this site.', 'multisite-ultimate'),
								'value'     => $this->get_object()->is_primary_domain(),
								'html_attr' => [
									'v-model' => 'primary_domain',
								],
							],
							'primary_note'   => [
								'type'              => 'note',
								'desc'              => __('By making this the primary domain, we will convert the previous primary domain for this site, if one exists, into an alias domain.', 'multisite-ultimate'),
								'wrapper_html_attr' => [
									'v-if' => "require('primary_domain', true)",
								],
							],
							'secure'         => [
								'type'  => 'toggle',
								'title' => __('Is Secure?', 'multisite-ultimate'),
								'desc'  => __('Force the load using HTTPS.', 'multisite-ultimate'),
								'value' => $this->get_object()->is_secure(),
							],
						],
					],
				],
			]
		);

		$this->add_list_table_widget(
			'sites',
			[
				'title'        => __('Linked Site', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Memberships_Site_List_Table(),
				'query_filter' => [$this, 'sites_query_filter'],
			]
		);

		add_meta_box('wp-ultimo-domain-log', __('Domain Test Log', 'multisite-ultimate'), [$this, 'render_log_widget'], get_current_screen()->id, 'normal', null);

		$this->add_list_table_widget(
			'events',
			[
				'title'        => __('Events', 'multisite-ultimate'),
				'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
				'query_filter' => [$this, 'query_filter'],
			]
		);

		$this->add_save_widget(
			'save',
			[
				'html_attr' => [
					'data-wu-app' => 'save',
					'data-state'  => wu_convert_to_state(
						[
							'stage' => $this->get_object()->get_stage(),
						]
					),
				],
				'fields'    => [
					'stage'   => [
						'type'              => 'select',
						'title'             => __('Stage', 'multisite-ultimate'),
						'placeholder'       => __('Select Stage', 'multisite-ultimate'),
						'desc'              => __('The stage in the checking lifecycle of this domain.', 'multisite-ultimate'),
						'options'           => Domain_Stage::to_array(),
						'value'             => $this->get_object()->get_stage(),
						'wrapper_html_attr' => [
							'v-cloak' => '1',
						],
						'html_attr'         => [
							'@change' => 'window.wu_basic.stage = $event.target.value',
							'v-model' => 'stage',
						],
					],
					'blog_id' => [
						'type'              => 'model',
						'title'             => __('Site', 'multisite-ultimate'),
						'placeholder'       => __('Search Site...', 'multisite-ultimate'),
						'desc'              => __('The target site of this domain.', 'multisite-ultimate'),
						'value'             => $this->get_object()->get_blog_id(),
						'tooltip'           => '',
						'html_attr'         => [
							'data-model'        => 'site',
							'data-value-field'  => 'blog_id',
							'data-label-field'  => 'title',
							'data-search-field' => 'title',
							'data-max-items'    => 1,
							'data-selected'     => $this->get_object()->get_site() ? wp_json_encode($this->get_object()->get_site()->to_search_results()) : '',
						],
						'wrapper_html_attr' => [
							'v-cloak' => '1',
						],
					],
				],
			]
		);

		$check_for_active_string = sprintf('%s.includes(stage)', wp_json_encode(\WP_Ultimo\Models\Domain::INACTIVE_STAGES));

		$this->add_fields_widget(
			'basic',
			[
				'title'     => __('Active', 'multisite-ultimate'),
				'html_attr' => [
					'data-wu-app' => 'basic',
					'data-state'  => wu_convert_to_state(
						[
							'stage' => $this->get_object()->get_stage(),
						]
					),
				],
				'fields'    => [
					'active' => [
						'type'              => 'toggle',
						'title'             => __('Active', 'multisite-ultimate'),
						'desc'              => __('Use this option to manually enable or disable this domain.', 'multisite-ultimate'),
						'value'             => $this->get_object()->is_active(),
						'html_attr'         => [
							'v-cloak'         => '1',
							'v-bind:disabled' => $check_for_active_string,
						],
						'wrapper_html_attr' => [
							'v-bind:class' => "$check_for_active_string ? 'wu-cursor-not-allowed wu-opacity-75' : ''",
						],

					],
					'note'   => [
						'type'              => 'note',
						'desc'              => __('This domain has a domain stage that forces it to be inactive. Change the status to Ready or Ready (without SSL) to be able to control the active status directly.', 'multisite-ultimate'),
						'classes'           => 'wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
						'wrapper_html_attr' => [
							'v-show'  => $check_for_active_string,
							'v-cloak' => '1',
						],
					],
				],
			]
		);
	}

	/**
	 * Renders the DNS widget
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_dns_widget(): void {

		wu_get_template(
			'domain/dns-table',
			[
				'domain' => $this->get_object(),
			]
		);
	}

	/**
	 * Renders the DNS widget
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_log_widget(): void {

		wu_get_template(
			'domain/log',
			[
				'domain'   => $this->get_object(),
				'log_path' => \WP_Ultimo\Logger::get_logs_folder(),
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

		return $this->edit ? __('Edit Domain', 'multisite-ultimate') : __('Add new Domain', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Domain', 'multisite-ultimate');
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
			'edit_label'          => __('Edit Domain', 'multisite-ultimate'),
			'add_new_label'       => __('Add new Domain', 'multisite-ultimate'),
			'updated_message'     => __('Domain updated with success!', 'multisite-ultimate'),
			'title_placeholder'   => __('Enter Domain', 'multisite-ultimate'),
			'title_description'   => '',
			'save_button_label'   => __('Save Domain', 'multisite-ultimate'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Domain', 'multisite-ultimate'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'multisite-ultimate'),
		];
	}

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function query_filter($args) {

		$extra_args = [
			'object_type' => 'domain',
			'object_id'   => absint($this->get_object()->get_id()),
		];

		return array_merge($args, $extra_args);
	}

	/**
	 * Filters the list table to return only relevant sites.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function sites_query_filter($args) {

		$args['blog_id'] = $this->get_object()->get_site_id();

		return $args;
	}

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Domain
	 */
	public function get_object() {

		if (null !== $this->object) {
			return $this->object;
		}

		$item_id = wu_request('id', 0);

		$item = wu_get_domain($item_id);

		if ( ! $item) {
			wp_safe_redirect(wu_network_admin_url('wp-ultimo-domains'));

			exit;
		}

		$this->object = $item;

		return $this->object;
	}

	/**
	 * Domains have titles.
	 *
	 * @since 2.0.0
	 */
	public function has_title(): bool {

		return false;
	}

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save(): void {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification happens in parent::handle_save()
		if ( ! wu_request('primary_domain')) {
			$_POST['primary_domain'] = false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification happens in parent::handle_save()
		if ( ! wu_request('active')) {
			$_POST['active'] = false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification happens in parent::handle_save()
		if ( ! wu_request('secure')) {
			$_POST['secure'] = false;
		}

		wu_enqueue_async_action('wu_async_process_domain_stage', ['domain_id' => $this->get_object()->get_id()], 'domain');

		parent::handle_save();
	}
}
