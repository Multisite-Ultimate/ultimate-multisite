# GridPane Integration

## Overview
GridPane is a specialized WordPress hosting control panel built for serious WordPress professionals. This integration enables automatic domain syncing and SSL certificate management between Multisite Ultimate and GridPane.

## Features
- Automatic domain syncing
- SSL certificate management
- Automatic configuration of SUNRISE constant

## Requirements
The following constants must be defined in your `wp-config.php` file:

```php
define('WU_GRIDPANE', true);
define('WU_GRIDPANE_API_KEY', 'your_api_key');
define('WU_GRIDPANE_SERVER_ID', 'your_server_id');
define('WU_GRIDPANE_APP_ID', 'your_app_id');
```

## Setup Instructions

### 1. Get Your GridPane API Credentials

1. Log in to your GridPane dashboard
2. Go to "Settings" > "API"
3. Generate an API key if you don't already have one
4. Copy your API key

### 2. Get Your Server and Site IDs

1. In your GridPane dashboard, go to "Servers"
2. Select the server where your WordPress multisite is hosted
3. Note the Server ID (visible in the URL or on the server details page)
4. Go to "Sites" and select your WordPress site
5. Note the Site ID (visible in the URL or on the site details page)

### 3. Add Constants to wp-config.php

Add the following constants to your `wp-config.php` file:

```php
define('WU_GRIDPANE', true);
define('WU_GRIDPANE_API_KEY', 'your_api_key');
define('WU_GRIDPANE_SERVER_ID', 'your_server_id');
define('WU_GRIDPANE_APP_ID', 'your_site_id');
```

### 4. Enable the Integration

1. In your WordPress admin, go to Multisite Ultimate > Settings
2. Navigate to the "Domain Mapping" tab
3. Scroll down to "Host Integrations"
4. Enable the GridPane integration
5. Click "Save Changes"

## How It Works

When a domain is mapped in Multisite Ultimate:

1. The integration sends a request to GridPane's API to add the domain to your site
2. GridPane automatically handles SSL certificate provisioning
3. When a domain mapping is removed, the integration will remove the domain from GridPane

The integration also automatically handles the SUNRISE constant in your wp-config.php file, which is required for domain mapping to work correctly.

## SUNRISE Constant Management

One unique feature of the GridPane integration is that it automatically reverts the SUNRISE constant in wp-config.php to prevent conflicts with GridPane's own domain mapping system. This ensures that both systems can work together without issues.

## Troubleshooting

### API Connection Issues
- Verify that your API key is correct
- Check that your server and site IDs are correct
- Ensure that your GridPane account has the necessary permissions

### SSL Certificate Issues
- GridPane may take some time to issue SSL certificates
- Verify that your domains are properly pointing to your server's IP address
- Check the GridPane SSL settings for your site

### Domain Not Added
- Check the Multisite Ultimate logs for any error messages
- Verify that the domain is not already added to GridPane
- Ensure that your domain's DNS records are properly configured
