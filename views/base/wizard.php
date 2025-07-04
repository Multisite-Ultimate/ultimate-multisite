<?php
/**
 * Wizard view.
 *
 * @since 2.0.0
 */
?>
<div id="wp-ultimo-wrap" class="wrap wu-wrap <?php echo esc_attr($classes); ?>">

	<h1 class="wp-heading-inline">
		<!-- This is here for admin notices placement only -->
	</h1>

	<?php if ($logo) : ?>

		<div class="wu-text-center">

		<img style="width: 200px;" src="<?php echo esc_attr($logo); ?>" alt="">

		</div>

	<?php endif; ?>

	<?php if (isset($_GET['deleted'])) : // phpcs:ignore WordPress.Security.NonceVerification ?>

		<div id="message" class="updated notice wu-admin-notice notice-success is-dismissible below-h2">

			<p><?php echo esc_html($page->labels['deleted_message']); ?></p>

		</div>

	<?php endif; ?>

	<hr class="wp-header-end">

	<div id="poststuff" class="md:wu-flex wu-mr-4 md:wu-mr-0">

		<div class="md:wu-w-2/12 wu-pt-10">

		<span class="wu-uppercase wu-block wu-px-4 wu-text-gray-700 wu-font-bold">

		<?php echo esc_html($page->get_title()); ?>

		</span>

		<?php
		/**
		 * Allow plugin developers to add additional buttons to list pages
		 *
		 * @since 1.8.2
		 * @param WU_Page Multisite Ultimate Page instance
		 */
		do_action('wu_page_wizard_after_title', $page);
		?>

			<!-- Navigator -->
			<ul class="">

				<?php

				/**
				 * We need to set a couple of flags in here to control clickable navigation elements.
				 * This flag makes sure only steps the user already went through are clickable.
				 */
				$is_pre_current_section = true;

				?>

				<?php foreach ($sections as $section_name => $section) : ?>

					<?php

					/**
					 * Updates the flag after the current section is looped.
					 */
					if ($current_section === $section_name) {
						$is_pre_current_section = false;
					}

					?>

					<?php if (wu_get_isset($section, 'separator')) : ?>

						<!-- Separator Item -->
						<li class="wu-sticky wu-py-2 wu-px-4">&nbsp;</li>

					<?php else : ?>

						<!-- Menu Item -->
						<li class="wu-sticky">

							<!-- Menu Link -->
							<a href="<?php echo esc_url($page->get_section_link($section_name)); ?>" class="wu-block wu-py-2 wu-px-4 wu-no-underline wu-text-sm wu-rounded <?php echo ! $clickable_navigation && ! $is_pre_current_section ? 'wu-pointer-events-none' : ''; ?> <?php echo $current_section === $section_name ? 'wu-bg-gray-300 wu-text-gray-800' : 'wu-text-gray-600 hover:wu-text-gray-700'; ?>">
								<?php echo esc_html($section['title']); ?>
							</a>
							<!-- End Menu Link -->

							<?php if ( ! empty($section['sub-sections'])) : ?>

								<!-- Sub-menu -->
								<ul class="classes">

									<?php foreach ($section['sub-sections'] as $sub_section_name => $sub_section) : ?>

										<li class="classes">
											<a href="#" class="wu-block wu-py-2 wu-px-4 wu-no-underline wu-text-gray-500 hover:wu-text-gray-600 wu-text-sm">
												&rarr; <?php echo esc_html($sub_section['title']); ?>
											</a>
										</li>

									<?php endforeach; ?>

								</ul>
								<!-- End Sub-menu -->

							<?php endif; ?>

						</li>
						<!-- End Menu Item -->

					<?php endif; ?>

				<?php endforeach; ?>

			</ul>
			<!-- End Navigator -->

		</div>

		<div class="md:wu-w-8/12 wu-px-4 metabox-holder">

			<form method="post" id="<?php echo esc_attr($form_id); ?>">

				<?php

				/**
				 * Print Side Metaboxes
				 *
				 * Allow plugin developers to add new metaboxes
				 *
				 * @since 1.8.2
				 * @param object Object being edited right now
				 */
				do_meta_boxes($screen->id, 'normal', false);

				?>

				<?php wp_nonce_field(sprintf('saving_%s', $current_section), sprintf('saving_%s', $current_section), false); ?>

				<?php wp_nonce_field(sprintf('saving_%s', $current_section), '_wpultimo_nonce'); ?>

			</form>

		</div>

	</div>

	<?php
	/**
	 * Allow plugin developers to add scripts to the bottom of the page
	 *
	 * @since 1.8.2
	 * @param WU_Page Multisite Ultimate Page instance
	 */
	do_action('wu_page_wizard_footer', $page);
	?>

</div>
