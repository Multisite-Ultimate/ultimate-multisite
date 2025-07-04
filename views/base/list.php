<?php
/**
 * List view.
 *
 * @since 2.0.0
 */
?>
<div id="wp-ultimo-wrap" class="<?php wu_wrap_use_container(); ?> wrap wu-wrap <?php echo esc_attr($classes); ?>">

	<h1 class="wp-heading-inline">

	<?php echo esc_html($page->get_title()); ?>

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
	 * @param WU_Page Multisite Ultimate Page instance
	 */
	do_action('wu_page_list_after_title', $page);
	?>

	</h1>

	<?php if (isset($_GET['deleted'])) : // phpcs:ignore WordPress.Security.NonceVerification ?>
	<div id="message" class="updated notice wu-admin-notice notice-success is-dismissible below-h2">
		<p><?php echo esc_html($page->get_labels()['deleted_message']); ?></p>
	</div>
	<?php endif; ?>

	<?php
	/**
	 * Allow plugin developers to add additional handlers to URL query redirects
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Admin_Pages\Base_Admin_Page $page The page object.
	 */
	do_action('wu_page_list_redirect_handlers', $page);
	?>

	<hr class="wp-header-end">

	<div id="poststuff">

	<div id="post-body" class="">

		<div id="post-body-content">

		<div class="">

			<?php $table->prepare_items(); ?>

			<?php $table->filters(); ?>

			<form id="posts-filter" method="post">

			<input type="hidden" name="page" value="<?php echo esc_attr($page->get_id()); ?>">

			<?php $table->display(); ?>

			</form>

		</div>
		<!-- /ui-sortable -->

		</div>
		<!-- /post-body-content -->

	</div>
	<!-- /post-body -->

	<br class="clear">

	</div>
	<!-- /poststuff -->

	<?php
	/**
	 * Allow plugin developers to add scripts to the bottom of the page
	 *
	 * @since 1.8.2
	 * @param WU_Page Multisite Ultimate Page instance
	 */
	do_action('wu_page_list_footer', $page);
	?>

</div>
