<?php
/**
 * Maintenance Mode toggle.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

	<div class="<?php echo esc_attr(wu_env_picker('', 'wu-widget-inset')); ?>">

	<?php $form->render(); ?>

	</div>

</div>

<style>
.wu-styling h3 {
	font-weight: 600 !important;
	font-size: 90% !important;
}
</style>
