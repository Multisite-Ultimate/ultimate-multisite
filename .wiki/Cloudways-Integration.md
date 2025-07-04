# Cloudways Integration

## Overview
Cloudways is a managed cloud hosting platform that allows you to deploy WordPress sites on various cloud providers like DigitalOcean, AWS, Google Cloud, and more. This integration enables automatic domain syncing and SSL certificate management between Multisite Ultimate and Cloudways.

## Features
- Automatic domain syncing
- SSL certificate management
- Support for extra domains
- DNS validation for SSL certificates

## Requirements
The following constants must be defined in your `wp-config.php` file:

```php
define('WU_CLOUDWAYS_EMAIL', 'your_cloudways_email');
define('WU_CLOUDWAYS_API_KEY', 'your_api_key');
define('WU_CLOUDWAYS_SERVER_ID', 'your_server_id');
define('WU_CLOUDWAYS_APP_ID', 'your_app_id');
```

Optionally, you can also define:

```php
define('WU_CLOUDWAYS_EXTRA_DOMAINS', 'comma,separated,list,of,domains');
```

## Setup Instructions

### 1. Get Your Cloudways API Credentials

1. Log in to your Cloudways dashboard
2. Go to "Account" > "API Keys"
3. Generate an API key if you don't already have one
4. Copy your email and API key

### 2. Get Your Server and Application IDs

1. In your Cloudways dashboard, go to "Servers"
2. Select the server where your WordPress multisite is hosted
3. The Server ID is visible in the URL: `https://platform.cloudways.com/server/{SERVER_ID}`
4. Go to "Applications" and select your WordPress application
5. The App ID is visible in the URL: `https://platform.cloudways.com/server/{SERVER_ID}/application/{APP_ID}`

### 3. Add Constants to wp-config.php

Add the following constants to your `wp-config.php` file:

```php
define('WU_CLOUDWAYS_EMAIL', 'your_cloudways_email');
define('WU_CLOUDWAYS_API_KEY', 'your_api_key');
define('WU_CLOUDWAYS_SERVER_ID', 'your_server_id');
define('WU_CLOUDWAYS_APP_ID', 'your_app_id');
```

If you have additional domains that should always be included:

```php
define('WU_CLOUDWAYS_EXTRA_DOMAINS', 'domain1.com,domain2.com,*.wildcard.com');
```

### 4. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the Cloudways integration
5. Click "Save Changes"

## How It Works

### Domain Syncing

When a domain is mapped in Multisite Ultimate:

1. The integration retrieves all currently mapped domains
2. It adds the new domain to the list (along with a www version if applicable)
3. It sends the complete list to Cloudways via the API
4. Cloudways updates the domain aliases for your application

Note: The Cloudways API requires sending the complete list of domains each time, not just adding or removing individual domains.

### SSL Certificate Management

After domains are synced:

1. The integration checks which domains have valid DNS records pointing to your server
2. It sends a request to Cloudways to install Let's Encrypt SSL certificates for those domains
3. Cloudways handles the SSL certificate issuance and installation

## Extra Domains

The `WU_CLOUDWAYS_EXTRA_DOMAINS` constant allows you to specify additional domains that should always be included when syncing with Cloudways. This is useful for:

- Domains that are not managed by Multisite Ultimate
- Wildcard domains (e.g., `*.example.com`)
- Development or staging domains

## Troubleshooting

### API Connection Issues
- Verify that your email and API key are correct
- Check that your server and application IDs are correct
- Ensure that your Cloudways account has the necessary permissions

### SSL Certificate Issues
- Cloudways requires that domains have valid DNS records pointing to your server before issuing SSL certificates
- The integration validates DNS records before requesting SSL certificates
- If SSL certificates are not being issued, check that your domains are properly pointing to your server's IP address

### Domain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the domain is not already added to Cloudways
- Ensure that your Cloudways plan supports the number of domains you're adding
