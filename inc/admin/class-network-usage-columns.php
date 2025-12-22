<?php
/**
 * Network Plugins/Themes usage columns.
 *
 * @package WP_Ultimo
 * @subpackage Admin
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin;

// Exit if accessed directly
use WP_Theme;

defined('ABSPATH') || exit;

/**
 * Adds a column on Network Plugins and Themes pages to show which sites use them.
 *
 * Inspired by bueltge/WordPress-Multisite-Enhancements but integrated and simplified
 * to avoid duplication and keep performance acceptable on medium networks.
 */
class Network_Usage_Columns {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * String for the transient string, there save the blog plugins.
	 *
	 * @var    string
	 */
	const SITE_TRANSIENT_BLOGS_PLUGINS = 'wu_blogs_data_plugins';

	/**
	 * Max sites to fetch.
	 *
	 * @var int
	 */
	private int $sites_limit = 199;

	/**
	 * Member variable to store data about active plugins for each blog.
	 *
	 * @var     array
	 */
	private array $blogs_data = [];


	/**
	 * Cached lookup of parent stylesheet => list of child theme names.
	 * Built on first use to keep is_parent() O(1) per call after O(n) init.
	 *
	 * @var array<string, string[]>
	 */
	private array $children_by_parent = [];


	/**
	 * Initialize the class.
	 */
	public function init(): void {

		add_action('activated_plugin', [$this, 'clear_site_transient'], 10, 2);
		add_action('deactivated_plugin', [$this, 'clear_site_transient'], 10, 2);
		add_action('switch_theme', [$this, 'clear_site_transient'], 10, 1);
		add_action('update_site_option_allowedthemes', [$this, 'clear_site_transient'], 10, 1);

		if ( ! is_network_admin() ) {
			return;
		}

		/**
		 * Filter to change the value for get pluginssites inside the network.
		 *
		 * @type integer $sites_limit
		 */
		$this->sites_limit = (int) apply_filters('wu_sites_column_limit', $this->sites_limit);

		add_filter('manage_plugins-network_columns', array($this, 'add_plugins_column'), 10, 1);
		add_action('manage_plugins_custom_column', array($this, 'manage_plugins_custom_column'), 10, 3);

		add_filter('manage_themes-network_columns', array($this, 'add_themes_column'), 10, 1);
		add_action('manage_themes_custom_column', array($this, 'manage_themes_custom_column'), 10, 3);
	}

	/**
	 * Add in a column header.
	 *
	 * @since  0.0.1
	 *
	 * @param  array $columns An array of displayed site columns.
	 *
	 * @return array
	 */
	public function add_plugins_column(array $columns): array {

		global $status;

		if ( empty($status) || ! in_array($status, ['dropins', 'mustuse'], true) ) {
			$columns['active_blogs'] = __('Usage', 'ultimate-multisite');
		}

		return $columns;
	}

	/**
	 * Get data for each row on each plugin.
	 * Echo the string.
	 *
	 * @since   0.0.1
	 *
	 * @param  string $column_name Name of the column.
	 * @param  string $plugin_file Path to the plugin file.
	 * @param  array  $plugin_data An array of plugin data.
	 */
	public function manage_plugins_custom_column(string $column_name, string $plugin_file, array $plugin_data): void {
		if ( 'active_blogs' !== $column_name ) {
			return;
		}
		// Is this plugin network activated.
		if ( ! function_exists('is_plugin_active_for_network') ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$active_on_network = is_plugin_active_for_network($plugin_file);
		if ( $active_on_network ) {
			// Translators: The plugin is network wide active, the string is for each plugin possible.
			echo '<span style="white-space:nowrap">' . esc_html__('Network Activated', 'ultimate-multisite') . '</span>';
		} else {
			$active_on_blogs = $this->get_blogs_with_plugin($plugin_file);
			$this->output_blog_list($active_on_blogs);
		}
		if ( ! empty($plugin_data['Network']) ) {
			echo '<br /><span style="white-space:nowrap" class="submitbox"><span class="submitdelete">'
						. esc_attr__('Network Only', 'ultimate-multisite')
						. '</span></span>';
		}
	}


	/**
	 * Add in a column header.
	 *
	 * @param array $columns An array of displayed site columns.
	 *
	 * @return array
	 */
	public function add_themes_column(array $columns): array {
		$columns['active_blogs'] = '<span class="non-breaking">' . __('Usage', 'ultimate-multisite') . '</span>';

		return $columns;
	}

	/**
	 * Get data for each row on each theme.
	 * Print the string about the usage.
	 *
	 * @param string   $column_name Name of the column.
	 * @param string   $theme_key Path to the theme file.
	 * @param WP_Theme $theme_data An array of theme data.
	 */
	public function manage_themes_custom_column(string $column_name, string $theme_key, WP_Theme $theme_data): void {
		if ( 'active_blogs' !== $column_name ) {
			return;
		}

		$active_on_blogs = $this->get_blogs_with_theme($theme_key);

		$this->output_blog_list($active_on_blogs);

		// Check, if is a child theme and return parent.
		$child_context = '';
		if ( $theme_data->parent() ) {
			echo '<br>' . sprintf(
				// Translators: The placeholder will be replaced by the name of the parent theme.
				esc_attr__('This is a child theme of %s.', 'ultimate-multisite'),
				'<strong>' . esc_attr($theme_data->parent()->Name) . '</strong>'
			);
		}

		// Check if used as a parent theme for a child.
		$used_as_parent = $this->is_parent($theme_key);
		if ( count($used_as_parent) ) {
			echo '<br>' . esc_attr__(
				'This is used as a parent theme by:',
				'ultimate-multisite'
			) . ' ';
			echo esc_html(implode(', ', $used_as_parent));
		}
	}

	/**
	 * Get child themes that use the given theme as parent (O(n)).
	 *
	 * @param string $theme_key Parent theme stylesheet (directory) key.
	 * @return array List of child theme names (escaped for output).
	 */
	public function is_parent(string $theme_key): array {
		if (isset($this->children_by_parent[ $theme_key ])) {
			return $this->children_by_parent[ $theme_key ];
		}

		if ( ! function_exists('wp_get_themes') ) {
			return [];
		}

		// Build cache once in O(n) over installed themes.
		if (empty($this->children_by_parent)) {
			$map    = [];
			$themes = wp_get_themes(); // Array of \WP_Theme keyed by stylesheet.
			foreach ($themes as $stylesheet => $theme) {
				if ( ! ($theme instanceof WP_Theme) ) {
					continue;
				}
				$parent_stylesheet = (string) $theme->get_template();
				// Only child themes have a parent template different from their own stylesheet.
				if ($parent_stylesheet && $parent_stylesheet !== $stylesheet) {
					$map[ $parent_stylesheet ][] = esc_html($theme->get('Name'));
				}
			}
			// Sort children lists for stable output.
			foreach ($map as &$children) {
				sort($children, SORT_STRING | SORT_FLAG_CASE);
			}
			$this->children_by_parent = $map;
		}

		return $this->children_by_parent[ $theme_key ] ?? [];
	}

	/**
	 * Output Blog List in cell.
	 *
	 * @param array $blogs The list of blogs.
	 *
	 * @return void
	 */
	private function output_blog_list(array $blogs): void {

		if ( ! $blogs ) {
			// Translators: The plugin is not activated, the string is for each plugin possible.
			echo '<span style="white-space:nowrap">' . esc_html__('Not Activated', 'ultimate-multisite') . '</span>';
		} else {
			$active_count = count($blogs);
			echo '<details ' . ($active_count > 4 ? '' : 'open') . ' ><summary class="non-breaking">';
			printf(
			// Translators: The placeholder will be replaced by the count and the toggle link of sites there use that plugin.
				esc_html(_n('Active on %1$d site', 'Active on %1$d sites', $active_count, 'ultimate-multisite')),
				esc_html($active_count),
			);
			echo '</summary>';
			echo '<ul>';
			foreach ($blogs as $blog_id => $blog) {
				// Check the site for archived and deleted.
				$class = '';
				$hint  = '';
				if ($blog['archived']) {
					$class = 'site-archived';
					$hint  = ', ' . esc_attr__('Archived', 'ultimate-multisite');
				}
				if ($blog['deleted']) {
					$class = 'site-deleted';
					$hint .= ', ' . esc_attr__('Deleted', 'ultimate-multisite');
				}
				echo '<li class="' . esc_attr($class) . '" title="Blog ID: ' . esc_attr($blog_id . $hint) . '">';
				echo '<span><a href="' . esc_attr(get_admin_url($blog_id)) . 'plugins.php">'
					. esc_html(trim($blog['name']) ?: $blog['path']) . '</a>' . esc_html($hint) . '</span></li>';
			}
			echo '</ul></details>';
		}
	}

	/**
	 * Is plugin active in blogs.
	 *
	 * @param    string $plugin_file A name of the plugin file.
	 *
	 * @return array $active_in_plugins Which Blog ID and Name of Blog for each item in Array.
	 */
	public function get_blogs_with_plugin(string $plugin_file): array {
		$blogs_data = $this->get_blogs_data();
		if (empty($blogs_data['plugins_blogs'][ $plugin_file ])) {
			return [];
		}
		return array_intersect_key($blogs_data['blogs'], array_flip((array) $blogs_data['plugins_blogs'][ $plugin_file ]));
	}

	/**
	 * Get all blogs with theme active.
	 *
	 * @param    string $theme_file A name of the plugin file.
	 *
	 * @return array all blogs with theme.
	 */
	public function get_blogs_with_theme(string $theme_file): array {
		$blogs_data = $this->get_blogs_data();
		if (empty($blogs_data['themes_blogs'][ $theme_file ])) {
			return [];
		}
		return array_intersect_key($blogs_data['blogs'], array_flip((array) $blogs_data['themes_blogs'][ $theme_file ]));
	}

	/**
	 * Gets an array of blog data including active plugins for each blog.
	 *
	 * @return array
	 */
	public function get_blogs_data(): array {

		if ( $this->blogs_data ) {
			return $this->blogs_data;
		}

		$blogs_plugins = get_site_transient(self::SITE_TRANSIENT_BLOGS_PLUGINS);
		if ( false === $blogs_plugins ) {
			$this->blogs_data = [
				'blogs'         => [],
				'plugins_blogs' => [],
				'themes_blogs'  => [],
			];

			$blogs = get_sites(
				[
					'fields' => 'ids',
					'number' => $this->sites_limit,
				]
			);

			foreach ( $blogs as $blog_id ) {
				$blog_details = get_blog_details(
					$blog_id
				);

				$this->blogs_data['blogs'][ $blog_id ] = [
					'path'     => $blog_details->path,
					'name'     => $blog_details->blogname,
					'archived' => (bool) $blog_details->archived,
					'deleted'  => (bool) $blog_details->deleted,
				];
				foreach (get_blog_option($blog_id, 'active_plugins', array()) as $plugin) {
					$this->blogs_data['plugins_blogs'][ $plugin ][] = $blog_id;
				}
				$theme_file = (string) get_blog_option($blog_id, 'stylesheet', '');
				if ($theme_file) {
					$this->blogs_data['themes_blogs'][ $theme_file ][] = $blog_id;
				}
			}

			set_site_transient(self::SITE_TRANSIENT_BLOGS_PLUGINS, $this->blogs_data);
		} else {
			$this->blogs_data = $blogs_plugins;
		}

		return $this->blogs_data;
	}

	/**
	 * Clears the $blogs_plugins site transient when any plugins are activated/deactivated.
	 *
	 * @param string $plugin The plugin being activated.
	 * @param bool   $network_wide If it's network wide.
	 */
	public function clear_site_transient($plugin, $network_wide = false): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		delete_site_transient(self::SITE_TRANSIENT_BLOGS_PLUGINS);
	}
}
