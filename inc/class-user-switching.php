<?php
/**
 * Multisite Ultimate User_Switching
 *
 * Log string messages to a file with a timestamp. Useful for debugging.
 *
 * @package WP_Ultimo
 * @subpackage User_Switching
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate User_Switching
 *
 * @since 2.0.0
 */
class User_Switching {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Constructor for the User_Switching.
	 */
	public function __construct() {

		add_action('plugins_loaded', [$this, 'register_forms']);
	}

	/**
	 * Check if Plugin User Switching is activated
	 *
	 * @since 2.0.0
	 */
	public function check_user_switching_is_activated(): bool {

		return class_exists('user_switching');
	}

	/**
	 * Register forms
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_forms(): void {

		wu_register_form(
			'install_user_switching',
			[
				'render' => [$this, 'render_install_user_switching'],
			]
		);
	}

	/**
	 * Create Install Form of User Switching
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function render_install_user_switching(): void {

		$fields = [
			'title' => [
				'type'          => 'text-display',
				'title'         => '',
				'display_value' => __('This feature requires the plugin <strong>User Switching</strong> to be installed and active.', 'multisite-ultimate'),
				'tooltip'       => '',
			],
			'link'  => [
				'type'            => 'link',
				'display_value'   => __('Install User Switching', 'multisite-ultimate'),
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end wu-text-center wu-bg-gray-100',
				'html_attr'       => [
					'href' => add_query_arg(
						[
							's'    => 'user-switching',
							'tab'  => 'search',
							'type' => 'tag',
						],
						network_admin_url('plugin-install.php')
					),
				],
			],
		];

		$form = new \WP_Ultimo\UI\Form(
			'install_user_switching',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => [],
			]
		);

		$form->render();
	}

	/**
	 * This function return should return the correct url
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User Id.
	 *
	 * @return string
	 */
	public function render($user_id) {

		$user = new \WP_User($user_id);

		if ( ! $this->check_user_switching_is_activated()) {
			return wu_get_form_url('install_user_switching');
		} else {
			$link = \user_switching::switch_to_url($user);

			return $link;
		}
	}
}
