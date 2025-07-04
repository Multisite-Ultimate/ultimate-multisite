# Getting Started with the GoCardless Payment Gateway

_GoCardless_ is a payment gateway available in **Europe and in the UK** that allows for **Direct Debit** payments.

The **Multisite Ultimate: GoCardless Gateway** adds _GoCardless_ support to Multisite Ultimate, allowing you to charge your customers in that region using Direct Debit.

This tutorial will cover how to setup _GoCardless_ to start accepting payments through it on your Multisite Ultimate network.

_**Note: GoCardless only works with the following currencies:**_ **EUR, GBP, AUD, SEK, and DKK.**

## Installing the Add-on

First of all, you'll need to install the GoCardless add-on for Multisite Ultimate. You can do that by going to **Multisite Ultimate > Settings > Add-ons (on the sidebar)**. And picking the GoCardless add-on on that list.

After the add-on is installed and activated, go to **Multisite Ultimate > Settings > Payments**, toggle the GoCardless option, and save.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-VRRNzphbjJ.png)

## Getting an Access Token

Create an account on _GoCardless_ (or on the _GoCardless_ sandbox, if you want to **test it first** , which is recommended). Then, after logging in, navigate to the **Developers** menu item.

![Developers menu position](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-kENJK2VMJy.png)

On the **Developers** page, click on the **Create** button at the top-right corner of the screen, then click on the option **Access Token**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-UrGjq2m5TX.png)

This will open a new modal window, allowing you to create a new token. Give it a recognizable name and make sure you select the **Read-write access** option in the **Scope** menu.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-MDY7J5ylvj.png)

The access token will appear on the screen. Copy that over and paste it onto your **Multisite Ultimate > Settings > Payments > GoCardless** menu.

Be sure to place it on the right field. If it's a **live toke** **n** , toggle the **sandbox mode off** before pasting the token.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-3DsJSYPv1Z.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FlMS0mucqq.png)

That's it for the first step. Next, you need to setup a webhook listener.

## Setting up the Webhook Listener

When dealing with subscriptions, Multisite Ultimate needs to hear about changes in the subscription status, new payments being made, and other such things.

The way payment gateways notify Multisite Ultimate of those changes is using **webhook calls**. For that reason, in order for the integration to work 100%, we need to tell _GoCardless_ which URL to call with all that info.

Go back to your **GoCardless > Developers** panel and click the Create button again, but this time, select the **Webhook endpoint** option.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-p3PuOhRO6b.png)A new window will open with the webhook endpoint options. It asks for the webhook URL, so head back to your **Multisite Ultimate settings > Payments > GoCardless** and copy the webhook URL listed in there:

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-7GKJY7tRgV.png)

Additionally, you can enter a **webhook secret** that will be used to sign your webhook calls. Whatever you enter on that field, you'll need to enter on the **Multisite Ultimate Webhook Secret field** as well.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-ICXNY1PnA6.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-YIJv3UFhhz.png)

Finish up by saving the endpoint by clicking the **Create webhook endpoint** button and then **save your Multisite Ultimate settings as well**.

You should be ready to accept _GoCardless_ now!
