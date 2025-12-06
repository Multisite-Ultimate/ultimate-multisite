<?php
/**
 * Command Palette REST Controller
 *
 * Handles REST API endpoints for command palette search functionality.
 *
 * @package WP_Ultimo
 * @subpackage Apis
 * @since 2.1.0
 */

namespace WP_Ultimo\Apis;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Command Palette REST Controller class.
 *
 * @since 2.1.0
 */
class Command_Palette_Rest_Controller {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The namespace for this controller's routes.
	 *
	 * @since 2.1.0
	 * @var string
	 */
	protected $namespace = 'ultimate-multisite/v1';

	/**
	 * The base for this controller's routes.
	 *
	 * @since 2.1.0
	 * @var string
	 */
	protected $rest_base = 'command-palette';

	/**
	 * Initialize the singleton.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function init(): void {

		add_action('rest_api_init', [$this, 'register_routes']);
	}

	/**
	 * Register the routes for command palette.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function register_routes(): void {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/search',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [$this, 'search'],
					'permission_callback' => [$this, 'search_permissions_check'],
					'args'                => $this->get_search_params(),
				],
			]
		);
	}

	/**
	 * Get the query params for search.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	public function get_search_params(): array {

		return [
			'query'       => [
				'description'       => __('Search query string.', 'ultimate-multisite'),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function ($param) {
					return strlen($param) >= 2;
				},
			],
			'entity_type' => [
				'description'       => __('Entity type to search (optional).', 'ultimate-multisite'),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
			],
			'limit'       => [
				'description'       => __('Maximum number of results to return.', 'ultimate-multisite'),
				'type'              => 'integer',
				'required'          => false,
				'default'           => 15,
				'minimum'           => 1,
				'maximum'           => 20,
				'sanitize_callback' => 'absint',
			],
		];
	}

	/**
	 * Check permissions for search endpoint.
	 *
	 * @since 2.1.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return bool|\WP_Error
	 */
	public function search_permissions_check($request): bool {
		unset($request);

		if (! current_user_can('manage_network')) {
			return new \WP_Error(
				'rest_forbidden',
				__('You do not have permission to search.', 'ultimate-multisite'),
				['status' => 403]
			);
		}

		return true;
	}

	/**
	 * Handle search request.
	 *
	 * @since 2.1.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function search($request) {

		$query       = $request->get_param('query');
		$entity_type = $request->get_param('entity_type');
		$limit       = $request->get_param('limit') ?: 15;

		// Minimum query length
		if (strlen($query) < 2) {
			return rest_ensure_response(
				[
					'results' => [],
					'message' => __('Query must be at least 2 characters.', 'ultimate-multisite'),
				]
			);
		}

		$results = [];

		// Get registered entities from Command Palette Manager
		$manager  = \WP_Ultimo\UI\Command_Palette_Manager::get_instance();
		$entities = $manager->get_registered_entities();

		// If no entities registered, return empty results
		if (empty($entities)) {
			return rest_ensure_response(
				[
					'results' => [],
					'total'   => 0,
					'message' => __('No searchable entities registered yet.', 'ultimate-multisite'),
				]
			);
		}

		// If entity_type is specified, search only that type
		if (! empty($entity_type) && isset($entities[ $entity_type ])) {
			$results = $this->search_entity_type($entity_type, $query, $limit);
		} else {
			// Search all entity types
			$entity_count   = count($entities);
			$per_type_limit = max(1, floor($limit / $entity_count));

			foreach ($entities as $slug => $config) {
				$entity_results = $this->search_entity_type($slug, $query, $per_type_limit);
				$results        = array_merge($results, $entity_results);

				// Stop if we've reached the limit
				if (count($results) >= $limit) {
					break;
				}
			}

			// Trim to limit
			$results = array_slice($results, 0, $limit);
		}

		/**
		 * Filter command palette search results.
		 *
		 * @since 2.1.0
		 *
		 * @param array  $results     Search results.
		 * @param string $query       Search query.
		 * @param string $entity_type Entity type (empty if searching all).
		 */
		$results = apply_filters('wu_command_palette_search_results', $results, $query, $entity_type);

		return rest_ensure_response(
			[
				'results' => $results,
				'total'   => count($results),
			]
		);
	}

	/**
	 * Search a specific entity type.
	 *
	 * @since 2.1.0
	 *
	 * @param string $entity_slug Entity slug.
	 * @param string $query Search query.
	 * @param int    $limit Maximum results.
	 * @return array
	 */
	protected function search_entity_type(string $entity_slug, string $query, int $limit): array {

		// Get the manager class for this entity
		$manager_class = $this->get_manager_class($entity_slug);

		if (! $manager_class || ! class_exists($manager_class)) {
			return [];
		}

		$manager = $manager_class::get_instance();

		if (! $manager || ! method_exists($manager, 'search_for_command_palette')) {
			return [];
		}

		// Check user capability
		$config = $manager->get_command_palette_config();

		if (! empty($config['capability']) && ! current_user_can($config['capability'])) {
			return [];
		}

		return $manager->search_for_command_palette($query, $limit);
	}

	/**
	 * Get the manager class name for an entity slug.
	 *
	 * @since 2.1.0
	 *
	 * @param string $entity_slug Entity slug.
	 * @return string|null
	 */
	protected function get_manager_class(string $entity_slug): ?string {

		$manager_name = str_replace(' ', '_', ucwords(str_replace(['_', '-'], ' ', $entity_slug)));

		$class_name = "\\WP_Ultimo\\Managers\\{$manager_name}_Manager";

		return class_exists($class_name) ? $class_name : null;
	}
}
