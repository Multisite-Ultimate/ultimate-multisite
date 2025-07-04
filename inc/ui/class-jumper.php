<?php
/**
 * Adds the Jumper UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Jumper UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Jumper {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * GET slug to force the jumper menu reset/fetching.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $reset_slug = 'wu-rebuild-jumper';

	/**
	 * Key to save the menu list on the transient database.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $transient_key = 'wu-jumper-menu-list';

	/**
	 * Element construct.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_action('init', [$this, 'add_settings'], 20);

		add_action('init', [$this, 'load_jumper']);
	}

	/**
	 * Checks if we should add the jumper or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	protected function is_jumper_enabled() {

		return apply_filters('wu_is_jumper_enabled', wu_get_setting('enable_jumper', true) && current_user_can('manage_network'));
	}

	/**
	 * Adds the Jumper trigger to the admin top pages.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Admin_Pages\Base_Admin_Page $page The current page.
	 * @return void
	 */
	public function add_jumper_trigger($page): void {

		wu_get_template(
			'ui/jumper-trigger',
			[
				'page'   => $page,
				'jumper' => $this,
			]
		);
	}

	/**
	 * Loads the necessary elements to display the Jumper.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_jumper(): void {

		if ($this->is_jumper_enabled() && is_admin()) {
			add_action('wu_header_right', [$this, 'add_jumper_trigger']);

			add_action('admin_init', [$this, 'rebuild_menu']);

			add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

			add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);

			add_action('admin_footer', [$this, 'output']);

			add_filter('update_footer', [$this, 'add_jumper_footer_message'], 200);

			add_action('wu_after_save_settings', [$this, 'clear_jump_cache_on_save']);

			add_filter('wu_link_list', [$this, 'add_wp_ultimo_extra_links']);

			add_filter('wu_link_list', [$this, 'add_user_custom_links']);
		}
	}

	/**
	 * Clear the jumper menu cache on settings save
	 *
	 * We need to do this to make sure that we clear the menu when the admin
	 * adds a new custom menu item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings Settings being saved.
	 * @return void
	 */
	public function clear_jump_cache_on_save($settings): void {

		if (isset($settings['jumper_custom_links'])) {
			delete_site_transient($this->transient_key);
		}
	}

	/**
	 * Rebuilds the jumper menu via a trigger URL.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function rebuild_menu(): void {

		if (isset($_GET[ $this->reset_slug ]) && isset($_GET['nonce']) && current_user_can('manage_network') && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'reset_password')) {
			delete_site_transient($this->transient_key);

			wp_safe_redirect(network_admin_url());

			exit;
		}
	}

	/**
	 * Retrieves the custom links added by the super admin
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_user_custom_links() {

		$treated_lines = [];

		$saved_links = wu_get_setting('jumper_custom_links');

		$lines = explode(PHP_EOL, (string) $saved_links);

		foreach ($lines as $line) {
			$link_elements = explode(':', $line, 2);

			if (count($link_elements) === 2) {
				$title = trim($link_elements[1]);

				$treated_lines[ $title ] = trim($link_elements[0]);
			}
		}

		return $treated_lines;
	}

	/**
	 * Add the custom links to the Jumper menu
	 *
	 * @since 2.0.0
	 *
	 * @param array $links Jumper links already saved.
	 * @return array
	 */
	public function add_user_custom_links($links) {

		$custom_links = $this->get_user_custom_links();

		if ( ! empty($custom_links)) {
			$links[ __('Custom Links', 'multisite-ultimate') ] = $custom_links;
		}

		return $links;
	}

	/**
	 * Add Multisite Ultimate settings links to the Jumper menu.
	 *
	 * @since 2.0.0
	 *
	 * @param array $links Multisite Ultimate settings array.
	 * @return array
	 */
	public function add_wp_ultimo_extra_links($links) {

		if (isset($links['WP Ultimo'])) {
			$settings_tabs = [
				'general'        => __('General', 'multisite-ultimate'),
				'network'        => __('Network Settings', 'multisite-ultimate'),
				'gateways'       => __('Payment Gateways', 'multisite-ultimate'),
				'domain_mapping' => __('Domain Mapping & SSL', 'multisite-ultimate'),
				'emails'         => __('Emails', 'multisite-ultimate'),
				'styling'        => __('Styling', 'multisite-ultimate'),
				'tools'          => __('Tools', 'multisite-ultimate'),
				'advanced'       => __('Advanced', 'multisite-ultimate'),
				'activation'     => __('Activation & Support', 'multisite-ultimate'),
			];

			foreach ($settings_tabs as $tab => $tab_label) {
				$url = network_admin_url('admin.php?page=wp-ultimo-settings&wu-tab=' . $tab);

				// translators: The placeholder represents the title of the Settings tab.
				$links['WP Ultimo'][ $url ] = sprintf(__('Settings: %s', 'multisite-ultimate'), $tab_label);
			}

			$links['WP Ultimo'][ network_admin_url('admin.php?page=wp-ultimo-settings&wu-tab=tools') ] = __('Settings: Webhooks', 'multisite-ultimate');

			$links['WP Ultimo'][ network_admin_url('admin.php?page=wp-ultimo-system-info&wu-tab=logs') ] = __('System Info: Logs', 'multisite-ultimate');

			/**
			 * Adds Main Site Dashboard
			 */
			if (isset($links[ __('Sites') ])) {
				$main_site_url = get_admin_url(get_current_site()->blog_id);

				$links[ __('Sites') ][ $main_site_url ] = __('Main Site Dashboard', 'multisite-ultimate');
			}
		}

		return $links;
	}

	/**
	 * Get the trigger key defined by the user.
	 *
	 * @since 2.0.0
	 */
	public function get_defined_trigger_key(): string {

		return substr((string) wu_get_setting('jumper_key', 'g'), 0, 1);
	}

	/**
	 * Get the trigger key combination depending on the OS
	 *
	 * - For Win & Linux: ctrl + alt + key defined by user;
	 * - For Mac: command + option + key defined by user.
	 *
	 * @since 2.0.0
	 *
	 * @param string $os OS to get the key combination for. Options: win or osx.
	 * @return array
	 */
	public function get_keys($os = 'win') {

		$trigger_key = $this->get_defined_trigger_key();

		$keys = [
			'win' => ['ctrl', 'alt', $trigger_key],
			'osx' => ['command', 'option', $trigger_key],
		];

		return $keys[ $os ] ?? $keys['win'];
	}

	/**
	 * Changes the helper footer message about the Jumper and its trigger
	 *
	 * @since 2.0.0
	 *
	 * @param string $text The default WordPress right footer message.
	 * @return string
	 */
	public function add_jumper_footer_message($text) {

		if ( ! wu_get_setting('jumper_display_tip', true)) {
			return $text;
		}

		$os = isset($_SERVER['HTTP_USER_AGENT']) && stristr(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])), 'mac') ? 'osx' : 'win';

		$keys = $this->get_keys($os);

		$html = '';

		foreach ($keys as $key) {
			$html .= '<span class="wu-keys-key">' . $key . '</span>+';
		}

		$html = trim($html, '+');

		// translators: the %s placeholder is the key combination to trigger the Jumper.
		return '<span class="wu-keys">' . sprintf(__('<strong>Quick Tip:</strong> Use %s to jump between pages.', 'multisite-ultimate'), $html) . '</span>' . $text;
	}

	/**
	 * Enqueues the JavaScript files necessary to make the jumper work.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_scripts(): void {

		wp_register_script('wu-mousetrap', wu_get_asset('mousetrap.js', 'js/lib'), ['jquery'], wu_get_version(), true);

		wp_register_script('wu-jumper', wu_get_asset('jumper.js', 'js'), ['jquery', 'wu-selectize', 'wu-mousetrap', 'underscore'], wu_get_version(), true);

		wp_localize_script(
			'wu-jumper',
			'wu_jumper_vars',
			[
				'not_found_message' => __('Nothing found for', 'multisite-ultimate'),
				'trigger_key'       => $this->get_defined_trigger_key(),
				'network_base_url'  => network_admin_url(),
				'ajaxurl'           => wu_ajax_url(),
				'base_url'          => get_admin_url(get_current_site()->blog_id),
			]
		);

		wp_enqueue_script('wu-jumper');

		wp_enqueue_style('wu-admin');
	}

	/**
	 * Enqueues the CSS files necessary to make the jumper work.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_styles(): void {

		wp_enqueue_style('wu-jumper', wu_get_asset('jumper.css', 'css'), [], wu_get_version());
	}

	/**
	 * Outputs the actual HTML markup of the Jumper.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output(): void {

		wu_get_template(
			'ui/jumper',
			[
				'menu_groups' => $this->get_link_list(),
			]
		);
	}

	/**
	 * Get the full page URL for admin pages.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url URL of the menu item.
	 */
	public function get_menu_page_url($url): string {

		$final_url = menu_page_url($url, false);

		return str_replace(admin_url(), network_admin_url(), $final_url);
	}

	/**
	 * Returns the URL of a jumper menu item
	 *
	 * If the URL is an absolute URL, returns the full-url.
	 * If the URL is relative, we return the full URL using WordPress url functions.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url URL of the menu item.
	 * @return string
	 */
	public function get_target_url($url) {

		if (str_contains($url, 'http')) {
			return $url;
		}

		if (str_contains($url, '.php')) {
			return network_admin_url($url);
		}

		return $this->get_menu_page_url($url);
	}

	/**
	 * Builds the list of links based on the $menu and $submenu globals.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function build_link_list() {

		return Logger::track_time(
			'jumper',
			__('Regenerating Jumper menu items', 'multisite-ultimate'),
			function () {

				global $menu, $submenu;

				// This variable is going to carry our options
				$choices = [];

				// Prevent first run bug
				if ( ! is_array($menu) || ! is_array($submenu)) {
					return [];
				}

				// Loop all submenus so que can get our final
				foreach ($submenu as $menu_name => $submenu_items) {
					$title = $this->search_recursive($menu_name, $menu);

					$string = wu_get_isset($title, 0, '');

					$title = preg_replace('/[0-9]+/', '', wp_strip_all_tags($string));

					// If parent does not exists, skip
					if ( ! empty($title) && is_array($submenu_items)) {

						// We have to loop now each submenu
						foreach ($submenu_items as $submenu_item) {
							$url = $this->get_target_url($submenu_item[2]);

							// Add to our choices the admin urls
						}
					}
				}

				$choices = apply_filters('wu_link_list', $choices);

				set_site_transient($this->transient_key, $choices, 10 * MINUTE_IN_SECONDS);

				return $choices;
			}
		);
	}

	/**
	 * Gets the cached menu list saved.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_saved_menu() {

		$saved_menu = get_site_transient($this->transient_key);

		return $saved_menu ?: [];
	}

	/**
	 * Returns the link list.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_link_list() {

		$should_rebuild_menu = ! get_site_transient($this->transient_key);

		return $should_rebuild_menu && is_network_admin() ? $this->build_link_list() : $this->get_saved_menu();
	}

	/**
	 * Filter the Multisite Ultimate settings to add Jumper options
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function add_settings(): void {

		wu_register_settings_section(
			'tools',
			[
				'title' => __('Tools', 'multisite-ultimate'),
				'desc'  => __('Tools', 'multisite-ultimate'),
				'icon'  => 'dashicons-wu-tools',
			]
		);

		wu_register_settings_field(
			'tools',
			'tools_header',
			[
				'title' => __('Jumper', 'multisite-ultimate'),
				'desc'  => __('Spotlight-like search bar that allows you to easily access everything on your network.', 'multisite-ultimate'),
				'type'  => 'header',
			]
		);

		wu_register_settings_field(
			'tools',
			'enable_jumper',
			[
				'title'   => __('Enable Jumper', 'multisite-ultimate'),
				'desc'    => __('Turn this option on to make the Jumper available on your network.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 1,
			]
		);

		wu_register_settings_field(
			'tools',
			'jumper_key',
			[
				'title'   => __('Trigger Key', 'multisite-ultimate'),
				'desc'    => __('Change the keyboard key used in conjunction with ctrl + alt (or cmd + option), to trigger the Jumper box.', 'multisite-ultimate'),
				'type'    => 'text',
				'default' => 'g',
				'require' => [
					'enable_jumper' => 1,
				],
			]
		);

		wu_register_settings_field(
			'tools',
			'jumper_custom_links',
			[
				'title'       => __('Custom Links', 'multisite-ultimate'),
				'desc'        => __('Use this textarea to add custom links to the Jumper. Add one per line, with the format "Title : url".', 'multisite-ultimate'),
				'placeholder' => __('Tile of Custom Link : http://link.com', 'multisite-ultimate'),
				'type'        => 'textarea',
				'html_attr'   => [
					'rows' => 4,
				],
				'require'     => [
					'enable_jumper' => 1,
				],
			]
		);
	}

	/**
	 * Helper function to recursively seach an array.
	 *
	 * @since 2.0.0
	 *
	 * @param string $needle String to seach recursively.
	 * @param array  $haystack Array to search.
	 * @return mixed
	 */
	public function search_recursive($needle, $haystack) {

		foreach ($haystack as $key => $value) {
			$current_key = $key;

			if ($needle === $value || (is_array($value) && $this->search_recursive($needle, $value) !== false)) {
				return $value;
			}
		}

		return false;
	}
}
