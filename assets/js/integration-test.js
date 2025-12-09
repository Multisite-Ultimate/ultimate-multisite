(function($) {
	$(document).ready(function() {
		new Vue({
			el: "#integration-test",
			data: {
				success: false,
				loading: false,
				results: wu_integration_test_data.waiting_message,
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
							integration: wu_integration_test_data.integration_id,
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