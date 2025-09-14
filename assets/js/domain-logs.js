(function ($) {
	$(document).ready(
		function () {
			const refresh_logs = function (callback) {
				$.ajax(
					{
						url: ajaxurl,
						method: 'GET',
						data: {
							action: 'wu_handle_view_logs',
							file: wu_domain_logs.log_file,
							return_ascii: 'no',
						},
						success(response) {
							$('#content').text(response.data.contents);

							if (typeof callback !== 'undefined') {
								callback();
							}
						},
					}
				);
			};

			refresh_logs();
			setInterval(refresh_logs, 60000);

			$(document).on(
				'click',
				'#refresh-logs',
				function (e) {
					const block_content = wu_block_ui('#content');
					e.preventDefault();

					refresh_logs(
						function () {
							block_content.unblock();
						}
					);
				}
			);
		}
	);
})(jQuery);