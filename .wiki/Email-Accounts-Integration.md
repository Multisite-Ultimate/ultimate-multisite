# Email Accounts Integration

The Email Accounts feature allows your customers to create and manage email accounts directly from their WordPress dashboard. This powerful feature integrates with various email providers to automate email provisioning as part of your multisite service offering.

## Overview

When enabled, customers can:
- Create email accounts using their mapped domains
- View credentials and connection details (IMAP/SMTP/Webmail)
- Change email account passwords
- Delete email accounts they no longer need

Network administrators can:
- Choose which email providers to enable
- Set limits on email accounts per membership plan
- Offer email accounts as membership-included or per-account purchases
- Configure default quotas and restrictions

## Supported Providers

Ultimate Multisite supports the following email providers:

| Provider | Best For | API Type |
|----------|----------|----------|
| [cPanel Email](cPanel-Email-Integration) | Self-hosted servers with cPanel | UAPI |
| [Purelymail](Purelymail-Email-Integration) | Budget-friendly email hosting | REST API |
| [Google Workspace](Google-Workspace-Email-Integration) | Enterprise email with Google apps | Admin SDK |
| [Microsoft 365](Microsoft-365-Email-Integration) | Enterprise email with Microsoft apps | Graph API |

## How It Works

### For Network Administrators

1. **Enable the Feature**: Go to **WP Ultimo > Settings > Email Accounts** and enable the email accounts feature.

2. **Configure a Provider**: Click the "Configure" button next to your chosen provider and follow the setup wizard:
   - Add API credentials to your `wp-config.php` file
   - Test the connection
   - Complete the setup

3. **Set Membership Limits**: Edit your membership products to configure email account limits:
   - Enable/disable email accounts for the plan
   - Set the maximum number of accounts
   - Choose unlimited accounts if desired

### For Customers

1. **Access Email Management**: Customers see an "Email Accounts" section in their account dashboard.

2. **Create an Account**: They enter a username and select a domain from their mapped domains.

3. **View Credentials**: After creation, they can view:
   - Email address and password
   - IMAP server and port
   - SMTP server and port
   - Webmail URL

4. **Manage Accounts**: They can change passwords or delete accounts as needed.

## Database Schema

Email accounts are stored in the `{prefix}_wu_email_accounts` table with the following key fields:

| Field | Description |
|-------|-------------|
| `email_address` | Full email address (user@domain.com) |
| `customer_id` | Associated customer |
| `membership_id` | Associated membership |
| `provider` | Email provider identifier |
| `status` | pending, active, suspended, or failed |
| `quota_mb` | Storage quota in megabytes |
| `external_id` | Provider's external identifier |

## Limitations Integration

Email accounts integrate with the limitations system. You can configure:

- **Enabled**: Whether email accounts are available
- **Limit**: Maximum number of accounts (0 = unlimited)

These settings appear in the product editor under the "Limits" tab.

## Security Considerations

### API Credentials

All API credentials are stored as constants in `wp-config.php`, not in the database. This provides:
- Better security (credentials not exposed in database dumps)
- Easier deployment across environments
- Protection from accidental exposure in admin panels

### Password Handling

- Passwords are generated securely using WordPress's `wp_generate_password()`
- Passwords are only displayed once to the customer after account creation
- Passwords are not stored in the database after successful provisioning

### Domain Validation

- Customers can only create email accounts on domains they own
- Domain ownership is verified through the domain mapping system
- Subdomains of the main network domain are excluded

## Troubleshooting

### Common Issues

1. **"No domains available"**: The customer hasn't mapped any custom domains yet, or mapped domains aren't verified.

2. **"Provider not configured"**: The email provider's API credentials are missing from `wp-config.php`.

3. **"Connection failed"**: Check that:
   - API credentials are correct
   - Server can reach the provider's API
   - Firewall isn't blocking outgoing connections

4. **"Account creation failed"**: Check:
   - The domain's DNS records are properly configured
   - The domain is verified with the email provider
   - You haven't exceeded provider quotas

### Debug Mode

Enable WordPress debug logging to see detailed error messages:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Logs are written to `wp-content/debug.log`.

## Related Documentation

- [cPanel Email Integration](cPanel-Email-Integration)
- [Purelymail Email Integration](Purelymail-Email-Integration)
- [Google Workspace Email Integration](Google-Workspace-Email-Integration)
- [Microsoft 365 Email Integration](Microsoft-365-Email-Integration)
- [Domain Mapping](Domain-Mapping)
- [Limitations System](Limitations)
