<?php
/**
 * Maintenance Mode toggle.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

	<div class="<?php echo esc_attr(wu_env_picker('', 'wu-widget-inset')); ?>">

	<?php $form->render(); ?>

	</div>

</div>

