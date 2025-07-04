<?php
/**
 * Adds the Limnits and Quotas element as BB, Elementor and Widget.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\UI\Base_Element;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Limits and Quotas element as BB, Elementor and Widget.
 *
 * @since 2.0.0
 */
class Limits_Element extends Base_Element {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The id of the element.
	 *
	 * Something simple, without prefixes, like 'checkout', or 'pricing-tables'.
	 *
	 * This is used to construct shortcodes by prefixing the id with 'wu_'
	 * e.g. an id checkout becomes the shortcode 'wu_checkout' and
	 * to generate the Gutenberg block by prefixing it with 'wp-ultimo/'
	 * e.g. checkout would become the block 'wp-ultimo/checkout'.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $id = 'limits';

	/**
	 * Controls if this is a public element to be used in pages/shortcodes by user.
	 *
	 * @since 2.0.24
	 * @var boolean
	 */
	protected $public = true;

	/**
	 * The current site.
	 *
	 * @since 2.2.0
	 * @var \WP_Ultimo\Models\Site
	 */
	protected $site;

	/**
	 * The icon of the UI element.
	 * e.g. return fa fa-search
	 *
	 * @since 2.0.0
	 * @param string $context One of the values: block, elementor or bb.
	 */
	public function get_icon($context = 'block'): string {

		if ('elementor' === $context) {
			return 'eicon-skill-bar';
		}

		return 'fa fa-search';
	}

	/**
	 * The title of the UI element.
	 *
	 * This is used on the Blocks list of Gutenberg.
	 * You should return a string with the localized title.
	 * e.g. return __('My Element', 'multisite-ultimate').
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Limits & Quotas', 'multisite-ultimate');
	}

	/**
	 * The description of the UI element.
	 *
	 * This is also used on the Gutenberg block list
	 * to explain what this block is about.
	 * You should return a string with the localized title.
	 * e.g. return __('Adds a checkout form to the page', 'multisite-ultimate').
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Adds a checkout form block to the page.', 'multisite-ultimate');
	}

	/**
	 * The list of fields to be added to Gutenberg.
	 *
	 * If you plan to add Gutenberg controls to this block,
	 * you'll need to return an array of fields, following
	 * our fields interface (@see inc/ui/class-field.php).
	 *
	 * You can create new Gutenberg panels by adding fields
	 * with the type 'header'. See the Checkout Elements for reference.
	 *
	 * @see inc/ui/class-checkout-element.php
	 *
	 * Return an empty array if you don't have controls to add.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function fields() {

		$fields = [];

		$fields['header'] = [
			'title' => __('General', 'multisite-ultimate'),
			'desc'  => __('General', 'multisite-ultimate'),
			'type'  => 'header',
		];

		$fields['title'] = [
			'type'    => 'text',
			'title'   => __('Title', 'multisite-ultimate'),
			'value'   => __('Site Limits', 'multisite-ultimate'),
			'desc'    => __('Leave blank to hide the title completely.', 'multisite-ultimate'),
			'tooltip' => '',
		];

		$fields['columns'] = [
			'type'    => 'number',
			'title'   => __('Columns', 'multisite-ultimate'),
			'desc'    => __('How many columns to use.', 'multisite-ultimate'),
			'tooltip' => '',
			'value'   => 1,
			'min'     => 1,
			'max'     => 10,
		];

		return $fields;
	}

	/**
	 * The list of keywords for this element.
	 *
	 * Return an array of strings with keywords describing this
	 * element. Gutenberg uses this to help customers find blocks.
	 *
	 * e.g.:
	 * return array(
	 *  'Multisite Ultimate',
	 *  'Checkout',
	 *  'Form',
	 *  'Cart',
	 * );
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function keywords() {

		return [
			'WP Ultimo',
			'Multisite Ultimate',
			'Account',
			'Limits',
			'Quotas',
		];
	}

	/**
	 * List of default parameters for the element.
	 *
	 * If you are planning to add controls using the fields,
	 * it might be a good idea to use this method to set defaults
	 * for the parameters you are expecting.
	 *
	 * These defaults will be used inside a 'wp_parse_args' call
	 * before passing the parameters down to the block render
	 * function and the shortcode render function.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		return [
			'columns' => 1,
			'title'   => __('Site Limits', 'multisite-ultimate'),
		];
	}

	/**
	 * Runs early on the request lifecycle as soon as we detect the shortcode is present.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup(): void {

		$this->site = WP_Ultimo()->currents->get_site();

		if ( ! $this->site || ! $this->site->is_customer_allowed()) {
			$this->set_display(false);
		}
	}

	/**
	 * Allows the setup in the context of previews.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_preview(): void {

		$this->site = wu_mock_site();
	}

	/**
	 * The content to be output on the screen.
	 *
	 * Should return HTML markup to be used to display the block.
	 * This method is shared between the block render method and
	 * the shortcode implementation.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Parameters of the block/shortcode.
	 * @param string|null $content The content inside the shortcode.
	 * @return string
	 */
	public function output($atts, $content = null) {

		$post_types = get_post_types(
			[
				'public' => true,
			],
			'objects'
		);

		/*
		 * Remove post types that where disabled or that are not available for display.
		 */
		$post_types = array_filter($post_types, fn($post_type_slug) => $this->site->get_limitations()->post_types->{$post_type_slug}->enabled, ARRAY_FILTER_USE_KEY);

		/**
		 * Allow developers to select which post types should be displayed.
		 *
		 * @since 2.0.0
		 * @param array $post_types List of post types.
		 * @return array New list.
		 */
		$post_types = apply_filters('wu_get_post_types', $post_types);

		$items_to_display = wu_get_setting('limits_and_quotas');

		$atts['site']             = $this->site;
		$atts['post_types']       = $post_types;
		$atts['items_to_display'] = $items_to_display ? array_keys($items_to_display) : false;
		$atts['post_type_limits'] = $this->site->get_limitations()->post_types;

		return wu_get_template_contents('dashboard-widgets/limits-and-quotas', $atts);
	}
}
