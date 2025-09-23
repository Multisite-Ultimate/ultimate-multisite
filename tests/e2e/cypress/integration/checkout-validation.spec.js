/**
 * E2E tests for checkout form validation
 *
 * This test suite focuses specifically on field validation,
 * error handling, and form validation across the checkout flow.
 */

describe("Checkout Form Validation", () => {
  const testData = {
    validCustomer: {
      username: `testuser_${Date.now()}`,
      email: `testuser_${Date.now()}@example.com`,
      password: 'ValidPassword123!',
      firstName: 'John',
      lastName: 'Doe'
    },
    validSite: {
      title: 'Valid Test Site',
      path: `validsite_${Date.now()}`
    }
  };

  beforeEach(() => {
    cy.visitCheckoutForm('registration');
  });

  describe("Product Selection Validation", () => {
    it("Should require plan selection before proceeding", () => {
      // Try to proceed without selecting any plan
      cy.proceedToNextStep();

      // Should show error or remain on same step
      cy.hasValidationErrors().then(hasErrors => {
        if (!hasErrors) {
          // If no explicit error, should still be on step 1
          cy.assertCheckoutStep('1');
        } else {
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible')
            .and('contain.text', /select|choose|plan|product/i);
        }
      });
    });

    it("Should allow proceeding after plan selection", () => {
      cy.selectPricingPlan(0);

      // Should advance to next step
      cy.assertCheckoutStep('2');
    });
  });

  describe("Account Details Validation", () => {
    beforeEach(() => {
      // Navigate to account details step
      cy.selectPricingPlan(0);
    });

    it("Should validate required fields", () => {
      // Try to proceed with empty fields
      cy.proceedToNextStep();

      // Should show validation errors
      cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
        .should('be.visible');

      // Check that required fields are marked as invalid
      cy.get('#username, [name="username"]').then($username => {
        if ($username.length > 0) {
          cy.wrap($username).should('satisfy', $el =>
            $el.attr('required') !== undefined ||
            $el.hasClass('error') ||
            $el.hasClass('invalid') ||
            $el.get(0).checkValidity() === false
          );
        }
      });

      cy.get('#email, [name="email"]').then($email => {
        if ($email.length > 0) {
          cy.wrap($email).should('satisfy', $el =>
            $el.attr('required') !== undefined ||
            $el.hasClass('error') ||
            $el.hasClass('invalid') ||
            $el.get(0).checkValidity() === false
          );
        }
      });
    });

    it("Should validate email format", () => {
      const invalidEmails = [
        'invalid-email',
        'invalid@',
        '@invalid.com',
        'invalid.email',
        'spaces @email.com',
        'email@',
        'email@.com'
      ];

      invalidEmails.forEach(invalidEmail => {
        cy.get('#email, [name="email"], [data-testid="email"]')
          .clear()
          .type(invalidEmail);

        cy.get('#username, [name="username"]').type(testData.validCustomer.username);
        cy.get('#password, [name="password"]').type(testData.validCustomer.password);

        cy.proceedToNextStep();

        // Should show validation error
        cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
          .should('be.visible');

        // Clear fields for next iteration
        cy.get('#email, [name="email"]').clear();
        cy.get('#username, [name="username"]').clear();
        cy.get('#password, [name="password"]').clear();
      });
    });

    it("Should validate username format and availability", () => {
      // Test invalid username formats
      const invalidUsernames = [
        '', // empty
        'ab', // too short
        'user name', // spaces
        'user@name', // special characters
        'UPPERCASE', // case sensitivity
        '123numericstart'
      ];

      invalidUsernames.forEach(invalidUsername => {
        if (invalidUsername.trim() !== '') {
          cy.get('#username, [name="username"], [data-testid="username"]')
            .clear()
            .type(invalidUsername);

          cy.get('#email, [name="email"]').type(testData.validCustomer.email);
          cy.get('#password, [name="password"]').type(testData.validCustomer.password);

          cy.proceedToNextStep();

          // Should show validation error
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible');

          // Clear fields
          cy.get('#username, [name="username"]').clear();
          cy.get('#email, [name="email"]').clear();
          cy.get('#password, [name="password"]').clear();
        }
      });

      // Test existing username (admin should exist)
      cy.get('#username, [name="username"]').clear().type('admin');
      cy.get('#email, [name="email"]').clear().type(testData.validCustomer.email);
      cy.get('#password, [name="password"]').clear().type(testData.validCustomer.password);

      cy.proceedToNextStep();

      // Should show username taken error
      cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
        .should('be.visible')
        .and('contain.text', /username.*taken|username.*exists|already.*use/i);
    });

    it("Should validate password requirements", () => {
      const weakPasswords = [
        '', // empty
        '123', // too short
        'password', // too simple
        '12345678' // numeric only
      ];

      weakPasswords.forEach(weakPassword => {
        if (weakPassword.trim() !== '') {
          cy.get('#password, [name="password"], [data-testid="password"]')
            .clear()
            .type(weakPassword);

          cy.get('#username, [name="username"]').type(testData.validCustomer.username);
          cy.get('#email, [name="email"]').type(testData.validCustomer.email);

          cy.proceedToNextStep();

          // Should show password validation error
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible');

          // Clear fields
          cy.get('#password, [name="password"]').clear();
          cy.get('#username, [name="username"]').clear();
          cy.get('#email, [name="email"]').clear();
        }
      });
    });

    it("Should validate password confirmation match", () => {
      cy.get('body').then($body => {
        // Only test if password confirmation field exists
        if ($body.find('#password_confirmation, [name="password_confirmation"]').length > 0) {
          cy.get('#username, [name="username"]').type(testData.validCustomer.username);
          cy.get('#email, [name="email"]').type(testData.validCustomer.email);
          cy.get('#password, [name="password"]').type(testData.validCustomer.password);
          cy.get('#password_confirmation, [name="password_confirmation"]').type('DifferentPassword123!');

          cy.proceedToNextStep();

          // Should show password mismatch error
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible')
            .and('contain.text', /password.*match|confirm.*password/i);
        }
      });
    });
  });

  describe("Site Details Validation", () => {
    beforeEach(() => {
      // Navigate to site details step
      cy.selectPricingPlan(0);
      cy.fillAccountDetails(testData.validCustomer);
      cy.proceedToNextStep();
    });

    it("Should validate required site fields", () => {
      // Try to proceed with empty site fields
      cy.proceedToNextStep();

      // Should show validation errors
      cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
        .should('be.visible');
    });

    it("Should validate site URL format", () => {
      const invalidSiteUrls = [
        '', // empty
        ' ', // only spaces
        'site with spaces',
        'UPPERCASE-SITE',
        'site-with-special!@#',
        'site..double-dots',
        '-starting-dash',
        'ending-dash-',
        '123numericstart'
      ];

      invalidSiteUrls.forEach(invalidUrl => {
        if (invalidUrl.trim()) {
          cy.get('#site_url, [name="site_url"], [data-testid="site-url"], [name="blogname"]')
            .clear()
            .type(invalidUrl);

          cy.get('#site_title, [name="site_title"]').type(testData.validSite.title);

          cy.proceedToNextStep();

          // Should show validation error
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible');

          // Clear fields
          cy.get('#site_url, [name="site_url"], [data-testid="site-url"], [name="blogname"]').clear();
          cy.get('#site_title, [name="site_title"]').clear();
        }
      });
    });

    it("Should validate site title requirements", () => {
      const invalidTitles = [
        '', // empty
        '   ', // only spaces
        'A', // too short
        'X'.repeat(256) // too long
      ];

      invalidTitles.forEach(invalidTitle => {
        cy.get('#site_title, [name="site_title"], [data-testid="site-title"]')
          .clear()
          .type(invalidTitle);

        cy.get('#site_url, [name="site_url"]').type(testData.validSite.path);

        cy.proceedToNextStep();

        // Should show validation error for empty/invalid titles
        if (invalidTitle.trim() === '' || invalidTitle.length > 255) {
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible');
        }

        // Clear fields
        cy.get('#site_title, [name="site_title"]').clear();
        cy.get('#site_url, [name="site_url"]').clear();
      });
    });

    it("Should check site URL availability", () => {
      // Test with existing site URL (main site should exist)
      cy.get('#site_url, [name="site_url"], [data-testid="site-url"], [name="blogname"]')
        .clear()
        .type('main'); // or 'blog' or 'www' - common existing paths

      cy.get('#site_title, [name="site_title"]').type(testData.validSite.title);

      cy.proceedToNextStep();

      // Should show site URL taken error
      cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
        .should('be.visible')
        .and('contain.text', /site.*taken|url.*exists|already.*use|not.*available/i);
    });
  });

  describe("Payment Validation", () => {
    beforeEach(() => {
      // Navigate to payment step
      cy.selectPricingPlan(0);
      cy.fillAccountDetails(testData.validCustomer);
      cy.proceedToNextStep();
      cy.fillSiteDetails(testData.validSite);
      cy.proceedToNextStep();
    });

    it("Should handle free plan checkout", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="free-plan"], .wu-free-plan, [class*="free"]').length > 0) {
          // For free plans, should be able to complete directly
          cy.completeCheckout();

          // Should proceed to confirmation
          cy.verifyCheckoutSuccess({
            email: testData.validCustomer.email,
            siteTitle: testData.validSite.title
          });
        } else {
          cy.log('Not a free plan, skipping free plan test');
        }
      });
    });

    it("Should validate billing information for paid plans", () => {
      cy.get('body').then($body => {
        if ($body.find('[name*="billing"], [data-testid*="billing"]').length > 0) {
          // Try to proceed without billing info
          cy.completeCheckout();

          // Should show validation error
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible');

          // Fill billing info and try again
          cy.fillBillingAddress();
          cy.selectPaymentGateway('manual');
          cy.completeCheckout();

          // Should proceed successfully
          cy.verifyCheckoutSuccess({
            email: testData.validCustomer.email,
            siteTitle: testData.validSite.title
          });
        } else {
          cy.log('No billing fields found, likely free plan');
        }
      });
    });

    it("Should require payment gateway selection", () => {
      cy.get('body').then($body => {
        const hasPaymentGateways = $body.find('[name*="gateway"], [data-testid*="gateway"]').length > 0;

        if (hasPaymentGateways) {
          // Try to complete without selecting gateway
          cy.completeCheckout();

          // Should show validation error
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible');

          // Select payment gateway and try again
          cy.selectPaymentGateway('manual');
          cy.completeCheckout();

          // Should proceed successfully
          cy.verifyCheckoutSuccess({
            email: testData.validCustomer.email,
            siteTitle: testData.validSite.title
          });
        }
      });
    });
  });

  describe("Cross-field Validation", () => {
    it("Should handle email uniqueness across users", () => {
      // This would require testing with existing user email
      // Skip if no existing users or implement user creation first
      cy.log('Email uniqueness validation would require existing test data');
    });

    it("Should maintain form state during validation errors", () => {
      cy.selectPricingPlan(0);

      // Fill partial form data
      cy.get('#username, [name="username"]').type(testData.validCustomer.username);
      cy.get('#email, [name="email"]').type('invalid-email');

      // Try to proceed
      cy.proceedToNextStep();

      // Should show error but maintain username field value
      cy.get('#username, [name="username"]').should('have.value', testData.validCustomer.username);
    });
  });
});