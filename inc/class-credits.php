<?php
/**
 * Footer Credits handler.
 *
 * @package WP_Ultimo
 * @subpackage Credits
 * @since 2.4.5
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles optional display of "Powered by" credits.
 *
 * - Opt-in via settings and setup wizard (default OFF).
 * - Optional custom HTML for the credit text.
 * - Optional allowance for per-site removal.
 */
class Credits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Boot hooks.
	 */
	public function init(): void {
		// Register settings into the General section so they show in wizard + settings page.
		add_action('init', [$this, 'register_settings'], 20);

		// Hook admin footer replacement.
		add_filter('admin_footer_text', [$this, 'filter_admin_footer_text'], 100);
		add_filter('update_footer', [$this, 'filter_update_footer_text'], 100);

		// Hook front-end/footer rendering.
		add_action('wp_footer', [$this, 'render_frontend_footer'], 100);
		add_action('login_footer', [$this, 'render_frontend_footer'], 100);
	}

	/**
	 * Register settings controls.
	 */
	public function register_settings(): void {
		// Header
		wu_register_settings_field(
			'general',
			'footer_credits_header',
			[
				'title' => __('Footer Credits', 'ultimate-multisite'),
				'desc'  => __('Optional footer credit for public site and admin. Off by default.', 'ultimate-multisite'),
				'type'  => 'header',
			],
			2000
		);

		// Enable/disable powered by (global)
		wu_register_settings_field(
			'general',
			'credits_enable',
			[
				'title'   => __('Show "Powered by Ultimate Multisite"', 'ultimate-multisite'),
				'desc'    => __('Adds a small credit in admin and front-end footers.', 'ultimate-multisite'),
				'type'    => 'toggle',
				'default' => 0,
			],
			2010
		);

		// Allow custom text instead of the link
		wu_register_settings_field(
			'general',
			'credits_custom_enable',
			[
				'title'   => __('Use Custom Footer Text', 'ultimate-multisite'),
				'desc'    => __('Use the custom HTML below instead of the default link.', 'ultimate-multisite'),
				'type'    => 'toggle',
				'default' => 0,
				'require' => [
					'credits_enable' => 1,
				],
			],
			2020
		);

		// Custom text value
		wu_register_settings_field(
			'general',
			'credits_custom_text',
			[
				'title'       => __('Custom Footer Text', 'ultimate-multisite'),
				'desc'        => __('HTML allowed. Use any text or link you prefer.', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => function () {
					$name = (string) get_network_option(null, 'site_name');
					$name = $name ?: __('this network', 'ultimate-multisite');
					$url  = function_exists('get_main_site_id') ? get_site_url(get_main_site_id()) : network_home_url('/');
					return sprintf(
						/* translators: 1: Opening anchor tag with URL to main site. 2: Network name. */
						__('Powered by %1$s%2$s</a>', 'ultimate-multisite'),
						'<a href="' . esc_url($url) . '" target="_blank" rel="nofollow noopener">',
						esc_html($name)
					);
				},
				'placeholder' => __('Powered by <a href="https://example.com">Your Company</a>', 'ultimate-multisite'),
				'require'     => [
					'credits_enable'        => 1,
					'credits_custom_enable' => 1,
				],
			],
			2030
		);

		// Allow sites to remove (per-site opt-out)
		wu_register_settings_field(
			'general',
			'credits_site_can_hide',
			[
				'title'   => __('Allow Sites to Remove Credit', 'ultimate-multisite'),
				'desc'    => __('Allow individual sites to opt out.', 'ultimate-multisite'),
				'type'    => 'toggle',
				'default' => 1,
				'require' => [
					'credits_enable' => 1,
				],
			],
			2040
		);
	}

	/**
	 * Build the credit text (HTML) based on settings.
	 */
	protected function build_credit_html(): string {
		$enabled = (bool) wu_get_setting('credits_enable', 0);
		if (! $enabled) {
			return '';
		}

		$use_custom = (bool) wu_get_setting('credits_custom_enable', 0);

		if ($use_custom) {
			$text = (string) wu_get_setting('credits_custom_text', '');
			return wp_kses_post($text);
		}

		// Default: "Powered by Ultimate Multisite" with link (only when explicitly opted-in).
		$label = esc_html__('Powered by', 'ultimate-multisite') . ' ';
		$link  = sprintf(
			'<a href="%s" target="_blank" rel="nofollow noopener">%s</a>',
			esc_url('https://ultimatemultisite.com'),
			esc_html__('Ultimate Multisite', 'ultimate-multisite')
		);
		return $label . $link;
	}

	/**
	 * Check if current site is allowed to show footer credit.
	 */
	protected function site_allows_credit(): bool {
		// If network disables site removable option, then always allowed.
		$allow_site_hide = (bool) wu_get_setting('credits_site_can_hide', 1);
		if (! $allow_site_hide) {
			return true;
		}

		// Respect a per-site opt-out flag if present.
		$blog_id = get_current_blog_id();
		$hidden  = (bool) get_blog_option($blog_id, 'wu_hide_footer_credit', false);
		return ! $hidden;
	}

	/**
	 * Admin footer replacement.
	 *
	 * Only show on customer-owned site admins (not network admin or main site admin).
	 */
	public function filter_admin_footer_text($text): string {
		if (is_network_admin()) {
			return $text;
		}

		$site = function_exists('wu_get_current_site') ? \wu_get_current_site() : null;
		if (! $site || ($site->get_type() !== \WP_Ultimo\Database\Sites\Site_Type::CUSTOMER_OWNED)) {
			return $text;
		}

		$credit = $this->build_credit_html();
		if ($credit && $this->site_allows_credit()) {
			return $credit;
		}
		return $text;
	}

	/**
	 * Remove default update footer text when our credit is enabled.
	 *
	 * @param string $text Default Text.
	 */
	public function filter_update_footer_text($text): string {
		if (is_network_admin()) {
			return $text;
		}

		$site = function_exists('wu_get_current_site') ? \wu_get_current_site() : null;
		if (! $site || ($site->get_type() !== \WP_Ultimo\Database\Sites\Site_Type::CUSTOMER_OWNED)) {
			return $text;
		}

		$enabled = (bool) wu_get_setting('credits_enable', 0);
		if ($enabled && $this->site_allows_credit()) {
			return '';
		}
		return $text;
	}

	/**
	 * Front-end footer output (appended near wp_footer).
	 */
	public function render_frontend_footer(): void {
		if (is_admin()) {
			return;
		}
		$credit = $this->build_credit_html();
		if (! $credit || ! $this->site_allows_credit()) {
			return;
		}
		echo '<div class="wu-powered-by" style="text-align:center;opacity:.7;font-size:12px;margin:10px 0;">' . $credit . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
