# Microsoft 365 Email Integration

Microsoft 365 (formerly Office 365) integration enables provisioning of professional email accounts with Outlook. This enterprise solution includes the full Microsoft productivity suite and is ideal for businesses already invested in the Microsoft ecosystem.

## Overview

- **Provider ID**: `microsoft365`
- **API Type**: Microsoft Graph API
- **Best For**: Enterprise customers wanting Outlook and Microsoft apps
- **Webmail**: https://outlook.office365.com/
- **Minimum Plan**: Microsoft 365 Business Basic ($6/user/month)

## Features

- Outlook web and desktop clients
- Microsoft Teams, OneDrive, SharePoint included
- 50GB mailbox storage
- Enterprise-grade security with Microsoft Defender
- Advanced spam and malware protection
- Mobile apps for iOS and Android
- Integration with Windows and Microsoft Office

## Requirements

- Microsoft 365 tenant with admin access
- Azure AD application registration
- Application permissions for user management
- Domains verified in Microsoft 365 admin center
- Available Microsoft 365 licenses

## Pricing

Microsoft 365 is licensed per user:

| Plan | Price | Storage | Features |
|------|-------|---------|----------|
| Business Basic | $6/user/month | 50GB email, 1TB OneDrive | Web apps, Teams |
| Business Standard | $12.50/user/month | 50GB email, 1TB OneDrive | + Desktop apps |
| Business Premium | $22/user/month | 50GB email, 1TB OneDrive | + Advanced security |
| Enterprise E3 | $36/user/month | 100GB email, Unlimited OneDrive | Full enterprise |

## Setup Instructions

### Step 1: Register an Azure AD Application

1. Go to [Azure Portal](https://portal.azure.com/)
2. Navigate to **Azure Active Directory > App registrations**
3. Click **New registration**
4. Name: "Ultimate Multisite Email Integration"
5. Supported account types: "Accounts in this organizational directory only"
6. Redirect URI: Leave blank (not needed for client credentials)
7. Click **Register**
8. Note the **Application (client) ID** and **Directory (tenant) ID**

### Step 2: Create a Client Secret

1. In your app registration, go to **Certificates & secrets**
2. Click **New client secret**
3. Description: "Ultimate Multisite"
4. Expiration: Choose based on your security policy (24 months recommended)
5. Click **Add**
6. **Important**: Copy the secret value immediately (it won't be shown again)

### Step 3: Configure API Permissions

1. Go to **API permissions**
2. Click **Add a permission**
3. Select **Microsoft Graph**
4. Choose **Application permissions** (not Delegated)
5. Add these permissions:
   - `User.ReadWrite.All` - Create, read, update, delete users
   - `Directory.ReadWrite.All` - Manage directory data
6. Click **Grant admin consent for [Your Organization]**

### Step 4: Get Your License SKU ID

To assign licenses automatically, you need the SKU ID:

1. Open PowerShell and connect to Microsoft Graph:
   ```powershell
   Connect-MgGraph -Scopes "Organization.Read.All"
   Get-MgSubscribedSku | Select SkuPartNumber, SkuId
   ```

2. Common SKU Part Numbers:
   - `O365_BUSINESS_ESSENTIALS` - Business Basic
   - `O365_BUSINESS_PREMIUM` - Business Standard
   - `SPB` - Business Premium

3. Note the `SkuId` (GUID format) for your plan

### Step 5: Configure WordPress

Add the following constants to your `wp-config.php` file:

```php
// Microsoft 365 Email Provider Configuration
define('WU_MS365_CLIENT_ID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
define('WU_MS365_CLIENT_SECRET', 'your_client_secret_here');
define('WU_MS365_TENANT_ID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
define('WU_MS365_LICENSE_SKU', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'); // Optional
```

**Important**: Place these constants BEFORE the line `/* That's all, stop editing! */`

### Step 6: Add Domains to Microsoft 365

1. Go to [Microsoft 365 Admin Center](https://admin.microsoft.com/)
2. Navigate to **Settings > Domains**
3. Click **Add domain**
4. Complete domain verification (TXT record)
5. Configure email DNS records as instructed

### Step 7: Complete the Setup Wizard

1. Go to **WP Ultimo > Settings > Email Accounts**
2. Click **Configure** next to "Microsoft 365"
3. Follow the wizard to test your connection
4. Once successful, the provider is ready to use

## Configuration Options

| Constant | Required | Description |
|----------|----------|-------------|
| `WU_MS365_CLIENT_ID` | Yes | Azure AD application ID |
| `WU_MS365_CLIENT_SECRET` | Yes | Application client secret |
| `WU_MS365_TENANT_ID` | Yes | Azure AD tenant ID |
| `WU_MS365_LICENSE_SKU` | No | License SKU ID for auto-assignment |

## How It Works

### Email Account Creation

When a customer creates an email account:

1. Ultimate Multisite authenticates using client credentials flow
2. Creates the user via Microsoft Graph API
3. Optionally assigns a license (if `WU_MS365_LICENSE_SKU` is configured)
4. Sets up the mailbox with the specified password

### API Endpoints Used

**Base URL**: `https://graph.microsoft.com/v1.0`

| Operation | Endpoint | Method |
|-----------|----------|--------|
| Create User | `/users` | POST |
| Delete User | `/users/{id}` | DELETE |
| Update User | `/users/{id}` | PATCH |
| Get User | `/users/{id}` | GET |
| Assign License | `/users/{id}/assignLicense` | POST |

### Authentication Flow

1. Request access token from Azure AD token endpoint
2. Use client credentials (client ID + secret)
3. Token is cached and refreshed automatically
4. All API requests include Bearer token

### User Creation Payload

```json
{
  "accountEnabled": true,
  "displayName": "John Doe",
  "mailNickname": "john",
  "userPrincipalName": "john@domain.com",
  "passwordProfile": {
    "forceChangePasswordNextSignIn": false,
    "password": "generated_password"
  },
  "usageLocation": "US"
}
```

**Note**: `usageLocation` is required for license assignment. Default is "US".

## Email Client Settings

Customers can configure their email clients with these settings:

### IMAP (Incoming Mail)

| Setting | Value |
|---------|-------|
| Server | outlook.office365.com |
| Port | 993 |
| Security | SSL/TLS |
| Username | Full email address |

### SMTP (Outgoing Mail)

| Setting | Value |
|---------|-------|
| Server | smtp.office365.com |
| Port | 587 |
| Security | STARTTLS |
| Username | Full email address |

### Webmail

```
https://outlook.office365.com/
```

Or via Microsoft 365 portal:
```
https://www.office.com/
```

## DNS Configuration

For each domain using Microsoft 365 email, configure these DNS records:

### MX Record

```
Type    Host    Value                                       Priority
MX      @       {domain-com}.mail.protection.outlook.com    0
```

Replace `{domain-com}` with your domain using hyphens (e.g., `contoso-com`).

### SPF Record

```
Type    Host    Value
TXT     @       v=spf1 include:spf.protection.outlook.com -all
```

### Autodiscover (Required for Outlook)

```
Type    Host            Value
CNAME   autodiscover    autodiscover.outlook.com
```

### DKIM Records

Configure DKIM in Microsoft 365 Admin Center:
1. Go to **Settings > Domains**
2. Select your domain
3. Click **DKIM**
4. Enable DKIM signing
5. Add the provided CNAME records

### DMARC (Recommended)

```
Type    Host        Value
TXT     _dmarc      v=DMARC1; p=quarantine; rua=mailto:dmarc@yourdomain.com
```

## Troubleshooting

### "Invalid client" Error

1. Verify Client ID is correct (from Azure AD app registration)
2. Ensure the application is registered in the correct tenant
3. Check that the app hasn't been deleted

### "Invalid client secret" Error

1. Client secrets expire - check the expiration date
2. Generate a new secret if expired
3. Ensure no extra whitespace in the secret value

### "Insufficient privileges" Error

1. Verify API permissions are granted
2. Check that admin consent was given
3. Required permissions:
   - `User.ReadWrite.All`
   - `Directory.ReadWrite.All`

### "License not available" Error

1. Check available licenses in Microsoft 365 Admin Center
2. Purchase additional licenses if needed
3. Verify the SKU ID is correct

### "Domain not verified" Error

1. Ensure domain is added to Microsoft 365
2. Complete domain verification
3. Wait for DNS propagation (can take up to 48 hours)

### "Usage location required" Error

Users need a usage location for license assignment. The integration defaults to "US". Contact support if you need to change this.

## Security Best Practices

1. **Rotate client secrets**: Create new secrets before expiration and update `wp-config.php`

2. **Use least privilege**: Only grant necessary API permissions

3. **Monitor sign-ins**: Review Azure AD sign-in logs for unusual activity

4. **Enable Conditional Access**: Add policies to protect admin accounts

5. **Audit regularly**: Use Microsoft 365 security center for compliance

## License Management

### Automatic Assignment

If `WU_MS365_LICENSE_SKU` is configured, licenses are assigned automatically when users are created.

### Manual Assignment

If not using automatic assignment:
1. User is created without email capabilities
2. Admin must assign license in Microsoft 365 Admin Center
3. Email becomes available after license assignment

### License Costs

Factor per-user license costs into your pricing model. Consider:
- Microsoft 365 Business Basic: $6/user/month
- Overage charges for exceeding license count
- Annual vs. monthly billing discounts

## Comparison with Other Providers

| Feature | Microsoft 365 | Google Workspace | Purelymail |
|---------|--------------|------------------|------------|
| Price | $6+/user/month | $6+/user/month | $0.10/user/month |
| Storage | 50GB-100GB | 30GB-5TB | 10GB included |
| Productivity Apps | Yes | Yes | No |
| Desktop Apps | With Standard+ | No | No |
| API Complexity | Medium | High | Low |
| Setup Difficulty | Medium | High | Low |

## Related Documentation

- [Email Accounts Integration](Email-Accounts-Integration)
- [Microsoft 365 Admin Documentation](https://docs.microsoft.com/microsoft-365/admin/)
- [Microsoft Graph API Reference](https://docs.microsoft.com/graph/api/overview)
- [Azure AD App Registration](https://docs.microsoft.com/azure/active-directory/develop/quickstart-register-app)
