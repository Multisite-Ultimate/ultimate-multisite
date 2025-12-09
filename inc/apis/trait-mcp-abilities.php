<?php
/**
 * A trait to be included in entities to enable MCP Abilities API integration.
 *
 * @package WP_Ultimo
 * @subpackage Apis
 * @since 2.5.0
 */

namespace WP_Ultimo\Apis;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * MCP Abilities trait.
 *
 * This trait provides methods to register abilities with the WordPress Abilities API
 * for use with the Model Context Protocol (MCP). It follows the same pattern as the
 * Rest_Api trait, allowing managers to expose their entities via MCP.
 *
 * @since 2.5.0
 */
trait MCP_Abilities {

	/**
	 * MCP abilities enabled for this entity.
	 *
	 * @since 2.5.0
	 * @var array
	 */
	protected $enabled_mcp_abilities = [
		'get_item',
		'get_items',
		'create_item',
		'update_item',
		'delete_item',
	];

	/**
	 * Returns the ability prefix used for this entity.
	 * Uses the slug property of the manager.
	 *
	 * @since 2.5.0
	 * @return string
	 */
	public function get_mcp_ability_prefix(): string {

		return 'multisite-ultimate/' . str_replace('_', '-', $this->slug);
	}

	/**
	 * Enable MCP abilities for this entity.
	 * Should be called by the manager to register abilities.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function enable_mcp_abilities(): void {

		$is_enabled = \WP_Ultimo\MCP_Adapter::get_instance()->is_mcp_enabled();

		if (! $is_enabled) {
			return;
		}

		if (! function_exists('wp_register_ability')) {
			return;
		}

		add_action('wp_abilities_api_categories_init', [$this, 'register_ability_category'], 10, 0);
		add_action('wp_abilities_api_init', [$this, 'register_abilities'], 10, 0);
	}

	/**
	 * Register the ability category for this entity.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function register_ability_category(): void {

		if (! function_exists('wp_register_ability_category')) {
			return;
		}

		if (wp_has_ability_category('ultimate-multisite')) {
			return;
		}
		wp_register_ability_category(
			'ultimate-multisite',
			[
				'label'       => __('Multisite Ultimate', 'ultimate-multisite'),
				'description' => __('CRUD operations for Multisite Ultimate entities including customers, sites, products, memberships, and more.', 'ultimate-multisite'),
			]
		);
	}

	/**
	 * Permission callback for MCP abilities.
	 * Checks if the current user has the required capabilities.
	 *
	 * @since 2.5.0
	 * @param array $input_data The input data passed to the ability.
	 * @return bool|\WP_Error True if user has permission, WP_Error otherwise.
	 */
	public function mcp_permission_callback(array $input_data) {
		unset($input_data);

		$capability = "wu_read_{$this->slug}";

		if (! current_user_can($capability) && ! current_user_can('manage_network')) {
			return new \WP_Error(
				'rest_forbidden',
				__('You do not have permission to access this resource.', 'ultimate-multisite'),
				['status' => 403]
			);
		}

		return true;
	}

	/**
	 * Register abilities with the Abilities API.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function register_abilities(): void {

		$ability_prefix = $this->get_mcp_ability_prefix();
		$model_name     = $this->slug;
		$display_name   = ucwords(str_replace(['_', '-'], ' ', $model_name));

		if (in_array('get_item', $this->enabled_mcp_abilities, true)) {
			$this->register_get_item_ability($ability_prefix, $display_name);
		}

		if (in_array('get_items', $this->enabled_mcp_abilities, true)) {
			$this->register_get_items_ability($ability_prefix, $display_name);
		}

		if (in_array('create_item', $this->enabled_mcp_abilities, true)) {
			$this->register_create_item_ability($ability_prefix, $display_name);
		}

		if (in_array('update_item', $this->enabled_mcp_abilities, true)) {
			$this->register_update_item_ability($ability_prefix, $display_name);
		}

		if (in_array('delete_item', $this->enabled_mcp_abilities, true)) {
			$this->register_delete_item_ability($ability_prefix, $display_name);
		}

		/**
		 * Fires after MCP abilities are registered for an entity.
		 *
		 * @since 2.5.0
		 * @param string $ability_prefix The ability prefix.
		 * @param string $model_name The model name.
		 * @param object $this The manager instance.
		 */
		do_action('wu_mcp_abilities_registered', $ability_prefix, $model_name, $this);
	}

	/**
	 * Register the get item ability.
	 *
	 * @since 2.5.0
	 * @param string $ability_prefix The ability prefix.
	 * @param string $display_name The display name.
	 * @return void
	 */
	protected function register_get_item_ability(string $ability_prefix, string $display_name): void {

		wp_register_ability(
			"$ability_prefix-get-item",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'               => sprintf(__('Get %s by ID', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description'         => sprintf(__('Retrieve a single %s by its ID', 'ultimate-multisite'), strtolower($display_name)),
				'category'            => 'ultimate-multisite',
				'execute_callback'    => [$this, 'mcp_get_item'],
				'permission_callback' => [$this, 'mcp_permission_callback'],
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'id' => [
							// translators: %s: entity name (e.g., customer, site, product)
							'description' => sprintf(__('The ID of the %s to retrieve', 'ultimate-multisite'), strtolower($display_name)),
							'type'        => 'integer',
						],
					],
					'required'   => ['id'],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'item' => [
							// translators: %s: entity name (e.g., customer, site, product)
							'description' => sprintf(__('The %s object', 'ultimate-multisite'), strtolower($display_name)),
							'type'        => 'object',
						],
					],
				],
				'meta'                => [
					'mcp' => [
						'public' => true, // Expose via MCP (required for MCP access)
						'type'   => 'tool',
					],
				],
			]
		);
	}

	/**
	 * Register the get items ability.
	 *
	 * @since 2.5.0
	 * @param string $ability_prefix The ability prefix.
	 * @param string $display_name The display name.
	 * @return void
	 */
	protected function register_get_items_ability(string $ability_prefix, string $display_name): void {

		wp_register_ability(
			"$ability_prefix-get-items",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'               => sprintf(__('List %s', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description'         => sprintf(__('Retrieve a list of %s with optional filters', 'ultimate-multisite'), strtolower($display_name)),
				'category'            => 'ultimate-multisite',
				'execute_callback'    => [$this, 'mcp_get_items'],
				'permission_callback' => [$this, 'mcp_permission_callback'],
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'per_page' => [
							'description' => __('Number of items to retrieve per page', 'ultimate-multisite'),
							'type'        => 'integer',
							'default'     => 10,
						],
						'page'     => [
							'description' => __('Page number to retrieve', 'ultimate-multisite'),
							'type'        => 'integer',
							'default'     => 1,
						],
					],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'items' => [
							// translators: %s: entity name (e.g., customer, site, product)
							'description' => sprintf(__('Array of %s objects', 'ultimate-multisite'), strtolower($display_name)),
							'type'        => 'array',
							'items'       => [
								'type' => 'object',
							],
						],
						'total' => [
							'description' => __('Total number of items', 'ultimate-multisite'),
							'type'        => 'integer',
						],
					],
				],
			]
		);
	}

	/**
	 * Register the create item ability.
	 *
	 * @since 2.5.0
	 * @param string $ability_prefix The ability prefix.
	 * @param string $display_name The display name.
	 * @return void
	 */
	protected function register_create_item_ability(string $ability_prefix, string $display_name): void {

		$input_schema = $this->get_mcp_schema_for_ability('create');

		wp_register_ability(
			"$ability_prefix-create-item",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'               => sprintf(__('Create %s', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description'         => sprintf(__('Create a new %s', 'ultimate-multisite'), strtolower($display_name)),
				'category'            => 'ultimate-multisite',
				'execute_callback'    => [$this, 'mcp_create_item'],
				'permission_callback' => [$this, 'mcp_permission_callback'],
				'input_schema'        => $input_schema,
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'item' => [
							// translators: %s: entity name (e.g., customer, site, product)
							'description' => sprintf(__('The created %s object', 'ultimate-multisite'), strtolower($display_name)),
							'type'        => 'object',
						],
					],
				],
			]
		);
	}

	/**
	 * Register the update item ability.
	 *
	 * @since 2.5.0
	 * @param string $ability_prefix The ability prefix.
	 * @param string $display_name The display name.
	 * @return void
	 */
	protected function register_update_item_ability(string $ability_prefix, string $display_name): void {

		$input_schema = $this->get_mcp_schema_for_ability('update');

		// Add ID to the properties
		$input_schema['properties']['id'] = [
			// translators: %s: entity name (e.g., customer, site, product)
			'description' => sprintf(__('The ID of the %s to update', 'ultimate-multisite'), strtolower($display_name)),
			'type'        => 'integer',
		];

		// Add ID to required fields
		if (! isset($input_schema['required'])) {
			$input_schema['required'] = [];
		}
		$input_schema['required'][] = 'id';

		wp_register_ability(
			"$ability_prefix-update-item",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'               => sprintf(__('Update %s', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description'         => sprintf(__('Update an existing %s', 'ultimate-multisite'), strtolower($display_name)),
				'category'            => 'ultimate-multisite',
				'execute_callback'    => [$this, 'mcp_update_item'],
				'permission_callback' => [$this, 'mcp_permission_callback'],
				'input_schema'        => $input_schema,
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'item' => [
							// translators: %s: entity name (e.g., customer, site, product)
							'description' => sprintf(__('The updated %s object', 'ultimate-multisite'), strtolower($display_name)),
							'type'        => 'object',
						],
					],
				],
			]
		);
	}

	/**
	 * Register the delete item ability.
	 *
	 * @since 2.5.0
	 * @param string $ability_prefix The ability prefix.
	 * @param string $display_name The display name.
	 * @return void
	 */
	protected function register_delete_item_ability(string $ability_prefix, string $display_name): void {

		wp_register_ability(
			"$ability_prefix-delete-item",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'               => sprintf(__('Delete %s', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description'         => sprintf(__('Delete a %s by its ID', 'ultimate-multisite'), strtolower($display_name)),
				'category'            => 'ultimate-multisite',
				'execute_callback'    => [$this, 'mcp_delete_item'],
				'permission_callback' => [$this, 'mcp_permission_callback'],
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'id' => [
							// translators: %s: entity name (e.g., customer, site, product)
							'description' => sprintf(__('The ID of the %s to delete', 'ultimate-multisite'), strtolower($display_name)),
							'type'        => 'integer',
						],
					],
					'required'   => ['id'],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [
							'description' => __('Whether the deletion was successful', 'ultimate-multisite'),
							'type'        => 'boolean',
						],
					],
				],
			]
		);
	}

	/**
	 * Get MCP schema for an ability (create or update).
	 * Returns JSON Schema format for input_schema.
	 *
	 * @since 2.5.0
	 * @param string $context The context (create or update).
	 * @return array
	 */
	protected function get_mcp_schema_for_ability(string $context = 'create'): array {

		if (! method_exists($this, 'get_arguments_schema')) {
			return [];
		}

		$rest_schema = $this->get_arguments_schema('update' === $context);

		$properties = [];
		$required   = [];

		foreach ($rest_schema as $key => $args) {
			$properties[ $key ] = [
				'description' => $args['description'] ?? ucfirst(str_replace('_', ' ', $key)),
				'type'        => $args['type'] ?? 'string',
			];

			if (isset($args['default'])) {
				$properties[ $key ]['default'] = $args['default'];
			}

			if (isset($args['enum'])) {
				$properties[ $key ]['enum'] = $args['enum'];
			}

			if (isset($args['required']) && $args['required']) {
				$required[] = $key;
			}
		}

		$schema = [
			'type'       => 'object',
			'properties' => $properties,
		];

		if (! empty($required)) {
			$schema['required'] = $required;
		}

		return $schema;
	}

	/**
	 * MCP callback to get a single item.
	 *
	 * @since 2.5.0
	 * @param array $args The arguments passed to the ability.
	 * @return array|\WP_Error
	 */
	public function mcp_get_item(array $args) {

		if (! isset($args['id'])) {
			return new \WP_Error('missing_id', __('ID is required', 'ultimate-multisite'));
		}

		$item = $this->model_class::get_by_id($args['id']);

		if (empty($item)) {
			return new \WP_Error(
				"wu_{$this->slug}_not_found",
				sprintf(
					// translators: %s: entity name (e.g., Customer, Site, Product)
					__('%s not found', 'ultimate-multisite'),
					ucfirst(str_replace('_', ' ', $this->slug))
				)
			);
		}

		return [
			'item' => $item->to_array(),
		];
	}

	/**
	 * MCP callback to get a list of items.
	 *
	 * @since 2.5.0
	 * @param array $args The arguments passed to the ability.
	 * @return array
	 */
	public function mcp_get_items(array $args): array {

		$query_args = array_merge(
			[
				'per_page' => 10,
				'page'     => 1,
			],
			$args
		);

		$items = $this->model_class::query($query_args);

		$total = $this->model_class::query(array_merge($query_args, ['count' => true]));

		return [
			'items' => array_map(
				function ($item) {
					return $item->to_array();
				},
				$items
			),
			'total' => $total,
		];
	}

	/**
	 * MCP callback to create an item.
	 *
	 * @since 2.5.0
	 * @param array $args The arguments passed to the ability.
	 * @return array|\WP_Error
	 */
	public function mcp_create_item(array $args) {

		$model_name = (new $this->model_class([]))->model;

		$saver_function = "wu_create_{$model_name}";

		if (function_exists($saver_function)) {
			$item = call_user_func($saver_function, $args);

			$saved = is_wp_error($item) ? $item : true;
		} else {
			$item = new $this->model_class($args);

			$saved = $item->save();
		}

		if (is_wp_error($saved)) {
			return $saved;
		}

		if (! $saved) {
			return new \WP_Error(
				"wu_{$this->slug}_save_failed",
				__('Failed to save item', 'ultimate-multisite')
			);
		}

		return [
			'item' => $item->to_array(),
		];
	}

	/**
	 * MCP callback to update an item.
	 *
	 * @since 2.5.0
	 * @param array $args The arguments passed to the ability.
	 * @return array|\WP_Error
	 */
	public function mcp_update_item(array $args) {

		if (! isset($args['id'])) {
			return new \WP_Error('missing_id', __('ID is required', 'ultimate-multisite'));
		}

		$id = $args['id'];
		unset($args['id']);

		$item = $this->model_class::get_by_id($id);

		if (empty($item)) {
			return new \WP_Error(
				"wu_{$this->slug}_not_found",
				sprintf(
					// translators: %s: entity name (e.g., Customer, Site, Product)
					__('%s not found', 'ultimate-multisite'),
					ucfirst(str_replace('_', ' ', $this->slug))
				)
			);
		}

		foreach ($args as $param => $value) {
			$set_method = "set_{$param}";

			if ('meta' === $param) {
				$item->update_meta_batch($value);
			} elseif (method_exists($item, $set_method)) {
				call_user_func([$item, $set_method], $value);
			}
		}

		$saved = $item->save();

		if (is_wp_error($saved)) {
			return $saved;
		}

		if (! $saved) {
			return new \WP_Error(
				"wu_{$this->slug}_save_failed",
				__('Failed to update item', 'ultimate-multisite')
			);
		}

		return [
			'item' => $item->to_array(),
		];
	}

	/**
	 * MCP callback to delete an item.
	 *
	 * @since 2.5.0
	 * @param array $args The arguments passed to the ability.
	 * @return array|\WP_Error
	 */
	public function mcp_delete_item(array $args) {

		if (! isset($args['id'])) {
			return new \WP_Error('missing_id', __('ID is required', 'ultimate-multisite'));
		}

		$item = $this->model_class::get_by_id($args['id']);

		if (empty($item)) {
			return new \WP_Error(
				"wu_{$this->slug}_not_found",
				sprintf(
					// translators: %s: entity name (e.g., Customer, Site, Product)
					__('%s not found', 'ultimate-multisite'),
					ucfirst(str_replace('_', ' ', $this->slug))
				)
			);
		}

		$result = $item->delete();

		return [
			'success' => ! is_wp_error($result) && $result,
		];
	}
}
