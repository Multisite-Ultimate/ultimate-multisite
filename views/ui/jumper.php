<?php
/**
 * Displays the Jumper UI.
 *
 * @package WP_Ultimo/Views
 * @subpackage Jumper
 * @since 2.0.0
 */

?>
<div id="wu-jumper" style="display: none;" class="wu-styling">

	<div class="wu-jumper-icon-container wu-relative wu-w-full wu-bg-gray-100 wu-rounded">

	<select id="wu-jumper-select" data-placeholder="<?php esc_attr_e('Search Anything...', 'multisite-ultimate'); ?>">

		<option></option>

		<?php if ( ! count($menu_groups)) : ?>

		<option></option>

		<optgroup label="<?php esc_attr_e('Error', 'multisite-ultimate'); ?>">

			<option value="<?php echo esc_attr(network_admin_url('?wu-rebuild-jumper=1')); ?>">

			<?php esc_html_e('Click to rebuild menu list', 'multisite-ultimate'); ?>

			</option>

		</optgroup>

		<?php endif; ?>

		<?php foreach ($menu_groups as $optgroup => $menus) : ?>

		<optgroup label="<?php esc_attr_e('Menu', 'multisite-ultimate'); ?> - <?php echo esc_attr($optgroup); ?>" value="<?php esc_attr_e('Menu', 'multisite-ultimate'); ?> - <?php echo esc_attr($optgroup); ?>">

			<?php foreach ($menus as $url => $menu) : ?>

			<option value="<?php echo esc_attr($url); ?>">

				<?php echo $menu; ?>

			</option>

			<?php endforeach; ?>

		</optgroup>

		<?php endforeach; ?>

		<optgroup label="<?php esc_attr_e('Settings', 'multisite-ultimate'); ?>" value="setting"></optgroup>

		<optgroup label="<?php esc_attr_e('Users', 'multisite-ultimate'); ?>" value="user"></optgroup>

		<optgroup label="<?php esc_attr_e('Customers', 'multisite-ultimate'); ?>" value="customer"></optgroup>

		<optgroup label="<?php esc_attr_e('Products', 'multisite-ultimate'); ?>" value="product"></optgroup>

		<optgroup label="<?php esc_attr_e('Domains', 'multisite-ultimate'); ?>" value="domain"></optgroup>

		<optgroup label="<?php esc_attr_e('Sites', 'multisite-ultimate'); ?>" value="site"></optgroup>

		<optgroup label="<?php esc_attr_e('Memberships', 'multisite-ultimate'); ?>" value="membership"></optgroup>

		<optgroup label="<?php esc_attr_e('Payments', 'multisite-ultimate'); ?>" value="payment"></optgroup>

		<optgroup label="<?php esc_attr_e('Discount Codes', 'multisite-ultimate'); ?>" value="discount-code"></optgroup>

		<optgroup label="<?php esc_attr_e('Webhooks', 'multisite-ultimate'); ?>" value="webhook"></optgroup>

		<optgroup label="<?php esc_attr_e('Broadcasts', 'multisite-ultimate'); ?>" value="broadcast"></optgroup>

		<optgroup label="<?php esc_attr_e('Checkout Forms', 'multisite-ultimate'); ?>" value="checkout-form"></optgroup>

		<?php

		/**
		 * Allow plugin developers to add new opt-groups.
		 *
		 * @since 2.0.0
		 */
		do_action('wu_jumper_options');

		?>

	</select>

	</div>

	<div class="wu-jumper-redirecting wu-bg-gray-200">

	<?php esc_html_e('Redirecting you to the target page...', 'multisite-ultimate'); ?>

	</div>

	<div class="wu-jumper-loading wu-bg-gray-200">

	<?php esc_html_e('Searching Results...', 'multisite-ultimate'); ?>

	</div>

</div>
