<?php
/**
 * Test view.
 *
 * @since 2.0.0
 */
?>
<h1><?php esc_html_e('Testing the Integration', 'multisite-ultimate'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6">
	<?php // translators: %s: name of integration. ?>
	<?php printf(esc_html__('We will send a test API call to %s to make sure we are able to connect. This will confirm if everything we did so far have worked.', 'multisite-ultimate'), esc_html($integration->get_title())); ?>
</p>

<div id="integration-test">

	<div v-if="loading" class="wu-flex wu-rounded wu-content-center wu-py-2 wu-px-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-m-0">
	<span class="dashicons dashicons-warning wu-text-blue-400 wu-self-center wu-mr-2"></span>
	<span>
		<?php esc_html_e('Sending API call...', 'multisite-ultimate'); ?>
	</span>
	</div>

	<div v-cloak v-if="!loading && success" class="wu-flex wu-rounded wu-content-center wu-py-2 wu-px-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-m-0">
	<span class="dashicons dashicons-yes-alt wu-text-green-400 wu-self-center wu-mr-2"></span>
	<span>
		<?php esc_html_e('Yey! Everything seems to be working!', 'multisite-ultimate'); ?>
	</span>
	</div>

	<div v-cloak v-if="!loading && !success" class="wu-flex wu-rounded wu-content-center wu-py-2 wu-px-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-m-0">
	<span class="dashicons dashicons-dismiss wu-text-red-400 wu-self-center wu-mr-2"></span>
	<span>
		<?php esc_html_e('Something wrong happened... We might need to make some adjustments to make this work.', 'multisite-ultimate'); ?>
	</span>
	</div>

	<pre class="wu-overflow-auto wu-p-4 wu-rounded wu-content-center wu-bg-gray-800 wu-text-white wu-font-mono wu-border wu-border-solid wu-border-gray-300" v-html="results"><?php esc_html_e('Waiting for results...', 'multisite-ultimate'); ?></pre>

	<div v-cloak v-if="!loading && !success">

	<h1><?php esc_html_e('Troubleshooting', 'multisite-ultimate'); ?></h1>

	<ol>
		<li>
		<?php // translators: %1$s and %2$s are <strong> and </strong> wrapping the word Configuration ?>
		<?php printf(esc_html__('Go back to the %1$sConfiguration%2$s step - if available - and make sure you entered all the necessary information correctly;', 'multisite-ultimate'), '<strong>', '</strong>'); ?>
		</li>
		<li>
		<?php echo wp_kses_post(__('If you have added the constants to your wp-config.php file manually, double check to make sure you\'ve added them to the right wp-config.php file and in the right place (just above the <code>/* That\'s all, stop editing! Happy publishing. */</code>)', 'multisite-ultimate')); ?>);
		</li>
		<li>
		<?php esc_html_e('If you are sure everything is right, take a screenshot of this screen and contact support.', 'multisite-ultimate'); ?>
		</li>
	</ol>

	</div>

	<!-- Submit Box -->
	<div v-cloak v-if="!loading && !success" class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<a href="<?php echo esc_url($page->get_prev_section_link()); ?>" class="wu-self-center button button-large wu-float-left">
		<?php esc_html_e('&larr; Go Back', 'multisite-ultimate'); ?>
	</a>

	</div>
	<!-- End Submit Box -->

	<div v-cloak v-if="!loading && success">
	<?php
	/**
	 * Default Submit box
	 */
	$page->render_submit_box();
	?>
	</div>

</div>

<script>
	(function($) {
	$(document).ready(function() {

		new Vue({
		el: "#integration-test",
		data: {
			success: false,
			loading: false,
			results: '<?php echo esc_js(__('Waiting for results...', 'multisite-ultimate')); ?>',
		},
		mounted: function() {

			var that = this;

			this.loading = true;

			setTimeout(() => {

			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
				action: 'wu_test_hosting_integration',
				integration: '<?php echo esc_js($integration->get_id()); ?>',
				},
				success: function(response) {
				console.log(response);
				that.loading = false;
				that.success = response.success;
				that.results = response.data;
				}
			});

			}, 1000);

		},
		});

	});
	})(jQuery);
</script>

