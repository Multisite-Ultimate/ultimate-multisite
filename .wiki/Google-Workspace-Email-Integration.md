# Google Workspace Email Integration

Google Workspace (formerly G Suite) integration allows you to provision professional email accounts with Gmail's powerful interface. This is ideal for businesses that want the reliability and features of Google's enterprise email solution.

## Overview

- **Provider ID**: `google_workspace`
- **API Type**: Google Admin SDK Directory API
- **Best For**: Enterprise customers wanting Gmail
- **Webmail**: https://mail.google.com/
- **Minimum Plan**: Google Workspace Business Starter ($6/user/month)

## Features

- Gmail interface for all email accounts
- Google Calendar, Drive, Docs, and Meet included
- 30GB-5TB storage per user (plan dependent)
- Advanced spam filtering
- Enterprise-grade security
- Mobile apps for iOS and Android

## Requirements

- Google Workspace account with Admin privileges
- Google Cloud project with Admin SDK enabled
- Service account with domain-wide delegation
- Domains verified in Google Workspace admin console

## Pricing

Google Workspace is licensed per user:

| Plan | Price | Storage | Features |
|------|-------|---------|----------|
| Business Starter | $6/user/month | 30GB | Email, Calendar, Meet (100 participants) |
| Business Standard | $12/user/month | 2TB | + Recording, 150 participants |
| Business Plus | $18/user/month | 5TB | + eDiscovery, vault |
| Enterprise | Custom | Unlimited | + Advanced security |

## Setup Instructions

### Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Note your project ID

### Step 2: Enable the Admin SDK API

1. In Google Cloud Console, go to **APIs & Services > Library**
2. Search for "Admin SDK API"
3. Click **Enable**

### Step 3: Create a Service Account

1. Go to **APIs & Services > Credentials**
2. Click **Create Credentials > Service Account**
3. Name your service account (e.g., "ultimate-multisite-email")
4. Grant no additional roles (we'll use domain-wide delegation)
5. Click **Done**

### Step 4: Create Service Account Key

1. Click on your new service account
2. Go to the **Keys** tab
3. Click **Add Key > Create new key**
4. Select **JSON** format
5. Download and save the JSON file securely

### Step 5: Enable Domain-Wide Delegation

1. In the service account details, click **Show Advanced Settings**
2. Enable **Domain-wide Delegation**
3. Note the **Client ID** (a long number)

### Step 6: Authorize in Google Workspace Admin

1. Go to [Google Workspace Admin Console](https://admin.google.com/)
2. Navigate to **Security > Access and data control > API controls**
3. Click **Manage Domain Wide Delegation**
4. Click **Add new**
5. Enter the **Client ID** from Step 5
6. Add these OAuth scopes:
   ```
   https://www.googleapis.com/auth/admin.directory.user
   ```
7. Click **Authorize**

### Step 7: Get Your Customer ID

1. In Google Workspace Admin Console, go to **Account > Account settings**
2. Find your **Customer ID** (starts with "C")

### Step 8: Configure WordPress

Add the following constants to your `wp-config.php` file:

```php
// Google Workspace Email Provider Configuration
define('WU_GOOGLE_SERVICE_ACCOUNT_JSON', '/path/to/service-account.json');
define('WU_GOOGLE_ADMIN_EMAIL', 'admin@yourdomain.com');
define('WU_GOOGLE_CUSTOMER_ID', 'C0xxxxxxx');
```

**Options for the JSON file**:

Option A: File path (recommended for security)
```php
define('WU_GOOGLE_SERVICE_ACCOUNT_JSON', '/secure/path/outside/webroot/service-account.json');
```

Option B: Inline JSON (if file storage isn't possible)
```php
define('WU_GOOGLE_SERVICE_ACCOUNT_JSON', '{"type":"service_account","project_id":"..."}');
```

**Important**:
- The admin email must be a super admin in your Google Workspace
- Place constants BEFORE the line `/* That's all, stop editing! */`

### Step 9: Add Domains to Google Workspace

1. In Google Admin Console, go to **Account > Domains**
2. Click **Add a domain**
3. Complete domain verification
4. Set up email routing (MX records)

### Step 10: Complete the Setup Wizard

1. Go to **WP Ultimo > Settings > Email Accounts**
2. Click **Configure** next to "Google Workspace"
3. Follow the wizard to test your connection
4. Once successful, the provider is ready to use

## Configuration Options

| Constant | Required | Description |
|----------|----------|-------------|
| `WU_GOOGLE_SERVICE_ACCOUNT_JSON` | Yes | Path to JSON key file or JSON string |
| `WU_GOOGLE_ADMIN_EMAIL` | Yes | Super admin email for impersonation |
| `WU_GOOGLE_CUSTOMER_ID` | Yes | Google Workspace customer ID |

## How It Works

### Email Account Creation

When a customer creates an email account:

1. Ultimate Multisite authenticates using the service account
2. It impersonates the admin email for API access
3. Creates the user via Admin SDK Directory API
4. Sets the password and email routing

### API Endpoints Used

**Base URL**: `https://admin.googleapis.com/admin/directory/v1`

| Operation | Endpoint | Method |
|-----------|----------|--------|
| Create User | `/users` | POST |
| Delete User | `/users/{userKey}` | DELETE |
| Update User | `/users/{userKey}` | PUT |
| Get User | `/users/{userKey}` | GET |

### Authentication Flow

1. Load service account credentials from JSON
2. Create JWT signed with service account private key
3. Exchange JWT for access token (with subject impersonation)
4. Use access token for API requests

## Email Client Settings

Customers can configure their email clients with these settings:

### IMAP (Incoming Mail)

| Setting | Value |
|---------|-------|
| Server | imap.gmail.com |
| Port | 993 |
| Security | SSL/TLS |
| Username | Full email address |

### SMTP (Outgoing Mail)

| Setting | Value |
|---------|-------|
| Server | smtp.gmail.com |
| Port | 465 or 587 |
| Security | SSL/TLS or STARTTLS |
| Username | Full email address |

**Note**: Users may need to enable "Less secure app access" or create an App Password for third-party email clients.

### Webmail

```
https://mail.google.com/
```

Or with account selection:
```
https://mail.google.com/mail/u/0/
```

## DNS Configuration

For each domain using Google Workspace email, configure these DNS records:

### MX Records

```
Type    Host    Value                           Priority
MX      @       aspmx.l.google.com              1
MX      @       alt1.aspmx.l.google.com         5
MX      @       alt2.aspmx.l.google.com         5
MX      @       alt3.aspmx.l.google.com         10
MX      @       alt4.aspmx.l.google.com         10
```

### SPF Record

```
Type    Host    Value
TXT     @       v=spf1 include:_spf.google.com ~all
```

### DKIM

DKIM is configured in Google Admin Console:
1. Go to **Apps > Google Workspace > Gmail > Authenticate email**
2. Generate DKIM record
3. Add the provided TXT record to DNS

### DMARC (Recommended)

```
Type    Host        Value
TXT     _dmarc      v=DMARC1; p=quarantine; rua=mailto:dmarc@yourdomain.com
```

## Troubleshooting

### "Invalid credentials" Error

1. Verify the service account JSON file exists and is readable
2. Ensure the JSON file is valid (not corrupted)
3. Regenerate the key if needed

### "Not authorized" Error

1. Check domain-wide delegation is enabled
2. Verify the OAuth scopes are correct in Admin Console
3. Ensure the Client ID matches your service account
4. Confirm the admin email is a super admin

### "User not found" or "Domain not found" Error

1. Verify the domain is added to Google Workspace
2. Check the domain is verified
3. Ensure email routing is configured

### "Quota exceeded" Error

Google APIs have rate limits. If creating many accounts:
1. Space out requests
2. Request quota increase from Google Cloud Console

### Service Account Issues

1. **File not found**: Check the path to your JSON file
2. **Permission denied**: Ensure web server can read the file
3. **Invalid JSON**: Validate the JSON structure

## Security Best Practices

1. **Protect the service account key**: Store outside web root with restrictive permissions

2. **Use a dedicated admin**: Create an admin account specifically for API access

3. **Audit access**: Regularly review Admin Console security reports

4. **Enable 2FA**: Require 2-factor authentication for all admin accounts

5. **Limit delegation scope**: Only grant necessary OAuth scopes

## License Management

Google Workspace requires a license for each user. Consider:

1. **Automatic licensing**: New users get the default license
2. **License costs**: Factor per-user costs into your pricing
3. **License limits**: Monitor your available licenses

## Related Documentation

- [Email Accounts Integration](Email-Accounts-Integration)
- [Google Workspace Admin Help](https://support.google.com/a)
- [Admin SDK Directory API](https://developers.google.com/admin-sdk/directory)
- [Domain-Wide Delegation](https://developers.google.com/identity/protocols/oauth2/service-account#delegatingauthority)
