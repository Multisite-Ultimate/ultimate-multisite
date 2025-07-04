<?php
/**
 * The Multisite Ultimate Overview QM Panel
 *
 * @package WP_Ultimo
 * @subpackage Development\Query_Monitor\Panel
 * @since 2.0.11
 */

namespace WP_Ultimo\Development\Query_Monitor\Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * The Multisite Ultimate Overview QM Panel
 *
 * @since 2.0.11
 */
class Overview extends \QM_Output_Html {

	/**
	 * Initializes the panel.
	 *
	 * @since 2.0.11
	 *
	 * @param \QM_Collector $collector The collector associated with the panel.
	 */
	public function __construct($collector) {

		parent::__construct($collector);

		add_filter('qm/output/menus', [$this, 'admin_menu'], 1000);

		add_filter('qm/output/panel_menus', [$this, 'panel_menu'], 1000);
	}

	/**
	 * The name of the panel.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	public function name() {

		return __('Multisite Ultimate', 'multisite-ultimate');
	}

	/**
	 * Output the contents of the panel.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function output(): void {

		$data = $this->collector->get_data();

		$this->before_tabular_output(); // phpcs:disable ?>

			<thead>
				<th>Header 1</th>
				<th>Header 2</th>
			</thead>

			<tbody>
				<td>Value Title</td>
				<td>Value</td>
			</tbody>

		<?php // phpcs:enable

		$this->after_tabular_output();

		$this->before_non_tabular_output();

		$data = $this->collector->get_data();

		$this->after_non_tabular_output();
	}

	/**
	 * Adds the panel to the admin panel.
	 *
	 * @since 2.0.11
	 *
	 * @param array $menu The original menu.
	 * @return array
	 */
	public function admin_menu(array $menu) {

		return $menu;
	}

	/**
	 * Adds a panel menu for the panel.
	 *
	 * @since 2.0.11
	 *
	 * @param array $menu The admin panel menu.
	 * @return array
	 */
	public function panel_menu(array $menu) {

		$new_menu = [
			'wp-ultimo' => $this->menu(
				[
					'title' => esc_html__('Multisite Ultimate', 'multisite-ultimate'),
					'id'    => 'wp-ultimo',
				]
			),
		];

		return $new_menu + $menu;
	}
}
