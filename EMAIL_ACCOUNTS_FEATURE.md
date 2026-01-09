# Email Accounts Feature

This document describes the Email Accounts feature introduced in Ultimate Multisite 2.3.0, which allows network administrators to provision and manage email accounts for their customers.

## Overview

The Email Accounts feature enables:
- **Network admins** to configure email providers (cPanel, Purelymail, Google Workspace, Microsoft 365)
- **Customers** to create and manage email accounts through their wp-admin dashboard
- Support for both **membership-included quotas** AND **per-account purchases**
- Multiple providers can be enabled simultaneously - customers choose when creating accounts

## Configuration

### Enabling the Feature

1. Go to **Ultimate Multisite > Settings > Email Accounts**
2. Enable "Email Accounts" toggle
3. Configure default quota and per-account purchase settings
4. Enable and configure one or more email providers

### Provider Configuration

Each provider requires specific constants to be defined in `wp-config.php`:

#### cPanel Email
```php
define('WU_CPANEL_USERNAME', 'your_cpanel_username');
define('WU_CPANEL_PASSWORD', 'your_cpanel_password');
define('WU_CPANEL_HOST', 'your-server.com');
define('WU_CPANEL_PORT', 2083); // Optional, defaults to 2083
```

#### Purelymail
```php
define('WU_PURELYMAIL_API_KEY', 'your_api_key');
```

Get your API key from your [Purelymail account settings](https://purelymail.com/manage/account).

#### Google Workspace
```php
define('WU_GOOGLE_SERVICE_ACCOUNT_JSON', '/path/to/service-account.json');
define('WU_GOOGLE_ADMIN_EMAIL', 'admin@yourdomain.com');
define('WU_GOOGLE_CUSTOMER_ID', 'C01234567');
```

Requirements:
1. Create a project in Google Cloud Console
2. Enable the Admin SDK API
3. Create a service account with domain-wide delegation
4. Download the JSON credentials file
5. Grant the service account the `https://www.googleapis.com/auth/admin.directory.user` scope

#### Microsoft 365
```php
define('WU_MS365_CLIENT_ID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
define('WU_MS365_CLIENT_SECRET', 'your_client_secret');
define('WU_MS365_TENANT_ID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
define('WU_MS365_LICENSE_SKU', 'license_sku_id'); // Optional
```

Requirements:
1. Register an application in Azure Active Directory
2. Grant the application `User.ReadWrite.All` permission
3. Create a client secret
4. Note: License assignment requires the SKU ID of the license to assign

### Membership Limitations

Control email account quotas per product/membership:

1. Edit a product in **Ultimate Multisite > Products**
2. Go to the **Limitations** tab
3. Enable "Email Accounts" limitation
4. Set the maximum number of accounts (0 = unlimited)

## Customer Experience

### Creating Email Accounts

Customers can create email accounts from their **Account** page:

1. Customer navigates to their Account page
2. Clicks "Create Email Account"
3. Selects provider (if multiple are enabled)
4. Enters username and selects domain
5. System provisions the account and displays credentials

### Managing Email Accounts

From the Account page, customers can:
- View all their email accounts
- Open webmail (provider-specific)
- View IMAP/SMTP settings
- View DNS setup instructions
- Delete email accounts

### DNS Configuration

When customers create email accounts, they need to configure DNS records for their domain. The system provides provider-specific DNS instructions including:
- MX records for mail routing
- SPF records for email authentication
- DKIM records for signature verification
- Optional DMARC policies

## Developer Reference

### Database Schema

**Table:** `{prefix}_wu_email_accounts`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint(20) | Primary key |
| customer_id | bigint(20) | FK to customers |
| membership_id | bigint(20) | FK to memberships (nullable) |
| site_id | bigint(20) | FK to sites (nullable) |
| email_address | varchar(255) | Full email address |
| domain | varchar(191) | Domain portion |
| provider | varchar(50) | Provider ID |
| status | varchar(20) | pending/provisioning/active/suspended/failed |
| quota_mb | int | Storage quota in MB (0 = unlimited) |
| external_id | varchar(255) | Provider's external ID |
| password_hash | text | Encrypted password |
| purchase_type | varchar(50) | membership_included/per_account |
| payment_id | bigint(20) | FK to payments (for per-account) |
| date_created | datetime | Created timestamp |
| date_modified | datetime | Modified timestamp |

### Helper Functions

```php
// Get an email account by ID
$account = wu_get_email_account($id);

// Get all accounts for a customer
$accounts = wu_get_email_accounts([
    'customer_id' => $customer_id,
]);

// Create an email account
$account = wu_create_email_account([
    'customer_id'   => $customer_id,
    'membership_id' => $membership_id,
    'email_address' => 'user@example.com',
    'provider'      => 'cpanel',
]);

// Check if customer can create more accounts
$can_create = wu_can_create_email_account($customer_id, $membership_id);

// Count accounts
$count = wu_count_email_accounts($customer_id, $membership_id);

// Get enabled providers
$providers = wu_get_enabled_email_providers();
```

### Hooks and Filters

#### Actions

```php
// Fired when account is successfully provisioned
do_action('wu_email_account_provisioned', $email_account, $password);

// Fired when provisioning fails
do_action('wu_email_account_provisioning_failed', $email_account, $error);

// Fired when account is suspended
do_action('wu_email_account_suspended', $email_account);

// Fired when account is reactivated
do_action('wu_email_account_reactivated', $email_account);

// Load additional providers
add_action('wu_email_providers_load', function() {
    // Register custom provider
});
```

#### Filters

```php
// Filter the list of registered providers
add_filter('wu_email_manager_get_providers', function($providers, $manager) {
    $providers['custom'] = My_Custom_Provider::class;
    return $providers;
}, 10, 2);
```

### Creating Custom Providers

Extend `Base_Email_Provider` to create custom email providers:

```php
namespace My_Plugin;

use WP_Ultimo\Integrations\Email_Providers\Base_Email_Provider;
use WP_Ultimo\Models\Email_Account;

class My_Custom_Provider extends Base_Email_Provider {

    protected $id = 'my_provider';
    protected $title = 'My Provider';
    protected $constants = ['MY_PROVIDER_API_KEY'];

    public function create_email_account(array $params) {
        // Implementation
    }

    public function delete_email_account($email_address) {
        // Implementation
    }

    public function change_password($email_address, $new_password) {
        // Implementation
    }

    public function get_account_info($email_address) {
        // Implementation
    }

    public function get_webmail_url(Email_Account $account) {
        return 'https://webmail.myprovider.com/';
    }

    public function get_dns_instructions($domain) {
        return [
            [
                'type'        => 'MX',
                'name'        => '@',
                'value'       => 'mail.myprovider.com',
                'priority'    => 10,
                'description' => 'Mail server record',
            ],
        ];
    }
}
```

Register your provider:

```php
add_action('wu_email_providers_load', function() {
    My_Custom_Provider::get_instance();
});
```

## File Structure

```
inc/
├── database/email-accounts/
│   ├── class-email-accounts-schema.php
│   ├── class-email-accounts-table.php
│   ├── class-email-account-query.php
│   └── class-email-account-status.php
├── models/
│   └── class-email-account.php
├── functions/
│   └── email-account.php
├── integrations/email-providers/
│   ├── class-base-email-provider.php
│   ├── class-cpanel-email-provider.php
│   ├── class-purelymail-provider.php
│   ├── class-google-workspace-provider.php
│   └── class-microsoft365-provider.php
├── managers/
│   └── class-email-account-manager.php
├── limitations/
│   └── class-limit-email-accounts.php
└── ui/
    └── class-email-accounts-element.php

views/dashboard-widgets/
└── email-accounts.php
```

## Security Considerations

1. **Password Storage**: Passwords are encrypted using sodium and stored temporarily with time-limited tokens for one-time display
2. **API Credentials**: All provider credentials are stored in `wp-config.php` constants, not in the database
3. **Permission Checks**: All operations verify customer ownership before execution
4. **Domain Validation**: Email addresses are validated against customer-owned domains
5. **Rate Limiting**: Account creation is controlled through membership quotas

## Provider Affiliate Links

When displaying setup instructions, the plugin includes affiliate referral links:
- **Purelymail**: `https://purelymail.com/?ref=ultimatemultisite`
- **Google Workspace**: Google Workspace partner program
- **Microsoft 365**: Microsoft partner program

These help support Ultimate Multisite development while providing quality email services.
