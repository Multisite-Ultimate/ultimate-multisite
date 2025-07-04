<?php
/**
 * Submit box view.
 *
 * @since 2.0.0
 */
?>
<!-- Submit Box -->
<div class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<a href="<?php echo esc_url($page->get_prev_section_link()); ?>" class="wu-self-center button button-large wu-float-left">
	<?php esc_html_e('← Go Back', 'multisite-ultimate'); ?>
	</a>

	<span class="wu-self-center wu-content-center wu-flex">

	<button name="submit" value="1" class="button button-primary button-large">
		<?php esc_html_e('Continue', 'multisite-ultimate'); ?>
	</button>

	</span>

</div>
<!-- End Submit Box -->
