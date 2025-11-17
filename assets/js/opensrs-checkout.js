/**
 * OpenSRS Integration for Ultimate Multisite
 * 
 * File: Product Integration, Checkout Forms, and Customer Dashboard JS
 * 
 * @package UltimateMultisite
 * @subpackage OpenSRS
 * @since 2.5.0
 */

// JavaScript file for checkout

jQuery(document).ready(function($) {
	var checkingDomain = false;
	
	// Toggle domain search fields
	$('#wu-register-domain').on('change', function() {
		$('#wu-domain-search-wrapper').toggle(this.checked);
		if (!this.checked) {
			$('#wu-domain-available').val('0');
			$('#wu-domain-result').html('');
			$('#wu-domain-pricing').hide();
		}
	});
	
	// Check domain availability
	$('#wu-check-domain').on('click', function() {
		if (checkingDomain) return;
		
		var domainName = $('#wu-domain-search').val().trim();
		var tld = $('#wu-domain-tld').val();
		
		if (!domainName) {
			alert('Please enter a domain name');
			return;
		}
		
		checkingDomain = true;
		$('#wu-domain-result').html('<div class="wu-alert wu-alert-info">' + wu_opensrs.checking + '</div>');
		$('#wu-check-domain').prop('disabled', true);
		
		$.post(wu_opensrs.ajax_url, {
			action: 'wu_check_domain_availability',
			domain: domainName,
			tld: tld,
			nonce: wu_opensrs.nonce
		}, function(response) {
			checkingDomain = false;
			$('#wu-check-domain').prop('disabled', false);
			
			if (response.success && response.data.available) {
				$('#wu-domain-result').html(
					'<div class="wu-alert wu-alert-success">' + 
					wu_opensrs.available + 
					'</div>'
				);
				$('#wu-domain-available').val('1');
				$('#wu-domain-full').val(response.data.domain);
				$('#wu-domain-price').text(response.data.formatted_price);
				$('#wu-domain-pricing').show();
			} else if (response.success && !response.data.available) {
				$('#wu-domain-result').html(
					'<div class="wu-alert wu-alert-error">' + 
					wu_opensrs.unavailable + 
					'</div>'
				);
				$('#wu-domain-available').val('0');
				$('#wu-domain-pricing').hide();
			} else {
				$('#wu-domain-result').html(
					'<div class="wu-alert wu-alert-error">' + 
					wu_opensrs.error + 
					'</div>'
				);
				$('#wu-domain-available').val('0');
				$('#wu-domain-pricing').hide();
			}
		}).fail(function() {
			checkingDomain = false;
			$('#wu-check-domain').prop('disabled', false);
			$('#wu-domain-result').html(
				'<div class="wu-alert wu-alert-error">' + 
				wu_opensrs.error + 
				'</div>'
			);
		});
	});
	
	// Allow checking with Enter key
	$('#wu-domain-search').on('keypress', function(e) {
		if (e.which === 13) {
			e.preventDefault();
			$('#wu-check-domain').click();
		}
	});
});

