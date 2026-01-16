/**
 * Payment Status Polling for Thank You Page.
 *
 * Polls the server to check if a pending payment has been completed.
 * This is a fallback mechanism when webhooks are delayed or not working.
 *
 * @since 2.x.x
 */
/* global wu_payment_poll, jQuery */
(function ($) {
	'use strict';

	if (typeof wu_payment_poll === 'undefined') {
		return;
	}

	const config = {
		paymentHash: wu_payment_poll.payment_hash || '',
		ajaxUrl: wu_payment_poll.ajax_url || '',
		pollInterval: parseInt(wu_payment_poll.poll_interval, 10) || 3000,
		maxAttempts: parseInt(wu_payment_poll.max_attempts, 10) || 20,
		statusSelector: wu_payment_poll.status_selector || '.wu-payment-status',
		successRedirect: wu_payment_poll.success_redirect || '',
	};

	let attempts = 0;
	let pollTimer = null;

	/**
	 * Check payment status via AJAX.
	 */
	function checkPaymentStatus() {
		attempts++;

		if (attempts > config.maxAttempts) {
			stopPolling();
			updateStatusMessage('timeout');
			return;
		}

		$.ajax({
			url: config.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wu_check_payment_status',
				payment_hash: config.paymentHash,
			},
			success (response) {
				if (response.success && response.data) {
					const status = response.data.status;

					if (status === 'completed') {
						stopPolling();
						updateStatusMessage('completed');

						// Reload page or redirect after a short delay
						setTimeout(function () {
							if (config.successRedirect) {
								window.location.href = config.successRedirect;
							} else {
								window.location.reload();
							}
						}, 1500);
					} else if (status === 'pending') {
						// Continue polling
						updateStatusMessage('pending', attempts);
					} else {
						// Unknown status, continue polling
						updateStatusMessage('checking', attempts);
					}
				}
			},
			error () {
				// Network error, continue polling
				updateStatusMessage('error', attempts);
			},
		});
	}

	/**
	 * Update the status message on the page.
	 *
	 * @param {string} status  The current status.
	 * @param {number} attempt Current attempt number.
	 */
	function updateStatusMessage(status, attempt) {
		const $statusEl = $(config.statusSelector);

		if (! $statusEl.length) {
			return;
		}

		let message = '';
		let className = '';

		switch (status) {
			case 'completed':
				message = wu_payment_poll.messages?.completed || 'Payment confirmed! Refreshing page...';
				className = 'wu-payment-status-completed';
				break;
			case 'pending':
				message = wu_payment_poll.messages?.pending || 'Verifying payment...';
				className = 'wu-payment-status-pending';
				break;
			case 'timeout':
				message = wu_payment_poll.messages?.timeout || 'Payment verification timed out. Please refresh the page or contact support if payment was made.';
				className = 'wu-payment-status-timeout';
				break;
			case 'error':
				message = wu_payment_poll.messages?.error || 'Error checking payment status. Retrying...';
				className = 'wu-payment-status-error';
				break;
			default:
				message = wu_payment_poll.messages?.checking || 'Checking payment status...';
				className = 'wu-payment-status-checking';
		}

		$statusEl
			.removeClass('wu-payment-status-completed wu-payment-status-pending wu-payment-status-timeout wu-payment-status-error wu-payment-status-checking')
			.addClass(className)
			.html(message);
	}

	/**
	 * Create the status element if it doesn't exist.
	 */
	function ensureStatusElement() {
		let $statusEl = $(config.statusSelector);

		if (! $statusEl.length) {
			// Try to find a good place to insert the status element
			const $container = $('.wu-checkout-form, .wu-styling, .entry-content, .post-content, main').first();

			if ($container.length) {
				$statusEl = $('<div class="wu-payment-status"></div>');
				$container.prepend($statusEl);
			}
		}

		return $statusEl;
	}

	/**
	 * Start polling for payment status.
	 */
	function startPolling() {
		if (! config.paymentHash || ! config.ajaxUrl) {
			return;
		}

		// Ensure the status element exists
		ensureStatusElement();

		// Initial status update
		updateStatusMessage('pending', 0);

		// Start polling
		pollTimer = setInterval(checkPaymentStatus, config.pollInterval);

		// Do first check immediately
		checkPaymentStatus();
	}

	/**
	 * Stop polling.
	 */
	function stopPolling() {
		if (pollTimer) {
			clearInterval(pollTimer);
			pollTimer = null;
		}
	}

	// Start polling when document is ready
	$(document).ready(function () {
		// Only poll if we have a payment hash and status is done
		if (config.paymentHash && wu_payment_poll.should_poll) {
			startPolling();
		}
	});

	// Expose for debugging
	window.wu_payment_poll_controller = {
		start: startPolling,
		stop: stopPolling,
		check: checkPaymentStatus,
	};
}(jQuery));
