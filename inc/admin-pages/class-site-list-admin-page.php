<?php
/**
 * Multisite Ultimate Sites Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Sites Admin Page.
 */
class Site_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-sites';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

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
		'network_admin_menu' => 'wu_read_sites',
	];

	/**
	 * Register ajax forms that we use for sites.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms(): void {
		/*
		 * Edit/Add New Site
		 */
		wu_register_form(
			'add_new_site',
			[
				'render'     => [$this, 'render_add_new_site_modal'],
				'handler'    => [$this, 'handle_add_new_site_modal'],
				'capability' => 'wu_add_sites',
			]
		);

		/*
		 * Publish pending site.
		 */
		wu_register_form(
			'publish_pending_site',
			[
				'render'     => [$this, 'render_publish_pending_site_modal'],
				'handler'    => [$this, 'handle_publish_pending_site_modal'],
				'capability' => 'wu_publish_sites',
			]
		);

		add_action('wu_handle_bulk_action_form_site_screenshot', [$this, 'handle_bulk_screenshots'], 10, 3);
	}

	/**
	 * Handles the screenshot bulk action.
	 *
	 * @since 2.0.0
	 *
	 * @param string $action The action.
	 * @param string $model The model.
	 * @param array  $ids The ids list.
	 * @return void
	 */
	public function handle_bulk_screenshots($action, $model, $ids): void {

		$item_ids = array_filter($ids);

		foreach ($item_ids as $item_id) {
			wu_enqueue_async_action(
				'wu_async_take_screenshot',
				[
					'site_id' => $item_id,
				],
				'site'
			);
		}
	}

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_publish_pending_site_modal(): void {

		$membership = wu_get_membership(wu_request('membership_id'));

		if ( ! $membership) {
			return;
		}

		$fields = [
			'confirm'       => [
				'type'      => 'toggle',
				'title'     => __('Confirm Publication', 'multisite-ultimate'),
				'desc'      => __('This action can not be undone.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'confirmed',
				],
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => __('Publish', 'multisite-ultimate'),
				'placeholder'     => __('Publish', 'multisite-ultimate'),
				'value'           => 'publish',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => [
					'v-bind:disabled' => '!confirmed',
				],
			],
			'wu-when'       => [
				'type'  => 'hidden',
				'value' => base64_encode('init'),
			],
			'membership_id' => [
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'total-actions',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'true',
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
	 * Handles the deletion of line items.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_publish_pending_site_modal(): void {

		$membership = wu_get_membership(wu_request('membership_id'));

		if ( ! $membership) {
			wp_send_json_error(new \WP_Error('not-found', __('Pending site not found.', 'multisite-ultimate')));
		}

		$pending_site = $membership->get_pending_site();

		if ( ! is_a($pending_site, '\\WP_Ultimo\\Models\\Site')) {
			wp_send_json_error(new \WP_Error('not-found', __('Pending site not found.', 'multisite-ultimate')));
		}

		$pending_site->set_type('customer_owned');

		$saved = $pending_site->save();

		if (is_wp_error($saved)) {
			wp_send_json_error($saved);
		}

		$membership->delete_pending_site();

		/*
		 * Trigger event that marks the publication of a site.
		 */
		do_action('wu_pending_site_published', $pending_site, $membership);

		$redirect = current_user_can('wu_edit_sites') ? 'wp-ultimo-edit-site' : 'wp-ultimo-sites';

		wp_send_json_success(
			[
				'redirect_url' => wu_network_admin_url(
					$redirect,
					[
						'id' => $pending_site->get_id(),
					]
				),
			]
		);
	}

	/**
	 * Handles the add/edit of line items.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_add_new_site_modal() {

		global $current_site;

		$domain_type = wu_request('tab', is_subdomain_install() ? 'sub-domain' : 'sub-directory');

		if ('domain' === $domain_type) {
			$domain = wu_request('domain', '');
			$path   = '/';
		} else {
			$d      = wu_get_site_domain_and_path(wu_request('domain', ''));
			$domain = $d->domain;
			$path   = $d->path;
		}

		$atts = [
			'domain'                => $domain,
			'path'                  => $path,
			'title'                 => wu_request('title'),
			'type'                  => wu_request('type'),
			'template_id'           => wu_request('template_site', 0),
			'membership_id'         => wu_request('membership_id', false),
			'duplication_arguments' => [
				'copy_media' => wu_request('copy_media'),
			],
		];

		$site = wu_create_site($atts);

		if (is_wp_error($site)) {
			return wp_send_json_error($site);
		}

		if ($site->get_blog_id() === false) {
			$error = new \WP_Error('error', __('Something wrong happened.', 'multisite-ultimate'));

			return wp_send_json_error($error);
		}

		$redirect = current_user_can('wu_edit_sites') ? 'wp-ultimo-edit-site' : 'wp-ultimo-sites';

		wp_send_json_success(
			[
				'redirect_url' => wu_network_admin_url(
					$redirect,
					[
						'id'           => $site->get_id(),
						'wu-new-model' => 1,
						'updated'      => 1,
					]
				),
			]
		);
	}

	/**
	 * Renders the add/edit line items form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_new_site_modal(): void {

		global $current_site;

		$duplicate_id = wu_request('id');

		$site = wu_get_site($duplicate_id);

		$type          = 'site_template';
		$title         = '';
		$path          = 'mysite';
		$template_id   = '';
		$membership_id = '';

		/*
		 * Checks if this is a duplication process.
		 */
		if ($duplicate_id && $site) {

			// translators: the %s is the thing copied.
			$title         = sprintf(__('Copy of %s', 'multisite-ultimate'), $site->get_title());
			$path          = sprintf('%s%s', trim($site->get_path(), '/'), 'copy');
			$type          = $site->get_type();
			$template_id   = $duplicate_id;
			$membership_id = $site->get_membership_id();
		}

		$save_label = $duplicate_id ? __('Duplicate Site', 'multisite-ultimate') : __('Add new Site', 'multisite-ultimate');

		$options = [
			'sub-domain'    => __('Subdomain', 'multisite-ultimate'),
			'sub-directory' => __('Subdirectory', 'multisite-ultimate'),
			'domain'        => __('Domain', 'multisite-ultimate'),
		];

		/*
		 * Only keep the tab that correspond to the install type.
		 */
		if (is_subdomain_install()) {
			unset($options['sub-directory']);
		} else {
			unset($options['sub-domain']);
		}

		$fields = [
			'tab'           => [
				'type'              => 'tab-select',
				'wrapper_html_attr' => [
					'v-cloak' => 1,
				],
				'html_attr'         => [
					'v-model' => 'tab',
				],
				'options'           => $options,
			],
			'title'         => [
				'type'        => 'text',
				'title'       => __('Site Title', 'multisite-ultimate'),
				'placeholder' => __('New Network Site', 'multisite-ultimate'),
				'value'       => $title,
			],
			'domain_group'  => [
				'type'   => 'group',
				// translators: the %s is the site preview url.
				'desc'   => sprintf(__('The site URL will be: %s', 'multisite-ultimate'), '<span class="wu-font-mono">{{ tab === "domain" ? domain : ( tab === "sub-directory" ? scheme + base_url + domain : scheme + domain + "." + base_url ) }}</span>'),
				'fields' => [
					'domain' => [
						'type'            => 'text',
						'title'           => __('Site Domain/Path', 'multisite-ultimate'),
						'tooltip'         => __('Enter the complete domain for the site', 'multisite-ultimate'),
						'wrapper_classes' => 'wu-w-full',
						'html_attr'       => [
							'v-bind:placeholder' => 'tab === "domain" ? "mysite.com" : "mysite"',
							'v-on:input'         => 'domain = tab === "domain" ? $event.target.value.toLowerCase().replace(/[^a-z0-9-.-]+/g, "") : $event.target.value.toLowerCase().replace(/[^a-z0-9-]+/g, "")',
							'v-bind:value'       => 'domain',
						],
					],
				],
			],
			'type'          => [
				'type'        => 'select',
				'title'       => __('Site Type', 'multisite-ultimate'),
				'value'       => $type,
				'placeholder' => '',
				'options'     => [
					'default'        => __('Regular WP Site', 'multisite-ultimate'),
					'site_template'  => __('Site Template', 'multisite-ultimate'),
					'customer_owned' => __('Customer-Owned', 'multisite-ultimate'),
				],
				'html_attr'   => [
					'v-model' => 'type',
				],
			],
			'membership_id' => [
				'type'              => 'model',
				'title'             => __('Associated Membership', 'multisite-ultimate'),
				'placeholder'       => __('Search Membership...', 'multisite-ultimate'),
				'value'             => '',
				'tooltip'           => '',
				'wrapper_html_attr' => [
					'v-show' => "type === 'customer_owned'",
				],
				'html_attr'         => [
					'data-model'        => 'membership',
					'data-value-field'  => 'id',
					'data-label-field'  => 'reference_code',
					'data-search-field' => 'reference_code',
					'data-max-items'    => 1,
				],
			],
			'copy'          => [
				'type'      => 'toggle',
				'title'     => __('Copy Site', 'multisite-ultimate'),
				'desc'      => __('Select an existing site to use as a starting point.', 'multisite-ultimate'),
				'html_attr' => [
					'v-model' => 'copy',
				],
			],
			'template_site' => [
				'type'              => 'model',
				'title'             => __('Template Site', 'multisite-ultimate'),
				'placeholder'       => __('Search Sites...', 'multisite-ultimate'),
				'desc'              => __('The site selected will be copied and used as a starting point.', 'multisite-ultimate'),
				'value'             => $template_id,
				'html_attr'         => [
					'data-model'        => 'site',
					'data-selected'     => $site ? wp_json_encode($site->to_search_results()) : '',
					'data-value-field'  => 'blog_id',
					'data-label-field'  => 'title',
					'data-search-field' => 'title',
					'data-max-items'    => 1,
				],
				'wrapper_html_attr' => [
					'v-show' => 'copy',
				],
			],
			'copy_media'    => [
				'type'              => 'toggle',
				'title'             => __('Copy Media on Duplication', 'multisite-ultimate'),
				'desc'              => __('Copy media files from the template site on duplication. Disabling this can lead to broken images on the new site.', 'multisite-ultimate'),
				'value'             => true,
				'wrapper_html_attr' => [
					'v-show' => 'copy',
				],
			],
			'wu-when'       => [
				'type'  => 'hidden',
				'value' => base64_encode('init'),
			],
			'submit_button' => [
				'type'            => 'submit',
				'title'           => $save_label,
				'placeholder'     => $save_label,
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end wu-text-right',
				'html_attr'       => [
					'v-bind:disabled' => 'install_type !== tab && tab !== "domain"',
				],
			],
		];

		$d = wu_get_site_domain_and_path('replace');

		$form = new \WP_Ultimo\UI\Form(
			'add_new_site',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [
					'data-wu-app' => 'add_new_site',
					'data-state'  => wu_convert_to_state(
						[
							'tab'          => is_subdomain_install() ? 'sub-domain' : 'sub-directory',
							'install_type' => is_subdomain_install() ? 'sub-domain' : 'sub-directory',
							'membership'   => $membership_id,
							'type'         => $type,
							'copy'         => $site ? $site->get_id() : 0,
							'base_url'     => str_replace('replace.', '', (string) $d->domain) . '/',
							'scheme'       => is_ssl() ? 'https://' : 'http://',
							'domain'       => $path,
						]
					),
				],
			]
		);

		$form->render();
	}

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {}

	/**
	 * Returns an array with the labels for the edit page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function get_labels() {

		return [
			'deleted_message' => __('Site removed successfully.', 'multisite-ultimate'),
			'search_label'    => __('Search Site', 'multisite-ultimate'),
		];
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Sites', 'multisite-ultimate');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Sites', 'multisite-ultimate');
	}

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Sites', 'multisite-ultimate');
	}

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return [
			[
				'label'   => __('Add Site', 'multisite-ultimate'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_site'),
			],
		];
	}

	/**
	 * Loads the list table for this particular page.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\List_Tables\Base_List_Table
	 */
	public function table() {

		return new \WP_Ultimo\List_Tables\Site_List_Table();
	}
}
