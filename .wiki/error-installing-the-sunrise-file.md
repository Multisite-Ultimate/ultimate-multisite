# Error Installing the Sunrise File

The sunrise.php file is a special file that WordPress looks for while it bootstraps itself. For WordPress to be able to detect the sunrise.php file, it needs to be located inside the **wp-content folder**.

When you activate Multisite Ultimate and go through the setup wizard like the one you have on the screenshot, Multisite Ultimate tries to copy our sunrise.php file to the wp-content folder.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-v6hosgLWpt.png)

Most of the time, we’re able to successfully copy the file and everything works. However, if something is not properly set up (folder permissions, for example), you might run into a scenario where Multisite Ultimate is not able to copy the file.

If you read the error message Ultimo gives you, you’ll see that’s exactly what happened here: **Sunrise copy failed**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-RXS5EbirfM.png)

To fix that, you can simply copy the sunrise.php file inside the wp-ultimo plugin folder and paste it into your wp-content folder. After you do that, reload the wizard page and the checks should pass.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-p6hz1I9ycl.png) In any case, this might warrant a general check of your folder permissions to avoid having problems in the future (not only with Multisite Ultimate but with other plugins and themes as well).

The **Health Check tool** that is part of WordPress (you can access it via your main site **admin panel > Tools > Health Check**) is capable of letting you know if you have folder permissions set to values that might cause problems with WordPress.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-oZEKeyxo2E.png)
