/* eslint-disable */
/* global wu_stripe, Stripe */
let _stripe;
let stripeElement;
let card;
let paymentElement;
let elements;
let usePaymentElement = false;

const stripeElements = function(publicKey) {

  _stripe = Stripe(publicKey);

  // Payment Element will be initialized dynamically after preflight response
  // We'll detect which element to use based on what's available in the DOM

  wp.hooks.addFilter('wu_before_form_submitted', 'nextpress/wp-ultimo', function(promises, checkout, gateway) {

    const cardEl = document.getElementById('card-element');
    const paymentEl = document.getElementById('payment-element');

    if (gateway === 'stripe' && checkout.order.totals.total > 0) {

      // If using Card Element (legacy/fallback)
      if (cardEl && cardEl.offsetParent && !usePaymentElement) {

        promises.push(new Promise( async (resolve, reject) => {

          try {

            const paymentMethod = await _stripe.createPaymentMethod({type: 'card', card});

            if (paymentMethod.error) {

              reject(paymentMethod.error);

            } // end if;

          } catch(err) {

          } // end try;

          resolve();

        }));

      } // end if;

    } // end if;

    return promises;

  });

  wp.hooks.addAction('wu_on_form_success', 'nextpress/wp-ultimo', function(checkout, results) {

    if (checkout.gateway === 'stripe' && (checkout.order.totals.total > 0 || checkout.order.totals.recurring.total > 0)) {

      // Check if we received a client_secret (required for Payment Element)
      if (results.gateway.data.stripe_client_secret) {

        const paymentEl = document.getElementById('payment-element');
        const cardEl = document.getElementById('card-element');

        // Initialize Payment Element with client_secret (preferred method)
        if (paymentEl) {

          usePaymentElement = true;

          // Create elements instance with client_secret for dynamic mode
          elements = _stripe.elements({
            clientSecret: results.gateway.data.stripe_client_secret,
            appearance: {
              theme: 'stripe',
            },
          });

          // Create and mount Payment Element
          paymentElement = elements.create('payment');
          paymentElement.mount('#payment-element');

          // Apply custom styles
          wu_stripe_update_payment_element_styles(paymentElement, '#field-payment_template');

          checkout.set_prevent_submission(false);

          // Handle payment with Payment Element
          handlePaymentElement(checkout, results, elements);

        } else if (cardEl && card) {

          // Fallback to Card Element (legacy)
          usePaymentElement = false;
          checkout.set_prevent_submission(false);
          handlePayment(checkout, results, card);

        } // end if;

      } else {

        // No client_secret, use legacy Card Element flow
        checkout.set_prevent_submission(false);

        if (card) {
          handlePayment(checkout, results, card);
        }

      } // end if;

    } // end if;

  });

  wp.hooks.addAction('wu_on_form_updated', 'nextpress/wp-ultimo', function(form) {

    if (form.gateway === 'stripe') {

      const cardEl = document.getElementById('card-element');
      const paymentEl = document.getElementById('payment-element');

      // Only mount Card Element if Payment Element is not available
      if (cardEl && !paymentEl && !usePaymentElement) {

        try {

          // Initialize Card Element for legacy support
          if (!card) {
            const cardElements = _stripe.elements();
            card = cardElements.create('card', {
              hidePostalCode: true,
            });

            // Element focus ring
            card.on('focus', function() {
              const el = document.getElementById('card-element');
              el.classList.add('focused');
            });

            card.on('blur', function() {
              const el = document.getElementById('card-element');
              el.classList.remove('focused');
            });
          }

          card.mount('#card-element');

          wu_stripe_update_styles(card, '#field-payment_template');

          /*
           * Prevents the form from submitting while Stripe is
           * creating a payment source.
           */
          form.set_prevent_submission(form.order && form.order.should_collect_payment && form.payment_method === 'add-new');

        } catch (error) {

          // Silence

        } // end try;

      } else {

        // Payment Element will be initialized in wu_on_form_success
        // Set prevent submission for Payment Element
        if (paymentEl) {
          form.set_prevent_submission(form.order && form.order.should_collect_payment && form.payment_method === 'add-new');
        } else {
          form.set_prevent_submission(false);
        }

      } // end if;

    } else {

      form.set_prevent_submission(false);

      try {

        if (card) {
          card.unmount('#card-element');
        }

      } catch (error) {

        // Silence is golden

      } // end try;

    } // end if;

  });

};

wp.hooks.addFilter('wu_before_form_init', 'nextpress/wp-ultimo', function(data) {

  data.add_new_card = wu_stripe.add_new_card;

  data.payment_method = wu_stripe.payment_method;

  return data;

});

wp.hooks.addAction('wu_checkout_loaded', 'nextpress/wp-ultimo', function() {

  stripeElement = stripeElements(wu_stripe.pk_key);

});

/**
 * Copy styles from an existing element to the Stripe Card Element.
 *
 * @param {Object} cardElement Stripe card element.
 * @param {string} selector Selector to copy styles from.
 *
 * @since 3.3
 */
function wu_stripe_update_styles(cardElement, selector) {

  if (undefined === typeof selector) {

    selector = '#field-payment_template';

  }

  const inputField = document.querySelector(selector);

  if (null === inputField) {

    return;

  }

  if (document.getElementById('wu-stripe-styles')) {

    return;

  }

  const inputStyles = window.getComputedStyle(inputField);

  const styleTag = document.createElement('style');

  styleTag.innerHTML = '.StripeElement {' +
    'background-color:' + inputStyles.getPropertyValue('background-color') + ';' +
    'border-top-color:' + inputStyles.getPropertyValue('border-top-color') + ';' +
    'border-right-color:' + inputStyles.getPropertyValue('border-right-color') + ';' +
    'border-bottom-color:' + inputStyles.getPropertyValue('border-bottom-color') + ';' +
    'border-left-color:' + inputStyles.getPropertyValue('border-left-color') + ';' +
    'border-top-width:' + inputStyles.getPropertyValue('border-top-width') + ';' +
    'border-right-width:' + inputStyles.getPropertyValue('border-right-width') + ';' +
    'border-bottom-width:' + inputStyles.getPropertyValue('border-bottom-width') + ';' +
    'border-left-width:' + inputStyles.getPropertyValue('border-left-width') + ';' +
    'border-top-style:' + inputStyles.getPropertyValue('border-top-style') + ';' +
    'border-right-style:' + inputStyles.getPropertyValue('border-right-style') + ';' +
    'border-bottom-style:' + inputStyles.getPropertyValue('border-bottom-style') + ';' +
    'border-left-style:' + inputStyles.getPropertyValue('border-left-style') + ';' +
    'border-top-left-radius:' + inputStyles.getPropertyValue('border-top-left-radius') + ';' +
    'border-top-right-radius:' + inputStyles.getPropertyValue('border-top-right-radius') + ';' +
    'border-bottom-left-radius:' + inputStyles.getPropertyValue('border-bottom-left-radius') + ';' +
    'border-bottom-right-radius:' + inputStyles.getPropertyValue('border-bottom-right-radius') + ';' +
    'padding-top:' + inputStyles.getPropertyValue('padding-top') + ';' +
    'padding-right:' + inputStyles.getPropertyValue('padding-right') + ';' +
    'padding-bottom:' + inputStyles.getPropertyValue('padding-bottom') + ';' +
    'padding-left:' + inputStyles.getPropertyValue('padding-left') + ';' +
    'line-height:' + inputStyles.getPropertyValue('height') + ';' +
    'height:' + inputStyles.getPropertyValue('height') + ';' +
    `display: flex;
    flex-direction: column;
    justify-content: center;` +
    '}';

  styleTag.id = 'wu-stripe-styles';

  document.body.appendChild(styleTag);

  cardElement.update({
    style: {
      base: {
        color: inputStyles.getPropertyValue('color'),
        fontFamily: inputStyles.getPropertyValue('font-family'),
        fontSize: inputStyles.getPropertyValue('font-size'),
        fontWeight: inputStyles.getPropertyValue('font-weight'),
        fontSmoothing: inputStyles.getPropertyValue('-webkit-font-smoothing'),
      },
    },
  });

}

/**
 * Update styles for Payment Element.
 *
 * @param {Object} paymentElement Stripe payment element.
 * @param {string} selector Selector to copy styles from.
 *
 * @since 2.x.x
 */
function wu_stripe_update_payment_element_styles(paymentElement, selector) {

  if (undefined === typeof selector) {
    selector = '#field-payment_template';
  }

  const inputField = document.querySelector(selector);

  if (null === inputField) {
    return;
  }

  const inputStyles = window.getComputedStyle(inputField);

  // Payment Element uses appearance API instead of style object
  paymentElement.update({
    appearance: {
      theme: 'stripe',
      variables: {
        colorPrimary: inputStyles.getPropertyValue('border-color'),
        colorBackground: inputStyles.getPropertyValue('background-color'),
        colorText: inputStyles.getPropertyValue('color'),
        fontFamily: inputStyles.getPropertyValue('font-family'),
        fontSize: inputStyles.getPropertyValue('font-size'),
        borderRadius: inputStyles.getPropertyValue('border-radius'),
      },
    },
  });

}

function wu_stripe_handle_intent(handler, client_secret, args) {

  const _handle_error = function (e) {

    wu_checkout_form.unblock();

    if (e.error) {

      wu_checkout_form.errors.push(e.error);

    } // end if;

  } // end _handle_error;

  try {

    _stripe[handler](client_secret, args).then(function(results) {

      if (results.error) {

        _handle_error(results);

        return;

      } // end if;

      wu_checkout_form.resubmit();

    }, _handle_error);

  } catch(e) {} // end if;

} // end if;

/**
 * Handle payment with Payment Element (modern method).
 *
 * @param {Object} form Checkout form object.
 * @param {Object} response Server response with client_secret.
 * @param {Object} elements Stripe Elements instance.
 *
 * @since 2.x.x
 */
function handlePaymentElement(form, response, elements) {

  const clientSecret = response.gateway.data.stripe_client_secret;

  if (!clientSecret) {
    return;
  }

  _stripe.confirmPayment({
    elements: elements,
    confirmParams: {
      return_url: window.location.href,
      payment_method_data: {
        billing_details: {
          name: response.customer.display_name,
          email: response.customer.user_email,
          address: {
            country: response.customer.billing_address_data.billing_country,
            postal_code: response.customer.billing_address_data.billing_zip_code,
          },
        },
      },
    },
    redirect: 'if_required', // Stay on page if redirect not needed
  }).then(function(result) {

    if (result.error) {

      wu_checkout_form.unblock();
      wu_checkout_form.errors.push(result.error);

    } else {

      // Payment succeeded - resubmit form to complete checkout
      wu_checkout_form.resubmit();

    }

  });

}

/**
 * After registration has been processed, handle card payments (legacy Card Element).
 *
 * @param form
 * @param response
 * @param card
 */
function handlePayment(form, response, card) {

  // Trigger error if we don't have a client secret.
  if (! response.gateway.data.stripe_client_secret) {

    return;

  } // end if;

  const handler = 'payment_intent' === response.gateway.data.stripe_intent_type ? 'confirmCardPayment' : 'confirmCardSetup';

  const args = {
    payment_method: form.payment_method !== 'add-new' ? form.payment_method : {
      card,
      billing_details: {
        name: response.customer.display_name,
        email: response.customer.user_email,
        address: {
          country: response.customer.billing_address_data.billing_country,
          postal_code: response.customer.billing_address_data.billing_zip_code,
        },
      },
    },
  };

  /**
   * Handle payment intent / setup intent.
   */
  wu_stripe_handle_intent(
    handler, response.gateway.data.stripe_client_secret, args
  );

}
