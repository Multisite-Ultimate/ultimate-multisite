# Closte Integration

## Overview
Closte is a managed WordPress hosting platform built on Google Cloud infrastructure. This integration enables automatic domain syncing and SSL certificate management between Multisite Ultimate and Closte.

## Features
- Automatic domain syncing
- SSL certificate management
- Wildcard domain support
- No configuration needed if running on Closte

## Requirements
The following constant must be defined in your `wp-config.php` file if you're using Closte:

```php
define('CLOSTE_CLIENT_API_KEY', 'your_api_key');
```

This constant is typically already defined if you're hosting on Closte.

## Setup Instructions

### 1. Verify Your Closte API Key

If you're hosting on Closte, the `CLOSTE_CLIENT_API_KEY` constant should already be defined in your `wp-config.php` file. You can verify this by checking your `wp-config.php` file.

### 2. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the Closte integration
5. Click "Save Changes"

## How It Works

When a domain is mapped in Multisite Ultimate:

1. The integration sends a request to Closte's API to add the domain to your application
2. Closte automatically handles SSL certificate provisioning
3. When a domain mapping is removed, the integration will remove the domain from Closte

The integration also works with the DNS check interval setting in Multisite Ultimate, allowing you to configure how frequently the system checks for DNS propagation and SSL certificate issuance.

## Domain Record Creation

This integration ensures that when a site is created or duplicated, a domain record is automatically created. This is particularly important for the Closte integration, as the domain record creation triggers the Closte API to create the domain and SSL certificate.

## Troubleshooting

### API Connection Issues
- Verify that your Closte API key is correct
- Ensure that your Closte account has the necessary permissions

### SSL Certificate Issues
- Closte may take some time to issue SSL certificates (usually 5-10 minutes)
- Verify that your domains are properly pointing to your Closte server's IP address
- Check the DNS records for your domain to ensure they're correctly configured

### Domain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the domain is not already added to Closte
- Ensure that your domain's DNS records are properly configured

### DNS Check Interval
- If SSL certificates are taking too long to issue, you can adjust the DNS check interval in the Domain Mapping settings
- The default interval is 300 seconds (5 minutes), but you can set it as low as 10 seconds for faster checking during testing
