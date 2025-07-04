<?php
/**
 * Basic Whitelabel
 *
 * @package WP_Ultimo
 * @subpackage Whitelabel
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the basic white-labeling of the WordPress admin interface.
 *
 * @since 2.0.0
 */
class Whitelabel {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Checks if the cache was initiated.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $init = false;

	/**
	 * Cached allowed domains.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_domains;

	/**
	 * Array of terms to search for.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $search = [];

	/**
	 * Array of terms to replace with. Must be a 1 to 1 relationship with the search array.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $replace = [];

	/**
	 * Adds the hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_action('init', [$this, 'add_settings'], 20);

		add_action('admin_init', [$this, 'clear_footer_texts']);

		add_action('init', [$this, 'hooks']);

		add_filter('gettext', [$this, 'replace_text'], 10, 3);
	}

	/**
	 * Add the necessary hooks when the feature is enabled.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks(): void {

		if (wu_get_setting('hide_wordpress_logo', true)) {
			add_action('wp_before_admin_bar_render', [$this, 'wp_logo_admin_bar_remove'], 0);

			add_action('wp_user_dashboard_setup', [$this, 'remove_dashboard_widgets'], 11);

			add_action('wp_dashboard_setup', [$this, 'remove_dashboard_widgets'], 11);
		}

		if (wu_get_setting('hide_sites_menu', true)) {
			add_action('network_admin_menu', [$this, 'remove_sites_admin_menu']);
		}
	}

	/**
	 * Loads the custom css file.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_styles(): void {

		WP_Ultimo()->scripts->register_style('wu-whitelabel', wu_get_asset('whitelabel.css', 'css'));

		wp_enqueue_style('wu-whitelabel');
	}

	/**
	 * Replaces the terms on the translated strings.
	 *
	 * @since 2.0.0
	 *
	 * @param string $translation The translation.
	 * @param string $text The original text before translation.
	 * @param string $domain The gettext domain.
	 * @return string
	 */
	public function replace_text($translation, $text, $domain) {

		if (null === $this->allowed_domains) {
			$this->allowed_domains = apply_filters(
				'wu_replace_text_allowed_domains',
				[
					'default',
					'wp-ultimo',
				]
			);
		}

		if ( ! in_array($domain, $this->allowed_domains, true)) {
			return $translation;
		}

		/**
		 * Prevent replacement when dealing with URLs.
		 *
		 * WordPress uses translation functions to allow for its own URLs to
		 * be changed to the different locales. This means that if we try to
		 * edit those URLs, we might break things. PHP 8.0 is specially
		 * unforgiving in that scenario.
		 *
		 * @since 2.1.0
		 */
		if (str_starts_with($translation, 'http')) {
			return $translation;
		}

		if (false === $this->init) {
			$search_and_replace = [];

			$site_plural = wu_get_setting('rename_site_plural');

			if ($site_plural) {
				$search_and_replace['sites'] = strtolower((string) $site_plural);
				$search_and_replace['Sites'] = ucfirst((string) $site_plural);
			}

			$site_singular = wu_get_setting('rename_site_singular');

			if ($site_singular) {
				$search_and_replace['site'] = strtolower((string) $site_singular);
				$search_and_replace['Site'] = ucfirst((string) $site_singular);
			}

			$wordpress = wu_get_setting('rename_wordpress');

			if ($wordpress) {
				$search_and_replace['wordpress'] = strtolower((string) $wordpress);
				$search_and_replace['WordPress'] = ucfirst((string) $wordpress);
				$search_and_replace['Wordpress'] = ucfirst((string) $wordpress);
				$search_and_replace['wordPress'] = ucfirst((string) $wordpress);
			}

			if ($search_and_replace) {
				$this->search  = array_keys($search_and_replace);
				$this->replace = array_values($search_and_replace);
			}

			$this->init = true;
		}

		if ( ! empty($this->search)) {
			return str_replace($this->search, $this->replace, $translation);
		}

		return $translation;
	}

	/**
	 * Adds the whitelabel options.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function add_settings(): void {

		wu_register_settings_section(
			'whitelabel',
			[
				'title' => __('Whitelabel', 'multisite-ultimate'),
				'desc'  => __('Basic Whitelabel', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-eye',
			]
		);

		wu_register_settings_field(
			'whitelabel',
			'whitelabel_header',
			[
				'title' => __('Whitelabel', 'multisite-ultimate'),
				'desc'  => __('Hide a couple specific WordPress elements and rename others.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		$preview_image = wu_preview_image(wu_get_asset('settings/settings-hide-wp-logo-preview.webp'));

		wu_register_settings_field(
			'whitelabel',
			'hide_wordpress_logo',
			[
				'title'   => __('Hide WordPress Logo', 'multisite-ultimate') . $preview_image,
				'desc'    => __('Hide the WordPress logo from the top-bar and replace the same logo on the My Sites top-bar item with a more generic icon.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		wu_register_settings_field(
			'whitelabel',
			'hide_sites_menu',
			[
				'title'   => __('Hide Sites Admin Menu', 'multisite-ultimate'),
				'desc'    => __('We recommend that you manage all of your sites using the Multisite Ultimate &rarr; Sites page. To avoid confusion, you can hide the default "Sites" item from the WordPress admin menu by toggling this option.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		wu_register_settings_field(
			'whitelabel',
			'rename_wordpress',
			[
				'title'       => __('Replace the word "WordPress"', 'multisite-ultimate'),
				'placeholder' => __('e.g. My App', 'multisite-ultimate'),
				'desc'        => __('Replace all occurrences of the word "WordPress" with a different word.', 'multisite-ultimate'),
				'type'        => 'text',
				'default'     => '',
			]
		);

		wu_register_settings_field(
			'whitelabel',
			'rename_site_singular',
			[
				'title'           => __('Replace the word "Site" (singular)', 'multisite-ultimate'),
				'placeholder'     => __('e.g. App', 'multisite-ultimate'),
				'desc'            => __('Replace all occurrences of the word "Site" with a different word.', 'multisite-ultimate'),
				'type'            => 'text',
				'default'         => '',
				'wrapper_classes' => 'wu-w-1/2',
			]
		);

		wu_register_settings_field(
			'whitelabel',
			'rename_site_plural',
			[
				'title'           => __('Replace the word "Sites" (plural)', 'multisite-ultimate'),
				'placeholder'     => __('e.g. Apps', 'multisite-ultimate'),
				'desc'            => __('Replace all occurrences of the word "Sites" with a different word.', 'multisite-ultimate'),
				'type'            => 'text',
				'default'         => '',
				'wrapper_classes' => 'wu-w-1/2',
			]
		);
	}

	/**
	 * Removes the WordPress original logo from the top-bar.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function wp_logo_admin_bar_remove(): void {

		global $wp_admin_bar;

		$wp_admin_bar->remove_menu('wp-logo');
		$this->enqueue_styles();
	}

	/**
	 * Remove the default widgets from the user panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function remove_dashboard_widgets(): void {

		global $wp_meta_boxes;

		unset($wp_meta_boxes['dashboard-user']['side']['core']['dashboard_quick_press']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_incoming_links']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_right_now']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_plugins']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_recent_drafts']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_recent_comments']);
		unset($wp_meta_boxes['dashboard-user']['side']['core']['dashboard_primary']);
		unset($wp_meta_boxes['dashboard-user']['side']['core']['dashboard_secondary']);
	}

	/**
	 * Removes the WordPress credits from the admin footer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function clear_footer_texts(): void {

		if (current_user_can('manage_network')) {
			return;
		}

		add_filter('admin_footer_text', '__return_empty_string', 11);

		add_filter('update_footer', '__return_empty_string', 11);
	}

	/**
	 * Remove the sites admin menu, if the option is selected.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function remove_sites_admin_menu(): void {

		global $menu;

		foreach ($menu as $i => $menu_item) {
			if ('sites.php' === $menu_item[2]) {
				unset($menu[ $i ]);
				break;
			}
		}
	}
}
