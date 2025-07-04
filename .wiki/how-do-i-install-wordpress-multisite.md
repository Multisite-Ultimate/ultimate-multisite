# How do I Install WordPress Multisite?

WordPress Multisite allows you to have a network of sites on a single installation. This is a built-in feature, but it’s not active by default.

Since Multisite Ultimate is a network-only plugin, in this tutorial, you are going to learn how to install and set up WordPress Multisite. This text is based on [How to Install and Setup WordPress Multisite Network](https://www.wpbeginner.com/wp-tutorials/how-to-install-and-setup-wordpress-multisite-network/), from WPBeginner.

**Things to pay attention to before creating your multisite network:**

  * Get good WordPress hosting! Websites on a network share the same server resources.

  * In case you have just a couple of sites with low traffic, shared hosting will probably work for you.

  * Most **Managed WordPress hosting providers** offer Multisite out-of-the-box (they install WordPress with Multisite already activated and configured for you). This is the case for WP Engine, Closte, Cloudways, etc. If you are not sure if that's the case for your host provider, contact their support before moving on with this tutorial.

  * It's also good to be familiar with installing WordPress and editing files using FTP .

_**IMPORTANT**_ **:** If you are setting up a multisite network on an existing WordPress website do not forget to:

  * Create a complete backup of your WordPress site

  * Deactivate all plugins on your site by going to your plugins page and selecting _Deactivate_ from bulk actions and then clicking _Apply_

[![](https://downloads.intercomcdn.com/i/o/141065015/09f448a371b8cab63280777c/Multisite+1.png)](https://downloads.intercomcdn.com/i/o/141065015/09f448a371b8cab63280777c/Multisite+1.png)

To enable Multisite, first connect to your site using a FTP client or cPanel file manager, and open your wp-config.php file for editing.

Before the _*That’s all, stop editing! Happy blogging.*_ line, add the following code snippet:

define('WP_ALLOW_MULTISITE', true);

Save and upload your wp-config.php file back to the server.

With the multisite feature enabled on your site, now it’s time to setup your network.

Go to **Tools » Network Setup**

[![](https://downloads.intercomcdn.com/i/o/141065542/5bb9b19a52ece96c52b659d8/Multisite+3.png)](https://downloads.intercomcdn.com/i/o/141065542/5bb9b19a52ece96c52b659d8/Multisite+3.png)

Now you need to tell WordPress what kind of domain structure you will be using for sites in your network: subdomains or subdirectories.

If you choose subdomains, you must change your DNS settings for domain mapping and make sure setup _**wildcard subdomains**_ for your multisite network.

Back to the Network Setup, give a title for your network and be sure that the email address in the Network admin email is correct. Click _Install_ to continue.

[![](https://downloads.intercomcdn.com/i/o/141066037/fd8a063b69988be1c372dac6/Multisite+4.png)](https://downloads.intercomcdn.com/i/o/141066037/fd8a063b69988be1c372dac6/Multisite+4.png)

Add this code, provided by WordPress, to your _**wp-config.php**_ :

define('MULTISITE', true); define('SUBDOMAIN_INSTALL', true); define('DOMAIN_CURRENT_SITE', 'multisite.local'); define('PATH_CURRENT_SITE', '/'); define('SITE_ID_CURRENT_SITE', 1); define('BLOG_ID_CURRENT_SITE', 1);

And this code, also provided by WordPress, to your _**.htaccess**_ file:

RewriteEngine On RewriteBase / RewriteRule ^index.php$ - [L]

# add a trailing slash to /wp-admin

RewriteRule ^wp-admin$ wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR] RewriteCond %{REQUEST_FILENAME} -d RewriteRule ^ - [L] RewriteRule ^(wp-(content|admin|includes)._) $1 [L] RewriteRule ^(._.php)$ $1 [L] RewriteRule . index.php [L]

Use an FTP client or a file manager (if you are using something like cPanel, for example) to copy and paste the code in these two files.

Finally, re-login to your WordPress site to access your multisite network.

**It is important to test and make sure that you are able to create a subsite on your WordPress Multisite installation before you install Multisite Ultimate.**

To create a subsite:

  1. Open your websites wp-admin

  2. Navigate to My Sites > Sites (/wp-admin/network/sites.php)

  3. Click Add New at the top

  4. Fill out all fields:

  * Site Address — Never use “www”

  * Subdomain: [siteaddress.yourdomain.com](http://siteaddress.yourdomain.com)

  * Subdirectory: [yourdomain.com/siteaddress](http://yourdomain.com/siteaddress)

  * Site Title — Title of the site, can be changed later

  * Admin Email — Set as the initial admin user for the subsite

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-hrA3XtntYQ.png)

After filling up the fields, click the "Add site" button. Once the new subsite is created, go ahead and access it to make sure that the subsite is functional.

## Common Problems:

### 1\. I can create new sites but they are not accessible.

If you chose subdomains, you also need to setup wildcard subdomains for your multisite network.

To do that, go to your Website’s hosting account’s control panel dashboard (e.g cPanel/Plesk/Direct Admin depending on your hosting provider).

Find an option for “Domains” or “Subdomains”. In some control panels it is labeled as “Domain administration”.

On the subdomain field, enter an asterisk (*). Then, it should ask you to select a domain name where you want the subdomain to be added under.

The document root for the selected domain name will automatically be detected. Click on the _Create_ or _Save_ button to add your wildcard subdomain. The entry should look “*.[mydomain.com](http://mydomain.com)”
