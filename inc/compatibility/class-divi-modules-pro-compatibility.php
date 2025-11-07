<?php
/**
 * Divi Modules Pro Compatibility
 *
 * Adds compatibility fixes for the Divi Modules Pro plugin.
 *
 * @package WP_Ultimo\Compatibility
 * @since   2.0.0
 */

namespace WP_Ultimo\Compatibility;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds compatibility fixes for the Divi Modules Pro plugin.
 *
 * @since 2.0.0
 */
class Divi_Modules_Pro_Compatibility {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Initializes the compatibility class.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		// Only load if Divi Modules Pro is active
		if (!$this->is_divi_modules_pro_active()) {
			return;
		}

		// Disable memory trap when using Divi editor
		add_action('admin_init', array($this, 'maybe_disable_memory_trap'), 5);

		// Optimize memory usage when using Divi editor
		add_action('admin_init', array($this, 'optimize_memory_usage'), 5);

		// Add compatibility notice
		add_action('admin_notices', array($this, 'add_compatibility_notice'));
	}

	/**
	 * Checks if Divi Modules Pro is active.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_divi_modules_pro_active() {
		
		// Check if the plugin is active
		if (function_exists('is_plugin_active')) {
			return is_plugin_active('divi-modules-pro/divi-modules-pro.php');
		}
		
		// Alternative check if function is not available
		return class_exists('DiviModulesPro') || defined('DIVI_MODULES_PRO_VERSION');
	}

	/**
	 * Checks if we're in the Divi editor.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_divi_editor() {
		
		// Check if we're in the Divi editor
		if (isset($_GET['et_fb']) && $_GET['et_fb'] == 1) {
			return true;
		}
		
		// Check if we're in the Divi builder
		if (isset($_GET['et_pb_preview']) && $_GET['et_pb_preview'] == 'true') {
			return true;
		}
		
		// Check if we're editing a page with Divi
		if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['post'])) {
			$post_id = absint($_GET['post']);
			$post = get_post($post_id);
			
			if ($post && $post->post_type == 'page' && function_exists('et_pb_is_pagebuilder_used')) {
				return et_pb_is_pagebuilder_used($post_id);
			}
		}
		
		return false;
	}

	/**
	 * Disables the memory trap when using the Divi editor.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_disable_memory_trap() {
		
		if ($this->is_divi_editor()) {
			// Remove the memory trap setup
			remove_all_actions('wu_setup_memory_limit_trap');
			
			// Set a reasonable memory limit instead of unlimited
			@ini_set('memory_limit', '256M');
		}
	}

	/**
	 * Optimizes memory usage when using the Divi editor.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function optimize_memory_usage() {
		
		if ($this->is_divi_editor()) {
			// Disable unnecessary WP Ultimo features that might consume memory
			add_filter('wu_should_log_api_calls', '__return_false');
			
			// Disable any large data structure serialization
			add_filter('wu_pre_json_encode', array($this, 'limit_json_encode_size'), 10, 2);
		}
	}

	/**
	 * Limits the size of data structures being JSON encoded.
	 *
	 * @since 2.0.0
	 * @param mixed $data The data to be encoded.
	 * @param string $context The context of the encoding.
	 * @return mixed
	 */
	public function limit_json_encode_size($data, $context = '') {
		
		// If it's an array or object, check its size
		if (is_array($data) || is_object($data)) {
			$data_size = $this->get_approximate_size($data);
			
			// If data is too large (over 1MB), return a simplified version
			if ($data_size > 1048576) { // 1MB in bytes
				if (is_object($data)) {
					return (object) array(
						'__truncated' => 'Data was too large to encode safely',
						'__size' => $this->format_bytes($data_size)
					);
				} else {
					return array(
						'__truncated' => 'Data was too large to encode safely',
						'__size' => $this->format_bytes($data_size)
					);
				}
			}
		}
		
		return $data;
	}

	/**
	 * Gets the approximate size of a variable in bytes.
	 *
	 * @since 2.0.0
	 * @param mixed $var The variable to check.
	 * @return int
	 */
	private function get_approximate_size($var) {
		
		$size = 0;
		
		if (is_null($var)) {
			$size = 0;
		} elseif (is_bool($var)) {
			$size = 1;
		} elseif (is_int($var) || is_float($var)) {
			$size = 8;
		} elseif (is_string($var)) {
			$size = strlen($var);
		} elseif (is_array($var)) {
			foreach ($var as $key => $value) {
				$size += $this->get_approximate_size($key) + $this->get_approximate_size($value);
				
				// Prevent excessive recursion
				if ($size > 5242880) { // 5MB
					return $size;
				}
			}
		} elseif (is_object($var)) {
			$props = get_object_vars($var);
			foreach ($props as $key => $value) {
				$size += $this->get_approximate_size($key) + $this->get_approximate_size($value);
				
				// Prevent excessive recursion
				if ($size > 5242880) { // 5MB
					return $size;
				}
			}
		}
		
		return $size;
	}

	/**
	 * Formats bytes into a human-readable format.
	 *
	 * @since 2.0.0
	 * @param int $bytes The number of bytes.
	 * @return string
	 */
	private function format_bytes($bytes) {
		
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		
		$bytes /= pow(1024, $pow);
		
		return round($bytes, 2) . ' ' . $units[$pow];
	}

	/**
	 * Adds a compatibility notice for Divi Modules Pro.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_compatibility_notice() {
		
		// Only show on Divi-related pages
		$screen = get_current_screen();
		
		if (!$screen || !$this->is_divi_modules_pro_active()) {
			return;
		}
		
		// Check if we're on a Divi-related page
		$divi_pages = array('et_divi_options', 'divi_modules_pro');
		
		if (!in_array($screen->id, $divi_pages) && strpos($screen->id, 'divi') === false) {
			return;
		}
		
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<strong><?php _e('WP Multisite WaaS & Divi Modules Pro Compatibility', 'wp-multisite-waas'); ?></strong>
			</p>
			<p>
				<?php _e('WP Multisite WaaS has detected that you are using Divi Modules Pro. We have enabled compatibility mode to prevent memory issues when editing pages with Divi.', 'wp-multisite-waas'); ?>
			</p>
			<p>
				<?php _e('If you still experience memory issues, consider increasing your PHP memory limit or disabling some unused plugins while editing with Divi.', 'wp-multisite-waas'); ?>
			</p>
		</div>
		<?php
	}

}
