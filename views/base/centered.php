<?php
/**
 * Dash view.
 *
 * @since 2.0.0
 */
?>
<div id="wp-ultimo-wrap" class="wrap wu-styling">

	<div class="sm:wu-container sm:wu-mx-auto">

	<h1 class="wp-heading-inline">

		<?php echo esc_html($page_title); ?>

		<?php
		/**
		 * You can filter the get_title_link using wu_page_list_get_title_link, see class-wu-page-list.php
		 *
		 * @since 1.8.2
		 */
		foreach ($page->get_title_links() as $action_link) :
			$action_classes = $action_link['classes'] ?? '';

			?>

		<a title="<?php echo esc_attr($action_link['label']); ?>" href="<?php echo esc_url($action_link['url']); ?>" class="page-title-action <?php echo esc_attr($action_classes); ?>">

			<?php if ($action_link['icon']) : ?>

			<span class="dashicons dashicons-<?php echo esc_attr($action_link['icon']); ?> wu-text-sm wu-align-middle wu-h-4 wu-w-4">
				&nbsp;
			</span>

			<?php endif; ?>

			<?php echo esc_html($action_link['label']); ?>

		</a>

		<?php endforeach; ?>

		<?php
		/**
		 * Allow plugin developers to add additional buttons to list pages
		 *
		 * @since 1.8.2
		 * @param WU_Page $page Multisite Ultimate Page instance
		 */
		do_action('wu_page_centered_after_title', $page);
		?>

	</h1>

	<?php if (isset($_GET['updated'])) : // phpcs:ignore WordPress.Security.NonceVerification ?>

		<div id="message" class="updated notice wu-admin-notice notice-success is-dismissible below-h2">
		<p><?php echo esc_html($labels['updated_message']); ?></p>
		</div>

	<?php endif; ?>

	<hr class="wp-header-end">

	<?php do_action('wu_centered_before_metaboxes', $page); ?>

	<?php if (apply_filters('wu_dashboard_display_widgets', true)) : ?>

		<div id="dashboard-widgets-wrap">

			<div id="dashboard-widgets" class="metabox-holder">

			<div class="wu-grid wu-grid-cols-1 md:wu-grid-cols-3 lg:wu-grid-cols-4">

				<div id="postbox-container" class="wu-order-2 md:wu-order-1">
					<?php
					/**
					 * Print Advanced Metaboxes
					 *
					 * Allow plugin developers to add new metaboxes
					 *
					 * @since 1.8.2
					 * @param object Object being edited right now
					 */
					do_meta_boxes($screen->id, 'left', null);
					?>
				</div>

				<div id="postbox-container" class="md:wu-col-span-2 wu-order-1 md:wu-order-2">

				<?php if ($content) : ?>

				<div class="wu-mx-2">

					<div id="wp-ultimo-checkout-element" class="postbox">

						<div class="wu-p-4 wu-flex wu-items-center wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-b wu-border-gray-200 wu-leading-snug">

						<h3 class="wu-m-0 wu-widget-title">

							<?php esc_html_e('Change Membership', 'multisite-ultimate'); ?>

						</h3>

						</div>

						<div class="wu-mx-2 wu-mt-2 wu-p-2">

						<div class="inside">

							<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

						</div>

						</div>

					</div>

					</div>

				<?php endif; ?>

				<?php

				/**
				 * Print Advanced Metaboxes
				 *
				 * Allow plugin developers to add new metaboxes
				 *
				 * @since 1.8.2
				 * @param object Object being edited right now
				 */
				do_meta_boxes($screen->id, 'normal', null);

				?>

				<div class="wu-px-2">

					<?php

					/**
					 * Allow plugin developers to add additional buttons to list pages
					 *
					 * @since 1.8.2
					 * @param WU_Page $page Multisite Ultimate Page instance
					 */
					do_action('wu_centered_content', $page);

					?>

				</div>

				</div>

				<div id="postbox-container" class="wu--mt-3 sm:wu-ml-2 wu-order-3 md:wu-order-3">
					<?php

					/**
					 * Allow plugin developers to add additional buttons to list pages
					 *
					 * @since 1.8.2
					 * @param WU_Page $page Multisite Ultimate Page instance
					 */
					do_action('wu_centered_right', $page);

					/**
					 * Print Advanced Metaboxes
					 *
					 * Allow plugin developers to add new metaboxes
					 *
					 * @since 1.8.2
					 * @param object Object being edited right now
					 */
					do_meta_boxes($screen->id, 'right', null);

					?>
				</div>

			</div>

			</div>

		<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

		<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>

		</div>

		<!-- dashboard-widgets-wrap -->

		<?php endif; ?>

	</div>

</div>
