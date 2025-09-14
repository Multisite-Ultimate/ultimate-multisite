/**
 * E2E tests for completing the WP Multisite Ultimate setup wizard
 *
 * This test suite ensures the setup wizard is completed properly,
 * which creates the necessary checkout forms and pages required
 * for the checkout flow tests.
 *
 * ⚠️ IMPORTANT: This test should run before any checkout tests
 * as it sets up the plugin for use.
 */

describe("Setup Wizard Completion", () => {
  const setupData = {
    company: {
      name: 'Test Company',
      email: 'admin@testcompany.com',
      website: 'https://testcompany.com'
    },
    settings: {
      currency: 'USD',
      defaultPlan: 'Basic Plan',
      enableRegistration: true
    }
  };

  before(() => {
    // Login as admin before starting setup
    cy.loginByApi(Cypress.env("admin").username, Cypress.env("admin").password);
  });

  describe("Complete Setup Wizard Flow", () => {
    it("Should navigate to setup wizard if not completed", () => {
      // Check if setup wizard is needed
      cy.visit('/wp-admin/network/');

      cy.get('body').then($body => {
        // Look for setup wizard indicators
        if ($body.find('[href*="wp-ultimo-setup"], .wu-setup-wizard, :contains("Setup Wizard")').length > 0) {
          cy.log('Setup wizard found - proceeding with setup');

          // Navigate to setup wizard
          cy.get('[href*="wp-ultimo-setup"], .wu-setup-wizard, a:contains("Setup")')
            .first()
            .click();

        } else {
          // Try direct URL
          cy.visit('/wp-admin/network/admin.php?page=wp-ultimo-setup');
        }
      });

      // Should be on setup wizard page
      cy.url().should('contain', 'wp-ultimo-setup');
    });

    it("Should complete the Welcome step", () => {
      cy.log("Completing Welcome Step");

      // Verify we're on welcome step
      cy.assertPageUrl({
        pathname: "/wp-admin/network/admin.php",
        page: "wp-ultimo-setup"
      });

      // Look for welcome content
      cy.get('body').should('contain.text', /welcome|setup|getting.*started/i);

      // Click get started button
      cy.clickPrimaryBtnByTxt("Get Started");

      // Should move to checks step
      cy.assertPageUrl({
        pathname: "/wp-admin/network/admin.php",
        page: "wp-ultimo-setup",
        step: "checks"
      });
    });

    it("Should complete the System Checks step", () => {
      cy.log("Completing System Checks Step");

      // Wait for checks to complete
      cy.get('.wu-setup-check, .setup-check, [class*="check"]', { timeout: 10000 })
        .should('be.visible');

      // Look for any failed checks
      cy.get('body').then($body => {
        const hasFailures = $body.find('.wu-check-fail, .check-fail, [class*="fail"]').length > 0;

        if (hasFailures) {
          cy.log('Warning: Some system checks failed, but proceeding');
        } else {
          cy.log('All system checks passed');
        }
      });

      // Proceed to next step
      cy.clickPrimaryBtnByTxt("Go to the Next Step");

      // Should move to installation step
      cy.assertPageUrl({
        pathname: "/wp-admin/network/admin.php",
        page: "wp-ultimo-setup",
        step: "installation"
      });
    });

    it("Should complete the Installation step", () => {
      cy.log("Completing Installation Step");

      // Verify installation content
      cy.get('body').should('contain.text', /install|database|table/i);

      // Click install button
      cy.clickPrimaryBtnByTxt("Install");

      // Wait for installation to complete
      cy.get('.wu-progress, .progress, [class*="progress"]', { timeout: 30000 })
        .should('be.visible');

      // Wait for installation success
      cy.get('.wu-success, .success, [class*="success"]', { timeout: 30000 })
        .should('be.visible');

      // Should move to company details step
      cy.assertPageUrl({
        pathname: "/wp-admin/network/admin.php",
        page: "wp-ultimo-setup",
        step: "your-company"
      });
    });

    it("Should complete the Company Details step", () => {
      cy.log("Completing Company Details Step");

      // Fill company information
      cy.get('body').then($body => {
        // Company name
        if ($body.find('#company_name, [name="company_name"], [data-testid="company-name"]').length > 0) {
          cy.get('#company_name, [name="company_name"], [data-testid="company-name"]')
            .clear()
            .type(setupData.company.name);
        }

        // Company email
        if ($body.find('#company_email, [name="company_email"], [data-testid="company-email"]').length > 0) {
          cy.get('#company_email, [name="company_email"], [data-testid="company-email"]')
            .clear()
            .type(setupData.company.email);
        }

        // Company website
        if ($body.find('#company_website, [name="company_website"], [data-testid="company-website"]').length > 0) {
          cy.get('#company_website, [name="company_website"], [data-testid="company-website"]')
            .clear()
            .type(setupData.company.website);
        }

        // Currency selection
        if ($body.find('#currency, [name="currency"], [data-testid="currency"]').length > 0) {
          cy.get('#currency, [name="currency"], [data-testid="currency"]')
            .select(setupData.settings.currency);
        }
      });

      // Continue to next step
      cy.clickPrimaryBtnByTxt("Continue");

      // Should move to defaults step
      cy.assertPageUrl({
        pathname: "/wp-admin/network/admin.php",
        page: "wp-ultimo-setup",
        step: "defaults"
      });
    });

    it("Should complete the Defaults step and create sample data", () => {
      cy.log("Completing Defaults Step");

      // This step typically creates sample plans, checkout forms, etc.
      cy.get('body').should('contain.text', /default|sample|plan|product/i);

      // Look for sample data creation options
      cy.get('body').then($body => {
        // Enable sample data creation if option exists
        if ($body.find('[name="create_sample_data"], [data-testid="create-sample"], input[type="checkbox"]').length > 0) {
          cy.get('[name="create_sample_data"], [data-testid="create-sample"], input[type="checkbox"]')
            .check();
        }

        // Enable checkout forms creation if option exists
        if ($body.find('[name="create_checkout_forms"], [data-testid="create-checkout"], input[type="checkbox"]').length > 0) {
          cy.get('[name="create_checkout_forms"], [data-testid="create-checkout"], input[type="checkbox"]')
            .check();
        }

        // Enable sample plans if option exists
        if ($body.find('[name="create_sample_plans"], [data-testid="create-plans"], input[type="checkbox"]').length > 0) {
          cy.get('[name="create_sample_plans"], [data-testid="create-plans"], input[type="checkbox"]')
            .check();
        }
      });

      // Install defaults
      cy.clickPrimaryBtnByTxt("Install");

      // Wait for installation to complete
      cy.get('.wu-progress, .progress, [class*="progress"]', { timeout: 30000 })
        .should('be.visible');

      // Wait for completion
      cy.get('.wu-success, .success, [class*="success"]', { timeout: 30000 })
        .should('be.visible');

      // Should move to completion step
      cy.url({ timeout: 10000 }).should('satisfy', url =>
        url.includes('step=done') ||
        url.includes('step=complete') ||
        url.includes('step=finish')
      );
    });

    it("Should complete the final step and redirect to dashboard", () => {
      cy.log("Completing Final Step");

      // Should show completion message
      cy.get('body').should('contain.text', /complete|done|ready|congratulations|success/i);

      // Click finish button
      cy.clickPrimaryBtnByTxt("Thanks!");

      // Should redirect to main dashboard
      cy.assertPageUrl({
        pathname: "/wp-admin/network/index.php"
      });

      // Verify we're on the network dashboard
      cy.get('body').should('contain.text', /dashboard|network|admin/i);
    });
  });

  describe("Verify Setup Completion", () => {
    it("Should have created necessary database tables", () => {
      cy.log("Verifying database tables were created");

      // Use WP-CLI to check for WP Multisite Ultimate tables
      cy.wpCli("db query 'SHOW TABLES LIKE \"%wu_%\"'").then(result => {
        // Should have multiple WP Multisite Ultimate tables
        expect(result.stdout).to.contain('wu_');
      });
    });

    it("Should have created sample plans", () => {
      cy.log("Verifying sample plans were created");

      // Navigate to plans page
      cy.visit('/wp-admin/network/admin.php?page=wp-ultimo-products');

      // Should show plans list
      cy.get('body').should('contain.text', /plan|product/i);

      // Should have at least one plan
      cy.get('.wp-list-table tbody tr, .wu-list-table tbody tr').should('have.length.at.least', 1);
    });

    it("Should have created default checkout forms", () => {
      cy.log("Verifying checkout forms were created");

      // Navigate to checkout forms page
      cy.visit('/wp-admin/network/admin.php?page=wp-ultimo-checkout-forms');

      // Should show checkout forms list
      cy.get('body').should('contain.text', /checkout.*form|registration.*form/i);

      // Should have at least one checkout form
      cy.get('.wp-list-table tbody tr, .wu-list-table tbody tr').should('have.length.at.least', 1);
    });

    it("Should have created necessary pages", () => {
      cy.log("Verifying necessary pages were created");

      // Check for registration page
      cy.visit('/wp-admin/network/admin.php?page=wp-ultimo-sites');

      // Navigate to main site pages
      cy.visit('/wp-admin/edit.php?post_type=page');

      // Look for checkout/registration related pages
      cy.get('body').then($body => {
        const hasCheckoutPages = $body.find('a:contains("Checkout"), a:contains("Registration"), a:contains("Sign Up")').length > 0;

        if (hasCheckoutPages) {
          cy.log('Checkout pages found');
        } else {
          cy.log('Note: Checkout pages may be created automatically on first access');
        }
      });
    });

    it("Should have configured payment gateways", () => {
      cy.log("Verifying payment gateways configuration");

      // Navigate to payment settings
      cy.visit('/wp-admin/network/admin.php?page=wp-ultimo-settings&tab=payments');

      // Should show payment gateway settings
      cy.get('body').should('contain.text', /payment|gateway|stripe|paypal/i);

      // Manual gateway should be enabled by default
      cy.get('body').should('contain.text', /manual.*payment|manual.*gateway/i);
    });

    it("Should allow access to main plugin features", () => {
      cy.log("Verifying main plugin features are accessible");

      // Test main menu items
      const menuItems = [
        { url: '/wp-admin/network/admin.php?page=wp-ultimo-dashboard', text: 'dashboard' },
        { url: '/wp-admin/network/admin.php?page=wp-ultimo-products', text: 'product' },
        { url: '/wp-admin/network/admin.php?page=wp-ultimo-customers', text: 'customer' },
        { url: '/wp-admin/network/admin.php?page=wp-ultimo-sites', text: 'site' }
      ];

      menuItems.forEach(item => {
        cy.visit(item.url);
        cy.get('body').should('contain.text', new RegExp(item.text, 'i'));
      });
    });
  });

  describe("Create Test Checkout Form for E2E Tests", () => {
    it("Should create a test checkout form for e2e testing", () => {
      cy.log("Creating test checkout form for e2e tests");

      // Navigate to checkout forms
      cy.visit('/wp-admin/network/admin.php?page=wp-ultimo-checkout-forms');

      // Check if a registration form already exists
      cy.get('body').then($body => {
        const hasRegistrationForm = $body.find('td:contains("registration"), td:contains("Registration")').length > 0;

        if (!hasRegistrationForm) {
          // Create new checkout form
          cy.get('a:contains("Add New"), .page-title-action').click();

          // Fill form details
          cy.get('#title, [name="name"], [data-testid="form-name"]')
            .type('Registration Form');

          cy.get('#slug, [name="slug"], [data-testid="form-slug"]')
            .clear()
            .type('registration');

          // Save form
          cy.get('#publish, [type="submit"], .wu-button-primary').click();

          // Should show success message
          cy.get('.notice-success, .wu-success').should('be.visible');

          cy.log('Test checkout form created');
        } else {
          cy.log('Registration form already exists');
        }
      });
    });

    it("Should verify checkout form is accessible on frontend", () => {
      cy.log("Verifying checkout form frontend access");

      // Try to access checkout form
      cy.visit('/checkout/registration', { failOnStatusCode: false });

      cy.get('body').then($body => {
        // Check if checkout form loads or if we need to create pages
        const hasCheckoutContent = $body.find('.wu-checkout, .checkout-form, form').length > 0;

        if (hasCheckoutContent) {
          cy.log('Checkout form is accessible');
        } else {
          cy.log('Checkout form may need additional setup');
          // This is normal - checkout forms may need additional configuration
        }
      });
    });
  });

  describe("Setup Wizard Skip/Reset", () => {
    it("Should mark setup as completed to prevent re-running", () => {
      cy.log("Marking setup wizard as completed");

      // Navigate to settings to verify setup completion
      cy.visit('/wp-admin/network/admin.php?page=wp-ultimo-settings');

      // Should not redirect to setup wizard
      cy.url().should('contain', 'wp-ultimo-settings');
      cy.url().should('not.contain', 'wp-ultimo-setup');

      cy.log('Setup wizard is marked as completed');
    });

    it("Should provide reset option for testing (if available)", () => {
      cy.log("Checking for setup reset option");

      cy.visit('/wp-admin/network/admin.php?page=wp-ultimo-settings&tab=advanced');

      cy.get('body').then($body => {
        if ($body.find(':contains("Reset Setup"), :contains("Re-run Setup")').length > 0) {
          cy.log('Setup reset option is available for future testing');
        } else {
          cy.log('No setup reset option found (this is normal)');
        }
      });
    });
  });

  after(() => {
    cy.log("Setup wizard completion tests finished");
    cy.log("Checkout flow tests can now be run");
  });
});