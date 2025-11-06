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

		return 'wu_' . $this->slug;
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

		if (! function_exists('register_ability')) {
			return;
		}

		add_action('wu_mcp_adapter_initialized', [$this, 'register_abilities']);
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

		register_ability(
			"{$ability_prefix}_get_item",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'       => sprintf(__('Get %s by ID', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description' => sprintf(__('Retrieve a single %s by its ID', 'ultimate-multisite'), strtolower($display_name)),
				'callback'    => [$this, 'mcp_get_item'],
				'inputs'      => [
					'id' => [
						'label'       => __('ID', 'ultimate-multisite'),
						// translators: %s: entity name (e.g., customer, site, product)
						'description' => sprintf(__('The ID of the %s to retrieve', 'ultimate-multisite'), strtolower($display_name)),
						'type'        => 'integer',
						'required'    => true,
					],
				],
				'outputs'     => [
					'item' => [
						'label'       => $display_name,
						// translators: %s: entity name (e.g., customer, site, product)
						'description' => sprintf(__('The %s object', 'ultimate-multisite'), strtolower($display_name)),
						'type'        => 'object',
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

		register_ability(
			"{$ability_prefix}_get_items",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'       => sprintf(__('List %s', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description' => sprintf(__('Retrieve a list of %s with optional filters', 'ultimate-multisite'), strtolower($display_name)),
				'callback'    => [$this, 'mcp_get_items'],
				'inputs'      => [
					'per_page' => [
						'label'       => __('Per Page', 'ultimate-multisite'),
						'description' => __('Number of items to retrieve per page', 'ultimate-multisite'),
						'type'        => 'integer',
						'required'    => false,
						'default'     => 10,
					],
					'page'     => [
						'label'       => __('Page', 'ultimate-multisite'),
						'description' => __('Page number to retrieve', 'ultimate-multisite'),
						'type'        => 'integer',
						'required'    => false,
						'default'     => 1,
					],
				],
				'outputs'     => [
					'items' => [
						'label'       => $display_name,
						// translators: %s: entity name (e.g., customer, site, product)
						'description' => sprintf(__('Array of %s objects', 'ultimate-multisite'), strtolower($display_name)),
						'type'        => 'array',
					],
					'total' => [
						'label'       => __('Total', 'ultimate-multisite'),
						'description' => __('Total number of items', 'ultimate-multisite'),
						'type'        => 'integer',
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

		$schema = $this->get_mcp_schema_for_ability('create');

		register_ability(
			"{$ability_prefix}_create_item",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'       => sprintf(__('Create %s', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description' => sprintf(__('Create a new %s', 'ultimate-multisite'), strtolower($display_name)),
				'callback'    => [$this, 'mcp_create_item'],
				'inputs'      => $schema,
				'outputs'     => [
					'item' => [
						'label'       => $display_name,
						// translators: %s: entity name (e.g., customer, site, product)
						'description' => sprintf(__('The created %s object', 'ultimate-multisite'), strtolower($display_name)),
						'type'        => 'object',
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

		$schema = $this->get_mcp_schema_for_ability('update');

		register_ability(
			"{$ability_prefix}_update_item",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'       => sprintf(__('Update %s', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description' => sprintf(__('Update an existing %s', 'ultimate-multisite'), strtolower($display_name)),
				'callback'    => [$this, 'mcp_update_item'],
				'inputs'      => array_merge(
					[
						'id' => [
							'label'       => __('ID', 'ultimate-multisite'),
							// translators: %s: entity name (e.g., customer, site, product)
							'description' => sprintf(__('The ID of the %s to update', 'ultimate-multisite'), strtolower($display_name)),
							'type'        => 'integer',
							'required'    => true,
						],
					],
					$schema
				),
				'outputs'     => [
					'item' => [
						'label'       => $display_name,
						// translators: %s: entity name (e.g., customer, site, product)
						'description' => sprintf(__('The updated %s object', 'ultimate-multisite'), strtolower($display_name)),
						'type'        => 'object',
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

		register_ability(
			"{$ability_prefix}_delete_item",
			[
				// translators: %s: entity name (e.g., Customer, Site, Product)
				'label'       => sprintf(__('Delete %s', 'ultimate-multisite'), $display_name),
				// translators: %s: entity name (e.g., customer, site, product)
				'description' => sprintf(__('Delete a %s by its ID', 'ultimate-multisite'), strtolower($display_name)),
				'callback'    => [$this, 'mcp_delete_item'],
				'inputs'      => [
					'id' => [
						'label'       => __('ID', 'ultimate-multisite'),
						// translators: %s: entity name (e.g., customer, site, product)
						'description' => sprintf(__('The ID of the %s to delete', 'ultimate-multisite'), strtolower($display_name)),
						'type'        => 'integer',
						'required'    => true,
					],
				],
				'outputs'     => [
					'success' => [
						'label'       => __('Success', 'ultimate-multisite'),
						'description' => __('Whether the deletion was successful', 'ultimate-multisite'),
						'type'        => 'boolean',
					],
				],
			]
		);
	}

	/**
	 * Get MCP schema for an ability (create or update).
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

		$mcp_schema = [];

		foreach ($rest_schema as $key => $args) {
			$mcp_schema[ $key ] = [
				'label'       => $args['description'] ?? ucfirst(str_replace('_', ' ', $key)),
				'description' => $args['description'] ?? '',
				'type'        => $args['type'] ?? 'string',
				'required'    => $args['required'] ?? false,
			];

			if (isset($args['default'])) {
				$mcp_schema[ $key ]['default'] = $args['default'];
			}

			if (isset($args['enum'])) {
				$mcp_schema[ $key ]['enum'] = $args['enum'];
			}
		}

		return $mcp_schema;
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
