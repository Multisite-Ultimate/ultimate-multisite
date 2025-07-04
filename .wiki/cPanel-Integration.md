# cPanel Integration

## Overview
cPanel is one of the most popular web hosting control panels used by many shared and dedicated hosting providers. This integration enables automatic domain syncing between Multisite Ultimate and cPanel, allowing you to automatically add domain aliases and subdomains to your cPanel account.

## Features
- Automatic addon domain creation in cPanel
- Automatic subdomain creation in cPanel (for subdomain multisite installations)
- Domain removal when mappings are deleted

## Requirements
The following constants must be defined in your `wp-config.php` file:

```php
define('WU_CPANEL_USERNAME', 'your_cpanel_username');
define('WU_CPANEL_PASSWORD', 'your_cpanel_password');
define('WU_CPANEL_HOST', 'your_cpanel_host');
```

Optionally, you can also define:

```php
define('WU_CPANEL_PORT', 2083); // Default is 2083
define('WU_CPANEL_ROOT_DIR', '/public_html'); // Default is /public_html
```

## Setup Instructions

### 1. Get Your cPanel Credentials

1. Obtain your cPanel username and password from your hosting provider
2. Determine your cPanel host (usually `cpanel.yourdomain.com` or `yourdomain.com:2083`)

### 2. Add Constants to wp-config.php

Add the following constants to your `wp-config.php` file:

```php
define('WU_CPANEL_USERNAME', 'your_cpanel_username');
define('WU_CPANEL_PASSWORD', 'your_cpanel_password');
define('WU_CPANEL_HOST', 'your_cpanel_host');
```

Optionally, you can customize the port and root directory:

```php
define('WU_CPANEL_PORT', 2083); // Change if your cPanel uses a different port
define('WU_CPANEL_ROOT_DIR', '/public_html'); // Change if your document root is different
```

### 3. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the cPanel integration
5. Click "Save Changes"

## How It Works

### Addon Domains

When a domain is mapped in Multisite Ultimate:

1. The integration sends a request to cPanel's API to add the domain as an addon domain
2. The domain is configured to point to your root directory
3. When a domain mapping is removed, the integration will remove the addon domain from cPanel

### Subdomains

For subdomain multisite installations, when a new site is created:

1. The integration extracts the subdomain part from the full domain
2. It sends a request to cPanel's API to add the subdomain
3. The subdomain is configured to point to your root directory

## Important Notes

- The integration uses cPanel's API2 to communicate with your cPanel account
- Your cPanel account must have permissions to add addon domains and subdomains
- Some hosting providers may limit the number of addon domains or subdomains you can create
- The integration does not handle DNS configuration; you still need to point your domains to your server's IP address

## Troubleshooting

### API Connection Issues
- Verify that your cPanel username and password are correct
- Check that your cPanel host is correct and accessible
- Ensure that your cPanel account has the necessary permissions
- Try using the full URL for the host (e.g., `https://cpanel.yourdomain.com`)

### Domain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the domain is not already added to cPanel
- Ensure that your cPanel account has not reached its limit for addon domains or subdomains

### SSL Certificate Issues
- The integration does not handle SSL certificate issuance
- You will need to use cPanel's SSL/TLS tools or AutoSSL feature to issue SSL certificates for your domains
- Alternatively, you can use a service like Let's Encrypt with cPanel's AutoSSL
