(function($) {
	$(document).ready(function() {
		$('#wu-container-toggle').on('click', function(e) {
			e.preventDefault();
			wu_block_ui('#wpcontent');
			
			$.ajax(ajaxurl + '?action=wu_toggle_container&nonce=' + wu_container_nonce).done(function() {
				$('.wrap').toggleClass('admin-lg:wu-container admin-lg:wu-mx-auto');
				$('body').toggleClass('has-wu-container');
				wu_block_ui('#wpcontent').unblock();
			});
		});
	});
}(jQuery));