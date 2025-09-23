# E2E Testing Guide for WP Multisite Ultimate

This comprehensive guide covers all end-to-end tests for WP Multisite Ultimate, including setup wizard completion and the complete checkout registration flow.

## 🚀 Quick Start

### Prerequisites Checklist
- [ ] WordPress Multisite network installed
- [ ] WP Multisite Ultimate plugin uploaded but not yet configured
- [ ] Node.js and npm installed
- [ ] Cypress installed (`npm install cypress --save-dev`)
- [ ] Test environment running on `http://localhost:8889`

### First Time Setup
```bash
# 1. Complete the setup wizard (MUST run first)
npx cypress run --spec "tests/e2e/cypress/integration/setup-wizard-complete.spec.js"

# 2. Run all checkout tests
npx cypress run --spec "tests/e2e/cypress/integration/checkout-*.spec.js"

# 3. Or run everything
npx cypress run
```

## 📋 Test Suite Overview

### ⚠️ CRITICAL: Test Execution Order

**The setup wizard MUST be completed before running any checkout tests**, as it:
- Creates necessary database tables
- Generates sample plans and products
- Creates default checkout forms
- Configures payment gateways
- Sets up required pages

## 🧪 Test Files

### 1. Prerequisites & Setup
| File | Purpose | Run Order | Required |
|------|---------|-----------|----------|
| `setup-wizard-complete.spec.js` | Complete initial plugin setup | **1st** | ✅ **MUST RUN FIRST** |
| `installation.spec.js` | Verify plugin installation | Before setup | Optional |

### 2. Core Checkout Flow Tests
| File | Purpose | Dependencies | Description |
|------|---------|-------------|-------------|
| `checkout-registration.spec.js` | Happy path registration | Setup wizard | Complete end-to-end registration flow |
| `checkout-validation.spec.js` | Form validation testing | Setup wizard | Field validation, error handling |
| `checkout-scenarios.spec.js` | Different scenarios | Setup wizard | Free/paid plans, mobile, gateways |
| `checkout-confirmation.spec.js` | Post-registration flow | Setup wizard | Confirmation page, site access |

### 3. Existing System Tests
| File | Purpose | Dependencies |
|------|---------|-------------|
| `wizard.spec.js` | Setup wizard navigation | None |
| `login.spec.js` | Authentication tests | Setup wizard |
| `plugin.spec.js` | Plugin functionality | Setup wizard |
| `mail.spec.js` | Email functionality | Setup wizard |

## 🔧 Detailed Test Descriptions

### Setup Wizard Complete (`setup-wizard-complete.spec.js`)

**Purpose**: Ensures the plugin is properly configured for use.

**What it does**:
1. **Welcome Step** - Starts the setup wizard
2. **System Checks** - Verifies server requirements
3. **Installation** - Creates database tables and core data
4. **Company Details** - Configures business information
5. **Defaults Creation** - Creates sample plans, checkout forms, pages
6. **Completion** - Marks setup as finished

**Critical for**: All other tests depend on this setup being completed.

**Verification**:
- Database tables created (`wu_*` tables)
- Sample plans exist
- Checkout forms created
- Payment gateways configured

### Checkout Registration (`checkout-registration.spec.js`)

**Happy path testing** of the complete registration flow:

1. **Plan Selection** - Choose from available pricing plans
2. **Account Details** - Username, email, password validation
3. **Site Details** - Site title, URL, template selection
4. **Payment Processing** - Billing info, gateway selection
5. **Confirmation** - Success verification

**Test cases**:
- ✅ Complete free plan registration
- ✅ Complete paid plan registration
- ✅ Field validation (email format, username availability)
- ✅ Site URL validation and availability
- ✅ Payment gateway handling

### Checkout Validation (`checkout-validation.spec.js`)

**Comprehensive validation testing**:

**Product Selection**:
- Require plan selection before proceeding
- Handle invalid plan selections

**Account Details**:
- Required field validation
- Email format validation (invalid@, @invalid.com, etc.)
- Username format and availability checking
- Password strength requirements
- Password confirmation matching

**Site Details**:
- Required site field validation
- Site URL format validation (spaces, special chars, etc.)
- Site title requirements
- Site URL availability checking

**Payment**:
- Free plan handling
- Billing information validation
- Payment gateway selection requirements

**Cross-field Validation**:
- Form state preservation during errors
- Email uniqueness across users

### Checkout Scenarios (`checkout-scenarios.spec.js`)

**Different checkout scenarios and edge cases**:

**Plan Types**:
- ✅ Free plan registration (skips payment)
- ✅ Paid plan with manual payment
- ✅ Different pricing tiers

**Navigation**:
- ✅ Browser back/forward navigation
- ✅ Step navigation via indicators
- ✅ Form data preservation

**Templates**:
- ✅ Template selection process
- ✅ Blank/custom template options

**Payment Gateways**:
- ✅ Manual payment gateway
- ✅ Free gateway for $0 orders
- ✅ Gateway selection validation

**Special Cases**:
- ✅ Discount code application
- ✅ Session timeout handling
- ✅ Network error recovery
- ✅ Mobile responsiveness testing

### Checkout Confirmation (`checkout-confirmation.spec.js`)

**Post-registration verification**:

**Confirmation Page**:
- ✅ Correct customer information display
- ✅ Site information verification
- ✅ Navigation options (dashboard, site visit, login)

**Email Verification**:
- ✅ Email verification notices
- ✅ Resend verification functionality

**Site Access**:
- ✅ Dashboard access verification
- ✅ Frontend site functionality

**Payment Details**:
- ✅ Payment information display
- ✅ Next payment date for recurring plans

**Onboarding**:
- ✅ Getting started information
- ✅ Support and help resources

## 🛠️ Custom Commands Reference

### Navigation Commands
```javascript
cy.visitCheckoutForm('registration')        // Navigate to checkout form
cy.selectPricingPlan(0)                     // Select plan by index
cy.proceedToNextStep()                      // Continue to next step
cy.completeCheckout()                       // Finalize registration
```

### Form Filling Commands
```javascript
cy.fillAccountDetails({
  username: 'testuser',
  email: 'test@example.com',
  password: 'TestPass123!'
})

cy.fillSiteDetails({
  title: 'Test Site',
  path: 'testsite'
})

cy.fillBillingAddress({
  address: '123 Test St',
  city: 'Test City',
  state: 'CA',
  zipCode: '12345'
})
```

### Verification Commands
```javascript
cy.verifyCheckoutSuccess({
  email: 'test@example.com',
  siteTitle: 'Test Site'
})

cy.assertCheckoutStep('2')                  // Verify current step
cy.hasValidationErrors()                    // Check for validation errors
```

### Payment & Gateway Commands
```javascript
cy.selectPaymentGateway('manual')           // Select payment method
cy.selectSiteTemplate(0)                    // Choose site template
```

## 🏃 Running Tests

### Development Workflow

#### 1. First Time Setup
```bash
# Start with fresh WordPress Multisite installation
# Install WP Multisite Ultimate plugin (don't activate yet)

# Complete setup wizard
npx cypress run --spec "tests/e2e/cypress/integration/setup-wizard-complete.spec.js"
```

#### 2. Development Testing
```bash
# Test specific functionality
npx cypress open --spec "tests/e2e/cypress/integration/checkout-registration.spec.js"

# Run validation tests
npx cypress run --spec "tests/e2e/cypress/integration/checkout-validation.spec.js"

# Test all scenarios
npx cypress run --spec "tests/e2e/cypress/integration/checkout-scenarios.spec.js"
```

#### 3. Full Test Suite
```bash
# Run all tests (setup wizard + checkout flow)
npx cypress run

# Run only checkout tests (assumes setup is complete)
npx cypress run --spec "tests/e2e/cypress/integration/checkout-*.spec.js"
```

### CI/CD Pipeline

```yaml
name: E2E Tests
on: [push, pull_request]

jobs:
  e2e-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      # Setup WordPress Multisite environment
      - name: Setup WordPress
        run: |
          # Your WordPress setup commands here

      - name: Install Dependencies
        run: npm ci

      - name: Run Setup Tests
        uses: cypress-io/github-action@v4
        with:
          start: npm run start:test
          wait-on: 'http://localhost:8889'
          spec: tests/e2e/cypress/integration/setup-wizard-complete.spec.js

      - name: Run Checkout Tests
        uses: cypress-io/github-action@v4
        with:
          start: npm run start:test
          wait-on: 'http://localhost:8889'
          spec: tests/e2e/cypress/integration/checkout-*.spec.js
```

## 🐛 Debugging & Troubleshooting

### Common Issues

#### 1. "Setup wizard not found"
```bash
# Verify plugin is installed but not configured
# Check WordPress Multisite is properly setup
# Ensure user has network admin permissions
```

#### 2. "Checkout form not accessible"
```bash
# Make sure setup wizard completed successfully
# Verify checkout forms were created in admin
# Check that sample data was installed
```

#### 3. "Element not found" errors
```bash
# Verify your checkout form matches expected selectors
# Check if custom test attributes are added
# Update selectors in custom commands if needed
```

#### 4. "Payment gateway not available"
```bash
# Ensure setup wizard configured payment gateways
# Check manual payment gateway is enabled
# Verify payment settings in admin
```

### Debug Commands

```bash
# Debug specific test with detailed output
DEBUG=cypress:* npx cypress run --spec "path/to/test.spec.js"

# Run in headed mode for visual debugging
npx cypress open --spec "path/to/test.spec.js"

# Generate video recordings
npx cypress run --record --key <your-key>
```

### Test Data Reset

If you need to reset test data:

```bash
# Reset WordPress database
wp db reset --yes

# Re-run setup wizard
npx cypress run --spec "tests/e2e/cypress/integration/setup-wizard-complete.spec.js"
```

## 📊 Test Coverage

### Current Coverage Areas

✅ **Setup & Configuration**
- Plugin installation verification
- Setup wizard completion
- Database table creation
- Sample data generation

✅ **User Registration**
- Account creation flow
- Field validation
- Username/email uniqueness
- Password requirements

✅ **Site Creation**
- Site details collection
- URL validation and availability
- Template selection
- Site provisioning

✅ **Payment Processing**
- Free plan handling
- Paid plan checkout
- Payment gateway selection
- Billing information collection

✅ **Error Handling**
- Form validation errors
- Network failure recovery
- Session timeout handling
- Invalid input rejection

✅ **Mobile & Accessibility**
- Responsive design testing
- Touch interaction validation
- Accessibility compliance checks

### Areas for Future Enhancement

🔄 **Advanced Scenarios**
- Multi-site creation limits
- Plan upgrades/downgrades
- Subscription management
- Domain mapping

🔄 **Integration Testing**
- Email delivery verification
- Third-party payment gateways
- Template marketplace integration
- API endpoint testing

## 🤝 Contributing

### Adding New Tests

1. **Follow naming convention**: `feature-aspect.spec.js`
2. **Use existing custom commands** from `commands/checkout.js`
3. **Include both positive and negative test cases**
4. **Test across different viewports**
5. **Update documentation** when adding new features

### Test Development Guidelines

```javascript
// ✅ Good: Use dynamic test data
const testUser = {
  username: `testuser_${Date.now()}`,
  email: `testuser_${Date.now()}@example.com`
};

// ✅ Good: Use multiple fallback selectors
cy.get('#username, [name="username"], [data-testid="username"]')

// ✅ Good: Handle optional elements
cy.get('body').then($body => {
  if ($body.find('#optional-field').length > 0) {
    cy.get('#optional-field').type('value');
  }
});

// ❌ Avoid: Hard-coded test data that may conflict
const testUser = {
  username: 'testuser',
  email: 'test@example.com'
};
```

## 📈 Metrics & Reporting

### Test Execution Metrics
- **Setup Wizard**: ~2-3 minutes
- **Registration Flow**: ~1-2 minutes per scenario
- **Validation Tests**: ~3-5 minutes
- **Full Suite**: ~10-15 minutes

### Success Criteria
- ✅ Setup wizard completes without errors
- ✅ All checkout scenarios pass
- ✅ Form validation works correctly
- ✅ Payment processing functions properly
- ✅ Post-registration flow verified

---

## 📞 Support

For questions about the test suite:

1. Check this documentation first
2. Review existing test files for examples
3. Check Cypress documentation for command reference
4. Open an issue for test-specific problems

**Happy Testing! 🎉**