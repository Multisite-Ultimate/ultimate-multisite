# How to use Multisite Ultimate snippets on our GitHub repository

There are code snippets available in GitHub repository that are frequently requested by Multisite Ultimate users who wish to add small functionalities like adding Google Analytics script on sign-up pages or hiding a meta box from the admin dashboard.

This article will show you how to use or more specifically where to place these codes.

You can find the snippets on the link below.

<https://github.com/next-press/wp-ultimo-snippets/>

There are 2 ways for you to add the code

  1. On your theme's functions.php file.

  2. Must-Use Plugins (mu-plugins)

# How to add the snippet on your theme's functions.php file.

  1. Log into your WordPress Network admin dashboard and go to Themes >Theme Editor (See screenshot below).

  2. On the "Edit Themes" page, make sure that you have your active theme selected on the dropdown field located on the upper right-hand side of your screen (#3 on the screenshot below).

  3. Click the functions.php file under the "Theme Files" section to load the file. Scroll down at the bottom and paste the Multisite Ultimate snippet you got from GitHub repository.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-image.png)

# How to create Must-Use Plugins (mu-plugins)

WordPress has a feature that allows you to load custom functionality called "Must-Use Plugins", or "mu-plugins" for short.

These special mu-plugins are loaded before all other regular plugins, and they can’t be deactivated. In a multisite network, the code in these mu-plugins will be loaded on all the sites in your installation.

1\. Use FTP or SSH to access the filesystem of your WordPress install.

2\. Inside the wp-content directory of your WordPress install, create a new directory named: mu-plugins.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-image.png)

3\. Create a new PHP file on your computer named wu-snippet.php using Notepad or any code editor.

4\. Place the Multisite Ultimate code snippet you got GitHub repository into the file and save it. You can also add this code on top of the code snippet to label your mu plugin.
