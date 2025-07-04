# Using Multisite Ultimate: MailChimp Integration

Syncing subscribers' emails with your MailChimp lists is really easy with our Multisite Ultimate: MailChimp Integration Add-on. This tutorial aims to help you get everything set up!

## Getting an API key

Before we start to get into the different settings available, we need a MailChimp API key. This will allow Multisite Ultimate to talk with the MailChimp API to retrieve lists, groups, and add email addresses to those lists and groups when users sign up.

Follow this tutorial to get your own MailChimp API key: [About API Keys | MailChimp](https://kb.mailchimp.com/integrations/api-integrations/about-api-keys)

[![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-zimbKYFZo-B4D1E28C-1203-4E9A-AC7B-D8E9B5659B93.png)](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-zimbKYFZo-B4D1E28C-1203-4E9A-AC7B-D8E9B5659B93.png)

_Add your MailChimp API key on_ _**Multisite Ultimate Settings - > Add-on Settings -> MailChimp**_

## Integration Mode

The MailChimp Integration add-on offers two “integration modes”, which means there are two different ways you can use the add-on to sync email addresses.

[![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-mc8dVzW5S-B7A9495E-05B8-4FBB-8954-80EBF4A9CB3B.png)](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-mc8dVzW5S-B7A9495E-05B8-4FBB-8954-80EBF4A9CB3B.png)

_Two integration modes for greater flexibility!_

### Mode 1: Multiple Lists

The default mode makes use of **Multiple Lists**. This mode will allow you to select multiple MailChimp lists for each of the plans. This is the best option if you segment your users into different lists.

For example, let’s suppose you have 3 different lists on your MailChimp account: **Newsletter** , **Plan A** , and **Plan B**. You want all users, regardless of their plan, to also be included on the Newsletter list, so they won’t miss your updates.

To achieve that, you can use the Multiple Lists mode to add both lists **Newsletter** and **Plan A** to the Plan A MailChimp Settings, and **Newsletter** and **Plan B** to the Plan B MailChimp Settings.

[![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-kZS8-S_eh-4145C1B8-B903-4DFF-9D92-AF7E0A61445E.png)](https://s3.amazonaws.com/helpscout.net/docs/assets/6017c85715d41b7c717cdcf9/images/6021265efb34b55df443e4ad/60212659ac2f834ec53865f3-kZS8-S%5Feh-4145C1B8-B903-4DFF-9D92-AF7E0A61445E.png)

_Select the default lists for new accounts and for canceled accounts_

### Mode 2: Single List + Multiple Groups

MailChimp allows you to segment your list into smaller groups of users called **Groups**. This allows you to have more control as you can send a campaign to a specific subset of your subscribers without having to create multiple copies of the same campaign on MailChimp (which makes it hard to analyze the metrics of that campaign afterward).

For example, you can have a single list called **My Network Subscribers,** and inside it have groups for **Plan A** and **Plan B**.

[![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-tOsErbJy6-A468DB31-B539-493E-AF5F-F44A4F90A974.png)](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-tOsErbJy6-A468DB31-B539-493E-AF5F-F44A4F90A974.png)

_Select the default lists and groups, if using the “single list + multiple groups” integration mode_

## Default Lists and Groups

After you select which integration mode makes more sense to your particular use case, it is time to select the default lists (and groups, depending on the mode). The default list will be used when a given plan does not have a specific List selected.

You can also select the default list for canceled accounts. Whenever a user terminates his or her subscription and removes his or her account from your network, his or her email address will be moved to the list set for canceled accounts.

## Enabling Double Subscription

On the same settings page, you’ll be able to enable **Double Subscription**. This option will add the user to both the default list selected AND the list selected to that user’s plan. If this option is not enabled, the user will only be added to the list selected for the user’s plan. If none is set for that particular plan, then the user will be added to the default list selected.

[![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-OPUDRnO94-5D4C267A-14EB-43B4-B86F-0328D330B521.png)](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-OPUDRnO94-5D4C267A-14EB-43B4-B86F-0328D330B521.png)

_You can have your users subscribed to both the default list and the plan’s list on sign-up_

## Selecting Lists and Groups for each of the Plans

Go to your **Plans** and select the plan you want to edit. Note that on the **Advanced Options** meta-box, a new tab called MailChimp will be available.

On that tab, you’ll be able to select multiple lists or a single list and multiple groups (depending on the integration mode) for that plan.

[![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-BMHjubpN5-C3183A30-687C-4837-8350-842CF959BB06.png)](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-60212659ac2f834ec53865f3-BMHjubpN5-C3183A30-687C-4837-8350-842CF959BB06.png)

_Select the Lists and Groups you want to use for each of your plans_

When a new user sign-up for that plan, his email address will be added to the selected lists and groups!
