=== Multisite Ultimate ===
Contributors: aanduque, superdav42 
Donate link: https://github.com/sponsors/superdav42/
Tags: multisite, waas, membership, domain-mapping, subscription
Requires at least: 5.3

Requires PHP: 7.4.30
Tested up to: 6.8
Stable tag: 2.4.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Complete Network Solution for transforming your WordPress Multisite into a Website as a Service (WaaS) platform.

== Description ==

**Multisite Ultimate** helps you transform your WordPress Multisite installation into a powerful Website as a Service (WaaS) platform. This plugin enables you to offer website creation, hosting, and management services to your customers through a streamlined interface.

This plugin was formerly known as WP Ultimo and is now community maintained.

= Key Features =

* **Site Creation** - Allow customers to create their own sites in your network
* **Domain Mapping** - Support for custom domains with automated DNS verification
* **Payment Processing** - Integrations with popular payment gateways like Stripe and PayPal
* **Plan Management** - Create and manage subscription plans with different features and limitations
* **Template Sites** - Easily clone and use template sites for new customer websites
* **Customer Dashboard** - Provide a professional management interface for your customers
* **White Labeling** - Brand the platform as your own
* **Hosting Integrations** - Connect with popular hosting control panels like cPanel, RunCloud, and more

= Where to find help =

* [GitHub Repository](https://github.com/superdav42/wp-multisite-waas)
* [Issue Tracker](https://github.com/superdav42/wp-multisite-waas/issues)

= Contributing =

We welcome contributions to Multisite Ultimate! To contribute effectively:

**Development Workflow:**

1. Fork the repository on GitHub
2. Create a feature branch from main
3. Run `npm install` and `composer install` to set up dependencies
4. Make your changes
5. Before committing, run `npm run build` to:
   * Generate translation POT files
   * Minify CSS and JS assets
   * Process and optimize other assets
6. **Important:** Update both README.md and readme.txt files when making changes that affect:
   * Version numbers
   * Required WordPress/PHP versions
   * Feature additions or changes
   * Installation instructions
   * Documentation
   * Changelog entries
7. Open a Pull Request with your changes

**Pull Request Guidelines:**

Please include a clear description of your changes and their purpose, reference any related issues, and ensure your code follows existing style conventions. Always verify that both README.md and readme.txt are updated and synchronized before submitting your PR.

**Release Process:**

Releases are automated using GitHub Actions workflows that trigger when a version tag is pushed. 

To trigger a new release build, push a tag following the semantic versioning format:
`git tag v2.3.5` (for version 2.3.5) and then `git push origin v2.3.5`

The tag must begin with "v" followed by the version number (v*.*.*).

When preparing for a release:
1. Update the version number in the main plugin file and readme.txt
2. Update the changelog in readme.txt
3. Ensure README.md and readme.txt are synchronized with the latest information
4. Create and push the appropriate version tag

For more detailed contribution guidelines, see the [GitHub repository](https://github.com/superdav42/wp-multisite-waas).

== Installation ==

There are two recommended ways to install Multisite Ultimate:

= Method 1: Using the pre-packaged release (Recommended) =

1. Download the latest release ZIP from the [Releases page](https://github.com/superdav42/wp-multisite-waas/releases)
2. Log in to your WordPress Network Admin dashboard
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded ZIP file and click "Install Now"
5. Network Activate the plugin through the 'Plugins' menu in WordPress
6. Follow the step by step Wizard to set the plugin up

= Method 2: Using Git and Composer (For developers) =

This method requires command-line access to your server and familiarity with Git and Composer.

1. Clone the repository to your plugins directory:
   ```
   cd wp-content/plugins/
   git clone https://github.com/superdav42/wp-multisite-waas.git
   cd wp-multisite-waas
   ```

2. Install the required dependencies using Composer:
   ```
   composer install
   ```

3. Network Activate the plugin in your WordPress Network Admin dashboard
4. Follow the setup wizard to complete the installation

= Common Installation Issues =

**"Failed opening required [...]/vendor/autoload_packages.php"**

This error occurs when the required vendor files are missing. This typically happens when:
- You've downloaded the repository directly from GitHub without using a release package
- The composer dependencies haven't been installed

Solution: Use the pre-packaged release from the [Releases page](https://github.com/superdav42/wp-multisite-waas/releases) or run `composer install` in the plugin directory.

**"Cannot declare class ComposerAutoloaderInitWPUltimoDependencies, because the name is already in use"**

This error usually occurs when updating from an older version of WP Ultimo or when multiple versions of the plugin are installed.

Solution: Deactivate and remove any older versions of WP Ultimo or Multisite Ultimate before activating the new version.

**"Class 'WP_Ultimo\Database\Sites\Site_Query' not found"**

This error can occur if the plugin's autoloader isn't properly loading all the necessary classes.

Solution: Use the pre-packaged release from the [Releases page](https://github.com/superdav42/wp-multisite-waas/releases) which includes all required files.

== Requirements ==

* WordPress Multisite 5.3 or higher
* PHP 7.4.30 or higher
* MySQL 5.6 or higher
* HTTPS enabled (recommended for secure checkout)

== Frequently Asked Questions ==

= Can I use this plugin with a regular WordPress installation? =

No, this plugin specifically requires WordPress Multisite to function properly. It transforms your Multisite network into a platform for hosting multiple customer websites.

= Does this plugin support custom domains? =

Yes, Multisite Ultimate includes robust domain mapping functionality that allows your customers to use their own domains for their websites within your network.

= Which payment gateways are supported? =

The plugin supports multiple payment gateways including Stripe, PayPal, and manually handled payments.

= Can I migrate from WP Ultimo to this plugin? =

Yes, Multisite Ultimate is a community-maintained fork of WP Ultimo. The plugin includes migration tools to help you transition from WP Ultimo.

== External Services ==

This plugin connects to several external services to provide its functionality. Below is a detailed list of all external services used, what data is sent, and when:

= Geolocation Services =

**MaxMind GeoLite2 Database**
- Service: Geolocation database for determining user location based on IP address
- Data sent: No personal data is sent - only downloads the database file
- When: Downloaded periodically when geolocation features are enabled
- Service URL: http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz
- Terms of Service: https://www.maxmind.com/en/terms-of-service
- Privacy Policy: https://www.maxmind.com/en/privacy-policy

**IP Lookup Services**
- Service: External APIs to determine the user's public IP address
- Data sent: HTTP request to determine IP address (no personal data stored)
- When: When geolocation features are enabled and user IP needs to be determined
- Services used:
  - IPify API (http://api.ipify.org/) - Terms: https://www.ipify.org/
  - IP Echo (http://ipecho.net/plain)
  - Ident.me (http://ident.me)
  - WhatIsMyIPAddress (http://bot.whatismyipaddress.com)
  - IPinfo.io (https://ipinfo.io/) - Terms: https://ipinfo.io/terms
  - IP-API.com (http://ip-api.com/) - Terms: https://ip-api.com/docs/legal

= Plugin Updates and Add-ons =

**Multisite Ultimate Update Server**
- Service: Official update server for the plugin and its add-ons (multisiteultimate.com)
- Data sent: Site URL, plugin version, license keys, authentication tokens
- When: During plugin/add-on updates and license checks
- Terms of Service: https://multisiteultimate.com/terms-of-service/
- Privacy Policy: https://multisiteultimate.com/privacy-policy/

= Payment Processing Services =

**PayPal**
- Service: PayPal payment processing for subscription payments
- Data sent: Customer email, payment amounts, subscription details, transaction IDs
- When: During checkout process and subscription management
- Terms of Service: https://www.paypal.com/us/legalhub/useragreement-full
- Privacy Policy: https://www.paypal.com/us/legalhub/privacy-full

**Stripe**
- Service: Stripe payment processing for credit card payments and subscriptions
- Data sent: Customer payment information, email addresses, subscription data
- When: During checkout process and recurring billing
- Terms of Service: https://stripe.com/legal/ssa
- Privacy Policy: https://stripe.com/privacy

= Hosting Provider Integrations =

**Cloudflare**
- Service: DNS management and domain configuration
- Data sent: Domain names, DNS records, API authentication tokens
- When: When customers add custom domains or manage DNS settings
- Terms of Service: https://www.cloudflare.com/terms/
- Privacy Policy: https://www.cloudflare.com/privacypolicy/

**Closte Hosting API**
- Service: Integration with Closte hosting provider for automated site management
- Data sent: Site configuration data, API credentials
- When: Only when Closte integration is enabled and configured
- Service URL: https://app.closte.com/api/client
- Terms of Service: https://closte.com/terms-of-service/
- Privacy Policy: https://closte.com/privacy-policy/

**Cloudways Hosting API**
- Service: Integration with Cloudways hosting provider for automated site management
- Data sent: Site configuration data, API credentials, authentication tokens
- When: Only when Cloudways integration is enabled and configured
- Service URL: https://api.cloudways.com/api/v1/oauth/access_token
- Terms of Service: https://www.cloudways.com/en/terms.php
- Privacy Policy: https://www.cloudways.com/en/privacy.php

**GridPane**
- Service: Server management and site provisioning
- Data sent: Site configuration data, domain information
- When: When sites are created or managed on GridPane hosting
- Terms of Service: https://gridpane.com/terms-of-service/
- Privacy Policy: https://gridpane.com/privacy-policy/

**WPMU DEV Hosting**
- Service: Hosting management and domain configuration
- Data sent: Site IDs, domain information, API keys
- When: When managing sites on WPMU DEV hosting platform
- Terms of Service: https://wpmudev.com/terms-of-service/
- Privacy Policy: https://incsub.com/privacy-policy/

= DNS and Domain Services =

**Google DNS Resolution**
- Service: DNS lookup service for domain verification
- Data sent: Domain names for DNS lookup (no personal data)
- When: During domain mapping setup and verification
- Service URL: https://dns.google/resolve
- Terms of Service: https://developers.google.com/terms/
- Privacy Policy: https://policies.google.com/privacy

= Newsletter and Analytics =

**Multisite Ultimate Newsletter Service**
- Service: Newsletter subscription for product updates (multisiteultimate.com)
- Data sent: Company email, name, country information
- When: During initial plugin setup (optional)
- This is our own service for providing plugin updates and announcements
- You can opt out of this service during setup
- Terms of Service: https://multisiteultimate.com/terms-of-service/
- Privacy Policy: https://multisiteultimate.com/privacy-policy/

All external service connections are clearly disclosed to users during setup, and most services are optional or can be configured based on your chosen hosting provider and payment methods.

== Screenshots ==

1. Dashboard overview with key metrics
2. Subscription plans management
3. Customer management interface
4. Site creation workflow
5. Domain mapping settings

== Support ==

For support, please open an issue on the [GitHub repository](https://github.com/superdav42/wp-multisite-waas/issues).

== Upgrade Notice ==

We recommend running this in a staging environment before updating your production environment.

== Changelog ==

Version [2.4.4] - Released on 2025-08-XX
- Fixed: Saving email templates without stripping html
- New: Option to allow site owners to edit users on their site
- Fixed: Invoices not loading when logo is not set
- Fixed: Verify DNS settings when using a reverse proxy
- Improved: Lazy load limitations for better performance and compatibility
- New: Add Admin Notice if sunrise.php is not setup
- New: Option to not always create www. subdomains with hosting integrations

Version [2.4.3] - Released on 2025-08-15
- Fixed: Bug in Slim SEO plugin
- New: Addon Marketplace
- Fixed: Custom logo not showing on emails and invoices
- Fixed: Limitations failing to load

Version [2.4.2] - Released on 2025-08-07
- Fixed: Authentication of the API
- Fixed: Saving checkout fields
- Fixed: Creating Products and Sites
- Fixed: Duplicating sites
- Improved: Performance of switch_blog
- Improved: Remove extra queries related update_meta_data hook and 1.X compat
- New: Addon Marketplace
- Improved: Update currencies to support all supported by Stripe
- Improved: Template previewer

Version [2.4.1] - Released on 2025-07-17
- Improved: Update Stripe PHP Library to latest version
- Improved: Update JS libs
- Fixed: Fatal error that may occur when upgrading from old name.
- Improved: Added check for custom domain count when downgrading.

Version [2.4.0] - Released on 2025-07-07
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

Version [2.3.4] - Released on 2024-01-31
- Fixed: Unable to checkout with any payment gateway
- Fixed: Warning Undefined global variable $pagenow

Version [2.3.3] - Released on 2024-01-29

- Improved: Plugin renamed to Multisite Ultimate
- Removed: Enforcement of paid license
- Fixed: Incompatibilities with WordPress 6.7 and i18n timing
- Improved: Reduced plugin size by removing many unnecessary files and shrinking images

For the complete changelog history, visit: https://github.com/superdav42/multisite-ultimate/releases
