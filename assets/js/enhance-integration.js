/* global jQuery, wu_enhance_data */
/**
 * Enhance Control Panel Integration
 *
 * Handles dynamic loading of websites from the Enhance API.
 *
 * @since 2.0.0
 * @param {Object} $ jQuery object.
 */
(function($) {
	'use strict';

	const EnhanceIntegration = {
		/**
		 * Initialize the integration.
		 */
		init() {
			this.bindEvents();
			this.checkInitialState();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents() {
			$('#wu-enhance-load-data').on('click', this.loadWebsites.bind(this));
		},

		/**
		 * Check initial state and enable/disable elements accordingly.
		 */
		checkInitialState() {
			const websiteId = $('#wu_enhance_website_id').val();
			if (websiteId) {
				$('#wu_enhance_website_id').prop('disabled', false);
			}
		},

		/**
		 * Load websites from the Enhance API.
		 *
		 * @param {Event} e Click event.
		 */
		loadWebsites(e) {
			e.preventDefault();

			const apiToken = $('#wu_enhance_api_token').val();
			const apiUrl = $('#wu_enhance_api_url').val();
			const orgId = $('#wu_enhance_org_id').val();

			if (! apiToken || ! apiUrl || ! orgId) {
				$('#wu-enhance-loader-status').text(wu_enhance_data.i18n.enter_credentials).css('color', 'red');
				return;
			}

			const self = this;
			const $btn = $('#wu-enhance-load-data');
			const $status = $('#wu-enhance-loader-status');

			$btn.prop('disabled', true);
			$status.text(wu_enhance_data.i18n.loading_websites).css('color', '');

			$.post(wu_enhance_data.ajax_url, {
				action: 'wu_enhance_get_websites',
				nonce: wu_enhance_data.nonce,
				api_token: apiToken,
				api_url: apiUrl,
				org_id: orgId
			}).done(function(response) {
				if (response.success && response.data.websites) {
					self.populateWebsites(response.data.websites);
					$('#wu_enhance_website_id').prop('disabled', false);
					$status.text(wu_enhance_data.i18n.websites_loaded).css('color', 'green');

					// If only one website, auto-select it
					if (response.data.websites.length === 1) {
						$('#wu_enhance_website_id').val(response.data.websites[ 0 ].id);
					}
				} else {
					$status.text(response.data.message || wu_enhance_data.i18n.websites_failed).css('color', 'red');
				}
			}).fail(function() {
				$status.text(wu_enhance_data.i18n.request_failed).css('color', 'red');
			}).always(function() {
				$btn.prop('disabled', false);
			});
		},

		/**
		 * Populate the websites dropdown.
		 *
		 * @param {Array} websites List of websites from API.
		 */
		populateWebsites(websites) {
			const $select = $('#wu_enhance_website_id');
			$select.empty().append('<option value="">' + wu_enhance_data.i18n.select_website + '</option>');

			$.each(websites, function(i, website) {
				$select.append(
					'<option value="' + website.id + '">' + website.name + '</option>'
				);
			});
		}
	};

	// Initialize when document is ready
	$(document).ready(function() {
		if ($('#wu-enhance-load-data').length) {
			EnhanceIntegration.init();
		}
	});

}(jQuery));
