<?php
/**
 * Nav Menu Subsite Links
 *
 * Adds a "Subsite" link type to WordPress nav menus that allows users
 * to add links to any subsite on the network. When the subsite is on a
 * different domain, the link will automatically include a magic login token.
 *
 * @package WP_Ultimo
 * @subpackage SSO
 * @since 2.5.0
 */

namespace WP_Ultimo\SSO;

defined('ABSPATH') || exit;

/**
 * Adds subsite links to WordPress nav menus with magic link support.
 *
 * @since 2.5.0
 */
class Nav_Menu_Subsite_Links {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Meta key for storing the subsite ID.
	 *
	 * @var string
	 */
	const META_KEY_SUBSITE_ID = '_wu_subsite_id';

	/**
	 * CSS class prefix for identifying subsite menu items.
	 *
	 * @var string
	 */
	const CLASS_PREFIX = 'wu-subsite-';

	/**
	 * Initialize hooks.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function init(): void {

		// Add the meta box to nav-menus.php.
		add_action('admin_head-nav-menus.php', [$this, 'add_nav_menu_meta_box']);

		// Save subsite ID when menu item is updated.
		add_action('wp_update_nav_menu_item', [$this, 'save_subsite_id'], 10, 3);

		// Filter menu item URLs on the frontend to use magic links.
		add_filter('wp_nav_menu_objects', [$this, 'filter_menu_item_urls'], 10, 2);

		// Set the menu item label in the admin.
		add_filter('wp_setup_nav_menu_item', [$this, 'setup_nav_menu_item']);
	}

	/**
	 * Add the Subsites meta box to the nav menus admin page.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function add_nav_menu_meta_box(): void {

		// Only show to super admins.
		if ( ! current_user_can('manage_network')) {
			return;
		}

		add_meta_box(
			'wu-nav-menu-subsites',
			__('Subsites', 'ultimate-multisite'),
			[$this, 'render_meta_box'],
			'nav-menus',
			'side',
			'low'
		);
	}

	/**
	 * Render the Subsites meta box content.
	 *
	 * Uses the same form structure as WooCommerce endpoints meta box
	 * so WordPress's built-in nav menu JavaScript handles adding items.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function render_meta_box(): void {

		$sites = wu_get_sites(
			[
				'number' => 100,
				'order'  => 'ASC',
			]
		);

		?>
		<div id="posttype-wu-subsites" class="posttypediv">
			<div id="tabs-panel-wu-subsites" class="tabs-panel tabs-panel-active" style="max-height: 200px; overflow-y: auto;">
				<ul id="wu-subsites-checklist" class="categorychecklist form-no-clear">
					<?php
					$i = -1;
					foreach ($sites as $site) :
						$blog_id = $site->get_id();

						// Skip the main site.
						if (is_main_site($blog_id)) {
							continue;
						}

						$title = $site->get_title();
						$url   = $site->get_active_site_url();
						?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-object-id]" value="<?php echo esc_attr($i); ?>" />
								<?php echo esc_html($title); ?>
								(<?php echo esc_html($url); ?>)
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-type]" value="custom" />
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-title]" value="<?php echo esc_attr($title); ?>" />
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-url]" value="<?php echo esc_url($url); ?>" />
							<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr($i); ?>][menu-item-classes]" value="<?php echo esc_attr(self::CLASS_PREFIX . $blog_id); ?>" />
						</li>
						<?php
						--$i;
					endforeach;

					if (-1 === $i) :
						?>
						<li style="padding: 10px; text-align: center; color: #666;">
							<?php esc_html_e('No subsites found.', 'ultimate-multisite'); ?>
						</li>
						<?php
					endif;
					?>
				</ul>
			</div>
			<p class="button-controls" data-items-type="posttype-wu-subsites">
				<span class="list-controls">
					<label>
						<input type="checkbox" class="select-all" />
						<?php esc_html_e('Select all', 'ultimate-multisite'); ?>
					</label>
				</span>
				<span class="add-to-menu">
					<button type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to menu', 'ultimate-multisite'); ?>" name="add-post-type-menu-item" id="submit-posttype-wu-subsites"><?php esc_html_e('Add to menu', 'ultimate-multisite'); ?></button>
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Save the subsite ID when a menu item is created/updated.
	 *
	 * Detects the wu-subsite-{id} class and saves the ID to post meta.
	 *
	 * @since 2.5.0
	 *
	 * @param int   $menu_id         The menu ID.
	 * @param int   $menu_item_db_id The menu item database ID.
	 * @param array $args            The menu item arguments.
	 * @return void
	 */
	public function save_subsite_id(int $menu_id, int $menu_item_db_id, array $args): void {

		if (empty($args['menu-item-classes'])) {
			return;
		}

		$classes = is_array($args['menu-item-classes']) ? $args['menu-item-classes'] : explode(' ', $args['menu-item-classes']);

		foreach ($classes as $class) {
			if (strpos($class, self::CLASS_PREFIX) === 0) {
				$subsite_id = absint(str_replace(self::CLASS_PREFIX, '', $class));

				if ($subsite_id > 0) {
					update_post_meta($menu_item_db_id, self::META_KEY_SUBSITE_ID, $subsite_id);
					return;
				}
			}
		}
	}

	/**
	 * Filter menu item URLs on the frontend to use magic links when needed.
	 *
	 * @since 2.5.0
	 *
	 * @param array     $items The menu items.
	 * @param \stdClass $args  The menu arguments.
	 * @return array The filtered menu items.
	 */
	public function filter_menu_item_urls(array $items, \stdClass $args): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		foreach ($items as $item) {
			// Check if this is a subsite menu item by looking for our meta.
			$subsite_id = get_post_meta($item->ID, self::META_KEY_SUBSITE_ID, true);

			if (empty($subsite_id)) {
				continue;
			}

			$subsite_id = absint($subsite_id);

			// Get the home URL with magic link if needed.
			$url = $this->get_subsite_url_with_magic_link($subsite_id);

			if ($url) {
				$item->url = $url;
			}
		}

		return $items;
	}

	/**
	 * Get the subsite URL, adding a magic link if the site has a mapped domain.
	 *
	 * This method handles both WP_Ultimo managed sites and regular WordPress sites
	 * that have domain mappings.
	 *
	 * @since 2.5.0
	 *
	 * @param int $blog_id The blog ID.
	 * @return string The URL, with magic link token if needed.
	 */
	protected function get_subsite_url_with_magic_link(int $blog_id): string {

		// Get current user - magic links only work for logged-in users.
		$current_user_id = get_current_user_id();

		if ( ! $current_user_id) {
			return get_home_url($blog_id);
		}

		// Check if magic links are enabled.
		if ( ! wu_get_setting('enable_magic_links', true)) {
			return get_home_url($blog_id);
		}

		// Get the magic link instance.
		$magic_link = Magic_Link::get_instance();

		// Get the site's active URL (may include mapped domain).
		$site     = wu_get_site($blog_id);
		$home_url = $site ? $site->get_active_site_url() : get_home_url($blog_id);

		// Parse the URL to get the domain.
		$site_domain = wp_parse_url($home_url, PHP_URL_HOST);

		if ( ! $site_domain) {
			return $home_url;
		}

		// Need a magic link - generate one.
		$magic_link_url = $magic_link->generate_magic_link($current_user_id, $blog_id, $home_url);

		return $magic_link_url ?: $home_url;
	}

	/**
	 * Setup the nav menu item for display in the admin.
	 *
	 * @since 2.5.0
	 *
	 * @param object $menu_item The menu item object.
	 * @return object The modified menu item object.
	 */
	public function setup_nav_menu_item(object $menu_item): object {

		// Check if this is a subsite menu item by looking for our meta.
		$subsite_id = get_post_meta($menu_item->ID, self::META_KEY_SUBSITE_ID, true);

		if (empty($subsite_id)) {
			return $menu_item;
		}

		$subsite_id = absint($subsite_id);
		$site       = wu_get_site($subsite_id);

		if ( ! $site) {
			$menu_item->type_label = __('Subsite (Deleted)', 'ultimate-multisite');
			$menu_item->_invalid   = true;
			return $menu_item;
		}

		$menu_item->type_label = __('Subsite', 'ultimate-multisite');
		$menu_item->url        = $site->get_active_site_url();

		// Set the title if not already set or if it's empty.
		if (empty($menu_item->title)) {
			$menu_item->title = $site->get_title();
		}

		return $menu_item;
	}
}
