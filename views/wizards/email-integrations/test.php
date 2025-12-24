<?php
/**
 * Email provider integrations test view.
 *
 * @since 2.3.0
 */
defined('ABSPATH') || exit;
?>
<div id="wu-email-integration-test">

	<h1><?php esc_html_e('Testing Connection', 'ultimate-multisite'); ?></h1>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php esc_html_e('Testing the connection to the email provider...', 'ultimate-multisite'); ?>
	</p>

	<div class="wu-bg-white wu-p-6 wu--mx-6 wu-text-center">

		<div v-if="loading" class="wu-py-8">
			<span class="wu-blinking-animation dashicons dashicons-update wu-text-4xl wu-text-blue-500"></span>
			<p class="wu-text-gray-600 wu-mt-4">{{ waiting_message }}</p>
		</div>

		<div v-if="success" class="wu-py-8">
			<span class="dashicons dashicons-yes-alt wu-text-4xl wu-text-green-500"></span>
			<p class="wu-text-green-700 wu-mt-4 wu-font-bold"><?php esc_html_e('Connection successful!', 'ultimate-multisite'); ?></p>
			<p class="wu-text-gray-600 wu-text-sm">{{ message }}</p>
		</div>

		<div v-if="error" class="wu-py-8">
			<span class="dashicons dashicons-dismiss wu-text-4xl wu-text-red-500"></span>
			<p class="wu-text-red-700 wu-mt-4 wu-font-bold"><?php esc_html_e('Connection failed!', 'ultimate-multisite'); ?></p>
			<p class="wu-text-gray-600 wu-text-sm">{{ message }}</p>
		</div>

	</div>

	<!-- Submit Box -->
	<div class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

		<a href="<?php echo esc_url($page->get_prev_section_link()); ?>" class="wu-self-center button button-large wu-float-left"><?php esc_html_e('&larr; Go Back', 'ultimate-multisite'); ?></a>

		<span class="wu-self-center wu-content-center wu-flex">

			<button @click="test_connection" :disabled="loading" class="button button-large wu-mr-2">
				<?php esc_html_e('Test Again', 'ultimate-multisite'); ?>
			</button>

			<a v-if="success" href="<?php echo esc_attr($page->get_next_section_link()); ?>" class="button button-primary button-large">
				<?php esc_html_e('Continue &rarr;', 'ultimate-multisite'); ?>
			</a>

			<a v-else href="<?php echo esc_attr($page->get_next_section_link()); ?>" class="button button-large">
				<?php esc_html_e('Skip &rarr;', 'ultimate-multisite'); ?>
			</a>

		</span>

	</div>
	<!-- End Submit Box -->

</div>

<script>
jQuery(document).ready(function($) {
	if (typeof Vue !== 'undefined') {
		new Vue({
			el: '#wu-email-integration-test',
			data: {
				loading: true,
				success: false,
				error: false,
				message: '',
				waiting_message: wu_email_integration_test_data.waiting_message
			},
			mounted: function() {
				this.test_connection();
			},
			methods: {
				test_connection: function() {
					var self = this;
					self.loading = true;
					self.success = false;
					self.error = false;
					self.message = '';

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wu_test_email_integration',
							integration_id: wu_email_integration_test_data.integration_id,
							_wpnonce: '<?php echo esc_js(wp_create_nonce('wu_test_email_integration')); ?>'
						},
						success: function(response) {
							self.loading = false;
							if (response.success) {
								self.success = true;
								self.message = response.data.message || '';
							} else {
								self.error = true;
								self.message = response.data.message || '<?php esc_html_e('Unknown error occurred.', 'ultimate-multisite'); ?>';
							}
						},
						error: function() {
							self.loading = false;
							self.error = true;
							self.message = '<?php esc_html_e('Failed to connect to server.', 'ultimate-multisite'); ?>';
						}
					});
				}
			}
		});
	}
});
</script>
