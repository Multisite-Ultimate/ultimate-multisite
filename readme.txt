=== Multisite Ultimate ===
Contributors: aanduque, superdav42 
Donate link: https://github.com/sponsors/superdav42/
Tags: multisite, waas, membership, domain-mapping, recurring payments, subscription
Requires at least: 5.3

Requires PHP: 7.4.30
Tested up to: 6.8
Stable tag: 2.4.0
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

Version [2.4.0] - Released on 2025-XX-XX
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

Version [2.3.2] - Released on 2023-12-05

- Improved: Ensure the initialization of the required class params during core updates verification to prevent errors in some environments
- Fixed: Make sure the amount in price variations is a float to avoid issues with the currency formatting
- Fixed: Ensure that the 'wu_original_cart' metadata is consistently set in payment during checkout process

Version [2.3.1] - Released on 2023-11-21

- Improved: Remove Freemius SDK from the plugin and add our own license validation
- Fixed: Remove double slash from Cloudways API calls, avoiding request rejection

Version [2.3.0] - Released on 2023-11-07

- Added: Allow the addition of custom meta fields in the customer edit page
- Improved: Change the WP_Ultimo\Helpers\Sender::email_sender() calls to use as a static method only
- Improved: Add more translated strings for Spanish, Brazilian Portuguese, and French
- Improved: Improve PHP 8.2 compatibility
- Fixed: Ensure scoped autoload dependencies with composer autoload
- Fixed: Some webhook events were not being triggered during the creation or update process
- Fixed: Bind the amount of the price variations to another field in product admin page to avoid errors with some currencies

Version 2.2.3 - Released on 2023-10-25

- Fixed: Resolved issues with certain popup form submissions failing due to an error in retrieving the form ID attribute

Version 2.2.2 - Released on 2023-10-24

- Improved: Avoid errors during site data duplication process
- Fixed: The invoices PDF loading
- Fixed: Errors not showing in form modals
- Fixed: Ensure the correct period is used in forms where the period switcher is in a upcoming step

Version 2.2.1 - Released on 2023-10-16

- Fixed: Ensure the default public title exhibition of all payment gateways in pdf invoices
- Fixed: Scope the mPDF dependency to avoid conflicts with other plugins
- Fixed: checkout forms duplicate feature
- Fixed: Avoid create a duplicated user during a site duplication with multiple accounts enabled

Version 2.2.0 - Released on 2023-09-28

- Added: PHP 8.2 compatibility
- Added: Webhook errors stack trace on logs
- Improved: Use webhook event name instead of event slug in the create webhook popup labels
- Improved: Removed unnecessary params in class WP_Ultimo\Compat\Multiple_Accounts_Compat
- Improved: Scope PSR classes to avoid conflicts with other plugins
- Improved: Ensure that the thank you page reloads after the pending site is created
- Improved: Ensure Domain::get_blog_id() method returns the correct type
- Improved: Allow float values in discount codes
- Improved: Allow discount codes with two or more characters in the code
- Improved: Validate user email and username in all steps in a multi-step checkout form
- Fixed: Login getting the right user via email in multiple accounts compat
- Fixed: Multiple account user query to avoid MySQL query errors
- Fixed: Pre-selected products field not loading at checkout form initialization
- Fixed: Pre-selected products field avoiding the auto-submit of the checkout form
- Fixed: Do not persist useremail in object cache on multiple accounts compat
- Fixed: Lost password redirection in subsites
- Fixed: Serverpilot integration instructions
- Fixed: Runcloud integration instructions
- Fixed: Remove the ID field from new database items to be added to avoid errors with auto-increment
- Fixed: Get correct product variation in Line_Item::get_product() method
- Fixed: Dismissal of the affiliation message
- Fixed: Keep custom body classes in customer-facing admin pages
- Fixed: Keep site title during template switch
- Fixed: Customer template in Selectize search

Version 2.1.5 - Released on 2023-09-01

- Fixed: Error preventing bulk delete popup and pending payment popup from loading

Version 2.1.4 - Released on 2023-08-29

- Added: New webhooks for Payment, Costumer and Membership
- Added: Select templates by categories in templates selection field on forms
- Added: Option to show all or owned sites on My Sites block
- Added: Divi Builder compatibility in page edit screen
- Added: Filter `wu_bulk_action_function_prefix` on `process_bulk_action()` method
- Added: Better messages for membership downgrade via PayPal
- Added: Remove jQuery from legacy-signup, template-previewer, thank-you.js, visit-counter.js, wubox.js and vue-apps.js files
- Fixed: Domain mapping allowing uppercase
- Fixed: Eliminates the fake email loophole and enables users to register on different subsites using the same email
- Fixed: Fixes the resetting password process
- Fixed: Allow user verification on wp-activate.php page
- Fixed: PayPal renew payments not showing line items
- Fixed: Change the status in membership and payment schemas for rest api validation
- Fixed: Fix the detection of pre-selected products in the checkout form
- Fixed: Rrlencode on my sites widget URL
- Fixed: Plan frequencies and duration on migration from v1 to v2
- Fixed: Add the username in login error handler message

Version 2.1.3 - Released on 2023-08-09

- Added: WordPress 6.3 compatibility.
- Added: Implemented periodic cleanup for possible forgotten pending sites from memberships.
- Added: Enabled membership addon products cancellation.
- Added: Synchronized membership products and prices with gateway subscriptions.
- Changed: Now ensures that the site is a customer site before syncing the site's plugin limitations.
- Fixed: Updated the checkout process to get all fields for search and replace in template sites.
- Fixed: Corrected the display of product limits in the legacy pricing table.
- Fixed: Added validation for site names to allow hyphens.
- Fixed: Addressed possible PayPal API errors during the checkout process.

Version 2.1.2 - Released on 2023-07-13

- Added: Loaded WordPress core screen API on checkout forms using the legacy template to avoid possible errors with other plugins.
- Added: A filter called "wp_ultimo_my_sites_show_add_new" to allow developers to hide the Add New Site button.
- Added: Allowed the use of the wu_restricted_content shortcode with checkout form current products.
- Added: Support for Kenyan Shilling currency.
- Added: Automatically tried to add the current country and state to the checkout form based on IP address.
- Added: Allowed customers to cancel the current payment gateway in use for a membership.
- Added: Allowed customers to cancel their membership from the account page.
- Added: An option to set a log level to write WP Ultimo logs (defaults to the current PHP report level).
- Added: A label in our invoices informing the customer when the taxes are included in the price.
- Added: Allowed users to select all template sites in the checkout form.
- Added: A CSS class to pricing table items to allow custom styling.
- Added: Defined variables in our checkout class.
- Changed: Removed the Add New Site button if the Enable Multiple Sites Per Membership and Enable Multiple Memberships options are disabled in settings.
- Changed: Allowed the use of the product_id variable in place of plan_id in the wu_restricted_content shortcode.
- Changed: Moved cache flushing to a new class to better manage cache processes.
- Changed: Added an optional label and description to the selectizer default template.
- Changed: Increased the number of tries for checking SSL while on WPMUDev.
- Changed: Cancelled the gateway subscription when the membership is saved as canceled or when deleted.
- Changed: Blocked sites with a canceled membership from being accessed at the frontend after the membership expiration.
- Changed: Added the Stripe Billing Portal as an optional feature in the gateway settings.
- Changed: Allowed the network admin to see the customer Stripe Billing Portal.
- Changed: Finished the checkout process with Stripe without the need to wait for the webhook.
- Changed: Removed support for subdomains in the Cloudways integration due to SSL generation limitations.
- Changed: Avoided performance issues by restricting the number of sites returned from the get_blogs_of_user function in WordPress core to 40 for network administrators.
- Changed: Avoided server cache during the SSO process.
- Fixed: Added models on global object cache groups to prevent errors with Redis and Memcached object cache.
- Fixed: Display of disk limits on the front end.
- Fixed: The legacy wu_restricted_content shortcode to correctly work with Ultimo v2.
- Fixed: Cleared cache upon maintenance mode toggling so that the new status is applied immediately.
- Fixed: Cloudways SSL should only be requested if the integration is active.
- Fixed: The use of the www. prefix on Runcloud domain mapping.
- Fixed: Removed singleton trait usage from the class base host provider and allocated it in each host provider class instead.
- Fixed: Ensured the setup of our blocks before showing them as metaboxes on admin pages.
- Fixed: Allowed ajax requests when the site is locked to avoid errors with admin requests.
- Fixed: The issue where allowed countries were not being loaded in checkout forms.
- Fixed: Tax rates were not loading on the tax list if the default category is deleted.
- Fixed: Added billing address to renewal payments to get the correct tax rates.
- Fixed: Removed backslashes when retrieving a model's content, avoiding double backslashes in model content and emails.
- Fixed: Added paragraphs and line breaks back when retrieving model content.
- Fixed: Switched to the main site in the wu_get_membership_customers function.
- Fixed: Disabled WP Core application password on WP Ultimo Routes so we can use our API keys.
- Fixed: Added customer address during Stripe Customer creation to avoid errors due to some Stripe regulations.
- Fixed: Added the description field in Stripe Payment Intent to avoid errors due to some Stripe regulations.
- Fixed: Removed HTML content from the author label on the limits tab to prevent JS errors on load.
- Fixed: Upgraded old database tables to the current version that allows null values on datetime fields.
- Fixed: Allowed deselecting the legacy "featured" option on the product edit page.
- Fixed: Removed a possible trailing dot from CNAME records to avoid validation failure.
- Fixed: A warning when the limit property is not found in the object.
- Fixed: Email Bcc header formatting to avoid errors with some email clients.
- Fixed: The Stripe product ID with the wrong name.
- Fixed: Set the free gateway on membership during the downgrade process to a free plan.
- Fixed: Limitation calculation during the save process.
- Fixed: site publishing being bypassed despite the "Allow Trials without Payment Method" setting.
- Fixed: Legacy params warnings due to direct usage in the products table.
- Fixed: Supported child themes as forced active themes for limitations.
- Fixed: Instantiated our Server Pilot Integration class.
- Fixed: Ensured async site publication when used.

Version 2.1.1 - Released on 2023-05-01

- Added: the filter "wu_checkout_add_field_{field_type}" to allow developers to change form fields in the checkout form
- Added: an option called "checkout_page" to set a page ID to redirect to `wu_templates_list` shortcode
- Added: Compatibility with PerfMatters plugin on registration pages
- Changed: Improved the limitation merge methods on plugins and themes permission rules
- Changed: Checks permissions before the deletion of sites, payments, and memberships, during customer deletion
- Changed: Checks permissions before the re-assignment of sites, payments, and memberships, during customer deletion
- Changed: Filter WP Ultimo admin bar menu items by capabilities
- Changed: Filter financial data widgets on the dashboard by capabilities
- Changed: Filter side panel buttons on the settings admin page by capabilities
- Changed: Disabled the save settings button for unauthorized users
- Changed: Check if the Billing Country field exists before setting the v-model
- Fixed: Allow free addons with free plans on membership update
- Fixed: Fixed rollback fatal error
- Fixed: Error where non-customer-owned sites were being affected by permissions
- Fixed: A fatal error triggered by generating a membership URL for non-customer-owned sites
- Fixed: Allow accentuated and other special chars when saving a checkout session
- Fixed: Data replacement on serialized objects during site duplication
- Fixed: Filter WP Ultimo checkout forms menu by `wu_read_memberships` capability
- Fixed: Permissions required to invite customers in `add_new_customer admin` form
- Fixed: Permissions required to transfer sites in the admin page
- Fixed: Permissions required for redirecting to the edit broadcast admin page after creating a new broadcast
- Fixed: Fix the login block redirecting to the default WordPress login page after an error
- Fixed: Fix capability checks when redirecting to the site edit admin page after site creation
- Fixed: Add setup fees to the first stripe invoice in case of trials to include it in the first payment
- Fixed: Cloudways SSL domain syncing methods
- Fixed: Redirect to the checkout page after selecting a site template on a `wu_templates_list` shortcode
- Fixed: Build process changed to guarantee backwards compatibility with php 7.4
- Fixed: Duplicate emails and other small inconsistencies after checkout when Stripe is used
- Fixed: Get the original cart order from the payment before processing checkout instead of creating a new one from the request data
- Fixed: Allow downgrade from free memberships to plans with a duration smaller than 1 month

Version 2.1.0 - Released on 2023-04-04

- Added: an option to export customer data as a CSV file in the customer list admin page
- Added: the `wu_add_product_setup_fee_line_item` filter to the cart Setup Fee line item
- Added: a new suite of Cypress tests to cover creating new sites from within the admin panel
- Added: Replace the PHP Unit task runner with Pest
- Added: Replace Cypress parallelization services with our self-hosted instance
- Added: CI actions to validate PR titles
- Added: Pull Request template with CI action to verify if checklists were properly followed
- Added: Removed the id `server-size-0-description-0` from the price description element of the order-bump/simple field template
- Added: dependabot settings for npm, composer, and GitHub actions alerts
- Added: a `display_product_description` field to the Order Bump field
- Added: `wu_after_switch_template` action (present in v1)
- Added: Github Action to test PHP 7.4 compatibility after Rector runs
- Added: the necessary adjustments to the samba.yml files to make the repository compatible with the new samba images for PHP 8.1
- Added: new ESLint rules from our shared library
- Added: new build tasks using the gulper library file
- Added: (initial) CHANGELOG.md file
- Added: the WP Ultimo plugin version as a const on the class `\WP_Ultimo`, allowing for access without the need for an instance - which might not yet exist
- Added: Improved compatibility with the last Rank Math release, avoiding plugin auto-activation during the site creation process when active on the main site
- Added: generation of a new .pot file containing countries, states, and cities using the new text-domain wp-ultimo-locations
- Added: user license data to System Info
- Added: NOBLOREDIRECT constant to System Info
- Added: a "catch-all" error handler around problematic hooks on the template previewer to prevent minor errors from breaking it
- Added: a filter to allow developers to change the "Unlock to Upgrade" URL
- Added: Allows overriding checkout template elements
- Added: capability verification to delete form handler on Admin Edit Pages to allow this action for Support Agents addon
- Added: filter `wu_list_row_actions` to row actions on Admin List Pages
- Added: a filter to the featured site image cropping size
- Added: the Add-ons link to the main plugin menu
- Changed: Redirect to the current payment page on the Paypal confirmation process instead of the main register page
- Changed: Reduced the amount of data sent to create a new order in checkout to prevent PHP warnings
- Changed: Improved PayPal confirmation values and trial message
- Changed: Deprecated the use of the property `$version` on the class `\WP_Ultimo` in favor of a class const
- Changed: Improved checkout error handler to display actual field names from the current checkout form
- Changed: Removed Cloudflare integration alert as it's no longer required (<https://blog.cloudflare.com/wildcard-proxy-for-everyone/>)
- Changed: Redirect the user to update the page when clicking to unlock a plugin or theme not allowed for the current membership
- Changed: Remove the X-Frame-Options header while in the template previewer
- Changed: Add a new logger class to better track errors
- Fixed: Event visualization not showing Payload
- Fixed: Breakage in limitation merging for sites, memberships, and products
- Fixed: Performance issues with the Freemius SDK, especially on larger networks
- Fixed: Incompatibility with UI Press Lite causing fatal errors on the login page
- Fixed: PayPal auto-renewal toggle not activating after IPN recurring profile created message
- Fixed: Fixed handling Checkout Forms with no fields
- Fixed: Replaced deprecated `wpmu_new_blog` used on our Rank Math compatibility code
- Fixed: Additional offset checks when trying to access Form fields
- Fixed: Remove the current site "Admin Panel" link from the Account page when the "show_admin_link" is set to false
- Fixed: Error when renewing a membership manually created by an admin
- Fixed: Products with "Contact Us" pricing type of "Contact Us" changing to free on save
- Fixed: Membership issues with activation on PayPal profile creation
- Fixed: Visits counter script is always loaded, ignoring the status of the actual setting controlling it
- Fixed: Ensure array value when searching for user email errors for multiple account features to avoid PHP warnings
- Fixed: Enforce site ownership before applying a new domain mapping via the frontend form
- Fixed: Prevent globally-scoped callables from being used as form field attributes
- Fixed: Replaced deprecated `wpmu_new_blog` used by WP E-Signature, which prevents sub-site creation from completing properly
- Fixed: Fixed the URL returned to register and other wp ultimo pages
- Fixed: Fixed checkout triggered by a shortcode template page not skipping the template selection step
- Fixed: Fixed checkout triggered by a shortcode pricing table page not preselecting the desired plan
- Fixed: Clears session for the sign-up process after the registration is successfully over
- Fixed: Add URLs as an exception to the white-labeling feature to avoid breakage with WordPress URL passes through the `sprintf` function
- Fixed: Fixed incompatibility with WP CLI when the `--skip-plugins` is present and the context is a customer sub-site
- Fixed: Improved the get method of items displayed in model selectors, to avoid not loading all available items
- Fixed: critical error while rendering pricing table without an active product
- Fixed: Domain Mapping and SSO inconsistencies in PHP 8.0 environments
- Fixed: Allow `wu_get_*` functions to be used as form field attributes callback
- Fixed: Fixed the display of tooltips on checkout form fields
- Fixed: Added an extra check to make sure product variations exist before replacing the cart product with it
- Fixed: Filters already defined default pages from selection in settings
- Fixed: Remove `height: 100%` from the legacy-shortcode.css file
- Fixed: Ensures that inactive sites are not included in the template previewer list
- Fixed: Ensures that inactive sites are not included in the template selector
- Fixed: Ensures the main site is always added to the My Sites list for super admins
- Fixed: Fix Stripe scripts being loaded even with the payment method disabled
- Fixed: Renewal invoices from Stripe webhooks without tax and discount
- Fixed: Ensure the cart is correctly built on checkout in cases where payment gateway webhooks complete the payment before we finish the process
- Fixed: Ensures that we only show the migration pending notice to networks there were not migrated yet
- Fixed: Fix template overrides
- Fixed: Changed the response code when subsite maintenance mode is on to 503, instead of 500
- Fixed: Fix email broadcast to target products
- Fixed: Use the default WordPress session on Checkout Form Editor to avoid errors with cookie sizes
- Fixed: Stripe refund amount received in the webhook call
- Fixed: gateway confirmation redirection used on Stripe Checkout and PayPal for update form
- Fixed: Stripe Checkout billing cycle anchor on downgrades
- Fixed: Added the customer data to the payment renewal event payload so we can send customer emails
- Fixed: Added the customer data to the mapped domain creation event payload so we can send customer emails
- Fixed: Ensures that we only show the migration pending notice to networks there were not migrated yet
- Fixed: Template overrides not being correctly loaded
- Fixed: Changed the response code when subsite maintenance mode is on to 503, instead of 500
- Fixed: Avoid pending site duplicates after creating an account
- Fixed: Set membership as trialing when creating without a payment method and user verification
- Fixed: Remove membership internal data from events
- Fixed: Remove the customer from sites different from the customer-owned type.
- Fixed: The test to create a new contact us product plan
- Fixed: Product description unexpected backslashes being added when apostrophe, backslash, single and double quotes were used
- Fixed: Limits and quotas widget to show limits only if limitations are enabled

Version 2.0.23 - Released on 2022-11-22

* Added: Custom Thank You page text for sites area when membership has no sites attached;
* Added: PayPal payment description with trial and recurring payment specification;
* Added: Network logo at PayPal checkout;
* Improvement: site_url and site_title removed as required fields in checkout form, allowing the creation of memberships without sites (both fields are now optional);
* Improvement: PayPal confirm page styles improved;
* Improvement: PayPal now accepts non recurring discounts and fees with trial period in membership;
* Improvement: WP Engine integration now also adds subdomains (we recommend the use of wildcard as first option);
* Improvement: Restrict site creation to active and trialing memberships;
* Improvement: Length limit of 63 chars in site_url checkout field according to DNS specifications;
* Improvement: Added CUSTOMER_ID, CUSTOMER_EMAIL, MEMBERSHIP_AMOUNT, ORDER_ID placeholders on Thank You page script field;
* Improvement: Changed ORDER_PRODUCTS placeholder on Thank You page scripts to show the list of products ids instead of the product hash;
* Fix: WP Engine integration install;
* Fix: Fix the use of wu_append_preview filter on plugins compatibility file;
* Fix: Allow removal of all categories on a template site;

Version 2.0.22 - Released on 2022-10-26

* UI Blocks
  * Added: Option to add a custom page to redirect user after click in a site in "My Sites" block;
  * Added: Option to redirect to the WP admin dashboard after click in a site in "My Sites" block;
  * Added: Option to show the site WP admin link on "Current Site" block;
  * Added: Option to limit the invoices showed in the "Invoices" block;
  * Added: Option to select a custom page to redirect the customer if de site is deleted in "Site Actions" block;
  * Added: Options to hide some links in "Site Actions" block;
  * Improvement: UI Blocks adjusted to load on main site with current membership set for customer;
  * Improvement: Show pending sites on "My Sites" block;
* Added: Option in WP Ultimo sites settings to allow the selection of a page on main site to create a new site;
* Added: Option in WP Ultimo membership settings to allow the selection of a page on main site to update membership;
* Added: Optional constant WU_GATEWAY_LISTENER_URL to allow us to change the URL to use on gateway webhooks (usefull on dev environments);
* Added: Option to filter by trialing memberships on memberships list page;
* Improvement: Paypal confirmation view with correct styles in table and button;
* Improvement: Get all published pages on pages select input;
* Improvement: Allow multiple sites generated by username as url/path (a number will be inserted after the username);
* Improvement: Get product line items on payments generated by Stripe webhook calls;
* Fix: Legacy checkout template styles on Wordpress 6+;
* Fix: Paypal gateway with trial period in membership;
* Fix: Multistep form not working correctly with fields in query params;
* Fix: Payment process after trial period not working in payment form;
* Fix: Product variation not setted in cart by current payment;
* Fix: Stripe Checkout gateway with non recurring discount coupon and trials in same order;
* Fix: Paypal gateway with trial period in membership;
* Fix: MRR calculated value on Ultimo dashboard for yearly memberships;
* Fix: Payment link on admin payment edit page;
* Fix: Template switch using default template sites;

Version 2.0.21 - Released on 2022-10-11

* Fix: Discont code error while finishing checkout;

Version 2.0.20 - Released on 2022-09-30

* Added: Security mode to deactivate all plugins except WP Ultimo and mu-plugins at once and reactivate after disable;
* Added: Allow customers to update the membership to plans and variations with different periods;
* Added: Allow customers to select one of their sites when creating a new one;
* Added: Error message when customers access the "add user" page over users limit;
* Added: wu_return_url filter, allowing custom redirections after checkout process;
* Improvement: New payments from manual gateway are now generated by current membership;
* Improvement: Elementor compatibility on mapped sites;
* Improvement: RankMath and RankMath Pro compatibility on main site;
* Improvement: WP Hide Pro compatibility on site previewer;
* Improvement: Membership limits with different product amounts;
* Improvement: Limits merge sum with multiple products;
* Fix: Deactivate site option on admin site edit page;
* Fix: Payment form checkout validation;
* Fix: Steps form field style on legacy template;
* Fix: Webhooks now work as expected;
* Fix: Checkout error when selected product forces a site as template and template selector is showing in current form;

Version 2.0.19 - Released on 2022-08-31

* Added: Event to send email when a new payment is created via cron in WP Ultimo (trial without payment method and manual payments for now);
* Added: Email template for new payment created event;
* Added: Popup after login with customer pending payments if exist;
* Added: Link to payment form on invoices element;
* Added: Support for CJK (Chinese, Japanese, and Korean) characters in invoice text;
* Added: Stripe gateways payment line items on WP Ultimo payment created on renewals;
* Added: wu_setup_step_done_name filter on setup wizard;
* Improvement: Form steps filtering for fields to hide when autosubmit;
* Fix: Unexpected behavior when advance form steps in forms like the create new site;
* Fix: Template site limits verification on create new site form;
* Fix: Post type limitation on legacy pricing table;
* Fix: New site form template selector styles;
* Fix: Limitations merge on products and memberships;

Version 2.0.18 - Released on 2022-08-05

* Added: Current currency in use on Stripe and StripeCheckout product gateway;
* Added: wu_pre_save_settings filter to allow data change before save settings;
* Added: Stripe and Stripe Checkout api keys verification on save settings;
* Added: Show messages to customer on forms according sistem and account limitations;
* Added: Current membership selected on Current class;
* Improvement: Set trial status according membership data on save process;
* Improvement: Delete object cache key of a model after save process;
* Improvement: Non recurring coupon value on stripe checkout;
* Improvement: New session system on forms;
* Improvement: Stripe and Stripe Checkout calls, ensuring correct API keys in use;
* Improvement: Create new payment with Stripe and Stripe Checkout only on "charge.succeeded" webhook event;
* Improvement: Check if membership uses Stripe or Stripe Checkout gateway on webhook calls to prevent duplicated processes which can cause subscription cancelation on gateway;
* Improvement: Public API load order to by present on setup wizard;
* Improvement: get_broadcast_targets method return on class Broadcast;
* Improvement: Multiple account with woocomerce billing_address value;
* Fix: Subtotal value on cart items;
* Fix: Stripe card input styles;
* Fix: Check the available template sites for selected products;
* Fix: Correct build the cart with trial period on checkout process;
* Fix: Template switch form;
* Fix: Stripe and Stripe Checkout error code on WP_Error;
* Fix: Stripe and Stripe Checkout webhook listener url shown in payment settings;
* Fix: Stripe and Stripe Checkout production mode setting value on save;

Version 2.0.17 - Released on 2022-07-04

* fix: New site creation not working on customer form;

Version 2.0.16 - Released on 2022-07-01

* Improvement: Allow the auto-submission of fields with the steps field present in form;
* Improvement: Do not calculate prorate in upgrades if membership is in trial period;
* Improvement: PayPal gateway rebuilt to run updates on memberships;
* Improvement: Set Stripe Webhook mode by request mode (live or sandbox);
* Fix: Checkout form editor not showing the input and steps settings;
* Fix: Step being duplicate when updating the step id in checkout form editor;
* Fix: Paypal gateway not completing the checkout process on recurring payments;
* Internal: Additional tests for Wordpress 6.0;
* Internal: Improved the multistep checkout test;
* Internal: Improved the model generators for better tests;
* Internal: Improved the code to check sent emails in automated tests;

Version 2.0.15 - Released on 2022-06-15

* Added: Currency Saudi Riyal
* Improvement: Removes unlisted countries from the billing address checkout field when using the "Restrict by country" option;
* Improvement: Disables the "Restrict by country" toggle when saving the form without allowed countries;
* Improvement: Improves the addition of billing address fields by allowing the removal of fields through the "wu_billing_address_fields" filter and avoiding error in the use of this filter;
* Improvement: Checks if payment status is completed when building the cart to prevent error with Ultimo defining the cart as "retry";
* Improvement: Verifies if the cart has a future value to be paid to better handle downgrades;
* Improvement: Checks if subscription is not already cancelled on Stripe and Stripe Checkout gateways before trying to cancel;
* Improvement: Improvement: Changes stripe.js handlers to better code readability and to follow Stripe recommendations;
* Improvement: Adds a line item in cart in case of downgrade to remove the current value from payment on swap schedule;
* Improvement: Calculates prorate credit when upgrading membership;
* Improvement: Changes the method that checks if we need to collect payments to consider the possibility of future payments before returning false to handle downgrade cases;
* Improvement: Changes next billing charge date method to consider downgrades with scheduled swap;
* Improvement: Schedules swap with Stripe and Stripe Checkout gateway to handle downgrades to paid plans;
* Improvement: Handles gateway subscription cancelation when membership gateway data has change;
* Improvement: Checks for Stripe webhooks in Stripe Checkout gateway;
* Improvement: Ensures data type of test_mode value to bool on Stripe, Stripe Checkout and Paypal to prevent errors;
* Fix: Added currency value when creating the cart from a payment or membership to avoid errors on gateway processes as with GoCardless;
* Fix: Mapped domains redirect not working;
* Fix: Problem with gateways as GoCardless that needs the currency value to finish checkout process;
* Fix: Limit site users by role not working;
* Fix: Problem with the broadcast message being delivered just to the first customer;
* Fix: Free membership not being activated even with email verification disabled;
* Fix: SSO not working on wp-admin page with mapped domains;
* Fix: Stripe saved cards not working;
* Fix: Downgrade cart not being correctly built when new plan is not free;
* Fix: Correctly define a cart as "retry" and postpone the payment verification when building it to prevent some errors on checkout validation process;
* Fix: Get the enable multiple membership value config from settings value;
* Fix: Allow updates with current plan on trial period;
* Fix: Stripe Checkout gateway id with wrong value on get_or_create_customer method;
* Fix: Stripe maybe_cancel_old_subscriptions method not working;
* Fix: Stripe Checkout sandbox toggle not working on settings;
* Fix: Site publish on trials with payment method;
* Fix: Cart build with membership causing error due currency error;
* Internal: Fix delete product test;

Version 2.0.14 - Released on 2022-05-27

* Added: Message on email verification if user is not logged in, with a link to login page including a redirection back to email verification;
* Improvement: Hide go back checkout button if it is in second step, the first is a plan selection and if plan is pre-selected in URL;
* Improvement: Grouped go back and next buttons in same line in checkout form;
* Improvement: Suppress email change notification on page duplication causing confusion about the current admin in template sites;
* Improvement: Upgrade from free to another free plan capability;
* Improvement: Error message on integration wizard containing missing constants on wp-admin.php;
* Improvement: Woocommerce performance when listing sites;
* Fix: Stripe webhook not being processed by ultimo;
* Fix: Go back action in checkout keeps previous step with loading block screen;
* Fix: Currency in payment with wrong symbol;
* Fix: Stripe checkout not redirecting in Safari;
* Fix: Stripe gateway causing error when using trials without payment method;
* Fix: Error with domain field not accepting dots when trying to create a new site via network admin page;
* Fix: Value "enable_custom_login_page" added with value 1 on ultimo install process to avoid errors on ultimo setup page;
* Fix: Downgrade to a free plan activating the current paid plan and giving lifetime membership if no payment accepted before;
* Fix: Payment invoice placeholders;
* Internal: Fix composer Symfony package versions;

Version 2.0.13 - Released on 2022-05-06

* Improvement: Set the first period in period selector as pre-selected if durations is not added before, preventing the mixing of products with different period if using the selector;
* Improvement: Removed verification for existing signatures in v1, reducing the chance of false negatives as the need to run migration process;
* Fixed: Stripe card check causing error with multi step forms wen using the product slug in url;
* Fixed: Product variations not being identified and not setting the price on cart overview;
* Fix: Get the correct address field in form to set the cart regardless of multi step or single step form type;
* Fix: Stripe checkout not working on Firefox;
* Fix: Delete option on broadcast admin list table;
* Fix: Broadcast list table causing fatal due error on customers column;
* Fix: Email footer with subsite url on link instead of main site;

Version 2.0.12 - Released on 2022-04-25

* Added: Created the wu_before_form_submitted javascript filter to allow bypass the checkout form before submit;
* Added: Sunrise.php data on WP Ultimo system info page;
* Improvement: Define COOKIE_DOMAIN constant on domain mapped sites to prevent cookie related errors;
* Improvement: Search models by hash improving the UX on admin forms and preventing errors when creating sites for customers on network admin dashboard;
* Fixed: Added a default setup to use with Stripe Portal to prevent errors when not configured on Stripe account;
* Fixed: Makes sure if card is valid before start the account creation process using Stripe Gateway;
* Fixed: Check if auth_redirect and wp_set_auth_cookie functions exist before define;
* Fixed: Template previewer error when product is not selected in checkout form;
* Fixed: Error on remaining sites calculation if limitation is not set;
* Fixed: Check if there's domain options available on create site form;
* Fixed: Prevent errors with variable types defined on limits;
* Fixed: Correct check the status of a pending site for a membership on thank you page;
* Fixed: Billing address not being saved on multi step checkout form when not in last step;
* Fixed: Gutenberg blocks not being loaded;
* Fixed: Login page not working on blocked sites;
* Fixed: Error on some checkout processes due pending site check;
* Internal: Improved our test structure with cypress to help catching error before releases;

Version 2.0.11 - Released on 2022-04-09

* Important
  * Fixed: Plugin & Theme Limitations not being loaded, leading to plugins not being hidden, or auto-activated after registration;
  * Fixed: Site duplicator now deals with plugins that have custom database tables with foreign key constraints - for example, Bookly;
  * Fixed: Incompatibility between Avada and mapped domains;
  * Fixed: Incompatibility between Avada and template previewer;
  * Fixed: Incompatibility with FluentCRM breaking the registration flow;
  * Fixed: Domain mapping issues on previous build;
  * Fixed: Payments pending on trial plans;
  * Fixed: Products with wrong duration after checkout;
  * Fixed: Sites created in double in some circumstances - specially when using Stripe & Stripe Checkout;
  * Added: A completely re-designed and re-written SSO module, built to work in a higher level of abstraction to support all current and future possible use cases; It deals natively with:
    * Security: there's a token exchange protocol that verifies both sides of the auth process;
    * Cross-scheme Auth: When we are not able to access remote cookies due to different schemes being used, we force a regular redirect flow to authenticate the customer regardless;
    * Admin Panel Access: Prevents the auth_redirect function from sending the request to wp-login.php before SSO has a chance to kick in;
    * Auth for different Domain Options: SSO no longer focuses on mapped domains only. It gets triggered anytime there's a mismatch between the target domain and the main network domain. This allows it to work with sites that were registered using different domain options offered on checkout;
    * Loading Screen: the new SSO offers a setting that adds a loading overlay when SSO is being performed on the front-end;
    * Support to Incognito Mode: most browsers prevent cookies from being set from third-parties, nowadays. Our SSO detects incognito mode and forces a full redirect, instead of trying to authenticate directly with the verify code;
  * Added: Placeholders on Thank You page snippet code editor, to pass values to conversion snippets;
  * Added: Country classes with state and city lists to allow for more granular control over how taxes apply territorially, as well as to guarantee that valid billing address info is entered during checkout. At the moment, the following countries are supported: US, GB, BR, FR, RU, DE, NE, ES, CA, CN, ZA (this list was devised based on our current customer base, new countries can be added as requested).
  * Added: REST API field requirements and descriptions are now compiled and saved as static files at build time. This is done because we use reflection on PHPDocBlocks to generate the documentation of the fields, and comments might not be available if op_cache is enabled on production, causing the REST API to break;
  * Improvement: Add CNAME records from Cloudflare to the DNS checking results, in addition to A and AAAA;
  * Improvement: Updated DNS lib to prevent memory leaks when checking for DNS;
  * Improvement: Adds fatal error catcher when the DNS resolver runs into a memory limit error, although this no longer happens due to the above fix;
  * Improvement: Using CSS grid to lay fields on the checkout field instead of flex/float. This cleaned up the fields markup a good bit and makes it more customizable. By default, the checkout form is a two-column grid, with fields spanning the two columns;
  * Improvement: Better responsiveness on the checkout form, resulting from the use of CSS grid;
  * Improvement: Replaced the old hacky implementation of the Site URL prefix and suffix blocks (disabled inputs) with a proper flex block with a prefix and suffix element;
  * Improvement: Checkout field blocks use less opinionated HTML tags (div, instead of p) to maintain semantic value and escape the default spacing CSS rules applied to paragraphs;
* Other
  * Fixed: Negative values on sign ups by country widget;
  * Fixed: Remove the email error message on sign up validation;
  * Fixed: Taxable toggle on product update;
  * Fixed: Discount code migrator not bypassing validation rules;
  * Fixed: Error on site creation process passing the customer rules in the main site;
  * Fixed: Makes sure the auto-submittable script is only added after wu-checkout was loaded;
  * Added: Filter available templates on template viewer with selected products in checkout form;
  * Added: Option to add a page on main site to redirect customer in blocked sites;
  * Added: Hide customer sites from network admin top bar;
  * Added: Created the wu_bypass_unset_current_user filter to allow developers to bypass the user unset on multiple account feature;
  * Added: Possibility to see and change on customer admin page,  the customer custom metadata set when user sign up.
  * Added: An public api to customer meta data that handles sign up form titles and types of fields;
  * Added: Memory trap to avoid memory limit fatal errors in some cases;
  * Added: Support for Jetpack plugin in mapped domains;
  * Added: Stripe Portal for customer payment manage;
  * Added: Option to add a custom redirect path in Login block;
  * Added: New image upload field layout with the stacked option;
  * Improvement: New field for company logo on settings;
  * Improvement: Block frontend site when a membership is not active;
  * Improvement: sunrise.php install step on WP Ultimo Install Wizard;
  * Improvement: better define of SUNRISE constant on wp-config.php on WP Ultimo Install Wizard;
  * Improvement: Better UX on thank you page, showing if the site is in creation process;
  * Improvement: Breaks the gigantic functions/helper.php file into lots of different small files with specific public apis, allowing us to make sure we only load what we really need in a sunrise time;
  * Improvement: Adds a sunrise meta file to control whether or not we need to run ultimo tasks when Ultimo is disabled or not present;
  * Improvement: First step in the direction of removing jQuery as a checkout form dependency given by dropping jQuery Migrate as a dependency;
* Internal
  * Internal: Replaced all development scripts, build tasks, and more with the internal development library called MPB;
  * Internal: Adds the Query Monitor panels to help debug issues while developing WP Ultimo core;
  * Internal: Adds the development sandbox toolkit that allows developers to run and listen to particular events in a isolated context. Useful for timing how long a given hook takes to run, or to trigger build tasks that rely on a real WordPress installation running Ultimo to work;
  * Internal: Updated node dependencies to their latest versions;
  * Internal: Switched Tailwind to JIT mode, to save precious KBs on the generated framework.css file;
  * Internal: Removed PHP Scoper as a composer dependency (it is now handled directly by MPB);
  * Internal: Removed unnecessary composer dependencies;
  * Internal: Updated composer dependencies to their latest versions;
  * Internal: Finally switched the composer version internally from v1 to v2;

Version 2.0.10 - Released on 2022-01-21

* Added: Workflow to automatically generate the [list of actions](https://github.com/next-press/wp-ultimo-hooks-and-filters/blob/main/wp-ultimo.md) and filters for the plugin;
* Improvement: Added extra checks on the field loops on checkout to prevent warnings;
* Improvement: Added checks to the customizer theme screen to prevent theme limitations from being bypassed;
* Improvement: cPanel integration adds alternative domain options to cPanel as sub-domains on site creation;
* Experiment: Added the SSO lax mode to deal with new browser restrictions;
* Fixed: Free memberships correctly being set as Lifetime, by default;
* Fixed: Product user roles not being applied to newly created sites;
* Fixed: User roles not being updated on up/downgrade;
* Fixed: User role restrictions being applied to editable_roles();
* Fixed: Incompatibility between Blocksy customizer and mapped domains on WP Ultimo;
* Fixed: Incompatibility between Brizy and mapped domains on WP Ultimo;
* Fixed: Capability names not matching with Support Agents options;
* Fixed: Broadcast notices not appearing on sub-site admin panels;
* Fixed: Discount Code use count not being increased if the cart total goes down to 0;
* Fixed: Conflict with Fluent Forms - where Ultimo broke the FF form edit UI;
* Fixed: Error migrating broadcast messages from v1 to v2;
* Fixed: Search and Replace not working on post titles in some cases;
* Fixed: Multiple Accounts replacing billing address with fake version even when it's not necessary;
* Fixed: Send customer address to Stripe;

Version 2.0.9 - Released on 2021-12-29

* Added: Hook wu_checkout_after_process_order added - required by the new AffiliateWP Integration;
* Added: Filters for class-current - wu_current_site_get_manage_url, wu_current_set_site, wu_current_set_customer. Useful for integrations and later front-end management functionality;
* Added: Template Switching capabilities;
* Improvement: cPanel integration now adds sub-domains when alternative domain names are offered on registration;
* Fixed: Selectizer templates not being loaded for Support Agents;

Version 2.0.8 - Released on 2021-12-21

* Added: Templates can be pre-selected using the URL format: /register/template/template_site_name_here;
* Added: Filter to change the /template/ portion of the pre-selected site template URL - "wu_template_selection_rewrite_rule_slug";
* Added: Adds the sv_SE and it_IT translations - thanks Annika Sjöberg and Edoardo Guzzi;
* Improvement: Updated the legacy template selection layout to use flex-box over older CSS rules;
* Improvement: Added a "cols" parameter to the wu_templates_list shortcode - with a default value of 3;
* Improvement: Caching results of plugin permissions on the same request to improve performance;
* Fixed: Dropdown and other elements of the template previewer page not working as expected;
* Fixed: Lazy loads the events default payloads via a callable - preventing errors during installation;
* Fixed: Changed the h1 tag on the legacy template selection layout to an h2 for SEO reasons;
* Fixed: Shortcode wu_pricing_table buttons now correctly select plans on the checkout form;
* Fixed: Order Summary containing some untranslatable strings - they are now part of the .pot file;
* Fixed: Product duplication not copying the limitations and other meta info;
* Fixed: Refactored the algo that decides if an install needs to run the migrator or not;

Version 2.0.7 - Released on 2021-12-14

* Added: Support widget added to the migrator error screen so customers can send the necessary info for the support team;
* Added: Domain hosting integrations are now handled and activated by the migrator automatically;
* Improvement: Condensed the Migrator checks into a single step so we can make sure transaction rollbacks are not affecting the results;
* Improvement: Clear domain stage logs when a domain name is deleted;
* Improvement: Added the skip_validation option to the wu_create_payment function - which is required for the migrator to work properly;
* Improvement: Adds dumb Mercator file and a Mercator load statement to the v2 sunrise file to increase compatibility with WP Ultimo after a rollback is performed;
* Fixed: Migrator now successfully migrates the gateway info for memberships;
* Fixed: The webhook listener endpoint for v1 now have dedicated logic to treat webhooks before handing it over to the new endpoint;
* Fixed: Stripe Checkout treating all payments as an upgrade, including the initial one;
* Fixed: DNS propagation check failing due to Cloudflare breaking the list of DNS entries regardless of Cloudflare being active;
* Fixed: Cloudflare DNS injection is only loaded when Cloudflare is active;

Version 2.0.6 - Released on 2021-12-07

* Added: Option to "emulate" post types to avoid having unnecessary plugins active on the main site;
* Improvement: Re-add deprecated Mercator hooks for backwards compatibility: mercator.mapping.created, mercator.mapping.updated, mercator.mapping.deleted;
* Fixed: Removed the 100 site template limitation on the [wu_templates_list] shortcode and the Template Selection field;
* Fixed: Selecting a template from the [wu_templates_list] starts the registration with the template pre-selected correctly;
* Fixed: cPanel Integration's step to check for a valid connection return success even when an "Access Denied" error had occurred;
* Fixed: Changing the field slug on the Checkout Form editor was creating a new field instead of modifying the existing one;
* Fixed: Added support for Elementor's render_widget ajax calls to prevent errors when saving pages that contain WP Ultimo elements;
* Fixed: Edge-case where some users were not able to install add-on after opening the "More Info" window;
* Fixed: Elementor editor not loading in some edge-case scenarios when Multiple Accounts was activated;
* Fixed: Legacy product pricing table behaving exactly like the pricing table of the previous version;
* Fixed: The main site was being marked as a site template and showing up on template selection fields - this fix only applies to newly migrated networks but added an extra check to prevent the main site from being displayed as a template option;
* Fixed: Hard-coded reference to 300 sites on the Legacy pricing table template;

Version 2.0.5 - Released on 2021-12-02

* Added: A new class Unsupported that performs security checks when v2 is first activated on a v1 network that has v1 add-ons active;
* Added: Initial version of the file with the elements public apis;
* Added: Send www. version alongside naked domain to Cloudways when new domains are added;
* Improvement: Wraps "wu_core_update" on a try/catch statement;
* Improvement: Strings used on price descriptions (day, week, month, year) are now translatable;
* Fixed: Fixed template id validation rules to prevent errors;
* Fixed: Limitation merging between plans and packages behaving as expected again;
* Fixed: Filter "wu_domain_has_correct_dns" returning the wrong base value;
* Fixed: Re-adds shortcode registration for customer-owned sites when the context requires it - such as the upgrade form;
* Fixed: Replaced the generic "Object removed successfully" with a contextualized message;
* Fixed: Templates not showing up despite being marked as available on plans;
* Fixed: Better logic on setting the active plan on the checkout form to avoid two plans from being selectable;
* Fixed: Adding our shortcodes to Elementor would break their editor after a initial save;
* Fixed: Prevents Ultimo elements from breaking Divi - still needs work to make sure element previews display correctly inside the visual editor;

Version 2.0.4 - Released on 2021-11-29

* Added: Link to resend verification email on the "Thank You" page;
* Added: Option to save checkout fields as user meta;
* Added: Option to restrict SSO calls to login pages only - on Settings → Domain Mapping;
* Added: Option to disable the Screenshot Generator on Settings → Sites;
* Added: Option to force synchronous site publication on Settings → Login and Registration;
* Improvement: General clean-up to the checkout form editor fields/steps options;
* Improvement: Performance impact of Theme Limits class greatly reduced;
* Improvement: Fetch Cloudflare DNS entries to comparison table when checking for DNS propagation;
* Improvement: Move SSO ajax calls to light ajax for 50%+ performance gains on those calls;
* Improvement: Add an option to disable the "Hover to Zoom" feature on Settings → Other Options;
* Improvement: Load block editor fields for WP Ultimo blocks with default values pre-loaded;
* Improvement: Display message when new products are created, mentioning that they need to be manually added to forms;
* Improvement: Display message when new site templates are created, mentioning that they need to be manually added to forms;
* Improvement: Better cPanel and Cloudflare integration descriptions, to make their purpose clearer;
* Improvement: Add a warning when the sunrise.php is still being loaded, even when WP Ultimo is no longer active;
* Improvement: The template selection and pricing table fields automatically submit the form when they are the only relevant fields of a checkout step;
* Improvement: Option to skip plan selection if value is pre-loaded via the URL;
* Improvement: Prevent Oxygen builder from removing default hooks - used to load styles - on the Template Previewer page;
* Improvement: Enforce validations rules for template selection and products, making these fields mandatory;
* Fixed: Confirmation email not being sent when email verification was enabled;
* Fixed: Auto-generate options for site_url, site_title, and username not working;
* Fixed: JavaScript incompatibility with FluentCRM, UIPress, and other JS-heavy plugins;
* Fixed: Cart validations for price variations passing in situations where errors should be displayed;
* Fixed: Broadcast list table breaking when products attached to a Broadcast gets delete;
* Fixed: Replaced deprecated wp_no_robots with wp_robots_no_robots, if available;
* Fixed: "Maintenance Mode Active" top-bar warning appearing on the front-end even when maintenance mode was disabled;
* Fixed: System Info, Account, and Job Queue page links being added to the footer before the installation was complete;
* Fixed: Manage Sites page search input not working;
* Fixed: Only register WP Ultimo blocks and shortcodes on sites that are not customer-owned;
* Fixed: Fatal error when duplicating site templates or publishing pending sites on certain scenarios;
* Fixed: cPanel integration not working when the port constant was omitted;
* Fixed: Removed unnecessary mock implementation of get_current_screen() from the signup-main template;
* Fixed: Domain Mapping element redirecting to /wp-admin regardless of original location after adding/removing a domain;
* Fixed: Auto-increasing discount codes "uses" count when payments that used those discount codes are received not working;
* Fixed: Unable to bulk delete, activate, and deactivate discount codes;
* Fixed: "Use this template" button on the template previewer communicates selection back to the checkout;
* Fixed: Editing the custom login page was not possible with any page builder as it redirected back to /wp-admin;
* Fixed: Fatal error when trying to locate the FpdfTpl class in certain environments, specially shared hosting;
* Fixed: Adjusted the layout to better fit the legacy template page;
* Fixed: Check for Elementor file manager instance before trying to call the clear_cache method;
* Fixed: Adding classes and an ID to a checkout form step not working;
* Fixed: Add and remove note forms not working;

Version 2.0.3 - Released on 2021-11-23

* Improvement: Remove the subdirectory/subdomain tab of the new site form depending on the install type;
* Fixed: "Install User Switching" form not available;
* Fixed: WooCommerce incompatibility with multiple accounts on login;
* Fixed: DNS checking for domains not working, keeping domains stuck on the checking dns status;

Version 2.0.2 - Released on 2021-11-22

* Fixed: "Unauthorized" error when trying to install add-ons;

Version 2.0.1 - Released on 2021-11-22

* Fixed: Trying to activate a hosting provider integration causing timeout/blank screen;
* Fixed: Selecting a new plan on checkout was not updating template list;
* Fixed: WP Ultimo and its add-ons not appearing on the main site's plugins list;

Version 2.0.0 - Released on 2021-11-21 (WP Ultimo's 5-year anniversary)

