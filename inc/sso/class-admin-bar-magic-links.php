<?php
/**
 * Admin Bar Magic Links
 *
 * Modifies WordPress core My Sites admin bar menu to use magic links
 * for sites with custom domains.
 *
 * @package WP_Ultimo
 * @since 2.0.0
 */

namespace WP_Ultimo\SSO;

defined('ABSPATH') || exit;

/**
 * Adds magic link support to the core WordPress My Sites admin bar menu.
 *
 * @since 2.0.0
 */
class Admin_Bar_Magic_Links {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Initialize hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		// Hook late to modify the URLs after WordPress core adds them.
		add_action('admin_bar_menu', array($this, 'modify_my_sites_menu'), 999);

		// Hook early into admin_page_access_denied to show magic links.
		add_action('admin_page_access_denied', array($this, 'show_access_denied_with_magic_links'), 5);
	}

	/**
	 * Modify the My Sites admin bar menu to use magic links.
	 *
	 * This function hooks into the admin bar after WordPress core has
	 * added all the My Sites menu items, and replaces dashboard URLs
	 * with magic links for sites that have custom domains.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The admin bar instance.
	 * @return void
	 */
	public function modify_my_sites_menu($wp_admin_bar): void {

		// Only process if user is logged in.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Process each node.
		foreach ($wp_admin_bar->get_nodes() as $node) {
			$parts = explode('-', $node->id);
			if (count($parts) >= 3 && 'blog' === $parts[0] && is_numeric($parts[1]) && 'd' === $parts[2]) {
				$site_id = (int) $parts[1];
			} else {
				continue;
			}

			// Generate magic link.
			$magic_link = wu_get_admin_url($site_id);

			if ( ! $magic_link ) {
				continue;
			}

			// Update the node with the magic link.
			$node->href = $magic_link;

			$wp_admin_bar->add_node($node);
		}
	}

	/**
	 * Show access denied splash screen with magic links.
	 *
	 * This replaces the WordPress core access denied splash screen
	 * with our own version that uses magic links for sites with custom domains.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function show_access_denied_with_magic_links(): void {

		// Only run in multisite and if user is logged in.
		if ( ! is_multisite() || ! is_user_logged_in() || is_network_admin() ) {
			return;
		}

		$blogs = get_blogs_of_user(get_current_user_id());

		// If user has blogs and current blog is not in their list, show our custom message.
		if ( wp_list_filter($blogs, array('userblog_id' => get_current_blog_id())) ) {
			return;
		}

		$blog_name = get_bloginfo('name');

		if ( empty($blogs) ) {
			wp_die(
				sprintf(
					/* translators: 1: Site title. */
					__('You attempted to access the "%1$s" dashboard, but you do not currently have privileges on this site. If you believe you should be able to access the "%1$s" dashboard, please contact your network administrator.'), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$blog_name // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				),
				403
			);
		}

		if ( 1 === count($blogs) ) {
			wp_safe_redirect(get_admin_url(current($blogs)->userblog_id));
			exit;
		}

		$output = '<p>' . sprintf(
			/* translators: 1: Site title. */
			__('You attempted to access the "%1$s" dashboard, but you do not currently have privileges on this site. If you believe you should be able to access the "%1$s" dashboard, please contact your network administrator.'),
			$blog_name
		) . '</p>';
		$output .= '<p>' . __('If you reached this screen by accident and meant to visit one of your own sites, here are some shortcuts to help you find your way.') . '</p>';

		$output .= '<h3>' . __('Your Sites') . '</h3>';
		$output .= '<table>';

		foreach ( $blogs as $blog ) {
			$site_id = (int) $blog->userblog_id;

			// Get dashboard URL (with magic link if needed).
			$dashboard_url = wu_get_admin_url($site_id);

			// Get home URL (with magic link if needed).
			$home_url = wu_get_home_url($site_id);

			$output .= '<tr>';
			$output .= '<td>' . esc_html($blog->blogname) . '</td>';
			$output .= '<td><a href="' . esc_url($dashboard_url) . '">' . __('Visit Dashboard') . '</a> | ' .
				'<a href="' . esc_url($home_url) . '">' . __('View Site') . '</a></td>';
			$output .= '</tr>';
		}

		$output .= '</table>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped above with esc_url() and esc_html().
		wp_die($output, 403);
	}
}
