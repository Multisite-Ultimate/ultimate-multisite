# Cloudflare Integration

## Overview
Cloudflare is a leading content delivery network (CDN) and security provider that helps protect and accelerate websites. This integration enables automatic domain management between Multisite Ultimate and Cloudflare, particularly for subdomain multisite installations.

## Features
- Automatic subdomain creation in Cloudflare
- Proxied subdomain support
- DNS record management
- Enhanced DNS record display in the Multisite Ultimate admin

## Requirements
The following constants must be defined in your `wp-config.php` file:

```php
define('WU_CLOUDFLARE_API_KEY', 'your_api_key');
define('WU_CLOUDFLARE_ZONE_ID', 'your_zone_id');
```

## Setup Instructions

### 1. Get Your Cloudflare API Key

1. Log in to your Cloudflare dashboard
2. Go to "My Profile" (click on your email in the top-right corner)
3. Select "API Tokens" from the menu
4. Create a new API token with the following permissions:
   - Zone.Zone: Read
   - Zone.DNS: Edit
5. Copy your API token

### 2. Get Your Zone ID

1. In your Cloudflare dashboard, select the domain you want to use
2. The Zone ID is visible in the "Overview" tab, in the right sidebar under "API"
3. Copy the Zone ID

### 3. Add Constants to wp-config.php

Add the following constants to your `wp-config.php` file:

```php
define('WU_CLOUDFLARE_API_KEY', 'your_api_token');
define('WU_CLOUDFLARE_ZONE_ID', 'your_zone_id');
```

### 4. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the Cloudflare integration
5. Click "Save Changes"

## How It Works

### Subdomain Management

When a new site is created in a subdomain multisite installation:

1. The integration sends a request to Cloudflare's API to add a CNAME record for the subdomain
2. The subdomain is configured to be proxied through Cloudflare by default (this can be changed with filters)
3. When a site is deleted, the integration will remove the subdomain from Cloudflare

### DNS Record Display

The integration enhances the DNS record display in the Multisite Ultimate admin by:

1. Fetching DNS records directly from Cloudflare
2. Displaying whether records are proxied or not
3. Showing additional information about the DNS records

## Important Notes

As of Cloudflare's recent updates, wildcard proxying is now available for all customers. This means that the Cloudflare integration is less critical for subdomain multisite installations than it used to be, as you can simply set up a wildcard DNS record in Cloudflare.

## Troubleshooting

### API Connection Issues
- Verify that your API token is correct and has the necessary permissions
- Check that your Zone ID is correct
- Ensure that your Cloudflare account has the necessary permissions

### Subdomain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the subdomain is not already added to Cloudflare
- Ensure that your Cloudflare plan supports the number of DNS records you're creating

### Proxying Issues
- If you don't want subdomains to be proxied, you can use the `wu_cloudflare_should_proxy` filter
- Some features may not work correctly when proxied (e.g., certain WordPress admin functions)
- Consider using Cloudflare's Page Rules to bypass the cache for admin pages
