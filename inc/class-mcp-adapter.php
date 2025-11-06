<?php
/**
 * MCP Adapter initialization and management.
 *
 * @package WP_Ultimo
 * @subpackage MCP
 * @since 2.5.0
 */

namespace WP_Ultimo;

use WP\MCP\Core\McpAdapter as McpAdapterCore;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * MCP Adapter integration for Ultimate Multisite.
 *
 * Initializes the WordPress MCP adapter and manages the integration
 * with the Abilities API to expose Ultimate Multisite functionality
 * via the Model Context Protocol.
 *
 * @since 2.5.0
 */
class MCP_Adapter implements \WP_Ultimo\Interfaces\Singleton {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The MCP adapter instance.
	 *
	 * @since 2.5.0
	 * @var McpAdapterCore|null
	 */
	private ?McpAdapterCore $adapter = null;

	/**
	 * Initiates the MCP adapter hooks.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function init(): void {

		/**
		 * Check if MCP adapter is available.
		 *
		 * @since 2.5.0
		 */
		if (! class_exists(McpAdapterCore::class)) {
			return;
		}

		/**
		 * Initialize the MCP adapter.
		 *
		 * @since 2.5.0
		 */
		add_action('init', [$this, 'initialize_adapter'], 10);

		/**
		 * Add the admin settings for MCP.
		 *
		 * @since 2.5.0
		 */
		add_action('init', [$this, 'add_settings'], 20);
	}

	/**
	 * Initialize the MCP adapter instance.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function initialize_adapter(): void {

		if (! $this->is_mcp_enabled()) {
			return;
		}

		try {
			$this->adapter = McpAdapterCore::instance();

			/**
			 * Fires after the MCP adapter is initialized.
			 *
			 * Allows other plugins and themes to register their own abilities.
			 *
			 * @since 2.5.0
			 * @param MCP_Adapter $mcp_adapter The MCP adapter instance.
			 */
			do_action('wu_mcp_adapter_initialized', $this);
		} catch (\Exception $e) {
			wu_log_add(
				'mcp-adapter',
				sprintf(
					// translators: %s: error message from the exception
					__('Failed to initialize MCP adapter: %s', 'ultimate-multisite'),
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * Add the admin interface to configure MCP adapter.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function add_settings(): void {

		wu_register_settings_field(
			'api',
			'mcp_header',
			[
				'title' => __('MCP Adapter Settings', 'ultimate-multisite'),
				'desc'  => __('Options related to the Model Context Protocol (MCP) adapter. MCP allows AI assistants to interact with Ultimate Multisite programmatically.', 'ultimate-multisite'),
				'type'  => 'header',
			]
		);

		wu_register_settings_field(
			'api',
			'enable_mcp',
			[
				'title'   => __('Enable MCP Adapter', 'ultimate-multisite'),
				'desc'    => __('Tick this box to enable the Model Context Protocol (MCP) adapter. This allows AI assistants to interact with Ultimate Multisite through the Abilities API.', 'ultimate-multisite'),
				'type'    => 'toggle',
				'default' => 0,
			]
		);

		wu_register_settings_field(
			'api',
			'mcp_server_url',
			[
				'title'   => __('MCP Server URL', 'ultimate-multisite'),
				'desc'    => sprintf(
					// translators: %s: the HTTP endpoint URL for the MCP server
					__('HTTP endpoint: %s', 'ultimate-multisite'),
					'<code>' . rest_url('mcp/mcp-adapter-default-server') . '</code>'
				),
				'tooltip' => __('This is the URL where the MCP server is accessible via HTTP.', 'ultimate-multisite'),
				'type'    => 'note',
				'classes' => 'wu-text-gray-700 wu-text-xs',
				'require' => [
					'enable_mcp' => 1,
				],
			]
		);

		wu_register_settings_field(
			'api',
			'mcp_stdio_command',
			[
				'title'   => __('STDIO Command', 'ultimate-multisite'),
				'desc'    => sprintf(
					// translators: %s: the WP-CLI command to run the MCP server
					__('Command: %s', 'ultimate-multisite'),
					'<code>wp mcp-adapter serve --server=mcp-adapter-default-server --user=admin</code>'
				),
				'tooltip' => __('This is the WP-CLI command to run the MCP server via STDIO transport.', 'ultimate-multisite'),
				'type'    => 'note',
				'classes' => 'wu-text-gray-700 wu-text-xs',
				'require' => [
					'enable_mcp' => 1,
				],
			]
		);
	}

	/**
	 * Get the MCP adapter instance.
	 *
	 * @since 2.5.0
	 * @return McpAdapterCore|null
	 */
	public function get_adapter(): ?McpAdapterCore {

		return $this->adapter;
	}

	/**
	 * Checks if the MCP adapter is enabled.
	 *
	 * @since 2.5.0
	 * @return bool
	 */
	public function is_mcp_enabled(): bool {

		/**
		 * Allow plugin developers to force a given state for the MCP adapter.
		 *
		 * @since 2.5.0
		 * @param bool $enabled Whether the MCP adapter is enabled.
		 * @return bool
		 */
		return apply_filters('wu_is_mcp_enabled', wu_get_setting('enable_mcp', false));
	}
}
