# cPanel Email Integration

The cPanel Email integration allows you to automatically provision email accounts on servers running cPanel. This is ideal if you're hosting your multisite on a cPanel-based server or have access to a cPanel server for email hosting.

## Overview

- **Provider ID**: `cpanel`
- **API Type**: cPanel UAPI
- **Best For**: Self-hosted environments with cPanel access
- **Webmail**: Roundcube at port 2096

## Features

- Automatic email account creation via cPanel UAPI
- Password management (create, change)
- Account deletion
- Configurable storage quotas
- Webmail access via Roundcube

## Requirements

- cPanel server with UAPI access enabled
- cPanel account credentials with email management permissions
- Network connectivity from WordPress server to cPanel server
- PHP cURL extension enabled

## Setup Instructions

### Step 1: Gather cPanel Credentials

1. Log in to your cPanel account
2. Note your cPanel username
3. Create an API token or use your cPanel password (API token recommended):
   - Go to **Security > Manage API Tokens**
   - Click **Create** and name your token
   - Copy the generated token

4. Note your cPanel hostname (e.g., `server.yourdomain.com`)

### Step 2: Configure WordPress

Add the following constants to your `wp-config.php` file:

```php
// cPanel Email Provider Configuration
define('WU_CPANEL_USERNAME', 'your_cpanel_username');
define('WU_CPANEL_PASSWORD', 'your_cpanel_password_or_api_token');
define('WU_CPANEL_HOST', 'server.yourdomain.com');
define('WU_CPANEL_PORT', 2083); // Optional, defaults to 2083
```

**Important**: Place these constants BEFORE the line `/* That's all, stop editing! */`

### Step 3: Complete the Setup Wizard

1. Go to **WP Ultimo > Settings > Email Accounts**
2. Click **Configure** next to "cPanel Email"
3. Follow the wizard to test your connection
4. Once successful, the provider is ready to use

## Configuration Options

| Constant | Required | Default | Description |
|----------|----------|---------|-------------|
| `WU_CPANEL_USERNAME` | Yes | - | Your cPanel username |
| `WU_CPANEL_PASSWORD` | Yes | - | cPanel password or API token |
| `WU_CPANEL_HOST` | Yes | - | cPanel server hostname |
| `WU_CPANEL_PORT` | No | 2083 | cPanel port (2083 for HTTPS) |

## How It Works

### Email Account Creation

When a customer creates an email account:

1. Ultimate Multisite calls the cPanel UAPI `Email/add_pop` endpoint
2. The account is created with the specified username, password, and quota
3. The account becomes immediately available

### API Endpoint Used

```
POST https://{host}:{port}/execute/Email/add_pop
```

Parameters:
- `email`: Username portion of the email
- `password`: Generated password
- `quota`: Storage quota in MB (0 = unlimited)
- `domain`: Domain for the email account

### Password Changes

Password changes use the `Email/passwd_pop` UAPI endpoint.

### Account Deletion

Deletion uses the `Email/delete_pop` UAPI endpoint.

## Email Client Settings

Customers can configure their email clients with these settings:

### IMAP (Incoming Mail)

| Setting | Value |
|---------|-------|
| Server | Your cPanel hostname |
| Port | 993 |
| Security | SSL/TLS |
| Username | Full email address |

### SMTP (Outgoing Mail)

| Setting | Value |
|---------|-------|
| Server | Your cPanel hostname |
| Port | 465 |
| Security | SSL/TLS |
| Username | Full email address |

### Webmail

Customers can access webmail at:
```
https://{cpanel_host}:2096/
```

## DNS Configuration

For each domain using cPanel email, configure these DNS records:

```
Type    Host    Value                   Priority
MX      @       mail.yourdomain.com     10
A       mail    {server_ip_address}     -
```

### SPF Record (Recommended)

```
TXT     @       v=spf1 +a +mx ~all
```

### DKIM (Optional)

cPanel can generate DKIM records. Find them in:
**cPanel > Email > Email Deliverability**

## Troubleshooting

### "Connection failed" Error

1. **Check credentials**: Verify username and password/token are correct
2. **Check hostname**: Ensure the hostname resolves correctly
3. **Check port**: Default is 2083 for HTTPS
4. **Check firewall**: Ensure your WordPress server can connect to cPanel

### "Account creation failed" Error

1. **Domain not on cPanel**: The domain must be added to cPanel first
2. **Account exists**: An account with that username may already exist
3. **Quota exceeded**: Check your cPanel account's email quota limits

### "Permission denied" Error

Your cPanel account may not have permission to create email accounts. Contact your hosting provider to enable this feature.

### SSL Certificate Issues

If you're getting SSL errors, you may need to add:

```php
// Only use this for testing - not recommended for production
define('WU_CPANEL_VERIFY_SSL', false);
```

## Security Best Practices

1. **Use API Tokens**: Instead of your cPanel password, create a dedicated API token with limited permissions

2. **Restrict Token Permissions**: When creating an API token, only grant access to email-related functions

3. **Secure wp-config.php**: Ensure your `wp-config.php` file is not publicly accessible

4. **Use HTTPS**: Always use port 2083 (HTTPS) instead of 2082 (HTTP)

## Related Documentation

- [Email Accounts Integration](Email-Accounts-Integration)
- [cPanel API Documentation](https://api.docs.cpanel.net/cpanel/operation/Email-add_pop/)
