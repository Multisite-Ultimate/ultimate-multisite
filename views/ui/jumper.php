<?php
/**
 * Displays the Jumper UI.
 *
 * @package WP_Ultimo/Views
 * @subpackage Jumper
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;

?>
<div id="wu-jumper" style="display: none;" class="wu-styling">

	<div class="wu-jumper-icon-container wu-relative wu-w-full wu-bg-gray-100 wu-rounded">

	<select id="wu-jumper-select" data-placeholder="<?php esc_attr_e('Search Anything...', 'ultimate-multisite'); ?>">

		<option></option>

		<?php if ( ! count($menu_groups)) : ?>

		<option></option>

		<optgroup label="<?php esc_attr_e('Error', 'ultimate-multisite'); ?>">

			<option value="<?php echo esc_attr(network_admin_url('?wu-rebuild-jumper=1')); ?>">

			<?php esc_html_e('Click to rebuild menu list', 'ultimate-multisite'); ?>

			</option>

		</optgroup>

		<?php endif; ?>

		<?php foreach ($menu_groups as $optgroup => $menus) : ?>

		<optgroup label="<?php esc_attr_e('Menu', 'ultimate-multisite'); ?> - <?php echo esc_attr($optgroup); ?>" value="<?php esc_attr_e('Menu', 'ultimate-multisite'); ?> - <?php echo esc_attr($optgroup); ?>">

			<?php foreach ($menus as $url => $menu) : ?>

			<option value="<?php echo esc_attr($url); ?>">

				<?php echo esc_html($menu); ?>

			</option>

			<?php endforeach; ?>

		</optgroup>

		<?php endforeach; ?>

		<optgroup label="<?php esc_attr_e('Settings', 'ultimate-multisite'); ?>" value="setting"></optgroup>

		<optgroup label="<?php esc_attr_e('Users', 'ultimate-multisite'); ?>" value="user"></optgroup>

		<optgroup label="<?php esc_attr_e('Customers', 'ultimate-multisite'); ?>" value="customer"></optgroup>

		<optgroup label="<?php esc_attr_e('Products', 'ultimate-multisite'); ?>" value="product"></optgroup>

		<optgroup label="<?php esc_attr_e('Domains', 'ultimate-multisite'); ?>" value="domain"></optgroup>

		<optgroup label="<?php esc_attr_e('Sites', 'ultimate-multisite'); ?>" value="site"></optgroup>

		<optgroup label="<?php esc_attr_e('Memberships', 'ultimate-multisite'); ?>" value="membership"></optgroup>

		<optgroup label="<?php esc_attr_e('Payments', 'ultimate-multisite'); ?>" value="payment"></optgroup>

		<optgroup label="<?php esc_attr_e('Discount Codes', 'ultimate-multisite'); ?>" value="discount-code"></optgroup>

		<optgroup label="<?php esc_attr_e('Webhooks', 'ultimate-multisite'); ?>" value="webhook"></optgroup>

		<optgroup label="<?php esc_attr_e('Broadcasts', 'ultimate-multisite'); ?>" value="broadcast"></optgroup>

		<optgroup label="<?php esc_attr_e('Checkout Forms', 'ultimate-multisite'); ?>" value="checkout-form"></optgroup>

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

	<?php esc_html_e('Redirecting you to the target page...', 'ultimate-multisite'); ?>

	</div>

	<div class="wu-jumper-loading wu-bg-gray-200">

	<?php esc_html_e('Searching Results...', 'ultimate-multisite'); ?>

	</div>

</div>
