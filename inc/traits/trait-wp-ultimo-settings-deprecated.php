<?php
/**
 * A trait to be included in entities to WP_Settings Class depecrated methods.
 *
 * @package WP_Ultimo
 * @subpackage Deprecated
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

/**
 * WP_Ultimo_Settings_Deprecated trait.
 */
trait WP_Ultimo_Settings_Deprecated {

	/**
	 * Adds the legacy scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_legacy_scripts(): void {
		/*
		* Mailchimp: Backwards compatibility.
		*/
		if (wp_script_is('wu-mailchimp', 'registered')) {
			wp_enqueue_script('wu-mailchimp');
		}
	}

	/**
	 * Handle legacy hooks to support old versions of our add-ons.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_legacy_filters(): void {

		$legacy_settings = [];

		/*
		* Fetch Extra Sections
		*/
		$sections = apply_filters_deprecated('wu_settings_sections', [[]], '2.0.0', 'wu_register_settings_section()');

		foreach ($sections as $section_key => $section) {
			if ('activation' === $section_key) {
				continue; // No activation stuff;

			}

			$legacy_settings = array_merge($legacy_settings, $section['fields']);
		}

		$filters = [
			'wu_settings_section_general',
			'wu_settings_section_network',
			'wu_settings_section_domain_mapping',
			'wu_settings_section_payment_gateways',
			'wu_settings_section_emails',
			'wu_settings_section_styling',
			'wu_settings_section_tools',
			'wu_settings_section_advanced',
		];

		foreach ($filters as $filter) {
			$message = __('Adding setting sections directly via filters is no longer supported.', 'multisite-ultimate');

			$legacy_settings = apply_filters_deprecated($filter, [$legacy_settings], '2.0.0', 'wu_register_settings_field()', $message);
		}

		if ($legacy_settings) {
			$this->add_section(
				'other',
				[
					'title' => __('Other', 'multisite-ultimate'),
					'desc'  => __('Other', 'multisite-ultimate'),
				]
			);

			foreach ($legacy_settings as $setting_key => $setting) {
				if (str_contains((string) $setting_key, 'license_key_')) {
					continue; // Remove old license key fields

				}

				$this->add_field('other', $setting_key, $setting);
			}
		}
	}
}
