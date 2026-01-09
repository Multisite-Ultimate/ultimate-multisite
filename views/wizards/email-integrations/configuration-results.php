<?php
/**
 * Email provider integrations configuration results view.
 *
 * @since 2.3.0
 */
defined('ABSPATH') || exit;

$post_data = json_decode(stripslashes($post), true);
?>
<h1><?php esc_html_e('Manual Configuration', 'ultimate-multisite'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4">
	<?php esc_html_e('Add the following code to your wp-config.php file:', 'ultimate-multisite'); ?>
</p>

<div class="wu-bg-gray-800 wu-text-white wu-p-4 wu-rounded wu-font-mono wu-text-sm wu-overflow-x-auto">
	<pre class="wu-m-0"><?php echo esc_html($integration->get_constants_string($post_data)); ?></pre>
</div>

<p class="wu-text-sm wu-text-gray-500 wu-mt-4">
	<?php esc_html_e('Add this code before the line that says "That\'s all, stop editing!"', 'ultimate-multisite'); ?>
</p>

<!-- Submit Box -->
<div class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<a href="<?php echo esc_url(remove_query_arg(['manual', 'post'])); ?>" class="wu-self-center button button-large wu-float-left"><?php esc_html_e('&larr; Go Back', 'ultimate-multisite'); ?></a>

	<a href="<?php echo esc_attr($page->get_next_section_link()); ?>" class="button button-primary button-large">
		<?php esc_html_e('I\'ve Added the Code &rarr;', 'ultimate-multisite'); ?>
	</a>

</div>
<!-- End Submit Box -->
