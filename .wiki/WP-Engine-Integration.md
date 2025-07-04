# WP Engine Integration

## Overview
WP Engine is a premium managed WordPress hosting platform that provides optimized performance, security, and scalability for WordPress sites. This integration enables automatic domain syncing between Multisite Ultimate and WP Engine.

## Features
- Automatic domain syncing
- Subdomain support for multisite installations
- Seamless integration with WP Engine's existing systems

## Requirements
The integration automatically detects if you're hosting on WP Engine and uses the built-in WP Engine API. No additional configuration is required if the WP Engine plugin is active and properly configured.

However, if you need to manually configure the integration, you can define one of these constants in your `wp-config.php` file:

```php
define('WPE_APIKEY', 'your_api_key'); // Preferred method
// OR
define('WPE_API', 'your_api_key'); // Alternative method
```

## Setup Instructions

### 1. Verify WP Engine Plugin

If you're hosting on WP Engine, the WP Engine plugin should already be installed and activated. Verify that:

1. The WP Engine plugin is active
2. The file `wp-content/mu-plugins/wpengine-common/class-wpeapi.php` exists

### 2. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the WP Engine integration
5. Click "Save Changes"

## How It Works

### Domain Syncing

When a domain is mapped in Multisite Ultimate:

1. The integration uses the WP Engine API to add the domain to your WP Engine installation
2. WP Engine handles the domain configuration and SSL certificate issuance
3. When a domain mapping is removed, the integration will remove the domain from WP Engine

### Subdomain Support

For subdomain multisite installations:

1. The integration adds each subdomain to WP Engine when a new site is created
2. WP Engine handles the subdomain configuration
3. When a site is deleted, the integration will remove the subdomain from WP Engine

## Important Notes

### Wildcard Domains

For subdomain multisite installations, it's recommended to contact WP Engine support to request a wildcard domain configuration. This allows all subdomains to work automatically without needing to add each one individually.

### SSL Certificates

WP Engine automatically handles SSL certificate issuance and renewal for all domains added through this integration. No additional configuration is required.

## Troubleshooting

### API Connection Issues
- Verify that the WP Engine plugin is active and properly configured
- If you've manually defined the API key, check that it's correct
- Contact WP Engine support if you're having trouble with the API

### Domain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the domain is not already added to WP Engine
- Ensure that your WP Engine plan supports the number of domains you're adding

### Subdomain Issues
- If subdomains are not working, contact WP Engine support to request a wildcard domain configuration
- Verify that your DNS settings are correctly configured for the main domain and subdomains
