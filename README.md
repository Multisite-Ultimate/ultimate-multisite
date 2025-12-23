# Ultimate Multisite

<p align="center">
  <img src="https://github.com/Multisite-Ultimate/ultimate-multisite/blob/main/assets/img/logo.png" alt="Ultimate Multisite Logo" width="300">
</p>

<p align="center">
  <strong>The Complete Network Solution for transforming your WordPress Multisite into a Website as a Service (WaaS) platform.</strong>
</p>

<p align="center">
  <a href="http://www.gnu.org/licenses/gpl-2.0.html"><img src="https://img.shields.io/badge/License-GPL%20v2-blue.svg" alt="License: GPL v2"></a>
  <a href="https://wordpress.org/"><img src="https://img.shields.io/badge/WordPress-6.8%20Tested-green.svg" alt="WordPress: 6.8 Tested"></a>
  <a href="https://php.net/"><img src="https://img.shields.io/badge/PHP-7.4.0%2B-purple.svg" alt="PHP: 7.4.0+"></a>
  <a href="https://php.net/"><img src="https://img.shields.io/badge/Up%20To%20PHP-8.4.6-purple.svg" alt="Up To PHP: 8.4.6"></a>
  <a href="https://github.com/Multisite-Ultimate/ultimate-multisite/releases"><img src="https://img.shields.io/github/v/release/Multisite-Ultimate/ultimate-multisite" alt="Latest Release"></a>
</p>
<p align="center">
  <a href="https://github.com/Multisite-Ultimate/ultimate-multisite/actions/workflows/tests.yml"><img src="https://github.com/Multisite-Ultimate/ultimate-multisite/actions/workflows/tests.yml/badge.svg" alt="Unit & Integration Tests"></a>
  <a href="https://github.com/Multisite-Ultimate/ultimate-multisite/actions/workflows/e2e.yml"><img src="https://github.com/Multisite-Ultimate/ultimate-multisite/actions/workflows/e2e.yml/badge.svg" alt="E2E Tests"></a>
  <a href="https://codecov.io/gh/Multisite-Ultimate/ultimate-multisite"><img src="https://codecov.io/gh/Multisite-Ultimate/ultimate-multisite/branch/main/graph/badge.svg" alt="Code Coverage"></a>
  <a href="https://github.com/Multisite-Ultimate/ultimate-multisite/actions/workflows/code-quality.yml"><img src="https://github.com/Multisite-Ultimate/ultimate-multisite/actions/workflows/code-quality.yml/badge.svg" alt="Code Quality"></a>
</p>

## 🌟 Overview

**Ultimate Multisite** helps you transform your WordPress Multisite installation into a powerful Website as a Service (WaaS) platform. This plugin enables you to offer website creation, hosting, and management services to your customers through a streamlined interface.

This plugin was formerly known as WP Ultimo and is now community maintained.

## ✨ Key Features

- **Site Creation** - Allow customers to create their own sites in your network
- **Domain Mapping** - Support for custom domains with automated DNS verification
- **Payment Processing** - Integrations with popular payment gateways like Stripe and PayPal
- **Plan Management** - Create and manage subscription plans with different features and limitations
- **Template Sites** - Easily clone and use template sites for new customer websites
- **Customer Dashboard** - Provide a professional management interface for your customers
- **White Labeling** - Brand the platform as your own
- **Hosting Integrations** - Connect with popular hosting control panels like cPanel, RunCloud, and more

## 📋 Requirements

- WordPress Multisite 5.3 or higher
- PHP 7.4.30 or higher
- MySQL 5.6 or higher

## 🔧 Installation

There are two recommended ways to install Ultimate Multisite:

### Method 1: Using the pre-packaged release (Recommended)

1. Download the latest release ZIP from the [Releases page](https://github.com/Multisite-Ultimate/ultimate-multisite/releases)
2. Log in to your WordPress Network Admin dashboard
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded ZIP file and click "Install Now"
5. Network Activate the plugin through the 'Plugins' menu in WordPress
6. Follow the step-by-step Wizard to set the plugin up

### Method 2: Using Git and Composer (For developers)

This method requires command-line access to your server and familiarity with Git and Composer.

1. Clone the repository to your plugins directory:

   ```bash
   cd wp-content/plugins/
   git clone https://github.com/Multisite-Ultimate/ultimate-multisite.git
   cd ultimate-multisite
   ```

2. Install the required dependencies using Composer:

   ```bash
   composer install
   ```

3. Network Activate the plugin in your WordPress Network Admin dashboard
4. Follow the setup wizard to complete the installation

## 🔍 Common Installation Issues

<details>
<summary><strong>"Failed opening required [...]/vendor/autoload_packages.php"</strong></summary>
<p>This error occurs when the required vendor files are missing. This typically happens when:</p>
<ul>
  <li>You've downloaded the repository directly from GitHub without using a release package</li>
  <li>The composer dependencies haven't been installed</li>
</ul>
<p><strong>Solution:</strong> Use the pre-packaged release from the <a href="https://github.com/Multisite-Ultimate/ultimate-multisite/releases">Releases page</a> or run <code>composer install</code> in the plugin directory.</p>
</details>

<details>
<summary><strong>"Cannot declare class ComposerAutoloaderInitWPUltimoDependencies, because the name is already in use"</strong></summary>
<p>This error usually occurs when updating from an older version of WP Ultimo or when multiple versions of the plugin are installed.</p>
<p><strong>Solution:</strong> Deactivate and remove any older versions of WP Ultimo or Ultimate Multisite before activating the new version.</p>
</details>

<details>
<summary><strong>"Class 'WP_Ultimo\Database\Sites\Site_Query' not found"</strong></summary>
<p>This error can occur if the plugin's autoloader isn't properly loading all the necessary classes.</p>
<p><strong>Solution:</strong> Use the pre-packaged release from the <a href="https://github.com/Multisite-Ultimate/ultimate-multisite/releases">Releases page</a> which includes all required files.</p>
</details>

## 🚀 Contributing

We welcome contributions to Ultimate Multisite! Here's how you can contribute effectively:

### Development Workflow

1. **Quick Setup:**
   ```bash
   git clone https://github.com/Multisite-Ultimate/ultimate-multisite.git
   cd wp-multisite-waas
   npm run dev:setup  # Installs dependencies and sets up Git hooks
   ```

2. **Development Commands:**
   ```bash
   npm test              # Run tests
   npm run test:coverage # Run tests with coverage
   npm run lint         # Check code style (PHPCS)
   npm run lint:fix     # Fix code style automatically
   npm run stan         # Run static analysis (PHPStan)
   npm run quality      # Run lint + stan
   npm run check        # Run all quality checks
   npm run build        # Production build
   npm run build:dev    # Development build
   npm run clean        # Clean build artifacts
   ```

3. **Making Changes:**
   - Create your feature branch (`git checkout -b feat/amazing-feature`)
   - Make your changes with tests
   - Git hooks will automatically run PHPCS and PHPStan on changed files
   - Commit using conventional format: `feat(scope): description`
   - Run `npm run check` before pushing
   - Push and create a Pull Request

4. **Code Quality:**
   - Pre-commit hooks run automatically
   - Follow WordPress coding standards
   - Include tests for new features
   - Maintain >80% code coverage
   - Use conventional commit messages

5. **Important:** Update both README.md and readme.txt files when making changes that affect versions, features, or documentation.

### Pull Request Guidelines

When submitting pull requests, please:

1. Include a clear description of the changes and their purpose
2. Reference any related issues using GitHub's issue linking syntax (#123)
3. Ensure your code follows the existing style and conventions
4. Include screenshots or GIFs for UI changes if applicable
5. Make sure all tests pass (if available)
6. Update documentation as needed
7. Verify that both README.md and readme.txt are updated and synchronized

### Release Process

Releases are automated using GitHub Actions workflows that trigger when a version tag is pushed.

**Tagging Convention:** To trigger a new release build, push a tag following the semantic versioning format:

```bash
git tag v2.3.5  # For example, for version 2.3.5
git push origin v2.3.5
```

The tag must begin with "v" followed by the version number (v*.*.\*)

This will automatically:

1. Build the plugin (run `npm run build`)
2. Create a properly packaged ZIP file
3. Create a GitHub release with the ZIP attached

When preparing for a release:

1. Update the version number in the main plugin file and readme.txt
2. Update the changelog in readme.txt
3. Ensure README.md and readme.txt are synchronized with the latest information
4. Create and push the appropriate version tag

## ✅ Testing

See [readme](tests/e2e/README.md) for e2e testing.

## 🆘 Support

For support, please open an issue on the [GitHub repository](https://github.com/Multisite-Ultimate/ultimate-multisite/issues).

## ⚠️ Upgrade Notice

We recommend running this in a staging environment before updating your production environment.

## 📝 Recent Changes

### Version [2.4.9] - Released on 2025-12-23
- New: Inline login prompt at checkout for existing users - returning customers can sign in directly without leaving the checkout flow.
- New: GitHub Actions workflow for PR builds with WordPress Playground testing - enables one-click browser-based testing of pull requests.
- Fixed: Template switching now preserves images - URLs in post content are correctly updated when switching templates.
- Fixed: Email manager initialization during setup wizard - system emails are now correctly created.
- Fixed: Template switching permission and capability checks improved with better error messaging.
- Fixed: Multiple primary domains being set.
- Improved: Template selection logic with better null safety and smart fallbacks for pre-selected templates.
- Improved: Compatibility for legacy filter `wu_create_site_meta` from WP Ultimo v1.
- Improved: Added support for Runcloud V3 API

### Version [2.4.8] - Released on 2025-11-21
- New: Added MCP (Model Context Protocol) Server integration.
- New: Added support for multi-network installations with network-specific customers, memberships, and products.
- New: Added magic login links for SSO when third-party cookies are disabled.
- New: Added admin notice when invalid COOKIE_DOMAIN constant is detected.
- Fixed: WooCommerce subscriptions incorrectly set to staging mode when site is duplicated.
- Fixed: Single-Sign-On (SSO) authentication issues with custom domains.
- Fixed: Template switching functionality and improved singleton pattern usage across codebase.
- Improved: Enhanced domain mapping element and login form handling.
- Improved: Better redirect handling for sites within the network.
- Improved: Faster site creation after checkout.

### Version [2.4.7] - Released on 2025-10-31
- Fixed: Conflict with YesCookie plugin.
- Improved: Thumbnail image quality on template selection in the checkout.
- Fixed: Redirect from secondary domains to primary domain.
- Fixed: Choosing templates for checkout form builder.
- Fixed: Extra domain creation with subdirectory installation.
- Improved: Allow html in custom domain instructions.

### Version [2.4.6] - Released on 2025-10-15
- Fixed: Toggle switches in RTL languages.
- Fixed: Rendering admin pages for legacy addons.
- Fixed: Some Stripe API errors.
- Improved: Better site URL autogeneration and added preview option.
- Fixed: Escaping too much HTML.
- Fixed: Saving HTML in credits field.
- Improved: Type safety in code.
- Fixed: Downgrading during a trial extending the trial period.
### Version [2.4.5] - Released on 2025-09-30
- Fixed: Custom domain check when downgrading.
- Fixed: Bug in Action Scheduler.
- Fixed: Hosting integration wizard freezing during setup.
- Improved: More robust handling for login URL obfuscation when 404 template unavailable.
- Improved: Better error messaging for installer with sanitized HTML display.
- Added: Recommended plugins installer functionality.
- Added: New end-to-end testing framework.
- Added: Option to include a "Powered by..." message in the footer of customer sites.
- Added: Install recommended "user-switching" plugin during setup wizard.
- Improved: Autogeneration of site urls and usernames to be more human friendly.
- Improved: Code style and return type consistency across codebase.

### Version [2.4.4] - Released on 2025-09-17
- Fixed: Saving email templates without stripping html
- New: Option to allow site owners to edit users on their site
- Fixed: Invoices not loading when logo is not set
- Fixed: Verify DNS settings when using a reverse proxy
- Improved: Lazy load limitations for better performance and compatibility
- New: Add Admin Notice if sunrise.php is not setup
- New: Option to not always create www. subdomains with hosting integrations
- Improved: Plugin renamed to Ultimate Multisite

### Version [2.4.3] - Released on 2025-08-15
- Fixed: Bug in Slim SEO plugin
- New: Addon Marketplace
- Fixed: Custom logo not showing on emails and invoices
- Fixed: Limitations failing to load

### Version [2.4.2] - Released on 2025-08-07
- Fixed: Authentication of the API
- Fixed: Saving checkout fields
- Fixed: Creating Products and Sites
- Fixed: Duplicating sites
- Improved: Performance of switch_blog
- Improved: Remove extra queries related update_meta_data hook and 1.X compat
- New: Addon Marketplace
- Improved: Update currencies to support all supported by Stripe

### Version [2.4.1] - Released on 2025-07-17

- Improved: Update Stripe PHP Library to latest version.
- Improved: Update JS libs.
- Fixed: Added a few more security checks.
- Fixed: Fatal error that may occur when upgrading from old name.
- Improved: Added check for custom domain count when downgrading.

### Version [2.4.0] - Released on 2025-07-07

- Improved: Prep Plugin for release on WordPress.org
- Improved: Update translation text domain
- Fixed: Escape everything that should be escaped.
- Fixed: Add nonce checks where needed.
- Fixed: Sanitize all inputs.
- Improved: Apply Code style changes across the codebase.
- Fixed: Many deprecation notices.
- Improved: Load order of many filters.
- Improved: Add Proper Build script
- Improved: Use emojii flags
- Fixed: i18n deprecation notice for translating too early
- Improved: Put all scripts in footer and load async
- Improved: Add discounts to thank you page
- Improved: Prevent downgrading a plan if it the post type could would be over the limit
- Fixed: Styles on thank you page of legacy checkout

### Version [2.3.4] - Released on 2024-01-31

- Fixed: Unable to checkout with any payment gateway
- Fixed: Warning Undefined global variable $pagenow

### Version [2.3.3] - Released on 2024-01-29

- Improved: Plugin renamed to Ultimate Multisite
- Removed: Enforcement of paid license
- Fixed: Incompatibilities with WordPress 6.7 and i18n timing
- Improved: Reduced plugin size by removing many unnecessary files and shrinking images

For the complete changelog, please see [readme.txt](readme.txt).

## 👥 Contributors

Ultimate Multisite is an open-source project with contributions from:

- [aanduque](https://github.com/aanduque)
- [superdav42](https://github.com/superdav42)
- [And the community](https://github.com/Multisite-Ultimate/ultimate-multisite/graphs/contributors)

## 📄 License

Ultimate Multisite is licensed under the GPL v2 or later.

Copyright © 2024 [Ultimate Multisite Contributors](https://github.com/Multisite-Ultimate/ultimate-multisite/graphs/contributors)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
