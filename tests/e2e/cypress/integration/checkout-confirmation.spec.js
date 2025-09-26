/**
 * E2E tests for checkout confirmation and post-registration flow
 *
 * This test suite covers the confirmation page, email verification,
 * and post-registration user experience.
 */

describe("Checkout Confirmation & Post-Registration", () => {
  const testCustomer = {
    username: `confirmuser_${Date.now()}`,
    email: `confirmuser_${Date.now()}@example.com`,
    password: 'ConfirmPass123!',
    firstName: 'Sarah',
    lastName: 'Wilson'
  };

  const testSite = {
    title: 'Confirmation Test Site',
    path: `confirmsite_${Date.now()}`
  };

  describe("Successful Registration Confirmation", () => {
    before(() => {
      // Complete a full registration to test confirmation
      cy.visitCheckoutForm('registration');
      cy.selectPricingPlan(0);
      cy.fillAccountDetails(testCustomer);
      cy.proceedToNextStep();
      cy.fillSiteDetails(testSite);
      cy.proceedToNextStep();

      // Handle payment/completion
      cy.get('body').then($body => {
        if ($body.find('[name*="billing"]').length > 0) {
          cy.fillBillingAddress();
          cy.selectPaymentGateway('manual');
        }
      });

      cy.completeCheckout();
    });

    it("Should display confirmation page with correct information", () => {
      // Verify we're on confirmation page
      cy.url({ timeout: 30000 }).should('satisfy', url =>
        url.includes('/confirmation') ||
        url.includes('/thank') ||
        url.includes('/success') ||
        url.includes('/complete')
      );

      // Verify page title/heading
      cy.get('h1, h2, .wu-title, [data-testid="page-title"]')
        .should('be.visible')
        .and('contain.text', /success|complete|welcome|thank|congratulations/i);

      // Verify success message
      cy.get('[data-testid="success-message"], .wu-success, .notice-success, [class*="success"]')
        .should('be.visible')
        .and('contain.text', /success|complete|registered|created/i);
    });

    it("Should display customer information correctly", () => {
      // Check customer email
      cy.get('[data-testid="customer-email"], .customer-email, .user-email')
        .should('contain.text', testCustomer.email);

      // Check username if displayed
      cy.get('body').then($body => {
        if ($body.find('[data-testid="customer-username"], .customer-username').length > 0) {
          cy.get('[data-testid="customer-username"], .customer-username')
            .should('contain.text', testCustomer.username);
        }
      });

      // Check full name if displayed
      cy.get('body').then($body => {
        if ($body.find('[data-testid="customer-name"], .customer-name').length > 0) {
          cy.get('[data-testid="customer-name"], .customer-name')
            .should('contain.text', testCustomer.firstName)
            .or('contain.text', testCustomer.lastName);
        }
      });
    });

    it("Should display site information correctly", () => {
      // Check site title
      cy.get('[data-testid="site-title"], .site-title, .site-name')
        .should('contain.text', testSite.title);

      // Check site URL
      cy.get('[data-testid="site-url"], .site-url, .site-address')
        .should('contain.text', testSite.path);

      // Check site status
      cy.get('[data-testid="site-status"], .site-status')
        .should('contain.text', /active|ready|live|created/i);
    });

    it("Should provide navigation options", () => {
      // Check for dashboard link
      cy.get('body').then($body => {
        if ($body.find('[data-testid="dashboard-link"], .dashboard-link, a:contains("Dashboard")').length > 0) {
          cy.get('[data-testid="dashboard-link"], .dashboard-link, a:contains("Dashboard")')
            .should('be.visible')
            .and('have.attr', 'href')
            .and('contain', '/wp-admin');
        }
      });

      // Check for site visit link
      cy.get('body').then($body => {
        if ($body.find('[data-testid="visit-site"], .visit-site, a:contains("Visit")').length > 0) {
          cy.get('[data-testid="visit-site"], .visit-site, a:contains("Visit")')
            .should('be.visible')
            .and('have.attr', 'href');
        }
      });

      // Check for login link
      cy.get('body').then($body => {
        if ($body.find('[data-testid="login-link"], .login-link, a:contains("Login")').length > 0) {
          cy.get('[data-testid="login-link"], .login-link, a:contains("Login")')
            .should('be.visible')
            .and('have.attr', 'href')
            .and('contain', 'login');
        }
      });
    });
  });

  describe("Email Verification Process", () => {
    it("Should display email verification notice if required", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="email-verification"], .email-verification, :contains("verify")').length > 0) {
          cy.get('[data-testid="email-verification"], .email-verification')
            .should('be.visible')
            .and('contain.text', /verify.*email|check.*email|activation/i);

          // Should show email address
          cy.get('[data-testid="verification-email"], .verification-email')
            .should('contain.text', testCustomer.email);

          // Should have resend option
          cy.get('body').then($resendBody => {
            if ($resendBody.find('[data-testid="resend-verification"], .resend-verification').length > 0) {
              cy.get('[data-testid="resend-verification"], .resend-verification')
                .should('be.visible')
                .and('contain.text', /resend|send.*again/i);
            }
          });
        }
      });
    });

    it("Should handle email verification link clicks", () => {
      // Test resend functionality if available
      cy.get('body').then($body => {
        if ($body.find('[data-testid="resend-verification"], .resend-verification').length > 0) {
          cy.get('[data-testid="resend-verification"], .resend-verification').click();

          // Should show confirmation message
          cy.get('[data-testid="resend-success"], .resend-success, [class*="success"]')
            .should('be.visible')
            .and('contain.text', /sent|resent|check.*email/i);
        }
      });
    });
  });

  describe("Site Access and Functionality", () => {
    it("Should allow access to site dashboard", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="dashboard-link"], .dashboard-link').length > 0) {
          cy.get('[data-testid="dashboard-link"], .dashboard-link').then($link => {
            const href = $link.attr('href');
            if (href) {
              // Visit the dashboard link
              cy.visit(href);

              // Should be on a dashboard page
              cy.url().should('contain', '/wp-admin');

              // Should show dashboard elements
              cy.get('#wpadminbar, .wp-admin, #adminmenu').should('exist');
            }
          });
        } else {
          cy.log('No dashboard link found on confirmation page');
        }
      });
    });

    it("Should allow access to frontend site", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="visit-site"], .visit-site').length > 0) {
          cy.get('[data-testid="visit-site"], .visit-site').then($link => {
            const href = $link.attr('href');
            if (href) {
              // Visit the site
              cy.visit(href);

              // Should show site title somewhere
              cy.get('title, h1, .site-title, .site-name')
                .should('contain.text', testSite.title);
            }
          });
        } else {
          // Try to construct site URL manually
          cy.visit(`//${testSite.path}.localhost:8889`);
          cy.get('title, h1, .site-title').should('contain.text', testSite.title);
        }
      });
    });
  });

  describe("Payment Confirmation", () => {
    it("Should display payment information for paid plans", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="payment-info"], .payment-info, .order-summary').length > 0) {
          // Check payment status
          cy.get('[data-testid="payment-status"], .payment-status')
            .should('contain.text', /paid|complete|pending|manual/i);

          // Check payment method
          cy.get('[data-testid="payment-method"], .payment-method')
            .should('be.visible');

          // Check amount if displayed
          cy.get('body').then($amountBody => {
            if ($amountBody.find('[data-testid="payment-amount"], .payment-amount').length > 0) {
              cy.get('[data-testid="payment-amount"], .payment-amount')
                .should('match', /\$[\d.,]+/);
            }
          });
        }
      });
    });

    it("Should show next payment date for recurring plans", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="next-payment"], .next-payment, :contains("next payment")').length > 0) {
          cy.get('[data-testid="next-payment"], .next-payment')
            .should('be.visible')
            .and('contain.text', /next.*payment|renewal/i);
        }
      });
    });
  });

  describe("Plan and Limitation Information", () => {
    it("Should display selected plan details", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="plan-info"], .plan-info, .membership-info').length > 0) {
          cy.get('[data-testid="plan-info"], .plan-info, .membership-info')
            .should('be.visible');

          // Check plan name
          cy.get('[data-testid="plan-name"], .plan-name')
            .should('be.visible');

          // Check limitations if displayed
          cy.get('body').then($limitBody => {
            if ($limitBody.find('[data-testid="plan-limits"], .plan-limits').length > 0) {
              cy.get('[data-testid="plan-limits"], .plan-limits')
                .should('be.visible');
            }
          });
        }
      });
    });
  });

  describe("Next Steps and Onboarding", () => {
    it("Should provide getting started information", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="getting-started"], .getting-started, :contains("getting started")').length > 0) {
          cy.get('[data-testid="getting-started"], .getting-started')
            .should('be.visible')
            .and('contain.text', /getting.*started|next.*steps|what.*next/i);
        }
      });
    });

    it("Should show support or help information", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="support-info"], .support-info, :contains("support")').length > 0) {
          cy.get('[data-testid="support-info"], .support-info')
            .should('be.visible');

          // Check for support links
          cy.get('[data-testid="support-link"], .support-link, a:contains("support")')
            .should('have.attr', 'href');
        }
      });
    });
  });

  describe("Error Handling on Confirmation Page", () => {
    it("Should handle missing registration data gracefully", () => {
      // Visit confirmation page directly without registration
      cy.visit('/confirmation');

      // Should either redirect or show appropriate error
      cy.url().then(url => {
        if (url.includes('confirmation')) {
          // If we stay on confirmation page, should show error or empty state
          cy.get('[data-testid="error"], .wu-error, .error, [class*="error"]')
            .should('be.visible')
            .or(() => {
              cy.get('[data-testid="no-data"], .no-data, :contains("no registration")')
                .should('be.visible');
            });
        } else {
          // Should redirect to appropriate page (registration, login, etc.)
          cy.url().should('satisfy', redirectUrl =>
            redirectUrl.includes('/checkout') ||
            redirectUrl.includes('/login') ||
            redirectUrl.includes('/register')
          );
        }
      });
    });
  });

  describe("Social Sharing and Notifications", () => {
    it("Should provide social sharing options if available", () => {
      cy.get('body').then($body => {
        if ($body.find('[data-testid="social-share"], .social-share').length > 0) {
          cy.get('[data-testid="social-share"], .social-share')
            .should('be.visible');

          // Check for common social platforms
          const socialPlatforms = ['facebook', 'twitter', 'linkedin'];
          socialPlatforms.forEach(platform => {
            cy.get(`[data-testid="${platform}-share"], .${platform}-share`).then($social => {
              if ($social.length > 0) {
                cy.wrap($social).should('be.visible');
              }
            });
          });
        }
      });
    });
  });

  describe("Accessibility and SEO", () => {
    it("Should have proper accessibility attributes", () => {
      // Check for proper heading hierarchy
      cy.get('h1').should('exist').and('have.length', 1);

      // Check for proper form labels if any forms exist
      cy.get('input').each($input => {
        const id = $input.attr('id');
        if (id) {
          cy.get(`label[for="${id}"]`).should('exist');
        }
      });

      // Check for alt text on images
      cy.get('img').each($img => {
        cy.wrap($img).should('have.attr', 'alt');
      });
    });

    it("Should have proper meta tags and page title", () => {
      cy.title().should('contain.text', /success|complete|confirmation/i);

      // Check for meta description
      cy.get('meta[name="description"]').should('exist');
    });
  });
});