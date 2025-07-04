# Setting Up The WooCommerce Integration (v2)

_**IMPORTANT NOTE: This article refers to Multisite Ultimate version 2.x.**_

_**ATTENTION:** Multisite Ultimate: WooCommerce Integration requires WooCommerce to be activated at least on your main site._

We understand that _Stripe_ and _PayPal_ are not available in some countries which limit or hinders Multisite Ultimate users from effectively using our plugin. So we created an **add-on to integrate _WooCommerce_**.

_WooCommerce_ is a very popular eCommerce plugin. Developers around the world created add-ons to integrate different payment gateways to it. We took advantage of this to extend the payments gateways you can use in the Multisite Ultimate billing system.

## Setting It Up

First, you need to install the WooCommerce Integration add-on which is **free for licensed users**. To install it, click on Multisite Ultimate on your super admin dashboard and select **Add-ons**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-RRyV5DYA9v.png)

Here you can find all Multisite Ultimate add-ons. Click on the **Multisite Ultimate: WooCommerce Integration** add-on.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-4AiiRzTKme.png)

A window will pop up with the add-on details. Just click on **Install Now** *.*

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-JMfodJJZN5.png)

After the installation is done, you will be redirected to the plugins page. Here, just click on **Network Activate** and the _WooCommerce_ add-on will be activated on your network.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-LQkSxAF1Sn.png)

After activating it, if you still don't have the WooCommerce plugin installed and activated on your website, **you will receive a reminder**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-1U4DaZPAiR.png)

Once installed, you will see an additional option under the Multisite Ultimate payments settings. You can then **enable** the _WooCommerce_ option from there.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-HDQ1SG1pYO.png)

You can configure some basic settings like the display name that will show in the front end and instructions to guide your users in their checkout flow. Then you can go to your **Main site’s _WooCommerce_ settings** to enable and configure the payment gateway you prefer to use.

## How to manage the payment

The WooCommerce Integration works in a very similar way to the way our [Manual Gateway](https://help.wpultimo.com/article/427-setting-up-manual-payments) works. The downside is that every payment must be paid manually at the end of the billing period. Here is how the flow works:

  * Once the user’s billing cycle ends, the membership is put **on hold**. At the same time, an order will be created on the _WooCommerce_ install on your network’s main site.

  * Users then receive an invoice in their email, with a link to perform the payment (the link also appears on their billing history from their subsite dashboard). That link redirects the user to the _**WooCommerce**_ **checkout** , where users will be able to select one of the **available _WooCommerce_ gateways** in order to process their payment.

  * Once the payment is received, the membership is **renewed** , the payment is logged on Multisite Ultimate and the order is set to complete.

### Some Important Notes:

  * The default functionality of Multisite Ultimate still works with this integration form. For example, **issuing refunds** from the **Subscription Management** screen will communicate with WooCommerce to issue the refund using the selected payment form.

  * Multisite Ultimate: WooCommerce Integration relies on WP-Cron to work. This works very well most times, but if the site has low traffic then the cron is not prompted to check for scheduled events and the event is missed, which can cause execution delays.

## Woocommerce Subscriptions Plugin

Manually initiating a payment each month is not ideal for end-users to do as a subscription is expected to automatically recur every billing cycle. So we designed the WooCommerce Integration addon to **support the WooCommerce Subscriptions plugin**. This will automate the payment to make the transaction easier for your end-users.

All you need to do is **install** the [Woocommerce Subscriptions plugin](https://woocommerce.com/products/woocommerce-subscriptions/) and have it **activated on your main site**. Then go back to Multisite Ultimate payments settings and **enable the integration**.

And that is it. Your end-users will no longer need to manually make a payment at the end of their billing cycle.

Aside from an order being created on your main site under _Woocommerce_ , it will also create a subscription for each account.
