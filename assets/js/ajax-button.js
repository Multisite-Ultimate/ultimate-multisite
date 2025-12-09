(function ($) {
	$(document).ready(
		function () {
			$('.wu-ajax-button').on(
				'click',
				function (e) {
					e.preventDefault();

					var $this         = $(this);
					var default_label = $this.html();
					var action_url    = $this.data('action-url');

					$this.html('...').attr('disabled', 'disabled');

					$.ajax(
						{
							url: action_url,
							dataType: 'json',
							success: function (response) {
								$this.html(response.message);

								setTimeout(
									function () {
										$this.html(default_label).removeAttr('disabled');
									},
									4000
								);
							}
						}
					);
				}
			);
		}
	);
})(jQuery);