<?php
/**
 * Handles limitations to post types, uploads and more.
 *
 * @package WP_Ultimo
 * @subpackage Limits
 * @since 2.0.0
 */

namespace WP_Ultimo\Limits;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles limitations to post types, uploads and more.
 *
 * @since 2.0.0
 */
class Theme_Limits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * List of themes that are not available.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $themes_not_available = [];

	/**
	 * Keep a cache of the results as the check is costly.
	 *
	 * @since 2.1.2
	 * @var null|false|string
	 */
	protected $forced_theme_stylesheet = null;

	/**
	 * Keep a cache of the template results as the check is costly.
	 *
	 * @since 2.1.2
	 * @var null|false|string
	 */
	protected $forced_theme_template = null;

	/**
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		/**
		 * We need to bail if we're inside the WP CLI context and the
		 * `skip-plugins` flag is present.
		 *
		 * This is due to the fact that without Multisite Ultimate being loaded,
		 * the functions and classes we'll need to perform any kind of proper
		 * checks won't be available. To validate if we're being loaded or not,
		 * we check for the function `wu_get_product`.
		 *
		 * @since 2.1.0
		 */
		if (wu_cli_is_plugin_skipped('multisite-ultimate')) {
			return;
		}

		add_action('wu_sunrise_loaded', [$this, 'load_limitations']);
	}

	/**
	 * Apply limitations if they are available.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_limitations(): void {

		if (wu_get_current_site()->has_limitations()) {
			add_filter('stylesheet', [$this, 'force_active_theme_stylesheet']);

			add_filter('template', [$this, 'force_active_theme_template']);

			add_filter('allowed_themes', [$this, 'add_extra_available_themes']);

			add_filter('site_allowed_themes', [$this, 'add_extra_available_themes']);

			add_filter('wp_prepare_themes_for_js', [$this, 'maybe_remove_activate_button']);

			add_action('admin_enqueue_scripts', [$this, 'hacky_remove_activate_button']);

			add_action('admin_footer-themes.php', [$this, 'modify_backbone_template']);

			add_action('customize_changeset_save_data', [$this, 'prevent_theme_activation_on_customizer'], 99, 2);
		}
	}

	/**
	 * Prevents sub-site admins from switching to locked themes inside the customizer.
	 *
	 * @since 2.0.10
	 *
	 * @param array $data The changeset array being saved.
	 * @param array $context The context array with tons of info about the current customizer session.
	 * @return array|void
	 */
	public function prevent_theme_activation_on_customizer($data, $context) {

		if (wu_get_current_site()->has_limitations() === false) {
			return $data;
		}

		$pending_theme_switch = $context['manager']->is_theme_active() === false;

		if (false === $pending_theme_switch) {
			return $data;
		}

		$new_theme = $context['manager']->theme()->stylesheet;

		$theme_limitations = wu_get_current_site()->get_limitations()->themes;

		if ($theme_limitations->allowed($new_theme, 'not_available')) {
			$response = [
				'code'    => 'not-available',
				'message' => __('This theme is not available on your current plan.', 'multisite-ultimate'),
			];

			wp_send_json($response, 'not-available');

			exit;
		}

		return $data;
	}

	/**
	 * Removes the activate button from not available themes.
	 *
	 * This uses a very hack-y approach due to a bug on WordPress
	 * Core. The problem is that the WP code assumes that no one
	 * with the capability of activating themes would be unable
	 * to activate a theme (in cases of external factors for example).
	 *
	 * @todo Send patch to WordPress core.
	 * @since 2.0.0
	 * @return void
	 */
	public function hacky_remove_activate_button(): void {

		global $pagenow;

		if ( ! function_exists('wu_generate_upgrade_to_unlock_url')) {
			return;
		}

		if ('themes.php' !== $pagenow) {
			return;
		}

		$membership = wu_get_current_site()->get_membership();

		if ( ! $membership) {
			return;
		}

		$upgrade_button = wu_generate_upgrade_to_unlock_button(
			__('Upgrade to unlock', 'multisite-ultimate'),
			[
				'module'  => 'themes',
				'type'    => 'EXTENSION',
				'classes' => 'button',
			]
		);

		wp_localize_script(
			'theme',
			'wu_theme_settings',
			[
				'themes_not_available' => $this->themes_not_available,
				'replacement_message'  => $upgrade_button,
			]
		);
	}

	/**
	 * Modifies the default WordPress theme page template.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function modify_backbone_template(): void { // phpcs:disable ?>

		<script type="text/javascript">

			if (typeof wu_theme_settings !== 'undefined') {

				let content = document.getElementById("tmpl-theme").innerHTML;

				content = content.replace(new RegExp('(<a class="button activate".*<\/a>)', 'g'), '<# if ( !wu_theme_settings.themes_not_available.includes(data.id) ) { #>$1<# } else { #> {{{ wu_theme_settings.replacement_message.replace("EXTENSION", data.id) }}} <# } #>');

				document.getElementById("tmpl-theme").innerHTML = content;

			}

		</script>

		<?php // phpcs:enable
	}

	/**
	 * Checks if a theme needs to have the activate button removed.
	 *
	 * @since 2.0.0
	 *
	 * @param array $themes The list of themes available.
	 * @return array
	 */
	public function maybe_remove_activate_button($themes) {

		if (is_main_site()) {
			return $themes;
		}

		$theme_limitations = wu_get_current_site()->get_limitations()->themes;

		foreach ($themes as $stylesheet => &$data) {
			$data['notAvailable'] = false;

			if ($theme_limitations->allowed($stylesheet, 'not_available')) {
				$data['actions']['activate'] = '';

				/*
				 * Hack solution due to core WP
				 * not allowing us to filter out the button.
				 */
				$data['notAvailable'] = true;

				/*
				 * Adds to the not available list
				 * for our hack-y solution.
				 */
				$this->themes_not_available[] = $stylesheet;
			}
		}

		return $themes;
	}

	/**
	 * Force the activation of one particularly selected theme.
	 *
	 * @since 2.0.0
	 *
	 * @param string $stylesheet The default theme being used.
	 * @return string
	 */
	public function force_active_theme_stylesheet($stylesheet) {

		if (is_main_site()) {
			return $stylesheet;
		}

		$forced_stylesheet = $this->get_forced_theme_stylesheet();

		return $forced_stylesheet ?: $stylesheet;
	}

	/**
	 * Force the activation of one particularly selected theme.
	 *
	 * @since 2.1.2
	 *
	 * @param string $template The default theme being used.
	 * @return string
	 */
	public function force_active_theme_template($template) {

		if (is_main_site()) {
			return $template;
		}

		$forced_template = $this->get_forced_theme_template();

		return $forced_template ?: $template;
	}

	/**
	 * Deactivates the plugins that people are not allowed to use.
	 *
	 * @since 2.0.0
	 *
	 * @param array $themes Array with the plugins activated.
	 * @return array
	 */
	public function add_extra_available_themes($themes) {
		/*
		 * Bail on network admin =)
		 */
		if (is_network_admin()) {
			return $themes;
		}

		$theme_limitations = wu_get_current_site()->get_limitations()->themes;

		$_themes = $theme_limitations->get_all_themes();

		foreach ($_themes as $theme_stylesheet) {
			$should_appear = $theme_limitations->allowed($theme_stylesheet, 'visible');

			if ( ! $should_appear && isset($themes[ $theme_stylesheet ])) {
				unset($themes[ $theme_stylesheet ]);
			} elseif ($should_appear && ! isset($themes[ $theme_stylesheet ])) {
				$themes[] = $theme_stylesheet;
			}
		}

		return $themes;
	}

	/**
	 * Get the stylesheet of the theme that is forced to be active.
	 *
	 * @since 2.1.2
	 *
	 * @return string|bool The stylesheet of the theme that is forced to be active or false.
	 */
	protected function get_forced_theme_stylesheet() {

		if (null === $this->forced_theme_stylesheet) {
			$this->forced_theme_stylesheet = wu_get_current_site()->get_limitations()->themes->get_forced_active_theme();
		}

		return $this->forced_theme_stylesheet;
	}

	/**
	 * Get the template of the theme that is forced to be active.
	 *
	 * @since 2.1.2
	 *
	 * @return string|bool The template of the theme that is forced to be active or false.
	 */
	protected function get_forced_theme_template() {

		if (null === $this->forced_theme_template) {
			$stylesheet = $this->get_forced_theme_stylesheet();

			$this->forced_theme_template = $stylesheet ? wp_get_theme($stylesheet)->get_template() : false;
		}

		return $this->forced_theme_template;
	}
}
