# WPMU DEV Integration

## Overview
WPMU DEV is a comprehensive WordPress platform that offers hosting, plugins, and services for WordPress sites. This integration enables automatic domain syncing and SSL certificate management between Multisite Ultimate and WPMU DEV hosting.

## Features
- Automatic domain syncing
- SSL certificate management
- Extended SSL certificate verification attempts

## Requirements
The integration automatically detects if you're hosting on WPMU DEV and uses the built-in API. No additional configuration is required if you're hosting on WPMU DEV.

The integration checks for the presence of the `WPMUDEV_HOSTING_SITE_ID` constant, which is automatically defined when hosting on WPMU DEV.

## Setup Instructions

### 1. Verify WPMU DEV Hosting

If you're hosting on WPMU DEV, the necessary constants should already be defined. Verify that:

1. The `WPMUDEV_HOSTING_SITE_ID` constant is defined in your environment
2. You have an active WPMU DEV membership with API access

### 2. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the WPMU DEV integration
5. Click "Save Changes"

## How It Works

### Domain Syncing

When a domain is mapped in Multisite Ultimate:

1. The integration uses the WPMU DEV API to add the domain to your hosting account
2. It also adds the www version of the domain automatically
3. WPMU DEV handles the domain configuration and SSL certificate issuance

### SSL Certificate Management

The integration is configured to increase the number of SSL certificate verification attempts for WPMU DEV hosting, as it may take some time for SSL certificates to be issued and installed. By default, it will try up to 10 times for SSL certificate verification, compared to the standard 5 attempts.

## Important Notes

### Domain Removal

Currently, the WPMU DEV API does not provide a way to remove domains. When a domain mapping is removed in Multisite Ultimate, the domain will remain in your WPMU DEV hosting account. You will need to manually remove it from the WPMU DEV hosting dashboard if necessary.

### API Authentication

The integration uses the WPMU DEV API key that is stored in your WordPress database as the `wpmudev_apikey` option. This is automatically set up when you connect your site to WPMU DEV.

## Troubleshooting

### API Connection Issues
- Verify that your site is properly connected to WPMU DEV
- Check that the `wpmudev_apikey` option is set in your WordPress database
- Ensure that your WPMU DEV membership is active

### SSL Certificate Issues
- WPMU DEV may take some time to issue SSL certificates (usually 5-15 minutes)
- The integration is configured to check up to 10 times for SSL certificates
- If SSL certificates are still not being issued after multiple attempts, contact WPMU DEV support

### Domain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the domain is not already added to WPMU DEV
- Ensure that your WPMU DEV hosting plan supports the number of domains you're adding
