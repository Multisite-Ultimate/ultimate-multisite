<?php
/**
 * Email provider integrations configuration view.
 *
 * @since 2.3.0
 */
defined('ABSPATH') || exit;
?>
<h1><?php esc_html_e('Configure Integration', 'ultimate-multisite'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4">
	<?php esc_html_e('Enter the API credentials for this email provider below.', 'ultimate-multisite'); ?>
</p>

<?php $form->render(); ?>

<?php wp_nonce_field('saving_config', 'saving_config'); ?>

<!-- Submit Box -->
<div class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<a href="<?php echo esc_url($page->get_prev_section_link()); ?>" class="wu-self-center button button-large wu-float-left"><?php esc_html_e('&larr; Go Back', 'ultimate-multisite'); ?></a>

	<span class="wu-self-center wu-content-center wu-flex">

		<button name="submit" value="0" class="button button-large wu-mr-2">
			<?php esc_html_e('Add Manually', 'ultimate-multisite'); ?>
		</button>

		<button name="submit" value="1" class="button button-primary button-large" data-testid="button-primary">
			<?php esc_html_e('Add Automatically &rarr;', 'ultimate-multisite'); ?>
		</button>

	</span>

</div>
<!-- End Submit Box -->
