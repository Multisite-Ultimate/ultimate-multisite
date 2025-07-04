# Code Snippets for v2

Basically, code snippets for **WordPress** are used to do certain actions that might otherwise require a dedicated smaller plugin. Such code snippets are placed in one of the WordPress core or theme files (generally the functions.php file of your theme) or they can be used as a MU plugin.

In this article we will show you three code snippets that can be used with **Multisite Ultimate v2** :

  * [**Changing the position of the Account menu item**](1677127282-code-snippets-for-v2.html#changing)

  * [**How to check if the user is under a given plan and/or has an active subscription**](1677127282-code-snippets-for-v2.html#plan)

  * [**Fixing CORS issues with Font -Icons in mapped domains**](1677127282-code-snippets-for-v2.html#fixing)

## Changing the position of the Account menu item

To change the position of the Account menu item on your client’s Dashboard, just add the following code snippet to the functions.php of your main site’s active theme. You can also put the snippet inside one of you mu-plugins or custom plugins.

add_filter('wu_my_account_menu_position', function() { return 10; // Tweak this value to place the menu in the desired position.

## How to check if the user is under a given plan and/or has an active subscription

As a network admin, you may need to create custom functions that will perform basic actions or make a service/feature available to a selected group of subscribers or end-users, based on the status of their subscription and the plan they are subscribed under.

These Multisite Ultimate native functions will help you with that.

To check if the user is a member of a given plan, you can use the function:

wu_has_plan($user_id, $plan_id)

To check if the subscription is active, you can use the function:

wu_is_active_subscriber($user_id)

Below is an example snippet that checks whether the current user is under a specific plan (_Plan ID 50_) and if the user subscription is active.

$user_id = get_current_user_id();$plan_id = 50;if (wu_has_plan($user_id, $plan_id) && wu_is_active_subscriber($user_id)) { // USER IS MEMBER OF PLAN AND HIS SUBSCRIPTION IS ACTIVE, DO STUFF} else { // USER IS NOT A MEMBER OF PLAN -- OR -- HIS SUBSCRIPTION IS NOT ACTIVE, DO OTHER STUFF} // end if;

Note that _**wu_has_plan**_ requires a "Plan ID" in order for it to function.

To get the ID of a plan, you can go to **Multisite Ultimate > Products**. The ID of each product will be shown on the right of the table.

Note that users can only be subscribed to a **Plan** , not a Package or Service, as they are only add-ons for a **Plan**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-LAYTqHqw5w.png)

## Fixing CORS issues with Font-Icons in mapped domains

After mapping a domain to a sub-site you might find out that the site is having trouble loading custom fonts. That’s caused by a cross-origin block on your server settings.

Since font files are almost always loaded directly from CSS, our domain mapping plugin is not able to rewrite the URLs to use the mapped domain instead of the original one, so in order to fix the issue, you’ll need to amend your server configuration files.

Below are code snippets to fix the issue for Apache and NGINX. These changes require advanced knowledge of server configuration files (.htaccess files and NGINX config files). If you are not comfortable with making those changes yourself, send this page to your hosting provider support agents when requiring assistance.

### Apache

On your .htaccess file, add:

<FilesMatch “.(ttf|ttc|otf|eot|woff|font.css|css)$”> Header set Access-Control-Allow-Origin “*” 

### NGINX

On your server config file (the location varies from server to server), add:

location ~ .(ttf|ttc|otf|eot|woff|font.css|css)$ { add_header Access-Control-Allow-Origin “*”;}
