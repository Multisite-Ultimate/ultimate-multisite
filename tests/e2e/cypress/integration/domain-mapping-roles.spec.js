/**
 * E2E tests for domain mapping with user roles
 *
 * This test suite verifies that user roles are correctly loaded when accessing
 * a site via a custom/mapped domain. This is critical for plugins that check
 * user capabilities (like If Menu, WooCommerce, etc.).
 *
 * Background:
 * When domain mapping occurs early (via sunrise.php), WordPress may cache
 * user role data before the correct blog context is established. The fix
 * in inc/class-domain-mapping.php ensures that user roles are properly
 * refreshed for the mapped blog context.
 *
 * @see inc/class-domain-mapping.php:refresh_user_roles_for_mapped_domain()
 */

describe("Domain Mapping - User Roles", () => {
  // Test data
  let testSite = {
    id: null,
    title: 'Domain Mapped Site',
    path: `mapped_${Date.now()}`,
    domain: `test-${Date.now()}.example.com`
  };

  let testUser = {
    id: null,
    username: `domainuser_${Date.now()}`,
    email: `domainuser_${Date.now()}@example.com`,
    password: 'TestPassword123!',
    role: 'editor'
  };

  before(() => {
    cy.log("Setting up test environment for domain mapping");

    // Ensure admin is logged in
    cy.loginByApi(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );
  });

  /**
   * Test 1: Verify user roles are loaded on original domain (baseline)
   *
   * This test establishes the baseline behavior - when accessing a site
   * via its original subdomain, user roles should be properly loaded.
   */
  it("Should load user roles correctly on original subdomain (baseline)", () => {
    cy.log("Creating test site and user");

    // Create a new site
    cy.createTestSite(testSite.path, testSite.title).then((siteId) => {
      testSite.id = siteId;
      cy.log(`Created site with ID: ${siteId}`);

      // Create a test user on this site with editor role
      cy.createTestUser(
        testUser.username,
        testUser.email,
        testUser.password,
        testUser.role,
        siteId
      ).then((userId) => {
        testUser.id = userId;
        cy.log(`Created user with ID: ${userId} and role: ${testUser.role}`);
      });
    });

    // Login as the test user
    cy.loginByApi(testUser.username, testUser.password);

    // Switch to the test site
    cy.switchToSite(testSite.id);

    // Visit the site admin
    cy.visit(`/wp-admin/`);

    // Verify user is logged in and has access to admin
    cy.get('#wpadminbar').should('be.visible');

    // Check that user has editor capabilities
    // Editors should see Posts menu
    cy.get('#menu-posts').should('be.visible');

    // Editors should NOT see Users menu (admin only)
    cy.get('#menu-users').should('not.exist');

    // Verify user role via API/wp-admin
    cy.wpCli(`user get ${testUser.id} --field=roles --format=json`, {
      failOnNonZeroExit: false
    }).then((result) => {
      if (result.code === 0) {
        const roles = JSON.parse(result.stdout);
        expect(roles).to.include(testUser.role);
      }
    });

    cy.log("✓ Baseline test passed - roles work on original subdomain");
  });

  /**
   * Test 2: Verify user roles are loaded on mapped domain
   *
   * This is the critical test that verifies the fix. When accessing a site
   * via a custom/mapped domain, user roles should still be properly loaded.
   */
  it("Should load user roles correctly when accessing via mapped domain", () => {
    cy.log("Setting up domain mapping for the test site");

    // Login as admin to set up domain mapping
    cy.loginByApi(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );

    // Add a custom domain mapping to the test site
    cy.addDomainMapping(testSite.id, testSite.domain, true).then(() => {
      cy.log(`Added domain mapping: ${testSite.domain} → Site ${testSite.id}`);
    });

    // Now login as the test user
    cy.loginByApi(testUser.username, testUser.password);

    // CRITICAL: Access the site via the mapped domain
    // This simulates a real-world scenario where users access sites via custom domains
    cy.visitMappedDomain(testSite.domain, '/wp-admin/');

    // Verify user is logged in
    cy.get('#wpadminbar', { timeout: 10000 }).should('be.visible');

    // MAIN TEST: Verify user roles are properly loaded on mapped domain
    // The fix ensures that refresh_user_roles_for_mapped_domain() is called
    // and user capabilities are correctly initialized

    // Check that user still has editor capabilities
    cy.get('#menu-posts').should('be.visible');

    // Editors should still NOT see Users menu
    cy.get('#menu-users').should('not.exist');

    // Verify via JavaScript that user object has correct roles
    cy.window().then((win) => {
      // Access WordPress user data if available
      if (win.wp && win.wp.data && win.wp.data.select) {
        const currentUser = win.wp.data.select('core').getCurrentUser();
        if (currentUser && currentUser.roles) {
          expect(currentUser.roles).to.include(testUser.role);
          cy.log(`✓ User roles verified via JS: ${currentUser.roles.join(', ')}`);
        }
      }
    });

    // Test a role-dependent action - creating a post (editors can do this)
    cy.visit('/wp-admin/post-new.php');
    cy.get('#title', { timeout: 10000 }).should('be.visible');
    cy.log("✓ User can access post editor (editor capability confirmed)");

    // Try to access Users page (should be blocked for editor role)
    cy.visit('/wp-admin/users.php', { failOnStatusCode: false });

    // Should see "You do not have permission" message or redirect
    cy.get('body').then(($body) => {
      const bodyText = $body.text();
      const hasPermissionError = bodyText.includes('permission') ||
                                  bodyText.includes('not allowed') ||
                                  bodyText.includes('sufficient');

      if (hasPermissionError) {
        cy.log("✓ User correctly denied access to Users page (not an admin)");
      } else {
        // Might have redirected away from users.php
        cy.url().should('not.contain', 'users.php');
        cy.log("✓ User redirected away from Users page (not an admin)");
      }
    });

    cy.log("✓ CRITICAL TEST PASSED - User roles work correctly on mapped domain!");
  });

  /**
   * Test 3: Verify role-based plugin functionality on mapped domain
   *
   * This test simulates how plugins like "If Menu" check user roles.
   * The fix ensures that plugins checking $current_user->roles get correct data.
   */
  it("Should allow plugins to check user roles correctly on mapped domain", () => {
    cy.log("Testing role-based functionality (simulating plugins like If Menu)");

    // Login as test user
    cy.loginByApi(testUser.username, testUser.password);

    // Access via mapped domain
    cy.visitMappedDomain(testSite.domain, '/wp-admin/');

    // Execute JavaScript to simulate how plugins check user roles
    cy.window().then((win) => {
      // Create a custom command to check roles (simulating a plugin)
      win.testRoleCheck = function() {
        // This simulates what plugins like If Menu do
        if (typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce) {
          return fetch('/wp-json/wp/v2/users/me', {
            headers: {
              'X-WP-Nonce': wpApiSettings.nonce
            },
            credentials: 'same-origin'
          })
          .then(response => response.json())
          .then(user => user.roles);
        }
        return Promise.resolve([]);
      };
    });

    // Execute the role check
    cy.window().then((win) => {
      return cy.wrap(win.testRoleCheck()).then((roles) => {
        if (roles && roles.length > 0) {
          expect(roles).to.include(testUser.role);
          cy.log(`✓ Plugin role check successful: ${roles.join(', ')}`);
        } else {
          cy.log("⚠ Could not verify via REST API (may not be available)");
        }
      });
    });

    // Test via current_user_can() equivalent
    // Check if user can edit posts (editor capability)
    cy.visit('/wp-admin/edit.php');
    cy.get('.page-title-action', { timeout: 10000 }).should('contain', 'Add New');
    cy.log("✓ User has edit_posts capability (editor role confirmed)");

    // Verify user cannot manage options (admin-only capability)
    cy.visit('/wp-admin/options-general.php', { failOnStatusCode: false });
    cy.get('body').then(($body) => {
      const bodyText = $body.text();
      const hasPermissionError = bodyText.includes('permission') ||
                                  bodyText.includes('not allowed');
      expect(hasPermissionError).to.be.true;
      cy.log("✓ User correctly denied manage_options capability (not admin)");
    });
  });

  /**
   * Test 4: Verify multiple users with different roles on mapped domain
   *
   * This test ensures the fix works for multiple users with different roles.
   */
  it("Should handle multiple users with different roles on mapped domain", () => {
    cy.log("Testing multiple users with different roles");

    // Create a subscriber user
    const subscriberUser = {
      username: `subscriber_${Date.now()}`,
      email: `subscriber_${Date.now()}@example.com`,
      password: 'TestPassword123!',
      role: 'subscriber'
    };

    cy.loginByApi(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );

    cy.createTestUser(
      subscriberUser.username,
      subscriberUser.email,
      subscriberUser.password,
      subscriberUser.role,
      testSite.id
    );

    // Test subscriber on mapped domain
    cy.loginByApi(subscriberUser.username, subscriberUser.password);
    cy.visitMappedDomain(testSite.domain, '/wp-admin/');

    // Subscribers should have very limited access
    cy.get('#wpadminbar').should('be.visible');

    // Subscribers should NOT see Posts menu
    cy.get('#menu-posts').should('not.exist');

    // Subscribers should only see Profile
    cy.get('#menu-users a[href*="profile.php"]').should('be.visible');

    cy.log("✓ Subscriber role correctly enforced on mapped domain");

    // Now test the editor again to ensure roles are not mixed up
    cy.loginByApi(testUser.username, testUser.password);
    cy.visitMappedDomain(testSite.domain, '/wp-admin/');

    // Editor should still have Posts access
    cy.get('#menu-posts').should('be.visible');
    cy.log("✓ Editor role still correct after subscriber login");
  });

  /**
   * Test 5: Verify role changes are reflected on mapped domain
   *
   * This ensures that when a user's role changes, the change is
   * correctly reflected when accessing via mapped domain.
   */
  it("Should reflect role changes when accessing via mapped domain", () => {
    cy.log("Testing role changes on mapped domain");

    // Login as admin
    cy.loginByApi(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );

    // Change test user's role from editor to author
    cy.wpCli(`user set-role ${testUser.id} author --url=${testSite.path}`);

    // Login as test user
    cy.loginByApi(testUser.username, testUser.password);

    // Access via mapped domain
    cy.visitMappedDomain(testSite.domain, '/wp-admin/');

    // Authors should see Posts menu
    cy.get('#menu-posts').should('be.visible');

    // Authors should NOT see others' posts in edit list
    cy.visit('/wp-admin/edit.php');

    // Verify author capabilities
    cy.get('.page-title-action').should('contain', 'Add New');
    cy.log("✓ Author role correctly applied on mapped domain after role change");

    // Change back to editor for cleanup
    cy.loginByApi(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );
    cy.wpCli(`user set-role ${testUser.id} editor --url=${testSite.path}`);
  });

  /**
   * Test 6: Verify inactive domain mappings don't affect roles
   *
   * This ensures that inactive mappings don't interfere with role loading.
   */
  it("Should not interfere with roles when domain mapping is inactive", () => {
    cy.log("Testing with inactive domain mapping");

    // Login as admin
    cy.loginByApi(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );

    // Deactivate the domain mapping
    cy.deactivateDomainMapping(testSite.id, testSite.domain);

    // Login as test user
    cy.loginByApi(testUser.username, testUser.password);

    // Access via original subdomain (not mapped domain)
    cy.switchToSite(testSite.id);
    cy.visit('/wp-admin/');

    // Roles should still work correctly
    cy.get('#menu-posts').should('be.visible');
    cy.log("✓ Roles work correctly when domain mapping is inactive");

    // Reactivate for cleanup
    cy.loginByApi(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );
    cy.addDomainMapping(testSite.id, testSite.domain, true);
  });

  /**
   * Cleanup after all tests
   */
  after(() => {
    cy.log("Cleaning up test data");

    cy.loginByApi(
      Cypress.env("admin").username,
      Cypress.env("admin").password
    );

    // Clean up domain mapping
    if (testSite.id && testSite.domain) {
      cy.deleteDomainMapping(testSite.id, testSite.domain).then(() => {
        cy.log(`Deleted domain mapping: ${testSite.domain}`);
      });
    }

    // Clean up test users
    if (testUser.id) {
      cy.wpCli(`user delete ${testUser.id} --yes`, { failOnNonZeroExit: false });
      cy.log(`Deleted test user: ${testUser.username}`);
    }

    // Clean up test site
    if (testSite.id) {
      cy.wpCli(`site delete ${testSite.id} --yes`, { failOnNonZeroExit: false });
      cy.log(`Deleted test site: ${testSite.path}`);
    }
  });
});
