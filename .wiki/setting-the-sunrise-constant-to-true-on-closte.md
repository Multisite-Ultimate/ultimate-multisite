# Setting the Sunrise constant to true on Closte

Some host providers lock the wp-config.php for security reasons. This means that Multisite Ultimate cannot automatically edit the file to include the necessary constants to get domain mapping and other features to work. Closte is one such host.

However, Closte offers a way to add constants to the wp-config.php in a secure manner. You just need to follow the steps below:

## On the Closte dashboard

First, [log into your Closte account](https://app.closte.com/), click on the Sites menu item, then click on the Dashboard link on the site you are currently working on:

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-dPRIeofCDK.png)

You be presented with a number of new menu items on the left side of the screen. Navigate to the **Settings** page using that menu:

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-JmSwHIOaGz.png)

Then, on the **Settings** , find the WP-Config tab, and then the "Additional wp-config.php content" field on that tab:

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FqVEnSTIu9.png)

In the context of installing Multisite Ultimate, you'll need to add the sunrise constant onto that field. Simply add a new line and paste the line below. After that, click the **Save All** button.

define('SUNRISE', true);

That's it, you're all set. Return to the Multisite Ultimate install wizard and refresh the page to continue the process.
