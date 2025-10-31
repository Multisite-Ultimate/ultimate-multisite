=== Ultimate Multisite ===
Contributors: aanduque, superdav42, vvwb, surferking
Donate link: https://github.com/sponsors/superdav42/
Tags: multisite, waas, membership, domain-mapping, subscription
Requires at least: 5.3
Requires PHP: 7.4.30
Tested up to: 6.8
Stable tag: 2.4.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
The Complete Network Solution for transforming your WordPress Multisite into a Website as a Service (WaaS) platform.

== Description ==

**Ultimate Multisite** turns your WordPress Multisite into a full WaaS (Website as a Service) platform—so you can sell plans, provision new sites from templates, map custom domains, and manage customers and billing with confidence.

Launch niche site builders, productized services, or large, branded networks without stitching together dozens of tools. From checkout to provisioning to ongoing management, Ultimate Multisite gives you the building blocks to create a modern, scalable website platform on top of WordPress.

Formerly known as WP Ultimo, now community‑maintained and actively improved.

= Key Features =

Build, sell, and scale with a feature set designed for WaaS operators:

- **Fast Site Creation** – Self‑serve signup that provisions new sites instantly from templates
- **Domain Mapping** – Custom domains with automated DNS verification and clear guidance
- **Payments & Subscriptions** – Stripe and PayPal support for recurring plans and one‑time fees
- **Flexible Plans & Limits** – Package features and enforce quotas/limitations across your network
- **Template Library** – Create high‑converting templates your customers can launch in minutes
- **Customer Dashboard** – Clean, branded UI for managing billing, sites, domains, and settings
- **White‑Label Ready** – Rename, rebrand, and tailor the experience to your business
- **Hosting Integrations** – Cloudflare, GridPane, Cloudways, WPMU DEV, and more
- **Developer‑Friendly** – Hooks, filters, and an add‑on system for deep customization

= Who Is It For? =

- Agencies productizing WordPress into packages and recurring plans
- Creators launching niche site builders (local business sites, portfolios, courses, communities)
- Hosts and MSPs offering white‑label WordPress at scale
- Franchises, universities, and multi‑location brands with many similar sites
- Internal teams rolling out microsites and campaigns on shared infrastructure

= Popular Use Cases =

- Productized website services with recurring billing and templates
- Franchise and multi‑location networks with brand‑consistent starter sites
- “Site builder” offerings for a specific industry or niche
- Private networks for internal departments, events, or communities
- Educational institutions provisioning class, club, or program sites

= Why Ultimate Multisite =

- **Open & Community‑Maintained** – Transparent development, active updates
- **WordPress‑Native** – Built specifically for Multisite; no heavy SaaS lock‑in
- **Proven Architecture** – Templates, plans, and domain mapping built‑in
- **Extensible** – Add‑on system, actions/filters, and hosting integrations
- **Owned Infrastructure** – Run your WaaS on your stack, your way

= Try It In Minutes =

1. Enable WordPress Multisite on a staging site
2. Install and Network Activate Ultimate Multisite
3. Run the setup wizard, create a plan, and add a template
4. Share your signup page and start selling

Ready to build your WaaS? Install the plugin and launch your first customer site today.

= Where to find help =

* [Issue Tracker](https://github.com/Multisite-Ultimate/multisite-ultimate/issues)
* Paid support at [the official Ultimate Multisite website](https://ultimatemultisite.com)

= Contributing =

We welcome contributions to Ultimate Multisite! To contribute see the [GitHub repository](https://github.com/Multisite-Ultimate/multisite-ultimate).

== Requirements ==

* WordPress Multisite 5.3 or higher
* PHP 7.4.30 or higher
* MySQL 5.6 or higher

== Frequently Asked Questions ==

= Can I use this plugin with a regular WordPress installation? =

No, this plugin specifically requires WordPress Multisite to function properly. It transforms your Multisite network into a platform for hosting multiple customer websites.

= Does this plugin support custom domains? =

Yes, Ultimate Multisite includes robust domain mapping functionality that allows your customers to use their own domains for their websites within your network.

= Which payment gateways are supported? =

The plugin supports multiple payment gateways including Stripe, PayPal, and manually handled payments.

= Can I migrate from WP Ultimo to this plugin? =

Yes, Ultimate Multisite is a community-maintained fork of WP Ultimo. Migration will happen automatically from WP Ultimo 2.X when this plugin is activated.

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

**Ultimate Multisite Update Server**
- Service: Official update server for the plugin and its add-ons (ultimatemultisite.com)
- Data sent: Site URL, plugin version, license keys, authentication tokens
- When: During plugin/add-on updates and license checks
- Terms of Service: https://ultimatemultisite.com/terms-of-service/
- Privacy Policy: https://ultimatemultisite.com/privacy-policy/

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

**Ultimate Multisite Newsletter Service**
- Service: Newsletter subscription for product updates (ultimatemultisite.com)
- Data sent: Company email, name, country information
- When: During initial plugin setup (optional)
- This is our own service for providing plugin updates and announcements
- You can opt out of this service during setup
- Terms of Service: https://ultimatemultisite.com/terms-of-service/
- Privacy Policy: https://ultimatemultisite.com/privacy-policy/

All external service connections are clearly disclosed to users during setup, and most services are optional or can be configured based on your chosen hosting provider and payment methods.

== Screenshots ==

1. One of many settings pages.
2. Setting up products and plans.
3. Listing sites on the network.
4. Registration Form builder.

== Support ==

For support, please open an issue on the [GitHub repository](https://github.com/superdav42/wp-multisite-waas/issues).

== Upgrade Notice ==

We recommend running this in a staging environment before updating your production environment.

== Changelog ==

Version [2.4.7] - Released on 2025-XX-XX
- Fixed: Conflict with YesCookie plugin.
- Improved: Thumbnail image quality on template selection in the checkout.
- Fixed: Redirect from secondary domains to primary domain.
- Fixed: Choosing templates for checkout form builder.
- Fixed: Extra domain creation with subdirectory installation.

Version [2.4.6] - Released on 2025-10-15
- Fixed: Toggle switches in RTL languages.
- Fixed: Rendering admin pages for legacy addons.
- Fixed: Some Stripe API errors.
- Improved: Better site URL autogeneration and added preview option.
- Fixed: Escaping too much HTML.
- Fixed: Saving HTML in credits field.
- Improved: Type safety in code.
- Fixed: Downgrading during a trial extending the trial period.


Version [2.4.5] - Released on 2025-09-30
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

Version [2.4.4] - Released on 2025-09-17
- Fixed: Saving email templates without stripping html
- New: Option to allow site owners to edit users on their site
- Fixed: Invoices not loading when logo is not set
- Fixed: Verify DNS settings when using a reverse proxy
- Improved: Lazy load limitations for better performance and compatibility
- New: Add Admin Notice if sunrise.php is not setup
- New: Option to not always create www. subdomains with hosting integrations
- Improved: Plugin renamed to Ultimate Multisite

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
- Improved: Prevent downgrading a plan if the post type would be over the limit
- Fixed: Styles on thank you page of legacy checkout

Version [2.3.4] - Released on 2024-01-31
- Fixed: Unable to check out with any payment gateway
- Fixed: Warning Undefined global variable $pagenow

Version [2.3.3] - Released on 2024-01-29

- Improved: Plugin renamed to Multisite Ultimate
- Removed: Enforcement of paid license
- Fixed: Incompatibilities with WordPress 6.7 and i18n timing
- Improved: Reduced plugin size by removing many unnecessary files and shrinking images

For the complete changelog history, visit: https://github.com/superdav42/multisite-ultimate/releases
