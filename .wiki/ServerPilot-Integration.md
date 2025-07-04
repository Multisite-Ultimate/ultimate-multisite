# ServerPilot Integration

## Overview
ServerPilot is a cloud service for hosting WordPress and other PHP websites on servers at DigitalOcean, Amazon, Google, or any other server provider. This integration enables automatic domain syncing and SSL certificate management between Multisite Ultimate and ServerPilot.

## Features
- Automatic domain syncing
- SSL certificate management with Let's Encrypt
- Automatic SSL renewal

## Requirements
The following constants must be defined in your `wp-config.php` file:

```php
define('WU_SERVER_PILOT_CLIENT_ID', 'your_client_id');
define('WU_SERVER_PILOT_API_KEY', 'your_api_key');
define('WU_SERVER_PILOT_APP_ID', 'your_app_id');
```

## Setup Instructions

### 1. Get Your ServerPilot API Credentials

1. Log in to your ServerPilot dashboard
2. Go to "Account" > "API"
3. Create a new API key if you don't already have one
4. Copy your Client ID and API Key

### 2. Get Your App ID

1. In your ServerPilot dashboard, go to "Apps"
2. Select the app where your WordPress multisite is hosted
3. The App ID is visible in the URL: `https://manage.serverpilot.io/apps/{APP_ID}`

### 3. Add Constants to wp-config.php

Add the following constants to your `wp-config.php` file:

```php
define('WU_SERVER_PILOT_CLIENT_ID', 'your_client_id');
define('WU_SERVER_PILOT_API_KEY', 'your_api_key');
define('WU_SERVER_PILOT_APP_ID', 'your_app_id');
```

### 4. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the ServerPilot integration
5. Click "Save Changes"

## How It Works

### Domain Syncing

When a domain is mapped in Multisite Ultimate:

1. The integration retrieves the current list of domains from ServerPilot
2. It adds the new domain to the list (along with a www version if applicable)
3. It sends the updated list to ServerPilot via the API
4. ServerPilot updates the domain list for your application

### SSL Certificate Management

After domains are synced:

1. The integration automatically enables AutoSSL for your application
2. ServerPilot handles the SSL certificate issuance and installation using Let's Encrypt
3. ServerPilot also handles automatic renewal of SSL certificates

## SSL Certificate Verification

The integration is configured to increase the number of SSL certificate verification attempts for ServerPilot, as it may take some time for ServerPilot to issue and install SSL certificates. By default, it will try up to 5 times, but this can be adjusted using filters.

## Troubleshooting

### API Connection Issues
- Verify that your Client ID and API Key are correct
- Check that your App ID is correct
- Ensure that your ServerPilot account has the necessary permissions

### SSL Certificate Issues
- ServerPilot requires that domains have valid DNS records pointing to your server before issuing SSL certificates
- If SSL certificates are not being issued, check that your domains are properly pointing to your server's IP address
- It may take some time for ServerPilot to issue and install SSL certificates (usually 5-15 minutes)

### Domain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the domain is not already added to ServerPilot
- Ensure that your ServerPilot plan supports the number of domains you're adding

### Domain Removal
- Currently, the ServerPilot API does not provide a way to remove individual domains
- When a domain mapping is removed in Multisite Ultimate, the integration will update the domain list in ServerPilot to exclude the removed domain
