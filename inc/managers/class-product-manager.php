<?php
/**
 * Product Manager
 *
 * Handles processes related to products.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Product_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to products.
 *
 * @since 2.0.0
 */
class Product_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api;
	use \WP_Ultimo\Apis\WP_CLI;
	use \WP_Ultimo\Apis\MCP_Abilities;
	use \WP_Ultimo\Apis\Command_Palette;
	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'product';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = \WP_Ultimo\Models\Product::class;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		$this->enable_mcp_abilities();

		$this->enable_command_palette();
	}
}
