<?php
/**
 * Submit box view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;

?>
<!-- Submit Box -->
<div class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<a href="<?php echo esc_url($page->get_prev_section_link()); ?>" class="wu-self-center button button-large wu-float-left">
	<?php esc_html_e('← Go Back', 'ultimate-multisite'); ?>
	</a>

	<span class="wu-self-center wu-content-center wu-flex">

	<button name="submit" value="1" class="button button-primary button-large" data-testid="button-primary">
		<?php esc_html_e('Continue', 'ultimate-multisite'); ?>
	</button>

	</span>

</div>
<!-- End Submit Box -->
