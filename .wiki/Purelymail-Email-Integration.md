# Purelymail Email Integration

Purelymail is a privacy-focused, affordable email hosting service that offers a simple API for email account management. It's an excellent choice for multisite operators who want reliable email without the complexity of enterprise solutions.

## Overview

- **Provider ID**: `purelymail`
- **API Type**: REST API
- **Best For**: Budget-conscious operators wanting reliable email
- **Webmail**: https://app.purelymail.com/
- **Affiliate Program**: Yes

## Features

- Simple REST API for account management
- Competitive pricing ($10/year unlimited domains, $0.10/user/month)
- Privacy-focused (no ads, no tracking)
- Automatic spam filtering
- DKIM/SPF/DMARC support
- Catch-all addresses

## Requirements

- Purelymail account with API access
- API key from Purelymail dashboard
- Domains verified in Purelymail

## Pricing

Purelymail offers straightforward pricing:

- **Routing Only**: $10/year for unlimited domains
- **Per User**: $0.10/user/month (billed annually)
- **Storage**: First 10GB included, then $3/year per additional 10GB

This makes it very cost-effective for multisite operators.

## Setup Instructions

### Step 1: Create a Purelymail Account

1. Visit [Purelymail](https://purelymail.com/?ref=ultimatemultisite)
2. Create an account and complete email verification
3. Add a payment method

### Step 2: Get Your API Key

1. Log in to the [Purelymail Dashboard](https://app.purelymail.com/)
2. Go to **Account Settings**
3. Find the **API** section
4. Generate or copy your API key

### Step 3: Configure WordPress

Add the following constant to your `wp-config.php` file:

```php
// Purelymail Email Provider Configuration
define('WU_PURELYMAIL_API_KEY', 'your_api_key_here');
```

**Important**: Place this constant BEFORE the line `/* That's all, stop editing! */`

### Step 4: Add Your Domains to Purelymail

Before customers can create email accounts, each domain must be added to your Purelymail account:

1. In Purelymail dashboard, go to **Domains**
2. Click **Add Domain**
3. Enter the domain name
4. Configure DNS records as instructed
5. Verify the domain

### Step 5: Complete the Setup Wizard

1. Go to **WP Ultimo > Settings > Email Accounts**
2. Click **Configure** next to "Purelymail"
3. Follow the wizard to test your connection
4. Once successful, the provider is ready to use

## Configuration Options

| Constant | Required | Description |
|----------|----------|-------------|
| `WU_PURELYMAIL_API_KEY` | Yes | Your Purelymail API key |

## How It Works

### Email Account Creation

When a customer creates an email account:

1. Ultimate Multisite calls the Purelymail API `/createUser` endpoint
2. The user is created under your Purelymail account
3. The account becomes immediately available

### API Endpoints Used

**Base URL**: `https://purelymail.com/api/v0`

| Operation | Endpoint | Method |
|-----------|----------|--------|
| Create Account | `/createUser` | POST |
| Delete Account | `/deleteUser` | POST |
| Change Password | `/modifyUserPassword` | POST |
| Get Account Info | `/getUser` | POST |

### Authentication

All API requests include the header:
```
Purelymail-Token: {your_api_key}
```

## Email Client Settings

Customers can configure their email clients with these settings:

### IMAP (Incoming Mail)

| Setting | Value |
|---------|-------|
| Server | mailserver.purelymail.com |
| Port | 993 |
| Security | SSL/TLS |
| Username | Full email address |

### SMTP (Outgoing Mail)

| Setting | Value |
|---------|-------|
| Server | mailserver.purelymail.com |
| Port | 465 |
| Security | SSL/TLS |
| Username | Full email address |

### Webmail

Customers can access webmail at:
```
https://app.purelymail.com/
```

## DNS Configuration

For each domain using Purelymail, configure these DNS records:

### Required Records

```
Type    Host                        Value                                   Priority
MX      @                           mailserver.purelymail.com               10
TXT     @                           v=spf1 include:_spf.purelymail.com ~all -
```

### DKIM Records

```
Type    Host                        Value
CNAME   purelymail1._domainkey      key1.dkimroot.purelymail.com
CNAME   purelymail2._domainkey      key2.dkimroot.purelymail.com
CNAME   purelymail3._domainkey      key3.dkimroot.purelymail.com
```

### DMARC Record (Recommended)

```
Type    Host        Value
TXT     _dmarc      v=DMARC1; p=quarantine; rua=mailto:dmarc@yourdomain.com
```

## Troubleshooting

### "Invalid API key" Error

1. Verify your API key is correct in `wp-config.php`
2. Generate a new API key if needed
3. Ensure there are no extra spaces or characters

### "Domain not found" Error

The domain must be added and verified in your Purelymail account before creating email accounts on it.

### "User already exists" Error

An email account with that address already exists in Purelymail. Check your Purelymail dashboard for existing accounts.

### "Rate limit exceeded" Error

Purelymail has API rate limits. If you're creating many accounts, space out the requests or contact Purelymail support.

### Connection Timeout

1. Check that your server can reach `purelymail.com`
2. Verify no firewall is blocking outgoing HTTPS connections
3. Check PHP cURL extension is enabled

## Best Practices

### Domain Management

1. **Pre-add domains**: Add all customer domains to Purelymail proactively
2. **Verify DNS**: Ensure DNS is configured before offering email on a domain
3. **Document DNS**: Provide customers with DNS instructions for their domains

### Account Management

1. **Set quotas**: Configure appropriate storage quotas for accounts
2. **Monitor usage**: Check your Purelymail dashboard for usage statistics
3. **Handle failures gracefully**: If account creation fails, provide clear error messages

### Cost Management

1. **Estimate users**: Plan for the number of email accounts your customers will create
2. **Monitor billing**: Keep track of per-user costs in Purelymail
3. **Set limits**: Use membership limits to control the number of email accounts

## Purelymail vs. Alternatives

| Feature | Purelymail | Google Workspace | Microsoft 365 |
|---------|------------|------------------|---------------|
| Starting Price | $10/year + $0.10/user/month | $6/user/month | $6/user/month |
| Unlimited Domains | Yes | No | No |
| API Complexity | Simple | Complex | Complex |
| Privacy Focus | High | Medium | Medium |
| Storage | 10GB included | 30GB | 50GB |

## Related Documentation

- [Email Accounts Integration](Email-Accounts-Integration)
- [Purelymail Official Documentation](https://purelymail.com/docs)
- [Purelymail API Reference](https://purelymail.com/docs/api)
