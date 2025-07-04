# Migrating from V1

## Multisite Ultimate has switched from its original 1.x family of releases to the 2.x family of releases.

Multisite Ultimate version 2.0 and up is a complete rewrite of the codebase, meaning that there's very little shared between the old version and the new one. For that reason, when upgrading from 1.x to 2.x, your data will need to be migrated to a format that the new versions can understand.

Thankfully, Multisite Ultimate 2.0+ **comes with a migrator** built into the core that is capable of detecting data from the old version and converting it to the new format. This migration happens during the **Setup Wizard** of version 2.0+.

This lesson covers how the migrator works, what to do in cases of failure, and how to troubleshoot issues that might arise during this process.

_**IMPORTANT: Before you begin upgrading from version 1.x to version 2.0 please make sure that you create a backup of your site database**_

## First steps

The first step is to download the plugin .zip file and install version 2.0 on your network admin dashboard.

After you [install and activate version 2.0](1677127281-installing-wp-ultimo.html), the system will automatically detect that your Multisite is running on the legacy version and you will see this message at the top of the plugin page.

_**NOTE:** If you have Multisite Ultimate 1.x installed on your Multisite, you'll have the option to replace the plugin with the version you've just downloaded. Please, go ahead and click to **Replace current with uploaded**._

![](https://support.delta.nextpress.co/rails/active_storage/blobs/redirect/eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaHBBcDRjIiwiZXhwIjpudWxsLCJwdXIiOiJibG9iX2lkIn19--c2aff9b312e5b7ec95c9e2c5355480d4aa7258fd/Migration.png)

The next page will let you know what legacy add-ons you have installed along with version 1.x. It will have instructions on whether the version you are using is compatible with version 2.0 or if you need to install an upgraded version of the add-on after the migration.

![Message on the top of the plugins page: Thanks for updating to Multisite Ultimate version 2.0. There's a link below it that leads the user to the version upgrader. Then, there's a list of add-ons that need to be updated.](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-4E9kAFlcb5.png)

Once you are ready to proceed, you can click the button that says **Visit the Installer to finish the upgrade**.

![Framed in red: button saying Visit the Installer to finish the upgrade](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-BnJrjt7Drw.png)

It will then bring you to the installation wizard page with some welcome messages. You just need to click **Get Started** to move to the next page.

![Setup Wizard's welcoming page. Framed in red at the bottom-right corner: Get Started button.](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-1dvRbsEnrN.png)

After clicking **Get Started** , it will redirect you to the Pre-install Checks_._ This will show you your System Information and WordPress installation and tell you if it meets [Multisite Ultimate's requirements](https://help.wpultimo.com/article/323-wp-ultimo-requirements).

![Pre-install Checks page showing confirmation messages that the installation meets Multisite Ultimate's requirements. Framed on red, on the bottom-right corner: Go to the next step button.](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-i0SwDNkaEO.png)

The next step is to key in your Multisite Ultimate license key and activate the plugin. This will ensure that all the features, including add-ons, will be available on your site.

![License activation page listing what the support includes and what it doesn't. There's a box on the bottom to insert the plugin's license. Framed in red, on the bottom-right corner: Agree and activate button.](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-QAwmR9oLQL.png)

After putting in your key, click **Agree & Activate**.

After license activation, you can begin the actual installation by clicking **Install** on the next page. This will automatically create the necessary files and database needed for version 2.0 to function.

![Installation page showing what will be updated in order to Multisite Ultimate to function. Framed in red, on the bottom-right: Install button](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FCyBH12y4d.png)

## Now, the migration

The migrator has a built-in safety feature wherein it will check your entire multisite to make sure that all your Multisite Ultimate data can be migrated without any issues. Click the **Run Check** button to start the process.

![Migration page explaining it will run a check to see if all your data from v1 can be converted. Framed in red, on the bottom-right corner: Run check button](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-vXLXFLjogz.png)

After running the check, you have two possibilities: the result can be either **with** an error or **without an error**.

### With Error

Should you get an error message, you will need to reach out to our support team so that they can assist you in fixing the error. Make sure you **provide the error log** when you create a ticket. You can download the log or you can click the link that says contact our support team. It will open the help widget on the right-hand side of your page with the fields pre-populated for you that include the error logs under the description.

_**Since the system found an error, you won't be able to proceed to migrate to version 2.0. You can then roll back to version 1.x to resume running your network until the error is fixed.**_

### Without Error

If the system doesn't find any error, you will see a success message and a **Migrate** button at the bottom that will allow you to proceed with the migration. On this page, you will be reminded to create a backup of your database before moving forward, which we strongly recommend. Hit **Migrate** if you already have a backup.

![Migration page showing a success message and a recommendation to create a backup.](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-T5ELIgTX5a.png)

![Framed in red, on the bottom-right corner: Migrate button](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Y2AfV93rpf.png)

And this is all it takes!

You can either continue to run the Wizard setup to update your logo and other things on your network or start navigating your Multisite Ultimate version 2.0 menu and its new interface. Go ahead and have some fun.
