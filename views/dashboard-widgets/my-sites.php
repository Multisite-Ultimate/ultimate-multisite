<?php
/**
 * My Sites
 *
 * @since 2.0.0
 */

$add_new_url = wu_get_setting('enable_multiple_sites') ? $element->get_new_site_url() : wu_get_registration_url();

// Redirect back to this page after create the site
$add_new_url = add_query_arg(
	[
		'redirect_url' => rawurlencode(wu_get_current_url()),
	],
	$add_new_url
);

$show_add_new = wu_get_setting('enable_multiple_sites') || wu_get_setting('enable_multiple_memberships');

$show_add_new = apply_filters('wp_ultimo_my_sites_show_add_new', $show_add_new);

?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

	<div class="<?php echo esc_attr(wu_env_picker('wu-mb-4', '')); ?>">

	<div class="wu-relative">

		<div
		class="wu-grid wu-gap-5 wu-grid-cols-<?php echo esc_attr((int) $columns); ?> sm:wu-grid-cols-<?php echo esc_attr((int) $columns); ?> xl:wu-grid-cols-<?php echo esc_attr((int) $columns); ?> lg:wu-max-w-none <?php echo esc_attr(wu_env_picker('', 'wu-py-4')); ?>">

		<?php foreach ( (array) $sites as $site) : ?>

			<div class="wu-flex wu-flex-col wu-rounded-lg wu-overflow-hidden wu-border-solid wu-border wu-border-gray-300">

			<div class="wu-flex-shrink-0">

				<div class="wu-absolute wu-m-2">

				<?php if ($site->get_membership()) : ?>

					<?php if ($site->get_id()) : ?>

					<span
						class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium <?php echo esc_attr($site->get_membership()->get_status_class()); ?>"
					>
						<?php echo esc_html($site->get_membership()->get_status_label()); ?>
					</span>

					<?php else : ?>

					<span
						class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium wu-bg-purple-200 wu-text-purple-700"
					>
						<?php esc_html_e('Pending', 'multisite-ultimate'); ?>
					</span>

					<?php endif; ?>

				<?php endif; ?>

				<!-- <span
					class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium wu-bg-yellow-200 wu-text-yellow-800">
					<span class="dashicons-wu-warning wu-mr-1 wu-text-xs"></span>
					Billing Issues
				</span> -->

				<?php if ($site->get_id() && $site->is_customer_primary_site()) : ?>

					<span
					class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium wu-bg-gray-800 wu-text-gray-300">
					<?php esc_html_e('Primary', 'multisite-ultimate'); ?>
					</span>

				<?php endif; ?>

				<!-- <span
					class="wu-shadow-sm wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-rounded wu-text-sm wu-font-medium wu-bg-red-100 wu-text-red-800">
					<span class="dashicons-wu-warning wu-mr-1 wu-text-xs"></span>
					Offline
				</span> -->

				</div>

				<?php if ($display_images) : ?>

				<img
					class="wu-h-48 wu-w-full wu-object-cover wu-block"
					src="<?php echo esc_attr($site->get_featured_image()); ?>"
					<?php // translators: %s: Site Title ?>
					alt="<?php printf(esc_attr__('Site Image: %s', 'multisite-ultimate'), esc_attr($site->get_title())); ?>"
					style="background-color: rgba(255, 255, 255, 0.5)"
				>

				<?php else : ?>

				<div class="">&nbsp;</div>

				<?php endif; ?>

			</div>

			<div class="wu-flex-1 wu-bg-white wu-py-6 wu-px-4 wu-flex wu-flex-col wu-justify-between">

				<div class="wu-flex-1">

				<?php if ($site->get_id()) : ?>
					<a href="<?php echo esc_attr($site->get_active_site_url()); ?>" class="wu-block wu-no-underline">

					<span class="wu-text-base wu-font-semibold wu-text-gray-800 wu-block" <?php echo wu_tooltip_text(__('Visit Site', 'multisite-ultimate')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo esc_html($site->get_title()); ?> <span class="wu-text-sm dashicons-wu-popup"></span>
					</span>

					<span class="wu-text-xs wu-text-gray-600 wu-block wu-mt-2">
						<?php echo esc_html(str_replace(['http://', 'https://'], '', $site->get_active_site_url())); ?>
					</span>

					</a>
				<?php else : ?>
					<div class="wu-block wu-no-underline">

					<span class="wu-text-base wu-font-semibold wu-text-gray-800 wu-block">
						<?php echo esc_html($site->get_title()); ?>
					</span>

					</div>
				<?php endif; ?>

				</div>

			</div>

			<?php if ($site->get_id()) : ?>

				<ul
				class="wu-p-0 wu-m-0 wu-px-4 wu-text-center wu-py-2 wu-my-0 wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-300">

				<?php if (WP_Ultimo()->currents->get_site() && WP_Ultimo()->currents->get_site()->get_id() == $site->get_id()) : ?>

					<li class="wu-block wu-my-2">
					<span
						class="wu-w-full wu-no-underline <?php echo esc_attr(wu_env_picker('wu-text-sm', 'button button-primary button-disabled')); ?>">
						<?php esc_html_e('Current Site', 'multisite-ultimate'); ?>
					</span>
					</li>

				<?php else : ?>

					<li class="wu-block wu-my-2">
					<a href="<?php echo esc_url($element->get_manage_url($site->get_id(), $site_manage_type, $custom_manage_page)); ?>"
						class="wu-w-full wu-no-underline <?php echo esc_attr(wu_env_picker('wu-text-sm', 'button button-primary')); ?>">
						<?php esc_html_e('Manage', 'multisite-ultimate'); ?>
					</a>
					</li>

				<?php endif; ?>

				</ul>

			<?php endif; ?>

			</div>

		<?php endforeach; ?>

		<?php if ($show_add_new) : ?>

			<a href="<?php echo esc_url($add_new_url); ?>"
			class="wu-no-underline wu-text-gray-600 wu-flex wu-flex-col wu-rounded-lg wu-border-2 wu-border-dashed wu-border-gray-400 wu-overflow-hidden wu-items-center wu-justify-center"
			style="background-color: rgba(255, 255, 255, 0.1)">

			<span class="wu-text-center wu-p-8">
				<span class="wu-text-3xl dashicons-wu-circle-with-plus"></span>
				<span class="wu-text-lg wu-mt-2 wu-block"><?php esc_html_e('Add new Site', 'multisite-ultimate'); ?></span>
			</span>

			</a>

		<?php endif; ?>

		</div>

	</div>

	</div>

</div>

<!-- <div class="md:wu-grid-cols-4"></div> -->
<!-- <div class="md:wu-grid-cols-5"></div> -->
<!-- <div class="md:wu-grid-cols-6"></div> -->
