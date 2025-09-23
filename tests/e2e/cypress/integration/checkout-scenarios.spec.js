/**
 * E2E tests for different checkout scenarios and edge cases
 *
 * This test suite covers various checkout scenarios including
 * different plan types, payment methods, and special cases.
 */

describe("Checkout Scenarios", () => {
  const testData = {
    customers: {
      basic: {
        username: `basicuser_${Date.now()}`,
        email: `basicuser_${Date.now()}@example.com`,
        password: 'BasicPass123!',
        firstName: 'Jane',
        lastName: 'Smith'
      },
      premium: {
        username: `premiumuser_${Date.now()}`,
        email: `premiumuser_${Date.now()}@example.com`,
        password: 'PremiumPass123!',
        firstName: 'John',
        lastName: 'Premium'
      }
    },
    sites: {
      basic: {
        title: 'Basic Test Site',
        path: `basicsite_${Date.now()}`
      },
      premium: {
        title: 'Premium Business Site',
        path: `premiumsite_${Date.now()}`
      }
    }
  };

  describe("Free Plan Registration", () => {
    it("Should complete registration with free plan", () => {
      cy.visitCheckoutForm('registration');

      // Look for and select free plan
      cy.get('body').then($body => {
        // Try to find free plan indicators
        const freePlanSelectors = [
          '[data-testid*="free"]',
          '[class*="free"]',
          '[data-price="0"]',
          ':contains("Free")',
          ':contains("$0")'
        ];

        let freePlanFound = false;

        freePlanSelectors.forEach(selector => {
          if (!freePlanFound && $body.find(selector).length > 0) {
            cy.get(selector).first().within(() => {
              cy.get('button, .wu-button, [type="submit"]').click();
            });
            freePlanFound = true;
          }
        });

        if (!freePlanFound) {
          // Fallback to first plan (might be free)
          cy.selectPricingPlan(0);
        }
      });

      // Fill account details
      cy.fillAccountDetails(testData.customers.basic);
      cy.proceedToNextStep();

      // Fill site details
      cy.fillSiteDetails(testData.sites.basic);
      cy.selectSiteTemplate(0);
      cy.proceedToNextStep();

      // Complete registration (should skip payment for free plan)
      cy.completeCheckout();

      // Verify success
      cy.verifyCheckoutSuccess({
        email: testData.customers.basic.email,
        siteTitle: testData.sites.basic.title
      });
    });
  });

  describe("Paid Plan Registration", () => {
    it("Should complete registration with paid plan using manual payment", () => {
      cy.visitCheckoutForm('registration');

      // Select a paid plan (try to find one that's not free)
      cy.get('body').then($body => {
        const paidPlanSelectors = [
          '[data-testid*="paid"]',
          '[class*="premium"]',
          '[class*="pro"]',
          ':contains("$") [data-testid*="plan"]',
          ':not(:contains("Free")) [data-testid*="plan"]'
        ];

        let paidPlanFound = false;

        paidPlanSelectors.forEach(selector => {
          if (!paidPlanFound && $body.find(selector).length > 0) {
            cy.get(selector).first().within(() => {
              cy.get('button, .wu-button, [type="submit"]').click();
            });
            paidPlanFound = true;
          }
        });

        if (!paidPlanFound) {
          // Fallback to second plan if available
          cy.get('[data-testid="pricing-table"], .wu-pricing-table, [id*="pricing"], [class*="plan"]')
            .then($plans => {
              if ($plans.length > 1) {
                cy.selectPricingPlan(1);
              } else {
                cy.selectPricingPlan(0);
              }
            });
        }
      });

      // Fill account details
      cy.fillAccountDetails(testData.customers.premium);
      cy.proceedToNextStep();

      // Fill site details
      cy.fillSiteDetails(testData.sites.premium);
      cy.selectSiteTemplate(0);
      cy.proceedToNextStep();

      // Handle payment
      cy.fillBillingAddress({
        address: '456 Premium Street',
        city: 'Business City',
        state: 'NY',
        zipCode: '54321'
      });

      cy.selectPaymentGateway('manual');
      cy.completeCheckout();

      // Verify success
      cy.verifyCheckoutSuccess({
        email: testData.customers.premium.email,
        siteTitle: testData.sites.premium.title
      });
    });
  });

  describe("Multi-step Navigation", () => {
    beforeEach(() => {
      cy.visitCheckoutForm('registration');
      cy.selectPricingPlan(0);
    });

    it("Should handle browser back/forward navigation", () => {
      // Fill account details and proceed
      cy.fillAccountDetails(testData.customers.basic);
      cy.proceedToNextStep();

      // Verify we're on step 3 (site details)
      cy.assertCheckoutStep('3');

      // Go back using browser navigation
      cy.go('back');

      // Should be back on step 2 with form data preserved
      cy.assertCheckoutStep('2');
      cy.get('#username, [name="username"]')
        .should('have.value', testData.customers.basic.username);

      // Go forward again
      cy.go('forward');

      // Should be on step 3 again
      cy.assertCheckoutStep('3');
    });

    it("Should allow step navigation via step indicators", () => {
      // Fill account details
      cy.fillAccountDetails(testData.customers.basic);
      cy.proceedToNextStep();

      // Check if step navigation is available
      cy.get('body').then($body => {
        const hasStepNavigation = $body.find('[data-testid="step-nav"], .wu-step-nav, [class*="step-nav"]').length > 0;

        if (hasStepNavigation) {
          // Try to navigate back to step 2
          cy.get('[data-testid="step-2"], [data-step="2"], .step-2').click();

          // Should be back on account details step
          cy.assertCheckoutStep('2');
          cy.get('#username, [name="username"]')
            .should('have.value', testData.customers.basic.username);
        } else {
          cy.log('Step navigation not available');
        }
      });
    });
  });

  describe("Template Selection Scenarios", () => {
    beforeEach(() => {
      cy.visitCheckoutForm('registration');
      cy.selectPricingPlan(0);
      cy.fillAccountDetails(testData.customers.basic);
      cy.proceedToNextStep();
    });

    it("Should handle sites with template selection", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="template-selection"], .wu-template-selection').length > 0) {
          // Test selecting different templates
          cy.get('[data-testid="template-selection"], .wu-template-selection')
            .should('have.length.at.least', 1);

          // Select first template
          cy.selectSiteTemplate(0);

          // Fill site details
          cy.fillSiteDetails(testData.sites.basic);
          cy.proceedToNextStep();

          // Should proceed to next step
          cy.url().should('not.contain', 'template');

        } else {
          cy.log('No template selection available');
          cy.fillSiteDetails(testData.sites.basic);
          cy.proceedToNextStep();
        }
      });
    });

    it("Should handle blank/custom template option", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="template-blank"], [data-template="blank"], :contains("Blank")').length > 0) {
          cy.get('[data-testid="template-blank"], [data-template="blank"], :contains("Blank")').click();

          cy.fillSiteDetails(testData.sites.basic);
          cy.proceedToNextStep();

          // Should proceed successfully
          cy.url().should('not.contain', 'template');
        }
      });
    });
  });

  describe("Payment Gateway Scenarios", () => {
    beforeEach(() => {
      cy.visitCheckoutForm('registration');
      cy.selectPricingPlan(0);
      cy.fillAccountDetails(testData.customers.basic);
      cy.proceedToNextStep();
      cy.fillSiteDetails(testData.sites.basic);
      cy.proceedToNextStep();
    });

    it("Should handle manual payment gateway", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-gateway="manual"], [value="manual"]').length > 0) {
          cy.selectPaymentGateway('manual');
          cy.completeCheckout();

          // Should complete successfully
          cy.verifyCheckoutSuccess({
            email: testData.customers.basic.email,
            siteTitle: testData.sites.basic.title
          });
        }
      });
    });

    it("Should handle free gateway for zero-cost orders", () => {
      cy.get('body').then($body => {
        const hasFreeGateway = $body.find('[data-gateway="free"], [value="free"]').length > 0;
        const isFreeOrder = $body.find(':contains("$0"), :contains("Free"), [data-price="0"]').length > 0;

        if (hasFreeGateway && isFreeOrder) {
          cy.selectPaymentGateway('free');
          cy.completeCheckout();

          cy.verifyCheckoutSuccess({
            email: testData.customers.basic.email,
            siteTitle: testData.sites.basic.title
          });
        }
      });
    });
  });

  describe("Discount Code Scenarios", () => {
    beforeEach(() => {
      cy.visitCheckoutForm('registration');
      cy.selectPricingPlan(0);
    });

    it("Should handle discount code application", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="discount-code"], [name*="discount"], [name*="coupon"]').length > 0) {
          // Try applying a discount code
          cy.get('[data-testid="discount-code"], [name*="discount"], [name*="coupon"]')
            .type('TESTCODE');

          cy.get('[data-testid="apply-discount"], [data-testid="apply-coupon"], button:contains("Apply")').click();

          // Should show either success or error message
          cy.get('[data-testid="discount-message"], .discount-message, .coupon-message')
            .should('be.visible');

          // Continue with checkout
          cy.fillAccountDetails(testData.customers.basic);
          cy.proceedToNextStep();
          cy.fillSiteDetails(testData.sites.basic);
          cy.proceedToNextStep();
          cy.completeCheckout();

          cy.verifyCheckoutSuccess({
            email: testData.customers.basic.email,
            siteTitle: testData.sites.basic.title
          });
        } else {
          cy.log('No discount code field found');
        }
      });
    });
  });

  describe("Error Recovery Scenarios", () => {
    it("Should handle session timeout gracefully", () => {
      cy.visitCheckoutForm('registration');
      cy.selectPricingPlan(0);
      cy.fillAccountDetails(testData.customers.basic);

      // Simulate session timeout by clearing cookies
      cy.clearCookies();

      cy.proceedToNextStep();

      // Should either redirect to login or show session error
      cy.url().then(url => {
        if (url.includes('login')) {
          cy.log('Redirected to login as expected');
        } else {
          cy.get('[data-testid="error"], .wu-error, .error')
            .should('be.visible')
            .and('contain.text', /session|expired|login/i);
        }
      });
    });

    it("Should handle network errors during submission", () => {
      cy.visitCheckoutForm('registration');
      cy.selectPricingPlan(0);
      cy.fillAccountDetails(testData.customers.basic);
      cy.proceedToNextStep();
      cy.fillSiteDetails(testData.sites.basic);
      cy.proceedToNextStep();

      // Intercept checkout request to simulate network error
      cy.intercept('POST', '**/checkout**', { forceNetworkError: true }).as('checkoutError');

      cy.completeCheckout();

      // Should handle error gracefully
      cy.wait('@checkoutError');
      cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
        .should('be.visible')
        .and('contain.text', /network|connection|try.*again/i);
    });
  });

  describe("Mobile Responsiveness", () => {
    beforeEach(() => {
      cy.viewport('iphone-x');
      cy.visitCheckoutForm('registration');
    });

    it("Should complete checkout on mobile device", () => {
      cy.selectPricingPlan(0);
      cy.fillAccountDetails(testData.customers.basic);
      cy.proceedToNextStep();
      cy.fillSiteDetails(testData.sites.basic);
      cy.proceedToNextStep();
      cy.completeCheckout();

      cy.verifyCheckoutSuccess({
        email: testData.customers.basic.email,
        siteTitle: testData.sites.basic.title
      });
    });

    it("Should handle mobile form interactions", () => {
      // Test that form fields are accessible on mobile
      cy.get('#username, [name="username"]').should('be.visible');
      cy.get('#email, [name="email"]').should('be.visible');

      // Test mobile-specific interactions
      cy.selectPricingPlan(0);

      cy.get('#username, [name="username"]').type(testData.customers.basic.username);
      cy.get('#email, [name="email"]').type(testData.customers.basic.email);

      // Should not have horizontal scroll
      cy.window().its('scrollX').should('equal', 0);
    });
  });

  afterEach(() => {
    // Reset viewport for subsequent tests
    cy.viewport(1000, 600);
  });
});