<?php
/**
 * Jumper trigger view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<small>
	<strong>
	<a id="wu-container-toggle" role="tooltip" aria-label='<?php esc_html_e('Toggle container', 'ultimate-multisite'); ?>' href="#" class="wu-tooltip wu-inline-block wu-py-1 wu-pl-2 md:wu-pr-3 wu-uppercase wu-text-gray-600 wu-no-underline">

		<span title="<?php esc_attr_e('Boxed', 'ultimate-multisite'); ?>" class="wu-use-container dashicons dashicons-wu-arrow-with-circle-left wu-text-sm wu-w-auto wu-h-auto wu-align-text-bottom wu-relative"></span>
		<span class="wu-font-bold wu-use-container">
		<?php esc_attr_e('Boxed', 'ultimate-multisite'); ?>
		</span>

		<span title="<?php esc_attr_e('Boxed', 'ultimate-multisite'); ?>" class="wu-no-container dashicons dashicons-wu-arrow-with-circle-right wu-text-sm wu-w-auto wu-h-auto wu-align-text-bottom wu-relative"></span>
		<span class="wu-font-bold wu-no-container">
		<?php esc_attr_e('Wide', 'ultimate-multisite'); ?>
		</span>

	</a>
	</strong>
</small>

<?php
wp_enqueue_style('wu-container-toggle', wu_get_asset('container-toggle.css', 'css'), [], wu_get_version());
wp_enqueue_script('wu-container-toggle', wu_get_asset('container-toggle.js', 'js'), ['jquery'], wu_get_version(), true);
wp_add_inline_script('wu-container-toggle', 'var wu_container_nonce = "' . esc_js(wp_create_nonce('wu_toggle_container')) . '";', 'before');
?>
