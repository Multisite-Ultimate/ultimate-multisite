<?php
/**
 * A trait to be included in entities to enable Command Palette integration.
 *
 * @package WP_Ultimo
 * @subpackage Apis
 * @since 2.1.0
 */

namespace WP_Ultimo\Apis;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Command Palette trait.
 *
 * This trait provides methods to register entities with the WordPress Command Palette
 * for quick navigation and search functionality. It follows the same pattern as the
 * Rest_Api and MCP_Abilities traits, allowing managers to expose their entities
 * via the command palette.
 *
 * @since 2.1.0
 */
trait Command_Palette {

	/**
	 * Whether command palette is enabled for this entity.
	 *
	 * @since 2.1.0
	 * @var bool
	 */
	protected $command_palette_enabled = true;

	/**
	 * Enable command palette for this entity.
	 * Should be called by the manager to register with the command palette.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function enable_command_palette(): void {

		if (! $this->command_palette_enabled) {
			return;
		}

		// Register this entity with the Command Palette Manager
		add_action('init', [$this, 'register_with_command_palette_manager'], 20);
	}

	/**
	 * Register this entity with the Command Palette Manager.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function register_with_command_palette_manager(): void {

		$manager = \WP_Ultimo\UI\Command_Palette_Manager::get_instance();

		if (! $manager) {
			return;
		}

		$config = $this->get_command_palette_config();

		if (empty($config)) {
			return;
		}

		$manager->register_entity_type($this->slug, $config);
	}

	/**
	 * Get the command palette configuration for this entity.
	 * Managers can override this method to customize their configuration.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	public function get_command_palette_config(): array {

		$display_name = $this->get_entity_display_name();

		return [
			'label'            => $display_name,
			'label_plural'     => $this->get_entity_display_name_plural(),
			'icon'             => $this->get_entity_icon(),
			'edit_url_pattern' => $this->get_edit_url_pattern(),
			'search_fields'    => $this->get_search_fields(),
			'capability'       => "wu_read_{$this->slug}",
		];
	}

	/**
	 * Get the display name for this entity (singular).
	 *
	 * @since 2.1.0
	 * @return string
	 */
	protected function get_entity_display_name(): string {

		$name = str_replace(['_', '-'], ' ', $this->slug);

		return ucfirst($name);
	}

	/**
	 * Get the display name for this entity (plural).
	 *
	 * @since 2.1.0
	 * @return string
	 */
	protected function get_entity_display_name_plural(): string {

		return $this->get_entity_display_name() . 's';
	}

	/**
	 * Get the dashicon for this entity.
	 *
	 * @since 2.1.0
	 * @return string
	 */
	protected function get_entity_icon(): string {

		$icons = [
			'customer'      => 'admin-users',
			'site'          => 'admin-multisite',
			'membership'    => 'id-alt',
			'payment'       => 'money-alt',
			'product'       => 'products',
			'domain'        => 'networking',
			'discount_code' => 'tickets-alt',
			'webhook'       => 'rest-api',
			'broadcast'     => 'megaphone',
			'checkout_form' => 'feedback',
		];

		return $icons[ $this->slug ] ?? 'admin-generic';
	}

	/**
	 * Get the edit URL pattern for this entity.
	 *
	 * @since 2.1.0
	 * @return string
	 */
	protected function get_edit_url_pattern(): string {

		return "admin.php?page=wp-ultimo-edit-{$this->slug}&id=%d";
	}

	/**
	 * Get the fields to search for this entity.
	 *
	 * @since 2.1.0
	 * @return array
	 */
	protected function get_search_fields(): array {

		$default_fields = ['name', 'display_name', 'title'];

		// Entity-specific search fields
		$entity_fields = [
			'customer'      => ['display_name', 'email', 'user_login'],
			'site'          => ['title', 'domain', 'path'],
			'membership'    => ['reference_code', 'customer_id'],
			'payment'       => ['reference_code', 'customer_id'],
			'product'       => ['name', 'slug'],
			'domain'        => ['domain', 'primary_domain'],
			'discount_code' => ['code', 'name'],
			'webhook'       => ['name', 'webhook_url'],
			'broadcast'     => ['title', 'slug'],
			'checkout_form' => ['name', 'slug'],
		];

		return $entity_fields[ $this->slug ] ?? $default_fields;
	}

	/**
	 * Search entities for the command palette.
	 * This method is called by the REST controller.
	 *
	 * @since 2.1.0
	 *
	 * @param string $query The search query.
	 * @param int    $limit Maximum number of results to return.
	 * @return array Array of search results.
	 */
	public function search_for_command_palette(string $query, int $limit = 15): array {

		if (strlen($query) < 2) {
			return [];
		}

		// Use the existing model query function
		$getter_function = "wu_get_{$this->slug}s";

		if (! function_exists($getter_function)) {
			return [];
		}

		$results = call_user_func(
			$getter_function,
			[
				'search' => "*{$query}*",
				'number' => $limit,
			]
		);

		if (empty($results)) {
			return [];
		}

		return array_map([$this, 'format_result_for_command_palette'], $results);
	}

	/**
	 * Format a single result for the command palette.
	 *
	 * @since 2.1.0
	 *
	 * @param object $item The entity object.
	 * @return array Formatted result.
	 */
	protected function format_result_for_command_palette($item): array {

		$config = $this->get_command_palette_config();

		$title    = $this->get_item_title($item);
		$subtitle = $this->get_item_subtitle($item);
		$url      = $this->get_item_url($item, $config['edit_url_pattern']);

		return [
			'id'       => $item->get_id(),
			'type'     => $this->slug,
			'title'    => $title,
			'subtitle' => $subtitle,
			'url'      => $url,
			'icon'     => $config['icon'],
		];
	}

	/**
	 * Get the display title for an item.
	 *
	 * @since 2.1.0
	 *
	 * @param object $item The entity object.
	 * @return string
	 */
	protected function get_item_title($item): string {

		if (method_exists($item, 'get_display_name')) {
			return $item->get_display_name();
		}

		if (method_exists($item, 'get_title')) {
			return $item->get_title();
		}

		if (method_exists($item, 'get_name')) {
			return $item->get_name();
		}

		return sprintf('#%d', $item->get_id());
	}

	/**
	 * Get the subtitle/description for an item.
	 *
	 * @since 2.1.0
	 *
	 * @param object $item The entity object.
	 * @return string
	 */
	protected function get_item_subtitle($item): string {

		$parts = [];

		// Add ID
		// Translators: %d the id of the item.
		$parts[] = sprintf(__('ID: %d', 'ultimate-multisite'), $item->get_id());

		// Entity-specific subtitles
		switch ($this->slug) {
			case 'customer':
				if (method_exists($item, 'get_email_address')) {
					$parts[] = $item->get_email_address();
				}
				break;

			case 'site':
				if (method_exists($item, 'get_domain')) {
					$parts[] = $item->get_domain();
				}
				break;

			case 'membership':
			case 'payment':
				if (method_exists($item, 'get_reference_code')) {
					$parts[] = $item->get_reference_code();
				}
				break;

			case 'domain':
				if (method_exists($item, 'get_domain')) {
					$parts[] = $item->get_domain();
				}
				break;

			case 'discount_code':
				if (method_exists($item, 'get_code')) {
					$parts[] = $item->get_code();
				}
				break;
		}

		return implode(' • ', array_filter($parts));
	}

	/**
	 * Get the edit URL for an item.
	 *
	 * @since 2.1.0
	 *
	 * @param object $item The entity object.
	 * @param string $pattern URL pattern with %d placeholder.
	 * @return string
	 */
	protected function get_item_url($item, string $pattern): string {

		$url = sprintf($pattern, $item->get_id());

		return network_admin_url($url);
	}
}
