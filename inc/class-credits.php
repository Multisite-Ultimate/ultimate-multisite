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
 * - Optional custom text with {Network Name} placeholder.
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

        // Replace specific front-end theme strings like "Powered by WordPress" when opted-in.
        add_filter('gettext', [$this, 'filter_powered_by_wordpress_string'], 10, 3);
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
                'desc'  => __('Optional footer attribution on the public site and admin. Per WordPress.org rules, this is opt-in and does not show by default.', 'ultimate-multisite'),
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
                'desc'    => __('When enabled, a small credit replaces the default WordPress credit in admin and is added to the front-end footer. Default is OFF.', 'ultimate-multisite'),
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
                'desc'    => __('When enabled, use the custom text below instead of the default link.', 'ultimate-multisite'),
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
                'desc'        => __('Supports {Network Name} placeholder. Example: "Powered by {Network Name}"', 'ultimate-multisite'),
                'type'        => 'text',
                'default'     => function () {
                    $network_name = get_network_option(null, 'site_name');
                    $network_name = $network_name ? (string) $network_name : __('this network', 'ultimate-multisite');
                    return sprintf(__('Powered by %s', 'ultimate-multisite'), $network_name);
                },
                'placeholder' => __('Powered by {Network Name}', 'ultimate-multisite'),
                'require'     => [
                    'credits_enable'       => 1,
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
                'desc'    => __('When enabled, individual sites can opt out (if a per-site option is set).', 'ultimate-multisite'),
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
            $network_name = (string) get_network_option(null, 'site_name');
            $text = str_replace('{Network Name}', $network_name ?: __('this network', 'ultimate-multisite'), $text);
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
     */
    public function filter_admin_footer_text($text): string {
        $credit = $this->build_credit_html();
        if ($credit && $this->site_allows_credit()) {
            return $credit;
        }
        return $text;
    }

    /**
     * Remove default update footer text when our credit is enabled.
     */
    public function filter_update_footer_text($text): string {
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

    /**
     * Replace specific front-end theme strings like "Powered by WordPress" with our credit when enabled.
     */
    public function filter_powered_by_wordpress_string($translation, $text, $domain) {
        if (is_admin()) {
            return $translation;
        }

        $enabled = (bool) wu_get_setting('credits_enable', 0);
        if (! $enabled || ! $this->site_allows_credit()) {
            return $translation;
        }

        // Only target common theme strings and only in default domain.
        if ('default' !== $domain) {
            return $translation;
        }

        $targets = [
            'Proudly powered by WordPress',
            'Powered by WordPress',
        ];

        if (in_array($text, $targets, true)) {
            return wp_strip_all_tags($this->build_credit_html());
        }

        return $translation;
    }
}

