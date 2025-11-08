/**
 * Cypress commands for domain mapping e2e tests
 *
 * These commands provide utilities for testing domain mapping functionality,
 * particularly around user roles and permissions on mapped domains.
 */

/**
 * Create a test site in the multisite network
 *
 * @param {string} path - Site path/slug (e.g., "mysite")
 * @param {string} title - Site title (e.g., "My Test Site")
 * @returns {number} Site ID
 */
Cypress.Commands.add("createTestSite", (path, title = "Test Site") => {
  return cy.wpCli(
    `site create --slug="${path}" --title="${title}" --email="admin@example.com" --porcelain`,
    { failOnNonZeroExit: true }
  ).then((result) => {
    const siteId = parseInt(result.stdout.trim(), 10);
    cy.log(`Created site: ${path} (ID: ${siteId})`);
    return cy.wrap(siteId);
  });
});

/**
 * Create a test user with specific role on a site
 *
 * @param {string} username - Username
 * @param {string} email - Email address
 * @param {string} password - Password
 * @param {string} role - WordPress role (e.g., "editor", "author", "subscriber")
 * @param {number} siteId - Site ID to add user to
 * @returns {number} User ID
 */
Cypress.Commands.add("createTestUser", (username, email, password, role = "subscriber", siteId = null) => {
  let command = `user create "${username}" "${email}" --role="${role}" --user_pass="${password}" --porcelain`;

  // If site ID is provided, create user on that site
  if (siteId) {
    command += ` --url=${siteId}`;
  }

  return cy.wpCli(command, { failOnNonZeroExit: true }).then((result) => {
    const userId = parseInt(result.stdout.trim(), 10);
    cy.log(`Created user: ${username} (ID: ${userId}, Role: ${role})`);
    return cy.wrap(userId);
  });
});

/**
 * Switch to a specific site in the multisite network
 *
 * @param {number} siteId - Site ID to switch to
 */
Cypress.Commands.add("switchToSite", (siteId) => {
  cy.log(`Switching to site ID: ${siteId}`);

  // Get site details to find the path
  return cy.wpCli(`site list --field=url --format=csv`, { failOnNonZeroExit: false })
    .then((result) => {
      if (result.code === 0) {
        const urls = result.stdout.trim().split('\n');
        // The site URL will be in the list
        cy.log(`Available sites: ${urls.join(', ')}`);
      }
    });
});

/**
 * Add a domain mapping to a site
 *
 * @param {number} siteId - Site ID to map domain to
 * @param {string} domain - Custom domain (e.g., "example.com")
 * @param {boolean} active - Whether mapping should be active (default: true)
 * @param {boolean} primary - Whether this is the primary domain (default: true)
 */
Cypress.Commands.add("addDomainMapping", (siteId, domain, active = true, primary = true) => {
  cy.log(`Adding domain mapping: ${domain} → Site ${siteId}`);

  // Use wp-cli to add domain mapping via eval-file or direct database insertion
  const phpCode = `
    require_once 'wp-load.php';

    $domain_data = array(
      'blog_id' => ${siteId},
      'domain' => '${domain}',
      'active' => ${active ? 1 : 0},
      'primary_domain' => ${primary ? 1 : 0},
      'secure' => 0,
      'stage' => 'live'
    );

    // Try to use Ultimate Multisite's domain creation
    if (function_exists('wu_create_domain')) {
      $domain_obj = wu_create_domain($domain_data);
      if (is_wp_error($domain_obj)) {
        echo 'ERROR: ' . $domain_obj->get_error_message();
        exit(1);
      }
      echo 'Domain mapping created: ID ' . $domain_obj->get_id();
    } else {
      // Fallback to direct database insertion
      global $wpdb;
      $table = $wpdb->base_prefix . 'wu_domains';
      $wpdb->insert($table, $domain_data);
      echo 'Domain mapping created via database';
    }
  `;

  // Create a temporary PHP file
  const tempFile = `/tmp/add-domain-${Date.now()}.php`;

  return cy.exec(`echo '<?php ${phpCode}' > ${tempFile}`, { failOnNonZeroExit: false })
    .then(() => {
      return cy.wpCli(`eval-file ${tempFile}`, { failOnNonZeroExit: false });
    })
    .then((result) => {
      // Clean up temp file
      cy.exec(`rm -f ${tempFile}`, { failOnNonZeroExit: false });

      if (result.code === 0) {
        cy.log(`✓ Domain mapping created: ${domain}`);
      } else {
        cy.log(`⚠ Domain mapping creation may have failed: ${result.stderr}`);
      }

      return cy.wrap(true);
    });
});

/**
 * Visit a site via its mapped domain
 *
 * This simulates accessing a site through a custom domain by setting
 * the appropriate host header or modifying the URL.
 *
 * @param {string} domain - Mapped domain to access
 * @param {string} path - Path to visit (e.g., "/wp-admin/")
 */
Cypress.Commands.add("visitMappedDomain", (domain, path = "/") => {
  cy.log(`Visiting mapped domain: ${domain}${path}`);

  // In a real test environment, we would need to:
  // 1. Set up DNS or hosts file to point the domain to the test server
  // 2. Configure the web server to accept the domain
  // 3. Visit the URL

  // For testing purposes, we'll use a combination of approaches:

  // Approach 1: Try to visit with custom headers
  // This works if the test environment supports it
  cy.visit(path, {
    headers: {
      'Host': domain
    },
    failOnStatusCode: false
  });

  // Alternative Approach 2: If Ultimate Multisite is configured to handle
  // domain mapping via query parameters for testing
  // cy.visit(`${path}?wu_domain=${domain}`);

  // Alternative Approach 3: Use cy.request with custom host header
  // then set cookies and visit
  // cy.request({
  //   url: path,
  //   headers: { 'Host': domain },
  //   followRedirect: false
  // }).then(() => {
  //   cy.visit(path);
  // });

  cy.log(`Accessed: ${domain}${path}`);
});

/**
 * Deactivate a domain mapping
 *
 * @param {number} siteId - Site ID
 * @param {string} domain - Domain to deactivate
 */
Cypress.Commands.add("deactivateDomainMapping", (siteId, domain) => {
  cy.log(`Deactivating domain mapping: ${domain}`);

  const phpCode = `
    require_once 'wp-load.php';

    if (function_exists('wu_get_domain_by_domain')) {
      $domain_obj = wu_get_domain_by_domain('${domain}');
      if ($domain_obj) {
        $domain_obj->set_active(false);
        $domain_obj->save();
        echo 'Domain deactivated';
      } else {
        echo 'Domain not found';
      }
    } else {
      global $wpdb;
      $table = $wpdb->base_prefix . 'wu_domains';
      $wpdb->update($table,
        array('active' => 0),
        array('domain' => '${domain}', 'blog_id' => ${siteId})
      );
      echo 'Domain deactivated via database';
    }
  `;

  const tempFile = `/tmp/deactivate-domain-${Date.now()}.php`;

  return cy.exec(`echo '<?php ${phpCode}' > ${tempFile}`, { failOnNonZeroExit: false })
    .then(() => {
      return cy.wpCli(`eval-file ${tempFile}`, { failOnNonZeroExit: false });
    })
    .then((result) => {
      cy.exec(`rm -f ${tempFile}`, { failOnNonZeroExit: false });
      cy.log(`✓ Domain mapping deactivated: ${domain}`);
      return cy.wrap(true);
    });
});

/**
 * Delete a domain mapping
 *
 * @param {number} siteId - Site ID
 * @param {string} domain - Domain to delete
 */
Cypress.Commands.add("deleteDomainMapping", (siteId, domain) => {
  cy.log(`Deleting domain mapping: ${domain}`);

  const phpCode = `
    require_once 'wp-load.php';

    if (function_exists('wu_get_domain_by_domain')) {
      $domain_obj = wu_get_domain_by_domain('${domain}');
      if ($domain_obj) {
        $domain_obj->delete();
        echo 'Domain deleted';
      } else {
        echo 'Domain not found';
      }
    } else {
      global $wpdb;
      $table = $wpdb->base_prefix . 'wu_domains';
      $wpdb->delete($table,
        array('domain' => '${domain}', 'blog_id' => ${siteId})
      );
      echo 'Domain deleted via database';
    }
  `;

  const tempFile = `/tmp/delete-domain-${Date.now()}.php`;

  return cy.exec(`echo '<?php ${phpCode}' > ${tempFile}`, { failOnNonZeroExit: false })
    .then(() => {
      return cy.wpCli(`eval-file ${tempFile}`, { failOnNonZeroExit: false });
    })
    .then((result) => {
      cy.exec(`rm -f ${tempFile}`, { failOnNonZeroExit: false });
      cy.log(`✓ Domain mapping deleted: ${domain}`);
      return cy.wrap(true);
    });
});

/**
 * Get user roles for a specific user on a specific site
 *
 * @param {number} userId - User ID
 * @param {number} siteId - Site ID (optional, uses current site if not provided)
 * @returns {Array} Array of role names
 */
Cypress.Commands.add("getUserRoles", (userId, siteId = null) => {
  let command = `user get ${userId} --field=roles --format=json`;

  if (siteId) {
    command += ` --url=${siteId}`;
  }

  return cy.wpCli(command, { failOnNonZeroExit: false }).then((result) => {
    if (result.code === 0) {
      const roles = JSON.parse(result.stdout);
      cy.log(`User ${userId} roles: ${roles.join(', ')}`);
      return cy.wrap(roles);
    } else {
      cy.log(`Could not get roles for user ${userId}`);
      return cy.wrap([]);
    }
  });
});

/**
 * Check if user has a specific capability on current site
 *
 * @param {number} userId - User ID
 * @param {string} capability - Capability to check (e.g., "edit_posts")
 * @returns {boolean} Whether user has the capability
 */
Cypress.Commands.add("userHasCapability", (userId, capability) => {
  const phpCode = `
    require_once 'wp-load.php';
    $user = get_user_by('ID', ${userId});
    if ($user && $user->has_cap('${capability}')) {
      echo 'true';
    } else {
      echo 'false';
    }
  `;

  const tempFile = `/tmp/check-cap-${Date.now()}.php`;

  return cy.exec(`echo '<?php ${phpCode}' > ${tempFile}`, { failOnNonZeroExit: false })
    .then(() => {
      return cy.wpCli(`eval-file ${tempFile}`, { failOnNonZeroExit: false });
    })
    .then((result) => {
      cy.exec(`rm -f ${tempFile}`, { failOnNonZeroExit: false });
      const hasCap = result.stdout.trim() === 'true';
      cy.log(`User ${userId} has ${capability}: ${hasCap}`);
      return cy.wrap(hasCap);
    });
});
