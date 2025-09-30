<?php
/**
 * Domain_Mapping Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
use WP_Ultimo\Managers\Domain_Manager;

defined('ABSPATH') || exit;

/**
 * Domain_Mapping Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Domain_Mapping extends Limit {

	/**
	 * The module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'domain_mapping';

	/**
	 * The mode of template assignment/selection.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $mode = 'default';

	/**
	 * Sets up the module based on the module data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data The module data.
	 * @return void
	 */
	public function setup($data): void {

		parent::setup($data);

		$this->mode = wu_get_isset($data, 'mode', 'default');
	}

	/**
	 * Returns the mode. Can be one of three: default, assign_template and choose_available_templates.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_mode() {

		return $this->mode;
	}

	/**
	 * The check method is what gets called when allowed is called.
	 *
	 * Each module needs to implement a check method, that returns a boolean.
	 * This check can take any form the developer wants.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed  $value_to_check Value to check.
	 * @param mixed  $limit The list of limits in this modules.
	 * @param string $type Type for sub-checking.
	 * @return bool
	 */
	public function check($value_to_check, $limit, $type = '') {

		if ( ! $this->is_enabled()) {
			return false;
		}

		// For simple boolean limits (enabled/disabled)
		if (is_bool($limit)) {
			return $limit;
		}

		// For numeric limits (number of allowed domains)
		if (is_numeric($limit)) {
			$current_count = $this->get_current_domain_count($value_to_check);
			return $current_count <= $limit;
		}

		// Default to enabled if limit is not properly set
		return true;
	}

	/**
	 * Check all domains for a specific site or membership during downgrade.
	 *
	 * @since 2.0.0
	 *
	 * @param int|null $site_id Site ID to check domains for.
	 * @return array|false Array with current and limit counts if over limit, false if within limit.
	 */
	public function check_all_domains($site_id = null) {

		$current_count = $this->get_current_domain_count($site_id);
		$limit         = $this->is_enabled() ? $this->get_limit() : 0;

		// If limit is boolean true, unlimited domains allowed
		if (is_bool($limit) && $limit) {
			return false;
		}

		// If limit is boolean false, no domains allowed
		if (is_bool($limit) && ! $limit) {
			return $current_count > 0 ? array(
				'current' => $current_count,
				'limit'   => 0,
			) : false;
		}

		// If numeric limit
		if (is_numeric($limit)) {
			return $current_count > $limit ? array(
				'current' => $current_count,
				'limit'   => $limit,
			) : false;
		}

		return false;
	}

	/**
	 * Get the current count of active domains for a site.
	 *
	 * @since 2.0.0
	 *
	 * @param int|null $site_id Site ID to check domains for.
	 * @return int Number of active domains.
	 */
	public function get_current_domain_count($site_id = null) {

		if ( ! $site_id) {
			$site_id = get_current_blog_id();
		}

		$domains = \WP_Ultimo\Models\Domain::get_by_site($site_id);

		if ( ! $domains) {
			return 0;
		}

		// If single domain returned, convert to array
		if ( ! is_array($domains)) {
			$domains = array($domains);
		}

		$active_count = 0;
		foreach ($domains as $domain) {
			$domain_url = $domain->get_domain();
			if (str_starts_with($domain_url, 'www.')) {
				$domain_url = substr($domain_url, 4);
			}

			if (Domain_Manager::is_main_domain($domain_url) && $domain->is_active()) {
				++$active_count;
			}
		}

		return $active_count;
	}

	/**
	 * Returns a default state.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function default_state() {

		return [
			'enabled' => true,
			'limit'   => null,
			'mode'    => 'default',
		];
	}
}
