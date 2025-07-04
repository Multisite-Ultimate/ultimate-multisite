<?php
/**
 * Products Compatibility Layer
 *
 * Handles product compatibility back-ports to Multisite Ultimate 1.X builds.
 *
 * @package WP_Ultimo
 * @subpackage Compat/Product_Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles product compatibility back-ports to Multisite Ultimate 1.X builds.
 *
 * @since 2.0.0
 */
class Product_Compat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_filter('wu_product_options_sections', [$this, 'add_legacy_section'], 100, 2);

		add_filter('update_post_metadata', [$this, 'check_update_plan'], 10, 5);
	}

	/**
	 * Saves meta data from old plugins on the new plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param null   $null Short-circuit control.
	 * @param int    $object_id Object ID, in this case, of the Post object.
	 * @param string $meta_key The meta key being saved.
	 * @param mixed  $meta_value The meta value.
	 * @param mixed  $prev_value The previous value.
	 * @return null
	 */
	public function check_update_plan($null, $object_id, $meta_key, $meta_value, $prev_value) {
		/*
		 * Check if we are in the main site of the network.
		 */
		if ( ! is_main_site()) {
			return;
		}

		/*
		 * Check if we have a new entity with this ID.
		 */
		$migrated_product = wu_get_product($object_id);

		if ( ! $migrated_product) {
			return;
		}

		/*
		 * Prevent double prefixing.
		 */
		$meta_key = str_replace('wpu_', '', $meta_key);

		/*
		 * Save using the new meta table.
		 */
		$migrated_product->update_meta('wpu_' . $meta_key, maybe_serialize($meta_value));

		/**
		 * Explicitly returns null so we don't forget that
		 * returning anything else will prevent meta data from being saved.
		 */
		return null;
	}

	/**
	 * Injects the compatibility panels to products Advanced Options.
	 *
	 * @since 2.0.0
	 *
	 * @param array                     $sections List of tabbed widget sections.
	 * @param \WP_Ultimo\Models\Product $object The model being edited.
	 * @return array
	 */
	public function add_legacy_section($sections, $object) {

		$sections['legacy_options_core'] = [
			'title'  => __('Legacy Options', 'multisite-ultimate'),
			'desc'   => __('Options used by old 1.X versions. ', 'multisite-ultimate'),
			'icon'   => 'dashicons-wu-spreadsheet',
			'state'  => [
				'legacy_options' => $object->get_legacy_options(),
			],
			'fields' => [
				'legacy_options' => [
					'type'      => 'toggle',
					'value'     => $object->get_legacy_options(),
					'title'     => __('Toggle Legacy Options', 'multisite-ultimate'),
					'desc'      => __('Toggle this option to edit legacy options.', 'multisite-ultimate'),
					'html_attr' => [
						'v-model' => 'legacy_options',
					],
				],
				'featured_plan'  => [
					'type'              => 'toggle',
					'value'             => $object->is_featured_plan(),
					'title'             => __('Featured Plan', 'multisite-ultimate'),
					'desc'              => __('Toggle this option to mark this product as featured on the legacy pricing tables.', 'multisite-ultimate'),
					'wrapper_html_attr' => [
						'v-show' => 'legacy_options',
					],
				],
				'feature_list'   => [
					'type'              => 'textarea',
					'title'             => __('Features List', 'multisite-ultimate'),
					'placeholder'       => __('E.g. Feature 1', 'multisite-ultimate') . PHP_EOL . __('Feature 2', 'multisite-ultimate'),
					'desc'              => __('Add a feature per line. These will be shown on the pricing tables.', 'multisite-ultimate'),
					'value'             => $object->get_feature_list(),
					'wrapper_html_attr' => [
						'v-show' => 'legacy_options',
					],
					'html_attr'         => [
						'rows' => 6,
					],
				],
			],
		];

		return $sections;
	}
}
