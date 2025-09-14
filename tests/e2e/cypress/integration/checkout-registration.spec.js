/**
 * E2E tests for the complete checkout registration flow
 *
 * This test suite covers the happy path for new user registration
 * including all typical checkout steps: product selection, user details,
 * site details, payment processing, and confirmation.
 */

describe("Checkout Registration Flow", () => {
  // Test data for consistent usage across tests
  const testCustomer = {
    username: `testuser_${Date.now()}`,
    email: `testuser_${Date.now()}@example.com`,
    password: 'TestPassword123!',
    firstName: 'John',
    lastName: 'Doe'
  };

  const testSite = {
    title: 'Test Site',
    path: `testsite_${Date.now()}`
  };

  beforeEach(() => {
    // Visit the registration/checkout page
    // Note: This assumes there's a checkout form with slug 'registration'
    cy.visit('/checkout/registration');
  });

  it("Should complete the full registration checkout flow successfully", () => {
    // Step 1: Plan/Product Selection
    cy.log("Starting Step 1: Plan Selection");

    cy.get('[data-testid="pricing-table"], .wu-pricing-table, [id*="pricing"], [class*="plan"]')
      .should('be.visible')
      .first()
      .within(() => {
        cy.get('button, .wu-button, [type="submit"]').first().click();
      });

    // Verify we moved to the next step
    cy.get('[data-testid="checkout-step"], .wu-step, [class*="step"]')
      .should('contain.text', '2')
      .or('contain.text', 'Account')
      .or('contain.text', 'Details');

    // Step 2: Account/User Details
    cy.log("Starting Step 2: Account Details");

    // Fill in username
    cy.get('#username, [name="username"], [data-testid="username"]')
      .should('be.visible')
      .clear()
      .type(testCustomer.username);

    // Fill in email
    cy.get('#email, [name="email"], [data-testid="email"]')
      .should('be.visible')
      .clear()
      .type(testCustomer.email);

    // Fill in password
    cy.get('#password, [name="password"], [data-testid="password"]')
      .should('be.visible')
      .clear()
      .type(testCustomer.password);

    // Fill in confirm password if it exists
    cy.get('body').then(($body) => {
      if ($body.find('#password_confirmation, [name="password_confirmation"], [data-testid="password-confirm"]').length > 0) {
        cy.get('#password_confirmation, [name="password_confirmation"], [data-testid="password-confirm"]')
          .clear()
          .type(testCustomer.password);
      }
    });

    // Fill in first name if it exists
    cy.get('body').then(($body) => {
      if ($body.find('#first_name, [name="first_name"], [data-testid="first-name"]').length > 0) {
        cy.get('#first_name, [name="first_name"], [data-testid="first-name"]')
          .clear()
          .type(testCustomer.firstName);
      }
    });

    // Fill in last name if it exists
    cy.get('body').then(($body) => {
      if ($body.find('#last_name, [name="last_name"], [data-testid="last-name"]').length > 0) {
        cy.get('#last_name, [name="last_name"], [data-testid="last-name"]')
          .clear()
          .type(testCustomer.lastName);
      }
    });

    // Continue to next step
    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .should('not.be.disabled')
      .click();

    // Step 3: Site Details
    cy.log("Starting Step 3: Site Details");

    // Verify we're on the site details step
    cy.get('[data-testid="checkout-step"], .wu-step, [class*="step"]')
      .should('contain.text', '3')
      .or('contain.text', 'Site')
      .or('contain.text', 'Domain');

    // Fill in site title
    cy.get('#site_title, [name="site_title"], [data-testid="site-title"]')
      .should('be.visible')
      .clear()
      .type(testSite.title);

    // Fill in site path/URL
    cy.get('#site_url, [name="site_url"], [data-testid="site-url"], [name="blogname"]')
      .should('be.visible')
      .clear()
      .type(testSite.path);

    // Select template if template selection exists
    cy.get('body').then(($body) => {
      if ($body.find('[data-testid="template-selection"], .wu-template-selection, [class*="template"]').length > 0) {
        cy.get('[data-testid="template-selection"], .wu-template-selection, [class*="template"]')
          .first()
          .click();
      }
    });

    // Continue to payment step
    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .should('not.be.disabled')
      .click();

    // Step 4: Payment Details (if not free)
    cy.log("Starting Step 4: Payment Processing");

    // Check if this is a free plan or paid plan
    cy.get('body').then(($body) => {
      const hasFreeIndicator = $body.find('[data-testid="free-plan"], .wu-free-plan, [class*="free"]').length > 0;
      const hasPaymentFields = $body.find('[data-testid="payment-form"], .wu-payment-form, [name*="card"], [name*="billing"]').length > 0;

      if (hasFreeIndicator || !hasPaymentFields) {
        cy.log("Free plan detected - skipping payment details");

        // For free plans, just click continue/complete
        cy.get('[data-testid="complete-btn"], [data-testid="continue-btn"], .wu-button, button[type="submit"]')
          .contains(/complete|finish|continue|create/i)
          .should('not.be.disabled')
          .click();

      } else {
        cy.log("Paid plan detected - filling payment details");

        // Fill in billing address if required
        cy.get('body').then(($billingBody) => {
          if ($billingBody.find('[name="billing_address"], [data-testid="billing-address"]').length > 0) {
            cy.get('[name="billing_address[address_line_1]"], [name="billing_address_line_1"]')
              .type('123 Test Street');
            cy.get('[name="billing_address[city]"], [name="billing_city"]')
              .type('Test City');
            cy.get('[name="billing_address[state]"], [name="billing_state"]')
              .type('CA');
            cy.get('[name="billing_address[zip_code]"], [name="billing_zip"]')
              .type('12345');
          }
        });

        // Select Manual Payment gateway (most reliable for testing)
        cy.get('[data-testid="gateway-manual"], [value="manual"], [data-gateway="manual"]')
          .should('be.visible')
          .click();

        // Complete the payment
        cy.get('[data-testid="complete-btn"], .wu-button, button[type="submit"]')
          .contains(/complete|finish|pay/i)
          .should('not.be.disabled')
          .click();
      }
    });

    // Step 5: Confirmation/Thank You
    cy.log("Step 5: Verifying Registration Completion");

    // Wait for redirect to confirmation page or success message
    cy.url({ timeout: 30000 }).should('contain', '/confirmation')
      .or('contain', '/thank')
      .or('contain', '/success');

    // Verify success message
    cy.get('[data-testid="success-message"], .wu-success, .notice-success, [class*="success"]')
      .should('be.visible')
      .and('contain.text', /success|complete|welcome|thank/i);

    // Verify customer details are displayed
    cy.get('[data-testid="customer-info"], .wu-customer-info')
      .should('contain.text', testCustomer.email);

    // Verify site information is displayed
    cy.get('[data-testid="site-info"], .wu-site-info')
      .should('contain.text', testSite.title);
  });

  it("Should validate required fields on each step", () => {
    cy.log("Testing field validation across checkout steps");

    // Step 1: Try to proceed without selecting a plan
    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .click();

    // Should show validation error or still be on the same step
    cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
      .should('be.visible')
      .or(() => {
        cy.get('[data-testid="checkout-step"], .wu-step, [class*="step"]')
          .should('contain.text', '1');
      });

    // Select a plan to proceed
    cy.get('[data-testid="pricing-table"], .wu-pricing-table, [id*="pricing"], [class*="plan"]')
      .should('be.visible')
      .first()
      .within(() => {
        cy.get('button, .wu-button, [type="submit"]').first().click();
      });

    // Step 2: Try to proceed with empty required fields
    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .click();

    // Should show validation errors
    cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
      .should('be.visible');

    // Verify specific field errors
    cy.get('#username, [name="username"]').then(($username) => {
      if ($username.length > 0) {
        cy.wrap($username).should('have.attr', 'required')
          .or('have.class', 'error')
          .or('have.class', 'invalid');
      }
    });

    cy.get('#email, [name="email"]').then(($email) => {
      if ($email.length > 0) {
        cy.wrap($email).should('have.attr', 'required')
          .or('have.class', 'error')
          .or('have.class', 'invalid');
      }
    });
  });

  it("Should handle email validation correctly", () => {
    cy.log("Testing email field validation");

    // Navigate to account step
    cy.get('[data-testid="pricing-table"], .wu-pricing-table, [id*="pricing"], [class*="plan"]')
      .should('be.visible')
      .first()
      .within(() => {
        cy.get('button, .wu-button, [type="submit"]').first().click();
      });

    // Test invalid email formats
    const invalidEmails = ['invalid', 'invalid@', '@invalid.com', 'invalid.com'];

    invalidEmails.forEach((invalidEmail) => {
      cy.get('#email, [name="email"], [data-testid="email"]')
        .should('be.visible')
        .clear()
        .type(invalidEmail);

      cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
        .contains(/continue|next|proceed/i)
        .click();

      // Should show validation error
      cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
        .should('be.visible');
    });

    // Test valid email
    cy.get('#email, [name="email"], [data-testid="email"]')
      .clear()
      .type(testCustomer.email);

    // Fill other required fields
    cy.get('#username, [name="username"], [data-testid="username"]')
      .clear()
      .type(testCustomer.username);

    cy.get('#password, [name="password"], [data-testid="password"]')
      .clear()
      .type(testCustomer.password);

    // Should be able to proceed
    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .should('not.be.disabled')
      .click();

    // Should advance to next step
    cy.get('[data-testid="checkout-step"], .wu-step, [class*="step"]')
      .should('contain.text', '3')
      .or('contain.text', 'Site')
      .or('contain.text', 'Domain');
  });

  it("Should handle username availability checking", () => {
    cy.log("Testing username availability");

    // Navigate to account step
    cy.get('[data-testid="pricing-table"], .wu-pricing-table, [id*="pricing"], [class*="plan"]')
      .should('be.visible')
      .first()
      .within(() => {
        cy.get('button, .wu-button, [type="submit"]').first().click();
      });

    // Test with admin username (should be taken)
    cy.get('#username, [name="username"], [data-testid="username"]')
      .should('be.visible')
      .clear()
      .type('admin');

    cy.get('#email, [name="email"], [data-testid="email"]')
      .clear()
      .type(testCustomer.email);

    cy.get('#password, [name="password"], [data-testid="password"]')
      .clear()
      .type(testCustomer.password);

    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .click();

    // Should show username taken error
    cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
      .should('be.visible')
      .and('contain.text', /username|taken|exists/i);

    // Use unique username
    cy.get('#username, [name="username"], [data-testid="username"]')
      .clear()
      .type(testCustomer.username);

    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .click();

    // Should advance to next step
    cy.get('[data-testid="checkout-step"], .wu-step, [class*="step"]')
      .should('contain.text', '3')
      .or('contain.text', 'Site')
      .or('contain.text', 'Domain');
  });

  it("Should validate site URL availability", () => {
    cy.log("Testing site URL validation");

    // Navigate through to site details step
    cy.get('[data-testid="pricing-table"], .wu-pricing-table, [id*="pricing"], [class*="plan"]')
      .first()
      .within(() => {
        cy.get('button, .wu-button, [type="submit"]').first().click();
      });

    // Fill account details
    cy.get('#username, [name="username"]').type(testCustomer.username);
    cy.get('#email, [name="email"]').type(testCustomer.email);
    cy.get('#password, [name="password"]').type(testCustomer.password);

    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .click();

    // Test invalid site URLs
    const invalidSiteUrls = ['', ' ', 'site with spaces', 'UPPERCASE', 'site-with-special!'];

    invalidSiteUrls.forEach((invalidUrl) => {
      if (invalidUrl.trim()) {
        cy.get('#site_url, [name="site_url"], [data-testid="site-url"], [name="blogname"]')
          .clear()
          .type(invalidUrl);

        cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
          .contains(/continue|next|proceed/i)
          .click();

        // Should show validation error
        cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
          .should('be.visible');
      }
    });

    // Test valid site URL
    cy.get('#site_title, [name="site_title"]').type(testSite.title);
    cy.get('#site_url, [name="site_url"], [data-testid="site-url"], [name="blogname"]')
      .clear()
      .type(testSite.path);

    cy.get('[data-testid="continue-btn"], .wu-button, button[type="submit"]')
      .contains(/continue|next|proceed/i)
      .click();

    // Should advance to payment/completion step
    cy.url().should('not.contain', 'step=site')
      .and('not.contain', 'step=domain');
  });

  // Cleanup: Remove test data after tests (if needed)
  after(() => {
    cy.log("Cleanup: Test data should be cleaned up by WordPress/plugin automatically");
    // Note: In a real scenario, you might want to clean up test users/sites
    // This could be done via WP-CLI commands or API calls
  });
});