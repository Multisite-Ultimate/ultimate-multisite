<?php
/**
 * Multisite Ultimate Customize/Add New Template Previewer Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Template Previewer Customize/Add New Admin Page.
 */
abstract class Customizer_Admin_Page extends Edit_Admin_Page {

	/**
	 * Should we force the admin menu into a folded state?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $fold_menu = true;

	/**
	 * The preview area height.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $preview_height = '120vh';

	/**
	 * Returns the preview URL. This is then added to the iframe.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview_url() {

		return get_site_url(null);
	}

	/**
	 * Adds hooks when the page loads.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded(): void {

		/**
		 * Process save, if necessary
		 */
		$this->process_save();

		$screen = get_current_screen();

		add_action("wu_edit_{$screen->id}_after_normal", [$this, 'display_preview_window']);
	}

	/**
	 * Adds the preview window.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_preview_window(): void {

		wu_get_template(
			'base/edit/editor-customizer',
			[
				'preview_iframe_url' => $this->get_preview_url(),
				'preview_height'     => $this->preview_height,
			]
		);
	}

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts(): void {

		parent::register_scripts();

		wp_enqueue_script(
			'wu-customizer',
			wu_get_asset('customizer.js', 'js'),
			['jquery', 'wu-vue', 'wu-block-ui'],
			\WP_Ultimo::VERSION,
			[
				'in_footer' => true,
			]
		);

		wp_enqueue_style('wp-color-picker');

		wp_enqueue_script('wp-color-picker');

		wp_enqueue_media();
	}

	/**
	 * Checkout_Forms have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return false;
	}

	/**
	 * Not needed.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_object() {}
}
