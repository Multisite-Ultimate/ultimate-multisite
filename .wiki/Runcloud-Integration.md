# RunCloud Integration

## Overview
RunCloud is a cloud-based server management platform that allows you to easily deploy and manage web applications on your own cloud servers. This integration enables automatic domain syncing and SSL certificate management between Multisite Ultimate and RunCloud.

## Features
- Automatic domain syncing
- SSL certificate management
- Domain removal when mappings are deleted

## Requirements
The following constants must be defined in your `wp-config.php` file:

```php
define('WU_RUNCLOUD_API_KEY', 'your_api_key');
define('WU_RUNCLOUD_API_SECRET', 'your_api_secret');
define('WU_RUNCLOUD_SERVER_ID', 'your_server_id');
define('WU_RUNCLOUD_APP_ID', 'your_app_id');
```

## Setup Instructions

### 1. Get Your RunCloud API Credentials

1. Log in to your RunCloud dashboard
2. Go to "User Profile" (click on your profile picture in the top-right corner)
3. Select "API" from the menu
4. Click "Generate API Key" if you don't already have one
5. Copy your API Key and API Secret

### 2. Get Your Server and App IDs

1. In your RunCloud dashboard, go to "Servers"
2. Select the server where your WordPress multisite is hosted
3. The Server ID is visible in the URL: `https://manage.runcloud.io/servers/{SERVER_ID}`
4. Go to "Web Applications" and select your WordPress application
5. The App ID is visible in the URL: `https://manage.runcloud.io/servers/{SERVER_ID}/apps/{APP_ID}`

### 3. Add Constants to wp-config.php

Add the following constants to your `wp-config.php` file:

```php
define('WU_RUNCLOUD_API_KEY', 'your_api_key');
define('WU_RUNCLOUD_API_SECRET', 'your_api_secret');
define('WU_RUNCLOUD_SERVER_ID', 'your_server_id');
define('WU_RUNCLOUD_APP_ID', 'your_app_id');
```

### 4. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the RunCloud integration
5. Click "Save Changes"

## How It Works

When a domain is mapped in Multisite Ultimate:

1. The integration sends a request to RunCloud's API to add the domain to your application
2. If the domain is successfully added, the integration will also redeploy SSL certificates
3. When a domain mapping is removed, the integration will remove the domain from RunCloud

For subdomain installations, the integration will automatically handle the creation of subdomains in RunCloud when new sites are added to your network.

## Troubleshooting

### API Connection Issues
- Verify that your API credentials are correct
- Check that your server and app IDs are correct
- Ensure that your RunCloud account has the necessary permissions

### SSL Certificate Issues
- RunCloud may take some time to issue SSL certificates
- Verify that your domains are properly pointing to your server's IP address
- Check the RunCloud SSL settings for your application

### Domain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the domain is not already added to RunCloud
- Ensure that your RunCloud plan supports multiple domains
