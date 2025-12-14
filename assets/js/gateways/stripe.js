/* eslint-disable */
/* global wu_stripe, Stripe */
let _stripe;
let stripeElement;
let cardElement;
let paymentElement;
let usePaymentElement = false;

const stripeElements = function(publicKey) {

  _stripe = Stripe(publicKey);

  // Check if we should use the new Payment Element or legacy Card Element
  const paymentElementContainer = document.getElementById('payment-element');
  const cardElementContainer = document.getElementById('card-element');

  // Dynamically set the amount and currency from form data if available
  let amount = wu_stripe.payment_amount || 1099; // default to $10.99 if not provided
  let currency = wu_stripe.currency || 'usd';

  // Try to get amount from checkout form if available
  if (typeof wu_checkout_form !== 'undefined' && wu_checkout_form.order) {
    amount = Math.round(wu_checkout_form.order.totals.total * 100); // Convert to cents
    currency = wu_checkout_form.order.currency || currency;
  }

  const elements = _stripe.elements({
    mode: 'payment',
    amount: amount,
    currency: currency,
  });

  // Initialize elements based on what's available in the DOM
  if (paymentElementContainer) {
    // Use Payment Element (new approach)
    usePaymentElement = true;
    paymentElement = elements.create('payment', {
      layout: {
        type: 'tabs',
        defaultCollapsed: false,
        radios: true,
        spacedAccordionItems: true,
      },
    });
  } else if (cardElementContainer) {
    // Use Card Element (legacy approach) for backward compatibility
    usePaymentElement = false;
    cardElement = elements.create('card', {
      hidePostalCode: true,
    });
  }

  wp.hooks.addFilter('wu_before_form_submitted', 'nextpress/wp-ultimo', function(promises, checkout, gateway) {
    const paymentEl = document.getElementById('payment-element');
    const cardEl = document.getElementById('card-element');

    if (gateway === 'stripe' && checkout.order.totals.total > 0) {
      if (usePaymentElement && paymentEl && paymentEl.offsetParent) {
        // Handle Payment Element
        promises.push(new Promise(async (resolve, reject) => {
          try {
            const { error } = await _stripe.confirmPayment({
              elements,
              confirmParams: {
                return_url: window.location.href,
              },
            });

            if (error) {
              reject(error);
            }
          } catch(err) {
            console.error('Payment Element error:', err);
          }

          resolve();
        }));
      } else if (!usePaymentElement && cardEl && cardEl.offsetParent) {
        // Handle Card Element for backward compatibility
        promises.push(new Promise(async (resolve, reject) => {
          try {
            const { paymentMethod, error } = await _stripe.createPaymentMethod({
              type: 'card',
              card: cardElement,
              billing_details: {
                name: checkout.customer.display_name || '',
                email: checkout.customer.user_email || '',
              },
            });

            if (error) {
              reject(error);
            }
          } catch(err) {
            console.error('Card Element error:', err);
          }

          resolve();
        }));
      }
    }

    return promises;
  });

  wp.hooks.addAction('wu_on_form_success', 'nextpress/wp-ultimo', function(checkout, results) {
    if (checkout.gateway === 'stripe' && (checkout.order.totals.total > 0 || checkout.order.totals.recurring.total > 0)) {
      checkout.set_prevent_submission(false);

      if (usePaymentElement) {
        handlePayment(checkout, results, elements);
      } else {
        handlePayment(checkout, results, cardElement);
      }
    }
  });

  wp.hooks.addAction('wu_on_form_updated', 'nextpress/wp-ultimo', function(form) {
    if (form.gateway === 'stripe') {
      try {
        if (usePaymentElement) {
          // Mount Payment Element
          paymentElement.mount('#payment-element');
          wu_stripe_update_styles(paymentElement, '#field-payment_template');
        } else {
          // Mount Card Element for backward compatibility
          cardElement.mount('#card-element');
          wu_stripe_update_styles(cardElement, '#field-payment_template');
        }

        /*
         * Prevents the from from submitting while Stripe is
         * creating a payment source.
         */
        form.set_prevent_submission(form.order && form.order.should_collect_payment && form.payment_method === 'add-new');

      } catch (error) {
        console.error('Stripe element mounting error:', error);
      }
    } else {
      form.set_prevent_submission(false);

      try {
        if (usePaymentElement) {
          paymentElement.unmount('#payment-element');
        } else {
          cardElement.unmount('#card-element');
        }
      } catch (error) {
        // Silence is golden
      }
    }
  });

  // Element focus ring - Card Element
  if (cardElement) {
    cardElement.on('focus', function() {
      const el = document.getElementById('card-element');
      el.classList.add('focused');
    });

    cardElement.on('blur', function() {
      const el = document.getElementById('card-element');
      el.classList.remove('focused');
    });
  }

  // Element focus handling - Payment Element
  if (paymentElement) {
    paymentElement.on('change', function(event) {
      const paymentElementContainer = document.getElementById('payment-element');
      if (event.complete) {
        paymentElementContainer.classList.add('focused');
      } else {
        paymentElementContainer.classList.remove('focused');
      }
    });
  }
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
 * After registration has been processed, handle card payments.
 *
 * @param form
 * @param response
 * @param element
 */
function handlePayment(form, response, element) {

  // Trigger error if we don't have a client secret.
  if (! response.gateway.data.stripe_client_secret) {
    return;
  }

  if (usePaymentElement) {
    // Handle Payment Element
    const confirmParams = {
      return_url: window.location.href,
    };

    // For Payment Element, we use confirmPayment instead of individual payment method confirmation
    _stripe.confirmPayment({
      elements: element,
      confirmParams: confirmParams,
    }).then(function(result) {
      if (result.error) {
        wu_checkout_form.unblock();
        wu_checkout_form.errors.push(result.error);
      } else {
        // Payment succeeded, redirect to success page
        window.location.href = result.paymentIntent ? result.paymentIntent.last_payment_error.return_url : window.location.href;
      }
    });
  } else {
    // Handle Card Element (legacy) for backward compatibility
    const handler = 'payment_intent' === response.gateway.data.stripe_intent_type ? 'confirmCardPayment' : 'confirmCardSetup';

    const args = {
      payment_method: form.payment_method !== 'add-new' ? form.payment_method : {
        card: element,
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
}
