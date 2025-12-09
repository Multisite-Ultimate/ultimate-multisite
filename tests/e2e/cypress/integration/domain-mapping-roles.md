# Domain Mapping Roles E2E Test

## Overview

This test suite verifies the fix for user role loading on domain-mapped sites (commit: `adf85bb`).

## The Problem Being Tested

When a WordPress Multisite site is accessed via a custom/mapped domain, user roles may not be properly loaded if domain mapping occurs early (via sunrise.php). This causes issues with:

- Plugins that check user roles (e.g., If Menu, WooCommerce)
- User capability checks (`current_user_can()`)
- Role-based UI elements (admin menus, etc.)

## The Fix

The fix adds a `refresh_user_roles_for_mapped_domain()` method to the `Domain_Mapping` class that:

1. Hooks into `set_current_user` action
2. Detects when a domain-mapped site is being accessed
3. Calls `$user->for_site($current_blog_id)` to refresh roles for the correct blog context

## Test Coverage

The test suite includes 6 comprehensive tests:

### 1. Baseline Test - Original Subdomain
Verifies that roles work correctly on the original subdomain (establishes baseline).

### 2. Critical Test - Mapped Domain
**This is the main test for the fix.** Verifies that user roles are correctly loaded when accessing a site via a mapped domain.

### 3. Plugin Compatibility Test
Simulates how plugins check user roles and ensures the fix works for plugin integrations.

### 4. Multiple Users Test
Verifies the fix works correctly for multiple users with different roles on the same mapped domain.

### 5. Role Change Test
Ensures that role changes are reflected when accessing via mapped domain.

### 6. Inactive Mapping Test
Verifies that inactive domain mappings don't interfere with role loading.

## Requirements

### Environment Setup

1. **WordPress Multisite**: Test requires a multisite installation
2. **Ultimate Multisite Plugin**: Must be active with domain mapping enabled
3. **Test Environment**: Uses `@wordpress/env` or similar

### Domain Configuration

**IMPORTANT**: For domain mapping tests to work properly, you need to configure domain resolution. There are several approaches:

#### Option 1: Hosts File (Recommended for local testing)
Add entries to `/etc/hosts`:
```
127.0.0.1  test-123456.example.com
127.0.0.1  test-123457.example.com
```

#### Option 2: DNS Wildcard (For CI/CD)
Configure wildcard DNS for `*.example.com` pointing to test server.

#### Option 3: Test Framework Configuration
Some test environments support custom host headers without DNS/hosts file changes.

### Running the Tests

```bash
# Open Cypress UI (recommended for debugging)
npm run cy:open:test

# Run tests headlessly
npm run cy:run:test

# Run only domain mapping tests
npx cypress run --spec "tests/e2e/cypress/integration/domain-mapping-roles.spec.js"
```

## Known Limitations

1. **Domain Resolution**: Tests may need environment-specific configuration for domain resolution
2. **Timing**: Some tests may need additional wait times depending on server performance
3. **Cleanup**: Test cleanup depends on wp-cli being available in the test environment

## Troubleshooting

### Test Fails: "Domain not accessible"
- Check that domain resolution is configured (hosts file or DNS)
- Verify web server accepts the test domains
- Check Ultimate Multisite domain mapping is enabled

### Test Fails: "User roles empty"
- This indicates the fix may not be working correctly
- Check that the `refresh_user_roles_for_mapped_domain()` method is being called
- Add debug logging to verify the hook is firing

### Test Fails: "Site creation failed"
- Ensure wp-cli is available in test environment
- Check WordPress multisite is properly configured
- Verify database permissions

## Debugging

To debug test failures:

1. **Run with Cypress UI**: `npm run cy:open:test`
2. **Check Screenshots**: Failed tests capture screenshots in `tests/e2e/cypress/screenshots/`
3. **Check Videos**: Test recordings are in `tests/e2e/cypress/videos/`
4. **Add cy.log()**: Insert additional logging in the test
5. **Use cy.pause()**: Add breakpoints in the test

## Success Criteria

All 6 tests should pass, demonstrating that:
- ✓ User roles load correctly on original subdomains (baseline)
- ✓ User roles load correctly on mapped domains (the fix)
- ✓ Plugins can check user roles on mapped domains
- ✓ Multiple users with different roles work correctly
- ✓ Role changes are reflected on mapped domains
- ✓ Inactive mappings don't interfere

## Related Code

- **Fix**: `inc/class-domain-mapping.php:refresh_user_roles_for_mapped_domain()`
- **Commit**: `adf85bb` - "fix roles with custom domain"
- **Issue**: User roles not loaded when accessing via custom domain

## Future Improvements

- Add tests for more complex role scenarios (custom capabilities)
- Test with actual plugins (If Menu, WooCommerce)
- Add performance tests for role loading
- Test with multiple concurrent users
