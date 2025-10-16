(function($) {
  'use strict';

  /**
   * AJAX Login Form Handler
   * 
   * This script converts the standard WordPress login form to use AJAX
   * to bypass caching issues and provide better feedback to users.
   */
  $(document).ready(function() {

    // Only run on the login form
    if (!$('.wu-login-form').length) {
      return;
    }

    // Add a class to identify our form
    $('.wu-login-form form').addClass('wu-ajax-login-form');

    // Handle form submission
    $(document).on('submit', '.wu-ajax-login-form', function(e) {
      e.preventDefault();

      var $form = $(this);
      var $submitButton = $form.find('input[type="submit"]');
      var $errorContainer = $('<div class="wu-login-error wu-p-4 wu-bg-red-100 wu-text-red-700 wu-rounded wu-mb-4" style="display:none;"></div>');
      
      // Remove any existing error messages
      $('.wu-login-error').remove();
      
      // Add the error container
      $form.prepend($errorContainer);
      
      // Disable the submit button and show loading state
      $submitButton.prop('disabled', true).val('Logging in...');
      
      // Get form data
      var formData = $form.serialize();
      
      // Add a nonce for security
      formData += '&security=' + wu_login_ajax.nonce;
      
      // Send the AJAX request
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: wu_login_ajax.ajax_url,
        data: {
          action: 'wu_ajax_login',
          data: formData
        },
        success: function(response) {
          if (response.success) {
            // Login successful - redirect
            window.location.href = response.data.redirect;
          } else {
            // Show error message
            $errorContainer.html(response.data.message).slideDown();
            $submitButton.prop('disabled', false).val('Log In');
          }
        },
        error: function() {
          // Show generic error message
          $errorContainer.html('An error occurred. Please try again.').slideDown();
          $submitButton.prop('disabled', false).val('Log In');
        }
      });
    });
  });

})(jQuery);
