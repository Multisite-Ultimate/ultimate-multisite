/**
 * Cypress custom commands for checkout flow testing
 */

/**
 * Navigate to a specific checkout form
 * @param {string} formSlug - The checkout form slug
 * @param {object} options - Additional options
 */
Cypress.Commands.add("visitCheckoutForm", (formSlug = 'registration', options = {}) => {
  const url = `/checkout/${formSlug}`;
  cy.visit(url, options);
});

/**
 * Select a pricing plan/product from the pricing table
 * @param {number} planIndex - Index of the plan to select (0-based)
 * @param {string} planSelector - Custom selector for pricing plans
 */
Cypress.Commands.add("selectPricingPlan", (planIndex = 0, planSelector = null) => {
  const selector = planSelector || '[data-testid="pricing-table"], .wu-pricing-table, [id*="pricing"], [class*="plan"]';

  cy.get(selector)
    .should('be.visible')
    .eq(planIndex)
    .within(() => {
      cy.get('button, .wu-button, [type="submit"], a[href*="checkout"]')
        .first()
        .click();
    });
});

/**
 * Fill checkout account details
 * @param {object} customerData - Customer information
 */
Cypress.Commands.add("fillAccountDetails", (customerData) => {
  const {
    username,
    email,
    password,
    firstName = '',
    lastName = ''
  } = customerData;

  // Username field
  cy.get('#username, [name="username"], [data-testid="username"]')
    .should('be.visible')
    .clear()
    .type(username);

  // Email field
  cy.get('#email, [name="email"], [data-testid="email"]')
    .should('be.visible')
    .clear()
    .type(email);

  // Password field
  cy.get('#password, [name="password"], [data-testid="password"]')
    .should('be.visible')
    .clear()
    .type(password);

  // Password confirmation (if exists)
  cy.get('body').then(($body) => {
    if ($body.find('#password_confirmation, [name="password_confirmation"], [data-testid="password-confirm"]').length > 0) {
      cy.get('#password_confirmation, [name="password_confirmation"], [data-testid="password-confirm"]')
        .clear()
        .type(password);
    }
  });

  // First name (if exists)
  if (firstName) {
    cy.get('body').then(($body) => {
      if ($body.find('#first_name, [name="first_name"], [data-testid="first-name"]').length > 0) {
        cy.get('#first_name, [name="first_name"], [data-testid="first-name"]')
          .clear()
          .type(firstName);
      }
    });
  }

  // Last name (if exists)
  if (lastName) {
    cy.get('body').then(($body) => {
      if ($body.find('#last_name, [name="last_name"], [data-testid="last-name"]').length > 0) {
        cy.get('#last_name, [name="last_name"], [data-testid="last-name"]')
          .clear()
          .type(lastName);
      }
    });
  }
});

/**
 * Fill site details
 * @param {object} siteData - Site information
 */
Cypress.Commands.add("fillSiteDetails", (siteData) => {
  const { title, path } = siteData;

  // Site title
  cy.get('#site_title, [name="site_title"], [data-testid="site-title"]')
    .should('be.visible')
    .clear()
    .type(title);

  // Site URL/path
  cy.get('#site_url, [name="site_url"], [data-testid="site-url"], [name="blogname"]')
    .should('be.visible')
    .clear()
    .type(path);
});

/**
 * Select a site template (if template selection is available)
 * @param {number} templateIndex - Index of template to select (0-based)
 */
Cypress.Commands.add("selectSiteTemplate", (templateIndex = 0) => {
  cy.get('body').then(($body) => {
    const templateSelectors = [
      '[data-testid="template-selection"]',
      '.wu-template-selection',
      '[class*="template"]',
      '.template-item'
    ];

    let templateFound = false;

    templateSelectors.forEach(selector => {
      if (!templateFound && $body.find(selector).length > 0) {
        cy.get(selector).eq(templateIndex).click();
        templateFound = true;
      }
    });

    if (!templateFound) {
      cy.log('No template selection found, skipping template selection');
    }
  });
});

/**
 * Fill billing address information
 * @param {object} billingData - Billing address data
 */
Cypress.Commands.add("fillBillingAddress", (billingData = {}) => {
  const {
    address = '123 Test Street',
    city = 'Test City',
    state = 'CA',
    zipCode = '12345',
    country = 'US'
  } = billingData;

  cy.get('body').then(($body) => {
    if ($body.find('[name*="billing"], [data-testid*="billing"]').length > 0) {

      // Address line 1
      const addressSelectors = [
        '[name="billing_address[address_line_1]"]',
        '[name="billing_address_line_1"]',
        '[name="billing_address"]',
        '[data-testid="billing-address"]'
      ];

      addressSelectors.forEach(selector => {
        cy.get('body').then(($addressBody) => {
          if ($addressBody.find(selector).length > 0) {
            cy.get(selector).type(address);
          }
        });
      });

      // City
      const citySelectors = [
        '[name="billing_address[city]"]',
        '[name="billing_city"]',
        '[data-testid="billing-city"]'
      ];

      citySelectors.forEach(selector => {
        cy.get('body').then(($cityBody) => {
          if ($cityBody.find(selector).length > 0) {
            cy.get(selector).type(city);
          }
        });
      });

      // State
      const stateSelectors = [
        '[name="billing_address[state]"]',
        '[name="billing_state"]',
        '[data-testid="billing-state"]'
      ];

      stateSelectors.forEach(selector => {
        cy.get('body').then(($stateBody) => {
          if ($stateBody.find(selector).length > 0) {
            cy.get(selector).type(state);
          }
        });
      });

      // Zip Code
      const zipSelectors = [
        '[name="billing_address[zip_code]"]',
        '[name="billing_zip"]',
        '[data-testid="billing-zip"]'
      ];

      zipSelectors.forEach(selector => {
        cy.get('body').then(($zipBody) => {
          if ($zipBody.find(selector).length > 0) {
            cy.get(selector).type(zipCode);
          }
        });
      });
    }
  });
});

/**
 * Select a payment gateway
 * @param {string} gateway - Gateway type ('manual', 'stripe', 'paypal', 'free')
 */
Cypress.Commands.add("selectPaymentGateway", (gateway = 'manual') => {
  const gatewaySelectors = [
    `[data-testid="gateway-${gateway}"]`,
    `[value="${gateway}"]`,
    `[data-gateway="${gateway}"]`,
    `#gateway_${gateway}`,
    `.gateway-${gateway}`
  ];

  let gatewayFound = false;

  gatewaySelectors.forEach(selector => {
    cy.get('body').then(($body) => {
      if (!gatewayFound && $body.find(selector).length > 0) {
        cy.get(selector).click();
        gatewayFound = true;
      }
    });
  });

  if (!gatewayFound) {
    cy.log(`Payment gateway ${gateway} not found, proceeding anyway`);
  }
});

/**
 * Proceed to next checkout step
 * @param {string} buttonText - Text to look for in the button
 */
Cypress.Commands.add("proceedToNextStep", (buttonText = '') => {
  const buttonSelectors = [
    '[data-testid="continue-btn"]',
    '[data-testid="next-btn"]',
    '.wu-button',
    'button[type="submit"]',
    'input[type="submit"]'
  ];

  const textPatterns = buttonText ? [buttonText] : [
    'continue',
    'next',
    'proceed',
    'complete',
    'finish',
    'create',
    'register'
  ];

  buttonSelectors.forEach(selector => {
    cy.get('body').then(($body) => {
      if ($body.find(selector).length > 0) {
        textPatterns.forEach(pattern => {
          cy.get(selector).then($buttons => {
            const matchingButton = Array.from($buttons).find(btn =>
              btn.textContent.toLowerCase().includes(pattern.toLowerCase())
            );
            if (matchingButton && !matchingButton.disabled) {
              cy.wrap(matchingButton).click();
              return false; // Break out of loops
            }
          });
        });
      }
    });
  });
});

/**
 * Complete the checkout process
 */
Cypress.Commands.add("completeCheckout", () => {
  const completionSelectors = [
    '[data-testid="complete-btn"]',
    '[data-testid="finish-btn"]',
    '.wu-button',
    'button[type="submit"]'
  ];

  const completionTexts = [
    'complete',
    'finish',
    'create account',
    'register',
    'pay now',
    'submit'
  ];

  completionSelectors.forEach(selector => {
    cy.get('body').then(($body) => {
      if ($body.find(selector).length > 0) {
        completionTexts.forEach(text => {
          cy.get(selector).then($buttons => {
            const matchingButton = Array.from($buttons).find(btn =>
              btn.textContent.toLowerCase().includes(text)
            );
            if (matchingButton && !matchingButton.disabled) {
              cy.wrap(matchingButton).click({ timeout: 10000 });
              return false;
            }
          });
        });
      }
    });
  });
});

/**
 * Verify checkout completion/success
 * @param {object} verificationData - Data to verify in success page
 */
Cypress.Commands.add("verifyCheckoutSuccess", (verificationData = {}) => {
  const { email, siteTitle, shouldRedirect = true } = verificationData;

  // Wait for redirect if expected
  if (shouldRedirect) {
    cy.url({ timeout: 30000 }).should('satisfy', url =>
      url.includes('/confirmation') ||
      url.includes('/thank') ||
      url.includes('/success') ||
      url.includes('/complete')
    );
  }

  // Verify success message
  const successSelectors = [
    '[data-testid="success-message"]',
    '.wu-success',
    '.notice-success',
    '[class*="success"]',
    '.checkout-success'
  ];

  successSelectors.forEach(selector => {
    cy.get('body').then(($body) => {
      if ($body.find(selector).length > 0) {
        cy.get(selector)
          .should('be.visible')
          .and('contain.text', /success|complete|welcome|thank|registered/i);
      }
    });
  });

  // Verify email if provided
  if (email) {
    cy.get('[data-testid="customer-info"], .wu-customer-info, .customer-details')
      .should('contain.text', email);
  }

  // Verify site title if provided
  if (siteTitle) {
    cy.get('[data-testid="site-info"], .wu-site-info, .site-details')
      .should('contain.text', siteTitle);
  }
});

/**
 * Assert current checkout step
 * @param {number|string} expectedStep - Expected step number or name
 */
Cypress.Commands.add("assertCheckoutStep", (expectedStep) => {
  cy.get('[data-testid="checkout-step"], .wu-step, [class*="step"], .checkout-progress')
    .should('be.visible')
    .and('contain.text', expectedStep);
});

/**
 * Check if checkout form has validation errors
 */
Cypress.Commands.add("hasValidationErrors", () => {
  return cy.get('body').then($body => {
    const errorSelectors = [
      '[data-testid="error"]',
      '.wu-error',
      '.error',
      '[class*="error"]',
      '.form-error',
      '.validation-error'
    ];

    return errorSelectors.some(selector => $body.find(selector + ':visible').length > 0);
  });
});