<?php
/**
 * Grid view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<?php $table->display_tablenav('top'); ?>

<div class="wu-mt-4 <?php echo esc_attr(implode(' ', $table->get_table_classes())); ?>">

	<div id="the-list" class="wu-grid-content wu-grid wu-gap-4 wu-grid-cols-1 md:wu-grid-cols-2 lg:wu-grid-cols-3 xl:wu-grid-cols-4">

	<?php $table->display_rows_or_placeholder(); ?>

	</div>
</div>
