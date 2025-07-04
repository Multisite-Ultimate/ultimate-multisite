<?php
/**
 * A trait to be included in entities to enable REST API endpoints.
 *
 * @package WP_Ultimo
 * @subpackage Apis
 * @since 2.0.0
 */

namespace WP_Ultimo\Apis;

/**
 * REST API trait.
 */
trait Rest_Api {

	/**
	 * The base used in the route right after the namespace: <namespace>/<rest_base>.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * REST endpoints enabled for this entity.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $enabled_rest_endpoints = [
		'get_item',
		'get_items',
		'create_item',
		'update_item',
		'delete_item',
	];

	/**
	 * Returns the base used right after the namespace.
	 * Uses the `rest_base` attribute if set, `slug` otherwise.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_rest_base() {

		return (! empty($this->rest_base)) ? $this->rest_base : $this->slug;
	}

	/**
	 * Registers the routes. Should be called by the entity
	 * to actually enable the REST API.
	 *
	 * @since 2.0.0
	 */
	public function enable_rest_api(): void {

		$is_enabled = \WP_Ultimo\API::get_instance()->is_api_enabled();

		if ($is_enabled) {
			add_action('rest_api_init', [$this, 'register_routes_general']);

			add_action('rest_api_init', [$this, 'register_routes_with_id']);
		}
	}

	/**
	 * Register the endpoints that don't need an ID,
	 * like creation and lists.
	 *
	 * @since 2.0.0
	 */
	public function register_routes_general(): void {

		$routes = [];

		if (in_array('get_items', $this->enabled_rest_endpoints, true)) {
			$routes = [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [$this, 'get_items_rest'],
					'permission_callback' => [$this, 'get_items_permissions_check'],
				],
			];
		}

		if (in_array('create_item', $this->enabled_rest_endpoints, true)) {
			$routes[] = [
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [$this, 'create_item_rest'],
				'permission_callback' => [$this, 'create_item_permissions_check'],
				'args'                => $this->get_arguments_schema(),
			];
		}

		if ( ! empty($routes)) {
			register_rest_route(
				\WP_Ultimo\API::get_instance()->get_namespace(),
				'/' . $this->get_rest_base(),
				$routes,
				true
			);
		}

		do_action('wu_rest_register_routes_general', $routes, $this->get_rest_base(), 'create', $this);
	}

	/**
	 * Register the endpoints that need an ID,
	 * like get, update and delete of a single element.
	 *
	 * @since 2.0.0
	 */
	public function register_routes_with_id(): void {

		$routes = [];

		if (in_array('get_item', $this->enabled_rest_endpoints, true)) {
			$routes[] = [
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [$this, 'get_item_rest'],
				'permission_callback' => [$this, 'get_item_permissions_check'],
			];
		}

		if (in_array('update_item', $this->enabled_rest_endpoints, true)) {
			$routes[] = [
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [$this, 'update_item_rest'],
				'permission_callback' => [$this, 'update_item_permissions_check'],
				'args'                => $this->get_arguments_schema(true),
			];
		}

		if (in_array('delete_item', $this->enabled_rest_endpoints, true)) {
			$routes[] = [
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [$this, 'delete_item_rest'],
				'permission_callback' => [$this, 'delete_item_permissions_check'],
			];
		}

		if ( ! empty($routes)) {
			register_rest_route(
				\WP_Ultimo\API::get_instance()->get_namespace(),
				'/' . $this->get_rest_base() . '/(?P<id>[\d]+)',
				$routes,
				true
			);
		}

		do_action('wu_rest_register_routes_with_id', $routes, $this->get_rest_base(), 'update', $this);
	}

	/**
	 * Returns a specific item.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item_rest($request) {

		$item = $this->model_class::get_by_id($request['id']);

		if (empty($item)) {
			return new \WP_Error("wu_rest_{$this->slug}_invalid_id", __('Item not found.', 'multisite-ultimate'), ['status' => 404]);
		}

		return rest_ensure_response($item);
	}

	/**
	 * Returns a list of items.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_items_rest($request) {

		$items = $this->model_class::query($request->get_params());

		return rest_ensure_response($items);
	}

	/**
	 * Creates an item.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item_rest($request) {

		$body = json_decode($request->get_body(), true);

		$model_name = (new $this->model_class([]))->model;

		$saver_function = "wu_create_{$model_name}";

		if (function_exists($saver_function)) {
			$item = call_user_func($saver_function, $body);

			$saved = is_wp_error($item) ? $item : true;
		} else {
			$item = new $this->model_class($body);

			$saved = $item->save();
		}

		if (is_wp_error($saved)) {
			return rest_ensure_response($saved);
		}

		if ( ! $saved) {
			return new \WP_Error("wu_rest_{$this->slug}", __('Something went wrong (Code 1).', 'multisite-ultimate'), ['status' => 400]);
		}

		return rest_ensure_response($item);
	}

	/**
	 * Updates an item.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item_rest($request) {

		$id = wu_get_isset($request->get_url_params(), 'id');

		$item = $this->model_class::get_by_id($id);

		if (empty($item)) {
			return new \WP_Error("wu_rest_{$this->slug}_invalid_id", __('Item not found.', 'multisite-ultimate'), ['status' => 404]);
		}

		$params = array_filter(
			json_decode($request->get_body(), true),
			[$this, 'is_not_credential_key'],
			ARRAY_FILTER_USE_KEY
		);

		foreach ($params as $param => $value) {
			$set_method = "set_{$param}";

			if ('meta' === $param) {
				$item->update_meta_batch($value);
			} elseif (method_exists($item, $set_method)) {
				call_user_func([$item, $set_method], $value);
			} else {
				$error_message = sprintf(
					/* translators: 1. Object class name; 2. Set method name */
					__('The %1$s object does not have a %2$s method', 'multisite-ultimate'),
					get_class($item),
					$set_method
				);

				return new \WP_Error(
					"wu_rest_{$this->slug}_invalid_set_method",
					$error_message,
					['status' => 400]
				);
			}
		}

		$saved = $item->save();

		if (is_wp_error($saved)) {
			return rest_ensure_response($saved);
		}

		if ( ! $saved) {
			return new \WP_Error("wu_rest_{$this->slug}", __('Something went wrong (Code 2).', 'multisite-ultimate'));
		}

		return rest_ensure_response($item);
	}

	/**
	 * Deletes an item.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item_rest($request) {

		$item = $this->model_class::get_by_id($request['id']);

		if (empty($item)) {
			return new \WP_Error("wu_rest_{$this->slug}_invalid_id", __('Item not found.', 'multisite-ultimate'), ['status' => 404]);
		}

		$result = $item->delete();

		return rest_ensure_response($result);
	}

	/**
	 * Check permissions to list items.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return bool
	 */
	public function get_items_permissions_check($request) {

		if ( ! \WP_Ultimo\API::get_instance()->check_authorization($request)) {
			return false;
		}

		/**
		 * Filters if it is allowed to proceed with the request or not.
		 *
		 * @since 2.0.0
		 *
		 * @param bool         $allowed   Initial return value.
		 * @param array        $rest_base Entity slug.
		 * @param Base_Manager $this      The object instance.
		 */
		return apply_filters('wu_rest_get_items', true, $this->get_rest_base(), $this);
	}

	/**
	 * Check permissions to create an item.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return bool
	 */
	public function create_item_permissions_check($request) {

		if ( ! \WP_Ultimo\API::get_instance()->check_authorization($request)) {
			return false;
		}

		/**
		 * Filters if it is allowed to proceed with the request or not.
		 *
		 * @since 2.0.0
		 *
		 * @param bool         $allowed   Initial return value.
		 * @param array        $rest_base Entity slug.
		 * @param Base_Manager $this      The object instance.
		 */
		return apply_filters('wu_rest_create_item', true, $this->get_rest_base(), $this);
	}

	/**
	 * Check permissions to get an item.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return bool
	 */
	public function get_item_permissions_check($request) {

		if ( ! \WP_Ultimo\API::get_instance()->check_authorization($request)) {
			return false;
		}

		/**
		 * Filters if it is allowed to proceed with the request or not.
		 *
		 * @since 2.0.0
		 *
		 * @param bool         $allowed   Initial return value.
		 * @param array        $rest_base Entity slug.
		 * @param Base_Manager $this      The object instance.
		 */
		return apply_filters('wu_rest_get_item', true, $this->get_rest_base(), $this);
	}

	/**
	 * Check permissions to update an item.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return bool
	 */
	public function update_item_permissions_check($request) {

		if ( ! \WP_Ultimo\API::get_instance()->check_authorization($request)) {
			return false;
		}

		/**
		 * Filters if it is allowed to proceed with the request or not.
		 *
		 * @since 2.0.0
		 *
		 * @param bool         $allowed   Initial return value.
		 * @param array        $rest_base Entity slug.
		 * @param Base_Manager $this      The object instance.
		 */
		return apply_filters('wu_rest_update_item', true, $this->get_rest_base(), $this);
	}

	/**
	 * Check permissions to delete an item.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return bool
	 */
	public function delete_item_permissions_check($request) {

		if ( ! \WP_Ultimo\API::get_instance()->check_authorization($request)) {
			return false;
		}

		/**
		 * Filters if it is allowed to proceed with the request or not.
		 *
		 * @since 2.0.0
		 *
		 * @param bool         $allowed   Initial return value.
		 * @param array        $rest_base Entity slug.
		 * @param Base_Manager $this      The object instance.
		 */
		return apply_filters('wu_rest_delete_item', true, $this->get_rest_base(), $this);
	}

	/**
	 * Checks if a value is not a credential key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value The value that will be checked.
	 * @return bool
	 */
	private function is_not_credential_key($value) {

		$credentials_keys = [
			'api_key',
			'api_secret',
			'api-key',
			'api-secret',
		];

		return ! in_array($value, $credentials_keys, true);
	}

	/**
	 * Checks if a value is not equal to "id".
	 *
	 * @since 2.0.0
	 *
	 * @param string $value The value that will be checked.
	 * @return bool
	 */
	private function is_not_id_key($value) {

		$arr = [
			'id',
		];

		if ('site' === $this->slug) {
			$arr = [
				'id',
				'blog_id',
			];
		}

		return ! in_array($value, $arr, true);
	}

	/**
	 * Get the arguments for an endpoint
	 *
	 * @since 2.0.0
	 *
	 * @param bool $edit Context. In edit, some fields, like ids, are not mandatory.
	 * @return array
	 */
	public function get_arguments_schema($edit = false) {

		$schema = wu_rest_get_endpoint_schema($this->model_class, $edit ? 'update' : 'create', true);

		$args = array_filter($schema, [$this, 'is_not_id_key'], ARRAY_FILTER_USE_KEY);

		return $this->filter_schema_arguments($args);
	}

	/**
	 * Remove some properties from the API schema.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Schema array.
	 * @return array
	 */
	public function filter_schema_arguments($args) {

		/**
		 * Filter the original api arguments.
		 *
		 * @since 2.0.0
		 *
		 * @param array $args API Arguments for this manager.
		 * @param object $this This manager.
		 */
		apply_filters('wu_before_' . $this->slug . '_api_arguments', $args, $this);

		if ('broadcast' !== $this->slug && isset($args['author_id'])) {
			unset($args['author_id']);
		}

		if (isset($args['list_order'])) {
			unset($args['list_order']);
		}

		$remove_status = apply_filters(
			"wu_api_{$this->slug}_remove_status",
			[
				'broadcast',
				'membership',
				'product',
				'payment',
			]
		);

		if ( ! in_array($this->slug, $remove_status, true) && isset($args['status'])) {
			unset($args['status']);
		}

		$remove_slug = apply_filters(
			"wu_api_{$this->slug}_remove_slug",
			[
				'broadcast',
				'product',
				'checkout_form',
				'event',
			]
		);

		if ( ! in_array($this->slug, $remove_slug, true) && isset($args['slug'])) {
			unset($args['slug']);
		}

		if ('product' === $this->slug && isset($args['price_variations'])) {
			unset($args['price_variations']);
		}

		if ('payment' === $this->slug && isset($args['line_items'])) {
			unset($args['line_items']);
		}

		if ('site' === $this->slug) {
			if (isset($args['duplication_arguments'])) {
				unset($args['duplication_arguments']);
			}

			if (isset($args['transient'])) {
				unset($args['transient']);
			}
		}

		if ('email' === $this->slug) {
			if (isset($args['status'])) {
				unset($args['status']);
			}

			if (isset($args['email_schedule'])) {
				unset($args['email_schedule']);
			}
		}

		if ('broadcast' === $this->slug) {
			if (isset($args['message_targets'])) {
				unset($args['message_targets']);
			}
		}

		if (isset($args['billing_address'])) {
			unset($args['billing_address']);
		}

		/**
		 * Filter after being changed.
		 *
		 * @since 2.0.0
		 *
		 * @param array $args API Arguments for this manager.
		 * @param object $this This manager.
		 */
		apply_filters('wu_after_' . $this->slug . '_api_arguments', $args, $this);

		return $args;
	}
}
