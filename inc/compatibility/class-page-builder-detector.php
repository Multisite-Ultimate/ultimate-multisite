<?php
/**
 * Page Builder Detector
 *
 * Detects active page builders and adjusts memory settings accordingly.
 *
 * @package WP_Ultimo\Compatibility
 * @since   2.0.0
 */

namespace WP_Ultimo\Compatibility;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Detects active page builders and adjusts memory settings accordingly.
 *
 * @since 2.0.0
 */
class Page_Builder_Detector {

    use \WP_Ultimo\Traits\Singleton;

    /**
     * List of known page builders and their detection methods.
     *
     * @since 2.0.0
     * @var array
     */
    private $page_builders = array(
        'divi' => array(
            'active_callback' => 'is_divi_active',
            'editor_callback' => 'is_divi_editor',
        ),
        'elementor' => array(
            'active_callback' => 'is_elementor_active',
            'editor_callback' => 'is_elementor_editor',
        ),
        'beaver' => array(
            'active_callback' => 'is_beaver_active',
            'editor_callback' => 'is_beaver_editor',
        ),
        'gutenberg' => array(
            'active_callback' => 'is_gutenberg_active',
            'editor_callback' => 'is_gutenberg_editor',
        ),
        // Add more page builders as needed
    );

    /**
     * Initializes the detector.
     *
     * @since 2.0.0
     * @return void
     */
    public function init() {
        // Check if any page builder is active
        if (!$this->is_any_page_builder_active()) {
            return;
        }

        // Configure memory management based on page builder detection
        add_action('admin_init', array($this, 'configure_memory_management'), 5);

        // Add compatibility notice
        add_action('admin_notices', array($this, 'add_compatibility_notice'));
    }

    /**
     * Checks if any supported page builder is active.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_any_page_builder_active() {
        foreach ($this->page_builders as $builder => $callbacks) {
            if (method_exists($this, $callbacks['active_callback']) && call_user_func(array($this, $callbacks['active_callback']))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if any page builder editor is active.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_any_editor_active() {
        foreach ($this->page_builders as $builder => $callbacks) {
            if (method_exists($this, $callbacks['editor_callback']) && call_user_func(array($this, $callbacks['editor_callback']))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the active page builder name.
     *
     * @since 2.0.0
     * @return string|false
     */
    public function get_active_builder() {
        foreach ($this->page_builders as $builder => $callbacks) {
            if (method_exists($this, $callbacks['editor_callback']) && call_user_func(array($this, $callbacks['editor_callback']))) {
                return $builder;
            }
        }
        return false;
    }

    /**
     * Gets the server's memory limit in bytes.
     *
     * @since 2.0.0
     * @return int
     */
    private function get_server_memory_limit() {
        $memory_limit = ini_get('memory_limit');

        // If memory_limit is set to -1 (unlimited), use a reasonable default
        if ($memory_limit == -1) {
            return 512 * 1024 * 1024; // 512MB as a reasonable default
        }

        // Convert memory limit to bytes
        $unit = strtolower(substr($memory_limit, -1));
        $value = (int) substr($memory_limit, 0, -1);

        switch ($unit) {
            case 'g':
                $value *= 1024;
                // Fall through
            case 'm':
                $value *= 1024;
                // Fall through
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Calculates an appropriate memory limit based on server settings.
     *
     * @since 2.0.0
     * @return string
     */
    private function calculate_adaptive_memory_limit() {
        $server_limit = $this->get_server_memory_limit();

        // Set the limit to 75% of the server's limit, but not less than 256MB
        $adaptive_limit = max(256 * 1024 * 1024, $server_limit * 0.75);

        // Convert back to a string with appropriate unit
        if ($adaptive_limit >= 1024 * 1024 * 1024) {
            return round($adaptive_limit / (1024 * 1024 * 1024), 1) . 'G';
        } else {
            return round($adaptive_limit / (1024 * 1024), 0) . 'M';
        }
    }

    /**
     * Configures memory management based on page builder detection.
     *
     * @since 2.0.0
     * @return void
     */
    public function configure_memory_management() {
        // If a page builder editor is active, use adaptive memory management
        if ($this->is_any_editor_active()) {
            // Get the Memory_Trap instance
            $memory_trap = \WP_Ultimo\Internal\Memory_Trap::get_instance();

            // Set an adaptive memory limit
            $adaptive_limit = $this->calculate_adaptive_memory_limit();
            $memory_trap->set_enabled(true);
            $memory_trap->set_memory_limit($adaptive_limit);

            // Disable unnecessary features that might consume memory
            add_filter('wu_should_log_api_calls', '__return_false');

            // Add a filter for JSON encoding to prevent memory issues
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
     * Adds a compatibility notice for page builders.
     *
     * @since 2.0.0
     * @return void
     */
    public function add_compatibility_notice() {
        // Only show on relevant pages
        $screen = get_current_screen();

        if (!$screen || !$this->is_any_editor_active()) {
            return;
        }

        $builder = $this->get_active_builder();
        if (!$builder) {
            return;
        }

        $builder_name = ucfirst($builder);
        $memory_limit = $this->calculate_adaptive_memory_limit();

        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong><?php printf(__('WP Multisite WaaS & %s Compatibility', 'wp-ultimo'), $builder_name); ?></strong>
            </p>
            <p>
                <?php printf(__('WP Multisite WaaS has detected that you are using %s. We have enabled compatibility mode with an adaptive memory limit of %s to prevent memory issues when editing pages.', 'wp-ultimo'), $builder_name, $memory_limit); ?>
            </p>
            <p>
                <?php _e('If you still experience memory issues, consider increasing your PHP memory limit in your hosting control panel or disabling some unused plugins while editing.', 'wp-ultimo'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Checks if Divi is active.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_divi_active() {
        return class_exists('ET_Builder_Plugin') ||
               class_exists('DiviModulesPro') ||
               defined('ET_BUILDER_VERSION') ||
               defined('DIVI_MODULES_PRO_VERSION');
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
     * Checks if Elementor is active.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_elementor_active() {
        return defined('ELEMENTOR_VERSION') || class_exists('\Elementor\Plugin');
    }

    /**
     * Checks if we're in the Elementor editor.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_elementor_editor() {
        if (!$this->is_elementor_active()) {
            return false;
        }

        // Check if we're in the Elementor editor
        if (isset($_GET['action']) && $_GET['action'] == 'elementor') {
            return true;
        }

        // Check if Elementor is in edit mode
        if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return true;
        }

        return false;
    }

    /**
     * Checks if Beaver Builder is active.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_beaver_active() {
        return class_exists('FLBuilder') || defined('FL_BUILDER_VERSION');
    }

    /**
     * Checks if we're in the Beaver Builder editor.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_beaver_editor() {
        if (!$this->is_beaver_active()) {
            return false;
        }

        // Check if we're in the Beaver Builder editor
        if (isset($_GET['fl_builder'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks if Gutenberg is active.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_gutenberg_active() {
        return function_exists('register_block_type');
    }

    /**
     * Checks if we're in the Gutenberg editor.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function is_gutenberg_editor() {
        if (!$this->is_gutenberg_active()) {
            return false;
        }

        // Check if we're in the Gutenberg editor
        $current_screen = get_current_screen();
        if ($current_screen && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) {
            return true;
        }

        return false;
    }
}
