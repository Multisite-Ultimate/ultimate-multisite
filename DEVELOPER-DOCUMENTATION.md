# WP Ultimo Developer Documentation

## Table of Contents

1. [Introduction](#introduction)
2. [REST API Reference](#rest-api-reference)
3. [Action Hooks Reference](#action-hooks-reference)
4. [Filter Hooks Reference](#filter-hooks-reference)
5. [Integration Guide](#integration-guide)
6. [Addon Development](#addon-development)
7. [Code Examples](#code-examples)

---

## Introduction

This comprehensive guide provides developers with everything needed to integrate with, extend, or develop addons for WP Ultimo (Multisite Ultimate). WP Ultimo transforms a WordPress Multisite network into a Website-as-a-Service (WaaS) platform.

### Key Features for Developers:
- **REST API** - Complete CRUD operations for all entities
- **Action Hooks** - 200+ hooks for lifecycle events
- **Filter Hooks** - 280+ filters for customization
- **Addon Framework** - Structured addon development system
- **Authentication** - API key-based authentication system

### Requirements:
- WordPress Multisite installation
- PHP 7.4 or higher
- WP Ultimo plugin activated

---

## REST API Reference

### Base Configuration

**Base URL:** `{site_url}/wp-json/wu/v2/`
**Authentication:** API Key & Secret (HTTP Basic Auth or URL Parameters)

### Authentication

#### Enable API
```php
// Enable API in WP Ultimo settings or programmatically
wu_save_setting('enable_api', true);
```

#### Get API Credentials
```php
$api_key = wu_get_setting('api_key');
$api_secret = wu_get_setting('api_secret');
```

#### Authentication Methods

**HTTP Basic Auth (Recommended):**
```bash
curl -u "api_key:api_secret" https://yoursite.com/wp-json/wu/v2/customers
```

**URL Parameters:**
```bash
curl "https://yoursite.com/wp-json/wu/v2/customers?api_key=your_key&api_secret=your_secret"
```

### Core Endpoints

#### 1. Customers API

**Base Route:** `/customers`

**Get All Customers**
```http
GET /wu/v2/customers
```

**Get Single Customer**
```http
GET /wu/v2/customers/{id}
```

**Create Customer**
```http
POST /wu/v2/customers
Content-Type: application/json

{
    "user_id": 123,
    "email_verification": "verified",
    "type": "customer",
    "has_trialed": false,
    "vip": false
}
```

**Update Customer**
```http
PUT /wu/v2/customers/{id}
Content-Type: application/json

{
    "vip": true,
    "extra_information": "VIP customer notes"
}
```

**Delete Customer**
```http
DELETE /wu/v2/customers/{id}
```

#### 2. Sites API

**Base Route:** `/sites`

**Create Site**
```http
POST /wu/v2/sites
Content-Type: application/json

{
    "customer_id": 5,
    "membership_id": 10,
    "domain": "example.com",
    "path": "/",
    "title": "My New Site",
    "template_id": 1,
    "type": "customer_owned"
}
```

#### 3. Memberships API

**Base Route:** `/memberships`

**Create Membership**
```http
POST /wu/v2/memberships
Content-Type: application/json

{
    "customer_id": 5,
    "plan_id": 3,
    "status": "active",
    "gateway": "stripe",
    "gateway_subscription_id": "sub_1234567890",
    "auto_renew": true
}
```

#### 4. Products API

**Base Route:** `/products`

**Get All Products**
```http
GET /wu/v2/products
```

#### 5. Payments API

**Base Route:** `/payments`

**Create Payment**
```http
POST /wu/v2/payments
Content-Type: application/json

{
    "customer_id": 5,
    "membership_id": 10,
    "status": "completed",
    "gateway": "stripe",
    "gateway_payment_id": "pi_1234567890",
    "total": 29.99,
    "currency": "USD"
}
```

#### 6. Domains API

**Base Route:** `/domains`

**Map Domain**
```http
POST /wu/v2/domains
Content-Type: application/json

{
    "domain": "custom-domain.com",
    "customer_id": 5,
    "primary_domain": 1,
    "stage": "domain-mapping"
}
```

### Registration Endpoint

The `/register` endpoint provides a complete checkout/registration flow:

```http
POST /wu/v2/register
Content-Type: application/json

{
    "customer": {
        "username": "newuser",
        "password": "securepass123",
        "email": "user@example.com"
    },
    "products": ["basic-plan"],
    "duration": 1,
    "duration_unit": "month",
    "auto_renew": true,
    "site": {
        "site_url": "mynewsite",
        "site_title": "My New Site",
        "template_id": 1
    },
    "payment": {
        "status": "completed"
    },
    "membership": {
        "status": "active"
    }
}
```

**Response:**
```json
{
    "customer": { ... },
    "membership": { ... },
    "payment": { ... },
    "site": { "id": 123 }
}
```

### Error Responses

```json
{
    "code": "wu_rest_invalid_parameter",
    "message": "Invalid parameter value",
    "data": {
        "status": 400,
        "params": {
            "email": "Invalid email format"
        }
    }
}
```

### Pagination and Filtering

**Query Parameters:**
```http
GET /wu/v2/customers?per_page=20&page=2&search=john&status=active
```

Common parameters:
- `per_page` - Items per page (default: 20, max: 100)
- `page` - Page number
- `search` - Search term
- `orderby` - Sort field
- `order` - Sort direction (asc/desc)
- `status` - Filter by status
- `date_created` - Filter by date range

---

## Action Hooks Reference

### Lifecycle Hooks

#### Plugin Activation
```php
/**
 * Fires when WP Ultimo is activated.
 *
 * @since 2.0.0
 */
do_action('wu_activation');

// Usage Example:
add_action('wu_activation', function() {
    // Initialize custom data
    add_option('my_addon_version', '1.0.0');
});
```

#### Settings Management
```php
/**
 * Fires after settings are saved.
 *
 * @since 2.0.0
 * @param array $settings The settings being saved.
 */
do_action('wu_after_save_settings', $settings);

// Usage Example:
add_action('wu_after_save_settings', function($settings) {
    if (isset($settings['enable_feature'])) {
        // React to feature toggle
        update_option('feature_enabled', $settings['enable_feature']);
    }
});
```

### Customer Hooks

#### Customer Creation
```php
/**
 * Fires after a customer is created.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Customer $customer The customer object.
 */
do_action('wu_customer_post_create', $customer);

// Usage Example:
add_action('wu_customer_post_create', function($customer) {
    // Send welcome email
    wp_mail(
        $customer->get_email(),
        'Welcome!',
        'Thanks for joining our platform!'
    );
});
```

#### Customer Status Change
```php
/**
 * Fires when customer status changes.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Customer $customer The customer object.
 * @param string $old_status Previous status.
 * @param string $new_status New status.
 */
do_action('wu_customer_status_change', $customer, $old_status, $new_status);
```

### Site Hooks

#### Site Creation
```php
/**
 * Fires after a site is published.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Site $site The site object.
 * @param WP_Ultimo\Models\Membership $membership The associated membership.
 */
do_action('wu_site_published', $site, $membership);

// Usage Example:
add_action('wu_site_published', function($site, $membership) {
    // Set up initial site configuration
    switch_to_blog($site->get_id());
    
    // Install default plugins
    activate_plugin('essential-plugin/essential-plugin.php');
    
    restore_current_blog();
}, 10, 2);
```

#### Site Template Application
```php
/**
 * Fires before applying a site template.
 *
 * @since 2.0.0
 * @param int $site_id The site ID being configured.
 * @param int $template_id The template being applied.
 */
do_action('wu_before_apply_template', $site_id, $template_id);

// Usage Example:
add_action('wu_before_apply_template', function($site_id, $template_id) {
    // Custom template preparation
    switch_to_blog($site_id);
    
    // Set custom options based on template
    if ($template_id === 5) { // E-commerce template
        update_option('woocommerce_store_setup', 'complete');
    }
    
    restore_current_blog();
}, 10, 2);
```

### Membership Hooks

#### Membership Status Changes
```php
/**
 * Fires when membership transitions to active status.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Membership $membership The membership object.
 */
do_action('wu_membership_status_to_active', $membership);

/**
 * Fires when membership expires.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Membership $membership The membership object.
 */
do_action('wu_membership_status_to_expired', $membership);

// Usage Example:
add_action('wu_membership_status_to_expired', function($membership) {
    // Suspend related sites
    $sites = $membership->get_sites();
    foreach ($sites as $site) {
        $site->set_status('suspended');
        $site->save();
    }
});
```

### Payment Hooks

#### Payment Processing
```php
/**
 * Fires when a payment is completed.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Payment $payment The payment object.
 */
do_action('wu_payment_completed', $payment);

/**
 * Fires when a payment fails.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Payment $payment The payment object.
 * @param string $error_message The error message.
 */
do_action('wu_payment_failed', $payment, $error_message);

// Usage Example:
add_action('wu_payment_failed', function($payment, $error) {
    // Notify administrators
    $admin_email = get_option('admin_email');
    wp_mail(
        $admin_email,
        'Payment Failed',
        sprintf('Payment #%d failed: %s', $payment->get_id(), $error)
    );
}, 10, 2);
```

### Checkout Hooks

#### Checkout Flow
```php
/**
 * Fires before checkout processing begins.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Checkout\Cart $cart The cart object.
 */
do_action('wu_checkout_before_processing', $cart);

/**
 * Fires after successful checkout completion.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Payment $payment The payment object.
 * @param WP_Ultimo\Models\Customer $customer The customer object.
 * @param WP_Ultimo\Models\Membership $membership The membership object.
 */
do_action('wu_checkout_completed', $payment, $customer, $membership);

// Usage Example:
add_action('wu_checkout_completed', function($payment, $customer, $membership) {
    // Track conversion in analytics
    if (function_exists('gtag')) {
        gtag('event', 'purchase', [
            'transaction_id' => $payment->get_id(),
            'value' => $payment->get_total(),
            'currency' => $payment->get_currency()
        ]);
    }
}, 10, 3);
```

### Domain Hooks

#### Domain Management
```php
/**
 * Fires when a domain is mapped successfully.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Domain $domain The domain object.
 */
do_action('wu_domain_mapped', $domain);

/**
 * Fires when domain SSL is verified.
 *
 * @since 2.0.0
 * @param WP_Ultimo\Models\Domain $domain The domain object.
 */
do_action('wu_domain_ssl_verified', $domain);

// Usage Example:
add_action('wu_domain_mapped', function($domain) {
    // Update CDN configuration
    if (function_exists('cloudflare_update_zone')) {
        cloudflare_update_zone($domain->get_domain());
    }
});
```

---

## Filter Hooks Reference

### Content and Output Filters

#### Customizing Output
```php
/**
 * Filters the checkout form fields.
 *
 * @since 2.0.0
 * @param array $fields The form fields.
 * @param WP_Ultimo\Models\Checkout_Form $form The checkout form object.
 * @return array Modified fields array.
 */
$fields = apply_filters('wu_checkout_form_fields', $fields, $form);

// Usage Example:
add_filter('wu_checkout_form_fields', function($fields, $form) {
    // Add custom field
    $fields['company'] = [
        'type' => 'text',
        'title' => 'Company Name',
        'required' => true,
        'placeholder' => 'Enter company name'
    ];
    
    return $fields;
}, 10, 2);
```

#### Email Content
```php
/**
 * Filters email content before sending.
 *
 * @since 2.0.0
 * @param string $content The email content.
 * @param string $email_type The email type.
 * @param WP_Ultimo\Models\Customer $customer The customer object.
 * @return string Modified content.
 */
$content = apply_filters('wu_email_content', $content, $email_type, $customer);

// Usage Example:
add_filter('wu_email_content', function($content, $type, $customer) {
    if ($type === 'welcome') {
        // Add custom welcome message
        $content .= "\n\nSpecial offer: Use code WELCOME10 for 10% off!";
    }
    return $content;
}, 10, 3);
```

### Pricing and Cart Filters

#### Price Modifications
```php
/**
 * Filters the final cart total.
 *
 * @since 2.0.0
 * @param float $total The cart total.
 * @param WP_Ultimo\Checkout\Cart $cart The cart object.
 * @return float Modified total.
 */
$total = apply_filters('wu_cart_total', $total, $cart);

// Usage Example:
add_filter('wu_cart_total', function($total, $cart) {
    $customer = $cart->get_customer();
    
    // VIP discount
    if ($customer && $customer->is_vip()) {
        $total = $total * 0.9; // 10% discount
    }
    
    return $total;
}, 10, 2);
```

#### Tax Calculations
```php
/**
 * Filters tax rates by location.
 *
 * @since 2.0.0
 * @param float $rate The tax rate.
 * @param string $country The country code.
 * @param string $state The state code.
 * @return float Modified tax rate.
 */
$rate = apply_filters('wu_tax_rate', $rate, $country, $state);

// Usage Example:
add_filter('wu_tax_rate', function($rate, $country, $state) {
    // Custom tax rate for specific region
    if ($country === 'US' && $state === 'CA') {
        return 0.0875; // 8.75% CA tax
    }
    
    return $rate;
}, 10, 3);
```

### Limitation Filters

#### Feature Limitations
```php
/**
 * Filters whether a feature is allowed for a site.
 *
 * @since 2.0.0
 * @param bool $allowed Whether the feature is allowed.
 * @param int $site_id The site ID.
 * @param WP_Ultimo\Models\Membership $membership The membership object.
 * @return bool Modified permission.
 */
$allowed = apply_filters('wu_limitation_feature_allowed', $allowed, $site_id, $membership);

// Usage Example:
add_filter('wu_limitation_feature_allowed', function($allowed, $site_id, $membership) {
    // Allow premium features for VIP customers
    if ($membership->get_customer()->is_vip()) {
        return true;
    }
    
    return $allowed;
}, 10, 3);
```

#### Storage Limits
```php
/**
 * Filters disk space limit for a site.
 *
 * @since 2.0.0
 * @param int $limit The disk space limit in MB.
 * @param int $site_id The site ID.
 * @param WP_Ultimo\Models\Membership $membership The membership object.
 * @return int Modified limit.
 */
$limit = apply_filters('wu_disk_space_limit', $limit, $site_id, $membership);

// Usage Example:
add_filter('wu_disk_space_limit', function($limit, $site_id, $membership) {
    // Bonus storage for long-term customers
    if ($membership->get_days_active() > 365) {
        $limit += 500; // Extra 500MB
    }
    
    return $limit;
}, 10, 3);
```

### Gateway and Payment Filters

#### Gateway Selection
```php
/**
 * Filters available payment gateways.
 *
 * @since 2.0.0
 * @param array $gateways Available gateways.
 * @param WP_Ultimo\Checkout\Cart $cart The cart object.
 * @return array Modified gateways array.
 */
$gateways = apply_filters('wu_available_gateways', $gateways, $cart);

// Usage Example:
add_filter('wu_available_gateways', function($gateways, $cart) {
    // Hide PayPal for enterprise plans
    if ($cart->get_total() > 1000) {
        unset($gateways['paypal']);
    }
    
    return $gateways;
}, 10, 2);
```

### Template and Theme Filters

#### Template Selection
```php
/**
 * Filters available site templates.
 *
 * @since 2.0.0
 * @param array $templates Available templates.
 * @param WP_Ultimo\Models\Customer $customer The customer object.
 * @return array Modified templates array.
 */
$templates = apply_filters('wu_available_templates', $templates, $customer);

// Usage Example:
add_filter('wu_available_templates', function($templates, $customer) {
    // Premium templates for VIP customers only
    if (!$customer->is_vip()) {
        foreach ($templates as $key => $template) {
            if ($template['category'] === 'premium') {
                unset($templates[$key]);
            }
        }
    }
    
    return $templates;
}, 10, 2);
```

---

## Integration Guide

### Third-Party Service Integration

#### CRM Integration Example
```php
// Hook into customer creation
add_action('wu_customer_post_create', 'sync_customer_to_crm');

function sync_customer_to_crm($customer) {
    $crm_api = new Your_CRM_API();
    
    $crm_api->create_contact([
        'email' => $customer->get_email(),
        'name' => $customer->get_display_name(),
        'signup_date' => $customer->get_date_registered(),
        'plan' => $customer->get_membership()->get_plan()->get_name()
    ]);
    
    // Store CRM ID for future reference
    $customer->add_meta('crm_contact_id', $crm_api->get_last_contact_id());
}
```

#### Analytics Integration
```php
// Track key events
add_action('wu_checkout_completed', 'track_conversion', 10, 3);
add_action('wu_membership_status_to_cancelled', 'track_churn');
add_action('wu_payment_failed', 'track_payment_failure');

function track_conversion($payment, $customer, $membership) {
    // Google Analytics 4
    gtag('event', 'purchase', [
        'transaction_id' => $payment->get_id(),
        'value' => $payment->get_total(),
        'currency' => $payment->get_currency(),
        'items' => [
            [
                'item_id' => $membership->get_plan()->get_id(),
                'item_name' => $membership->get_plan()->get_name(),
                'category' => 'subscription',
                'quantity' => 1,
                'price' => $payment->get_total()
            ]
        ]
    ]);
}
```

### Custom Gateway Development

#### Create Custom Gateway
```php
class My_Custom_Gateway extends \WP_Ultimo\Gateways\Base_Gateway {
    
    public $id = 'my_gateway';
    
    public function __construct() {
        $this->title = 'My Payment Gateway';
        $this->description = 'Custom payment processing';
        $this->supports = ['one-time', 'recurring'];
        
        parent::__construct();
    }
    
    public function process_single_payment($payment, $cart, $order) {
        // Process one-time payment
        $result = $this->api_call('charge', [
            'amount' => $payment->get_total(),
            'currency' => $payment->get_currency(),
            'customer' => $payment->get_customer_id()
        ]);
        
        if ($result->success) {
            $payment->set_gateway_payment_id($result->transaction_id);
            $payment->set_status('completed');
            return true;
        }
        
        return new WP_Error('payment_failed', $result->error_message);
    }
    
    public function process_signup($membership, $customer, $cart, $order) {
        // Set up recurring subscription
        $subscription = $this->api_call('subscription/create', [
            'customer_id' => $customer->get_gateway_customer_id(),
            'plan_id' => $membership->get_plan()->get_gateway_plan_id(),
            'trial_days' => $membership->get_trial_days()
        ]);
        
        if ($subscription->success) {
            $membership->set_gateway_subscription_id($subscription->id);
            return true;
        }
        
        return new WP_Error('subscription_failed', $subscription->error);
    }
}

// Register the gateway
add_filter('wu_payment_gateways', function($gateways) {
    $gateways['my_gateway'] = 'My_Custom_Gateway';
    return $gateways;
});
```

### Webhook Handling

#### Custom Webhook Endpoint
```php
// Register webhook endpoint
add_action('rest_api_init', function() {
    register_rest_route('my-addon/v1', '/webhook', [
        'methods' => 'POST',
        'callback' => 'handle_my_webhook',
        'permission_callback' => 'verify_webhook_signature'
    ]);
});

function handle_my_webhook($request) {
    $payload = $request->get_json_params();
    
    switch ($payload['event_type']) {
        case 'customer.updated':
            $customer = wu_get_customer($payload['customer_id']);
            if ($customer) {
                // Sync changes from external system
                $customer->set_vip($payload['data']['is_vip']);
                $customer->save();
            }
            break;
            
        case 'subscription.cancelled':
            $membership = wu_get_membership_by_hash($payload['subscription_id']);
            if ($membership) {
                $membership->cancel();
            }
            break;
    }
    
    return ['status' => 'processed'];
}

function verify_webhook_signature($request) {
    $signature = $request->get_header('X-Webhook-Signature');
    $payload = $request->get_body();
    $secret = get_option('my_webhook_secret');
    
    return hash_hmac('sha256', $payload, $secret) === $signature;
}
```

---

## Addon Development

### Addon Structure

```
my-addon/
├── my-addon.php                 # Main plugin file
├── inc/
│   ├── class-my-addon.php       # Main addon class
│   ├── admin-pages/             # Admin interface
│   ├── models/                  # Custom data models
│   └── integrations/            # Third-party integrations
├── assets/
│   ├── js/
│   └── css/
└── templates/                   # Template files
```

### Main Addon File Template

```php
<?php
/**
 * Plugin Name: My WP Ultimo Addon
 * Description: Custom addon for WP Ultimo
 * Version: 1.0.0
 * Author: Your Name
 * Requires PHP: 7.4
 * WP Ultimo: 2.0.0
 */

namespace My_Addon;

// Exit if accessed directly
defined('ABSPATH') || exit;

// Define constants
define('MY_ADDON_VERSION', '1.0.0');
define('MY_ADDON_PLUGIN_FILE', __FILE__);
define('MY_ADDON_PATH', plugin_dir_path(__FILE__));
define('MY_ADDON_URL', plugin_dir_url(__FILE__));

// Check if WP Ultimo is active
add_action('plugins_loaded', function() {
    if (!class_exists('WP_Ultimo\WP_Ultimo')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo 'My Addon requires WP Ultimo to be installed and activated.';
            echo '</p></div>';
        });
        return;
    }
    
    // Initialize addon
    My_Addon::get_instance();
});

/**
 * Main addon class
 */
class My_Addon {
    
    use \WP_Ultimo\Traits\Singleton;
    
    /**
     * Initialize the addon
     */
    public function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Setup hooks
        $this->setup_hooks();
        
        // Initialize components
        $this->init_components();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once MY_ADDON_PATH . 'inc/class-my-addon.php';
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Activation/deactivation
        register_activation_hook(MY_ADDON_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(MY_ADDON_PLUGIN_FILE, [$this, 'deactivate']);
        
        // WP Ultimo hooks
        add_action('wu_checkout_completed', [$this, 'on_checkout_completed'], 10, 3);
        add_filter('wu_checkout_form_fields', [$this, 'add_custom_fields'], 10, 2);
    }
    
    /**
     * Initialize addon components
     */
    private function init_components() {
        // Initialize admin pages, models, etc.
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom tables, set options, etc.
        $this->create_custom_table();
        update_option('my_addon_version', MY_ADDON_VERSION);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }
    
    /**
     * Handle checkout completion
     */
    public function on_checkout_completed($payment, $customer, $membership) {
        // Custom logic when checkout completes
        $this->send_welcome_email($customer);
        $this->setup_customer_account($customer, $membership);
    }
    
    /**
     * Add custom checkout fields
     */
    public function add_custom_fields($fields, $form) {
        $fields['company_size'] = [
            'type' => 'select',
            'title' => 'Company Size',
            'options' => [
                'small' => '1-10 employees',
                'medium' => '11-100 employees',
                'large' => '100+ employees'
            ],
            'required' => false
        ];
        
        return $fields;
    }
}
```

### Custom Model Example

```php
<?php

namespace My_Addon\Models;

/**
 * Custom Lead model
 */
class Lead extends \WP_Ultimo\Models\Base_Model {
    
    /**
     * Model name
     */
    protected $model = 'lead';
    
    /**
     * Set the database table
     */
    protected function set_table() {
        global $wpdb;
        $this->table_name = "{$wpdb->prefix}my_addon_leads";
    }
    
    /**
     * Get the company name
     */
    public function get_company() {
        return $this->get_meta('company');
    }
    
    /**
     * Set the company name
     */
    public function set_company($company) {
        return $this->add_meta('company', $company);
    }
    
    /**
     * Convert lead to customer
     */
    public function convert_to_customer($user_data = []) {
        // Create WordPress user
        $user_id = wp_create_user(
            $user_data['username'] ?? $this->get_email(),
            $user_data['password'] ?? wp_generate_password(),
            $this->get_email()
        );
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Create WP Ultimo customer
        $customer = wu_create_customer([
            'user_id' => $user_id,
            'email_verification' => 'verified',
            'type' => 'customer'
        ]);
        
        if (is_wp_error($customer)) {
            return $customer;
        }
        
        // Copy lead data to customer
        $customer->add_meta('company', $this->get_company());
        $customer->add_meta('lead_source', $this->get_source());
        
        // Mark lead as converted
        $this->set_status('converted');
        $this->add_meta('converted_customer_id', $customer->get_id());
        $this->save();
        
        return $customer;
    }
}
```

### Admin Page Integration

```php
<?php

namespace My_Addon\Admin_Pages;

/**
 * Custom admin page
 */
class Leads_Admin_Page extends \WP_Ultimo\Admin_Pages\Base_Admin_Page {
    
    /**
     * Page ID
     */
    protected $id = 'my-addon-leads';
    
    /**
     * Menu position
     */
    protected $position = 30;
    
    /**
     * Initialize page
     */
    public function init() {
        // Register with WP Ultimo
        add_action('wu_register_admin_pages', [$this, 'register']);
    }
    
    /**
     * Register the admin page
     */
    public function register() {
        wu_register_admin_page($this->id, [
            'title' => __('Leads', 'my-addon'),
            'menu_title' => __('Leads', 'my-addon'),
            'capability' => 'wu_read_leads',
            'position' => $this->position,
            'parent' => 'multisite-ultimate',
            'callback' => [$this, 'render']
        ]);
    }
    
    /**
     * Render the page
     */
    public function render() {
        // Get leads data
        $leads = My_Addon\Models\Lead::query([
            'number' => 20,
            'paged' => absint($_GET['paged'] ?? 1)
        ]);
        
        // Render template
        wu_get_template('admin/leads-list', [
            'leads' => $leads,
            'page_title' => __('Manage Leads', 'my-addon')
        ]);
    }
}
```

---

## Code Examples

### Advanced Integration Examples

#### 1. Multi-Gateway Payment Processing

```php
/**
 * Process payment with fallback gateways
 */
class Smart_Payment_Processor {
    
    private $gateway_priority = ['stripe', 'paypal', 'manual'];
    
    public function process_payment_with_fallback($payment, $cart) {
        foreach ($this->gateway_priority as $gateway_id) {
            $gateway = wu_get_gateway($gateway_id);
            
            if (!$gateway || !$gateway->is_available()) {
                continue;
            }
            
            $result = $gateway->process_single_payment($payment, $cart);
            
            if (!is_wp_error($result)) {
                // Payment successful
                do_action('wu_payment_processed_successfully', $payment, $gateway_id);
                return $result;
            }
            
            // Log failed attempt
            wu_log_add('payment-processing', sprintf(
                'Gateway %s failed for payment %d: %s',
                $gateway_id,
                $payment->get_id(),
                $result->get_error_message()
            ));
        }
        
        // All gateways failed
        do_action('wu_payment_processing_failed', $payment);
        return new WP_Error('all_gateways_failed', 'All payment methods failed');
    }
}
```

#### 2. Dynamic Pricing Engine

```php
/**
 * Advanced pricing rules engine
 */
class Dynamic_Pricing_Engine {
    
    public function __construct() {
        add_filter('wu_cart_total', [$this, 'apply_dynamic_pricing'], 20, 2);
        add_filter('wu_product_price', [$this, 'modify_product_price'], 10, 3);
    }
    
    public function apply_dynamic_pricing($total, $cart) {
        $customer = $cart->get_customer();
        $rules = $this->get_pricing_rules();
        
        foreach ($rules as $rule) {
            if ($this->rule_applies($rule, $cart, $customer)) {
                $total = $this->apply_rule($rule, $total, $cart);
            }
        }
        
        return $total;
    }
    
    private function get_pricing_rules() {
        return [
            [
                'type' => 'volume_discount',
                'condition' => ['total_greater_than' => 100],
                'discount' => 0.1 // 10%
            ],
            [
                'type' => 'loyalty_discount',
                'condition' => ['customer_tenure_months' => 12],
                'discount' => 0.15 // 15%
            ],
            [
                'type' => 'seasonal_promo',
                'condition' => ['date_range' => ['2024-11-01', '2024-12-31']],
                'discount' => 0.2 // 20%
            ]
        ];
    }
    
    private function rule_applies($rule, $cart, $customer) {
        foreach ($rule['condition'] as $condition => $value) {
            switch ($condition) {
                case 'total_greater_than':
                    if ($cart->get_total() <= $value) return false;
                    break;
                    
                case 'customer_tenure_months':
                    if (!$customer || $customer->get_months_active() < $value) return false;
                    break;
                    
                case 'date_range':
                    $now = current_time('Y-m-d');
                    if ($now < $value[0] || $now > $value[1]) return false;
                    break;
            }
        }
        
        return true;
    }
    
    private function apply_rule($rule, $total, $cart) {
        $discount_amount = $total * $rule['discount'];
        
        // Log the discount application
        wu_log_add('pricing', sprintf(
            'Applied %s rule: %.2f discount on total %.2f',
            $rule['type'],
            $discount_amount,
            $total
        ));
        
        return $total - $discount_amount;
    }
}

new Dynamic_Pricing_Engine();
```

#### 3. Advanced Site Provisioning

```php
/**
 * Custom site provisioning with external services
 */
class Advanced_Site_Provisioner {
    
    public function __construct() {
        add_action('wu_site_published', [$this, 'provision_site'], 10, 2);
        add_action('wu_membership_status_to_expired', [$this, 'suspend_site_services']);
    }
    
    public function provision_site($site, $membership) {
        $plan = $membership->get_plan();
        
        // Configure based on plan features
        switch_to_blog($site->get_id());
        
        // Install plugins based on plan
        $this->install_plan_plugins($plan);
        
        // Configure SSL
        if ($plan->has_feature('ssl')) {
            $this->setup_ssl($site);
        }
        
        // Setup CDN
        if ($plan->has_feature('cdn')) {
            $this->configure_cdn($site);
        }
        
        // Configure backups
        if ($plan->has_feature('backups')) {
            $this->setup_automated_backups($site, $plan->get_backup_frequency());
        }
        
        // Setup monitoring
        $this->setup_site_monitoring($site, $membership->get_customer());
        
        restore_current_blog();
        
        // Send completion notification
        $this->send_provisioning_complete_email($site, $membership);
    }
    
    private function install_plan_plugins($plan) {
        $plugins = $plan->get_included_plugins();
        
        foreach ($plugins as $plugin_slug) {
            if ($this->plugin_exists($plugin_slug)) {
                activate_plugin($plugin_slug);
                
                // Configure plugin if needed
                $this->configure_plugin($plugin_slug, $plan);
            }
        }
    }
    
    private function setup_ssl($site) {
        $domain = $site->get_domain();
        
        // API call to SSL provider
        $ssl_service = new SSL_Provider_API();
        $result = $ssl_service->request_certificate($domain);
        
        if ($result->success) {
            $site->add_meta('ssl_certificate_id', $result->certificate_id);
            $site->add_meta('ssl_status', 'active');
        }
    }
    
    private function configure_cdn($site) {
        $cdn_service = new CDN_Provider_API();
        
        $zone = $cdn_service->create_zone([
            'name' => $site->get_domain(),
            'type' => 'full'
        ]);
        
        if ($zone->success) {
            $site->add_meta('cdn_zone_id', $zone->id);
            
            // Update DNS records
            $this->update_cdn_dns($site, $zone);
        }
    }
    
    private function setup_automated_backups($site, $frequency) {
        $backup_service = new Backup_Provider_API();
        
        $schedule = $backup_service->create_schedule([
            'site_id' => $site->get_id(),
            'frequency' => $frequency,
            'retention' => 30 // days
        ]);
        
        $site->add_meta('backup_schedule_id', $schedule->id);
    }
    
    private function setup_site_monitoring($site, $customer) {
        $monitoring_service = new Monitoring_API();
        
        $monitor = $monitoring_service->create_monitor([
            'url' => $site->get_domain(),
            'customer_email' => $customer->get_email(),
            'check_interval' => 300 // 5 minutes
        ]);
        
        $site->add_meta('monitoring_id', $monitor->id);
    }
}

new Advanced_Site_Provisioner();
```

#### 4. Custom Limitations System

```php
/**
 * Advanced limitations with usage tracking
 */
class Advanced_Limitations {
    
    public function __construct() {
        add_filter('wu_limitation_plugins_allowed', [$this, 'check_plugin_limit'], 10, 3);
        add_filter('wu_limitation_storage_allowed', [$this, 'check_storage_limit'], 10, 3);
        add_action('activated_plugin', [$this, 'track_plugin_activation'], 10, 2);
    }
    
    public function check_plugin_limit($allowed, $site_id, $membership) {
        $plan = $membership->get_plan();
        $max_plugins = $plan->get_limit('max_plugins', 10);
        
        // Count active plugins
        switch_to_blog($site_id);
        $active_plugins = count(get_option('active_plugins', []));
        restore_current_blog();
        
        if ($active_plugins >= $max_plugins) {
            // Send warning notification
            $this->send_limit_warning($membership->get_customer(), 'plugins', $max_plugins);
            return false;
        }
        
        return true;
    }
    
    public function check_storage_limit($allowed, $site_id, $membership) {
        $plan = $membership->get_plan();
        $max_storage = $plan->get_limit('max_storage_mb', 1000); // MB
        
        $current_usage = $this->get_site_storage_usage($site_id);
        
        if ($current_usage >= $max_storage) {
            // Log limit reached
            wu_log_add('limitations', sprintf(
                'Site %d reached storage limit: %dMB/%dMB',
                $site_id,
                $current_usage,
                $max_storage
            ));
            
            return false;
        }
        
        // Warn at 80% usage
        if ($current_usage >= ($max_storage * 0.8)) {
            $this->send_storage_warning($membership->get_customer(), $current_usage, $max_storage);
        }
        
        return true;
    }
    
    private function get_site_storage_usage($site_id) {
        // Calculate actual storage usage
        $upload_dir = wp_upload_dir();
        $size = $this->get_directory_size($upload_dir['basedir']);
        
        // Convert to MB
        return round($size / 1024 / 1024, 2);
    }
    
    private function get_directory_size($directory) {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    private function send_limit_warning($customer, $limit_type, $limit_value) {
        wu_mail_customer($customer, 'limit_warning', [
            'limit_type' => $limit_type,
            'limit_value' => $limit_value,
            'upgrade_url' => wu_get_current_url('upgrade')
        ]);
    }
}

new Advanced_Limitations();
```

### Testing Your Integration

#### Unit Test Example

```php
<?php

class Test_My_Integration extends WP_UnitTestCase {
    
    public function setUp() {
        parent::setUp();
        
        // Create test customer
        $this->customer = wu_create_customer([
            'user_id' => $this->factory->user->create(),
            'type' => 'customer'
        ]);
        
        // Create test membership
        $this->membership = wu_create_membership([
            'customer_id' => $this->customer->get_id(),
            'plan_id' => $this->create_test_plan()
        ]);
    }
    
    public function test_custom_field_saves_correctly() {
        $checkout = new WP_Ultimo\Checkout\Checkout();
        
        // Simulate form submission
        $_POST['company_size'] = 'medium';
        
        $result = $checkout->process_step_data([
            'company_size' => 'medium'
        ]);
        
        $this->assertTrue($result);
        
        // Verify data was saved
        $saved_value = $this->customer->get_meta('company_size');
        $this->assertEquals('medium', $saved_value);
    }
    
    public function test_pricing_rule_applies() {
        $engine = new Dynamic_Pricing_Engine();
        
        $cart = new WP_Ultimo\Checkout\Cart([
            'customer_id' => $this->customer->get_id(),
            'products' => ['test-plan']
        ]);
        
        $cart->set_total(150); // Above $100 threshold
        
        $new_total = $engine->apply_dynamic_pricing(150, $cart);
        
        // Should have 10% discount
        $this->assertEquals(135, $new_total);
    }
    
    private function create_test_plan() {
        return wu_create_product([
            'name' => 'Test Plan',
            'type' => 'plan',
            'price' => 50,
            'duration' => 1,
            'duration_unit' => 'month'
        ])->get_id();
    }
}
```

This comprehensive documentation provides developers with all the tools and knowledge needed to integrate with, extend, and build upon WP Ultimo's powerful platform. The extensive API, hook system, and examples enable creation of sophisticated SaaS solutions and custom integrations.