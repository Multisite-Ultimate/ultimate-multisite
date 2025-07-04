# Getting Paid (v2)

_**IMPORTANT NOTE: This article refers to Multisite Ultimate version 2.x.**_

Multisite Ultimate has a built-in membership and billing system. For our billing system to function, we have integrated the most common payment gateways used in e-commerce. The default payment gateways in Multisite Ultimate are _Stripe_ , _PayPal_ , and Manual Payment. You can also use _WooCommerce_ , _GoCardless_ and _Payfast_ to receive payments by installing their respective add-ons.

## Basic Settings

You can configure any of these payment gateways under Multisite Ultimate payment settings. You can find it by going to **Multisite Ultimate menu > Settings > Payments.**

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-42sl37Fn5G.png)

Before you setup your payment gateway, please take a look at the basic payment settings you can configure:

**Force auto-rene** **w:** This will make sure that the payment will automatically recur at the end of every billing cycle depending on the billing frequency the user selected.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Og3iJwLdmn.png)

**Allow trials without payment** **method:** With this option enabled your client won't have to add any financial information during the registration process. This will only be required once the trial period expires.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-aA5Olqe9M9.png)

**Send invoice on payment confirmation:** This gives you an option whether or not to send an invoice after payment. Note that users will have access to their payment history under their subsite dashboard. This option doesn't apply to the Manual Gateway.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-RGupao7GvW.png)

**Invoice numbering scheme:** Here, you can select either a payment reference code or a sequential number scheme. If you choose to use a payment reference code for your invoices, you don't need to configure anything. If you choose to use a sequential number scheme, you will need to configure the **next invoice number** (This number will be used as the invoice number for the next invoice generated on the system. It is incremented by one every time a new invoice is created. You can change it and save it to reset the invoice sequential number to a specific value) and the **invoice number prefix.**

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-mP0949Eawa.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-oD5LaLaw7t.png)

## Where to find the gateways:

You can setup the payment gateways on the same page ( **Multisite Ultimate > Settings > Payments**). Right below **active payment gateways** , you will be able to see: _Stripe_ , _Stripe_ _Checkout_ , _PayPal_ and _Manual_.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-g9RMYx84r5.png)

We have a dedicated article for each payment gateway that will guide you through the steps of setting it up which you can find on the links below.

[Setting up the Stripe gateway](https://help.wpultimo.com/article/428-setting-up-the-stripe-gateway)

[Setting up the PayPal gateway](https://help.wpultimo.com/article/429-setting-up-the-paypal-gateway)[ ](https://help.wpultimo.com/article/271-how-to-integrate-with-paypal)

[Setting up manual payments](https://help.wpultimo.com/article/427-setting-up-manual-payments)

Now, if you want to use _WooCommerce_ , _GoCardless_ or _Payfast_ as your payment gateway, you will need to **install and configure their add-ons**.

### How to install the WooCommerce add-on:

We understand that _Stripe_ and _PayPal_ are not available in some countries which limit or hinders Multisite Ultimate users from effectively using our plugin. So we created an add-on to integrate _WooCommerce,_ which is a very popular e-commerce plugin. Developers around the world created add-ons to integrate different payment gateways to it. We took advantage of this to extend the payment gateways you can use with the Multisite Ultimate billing system.

_**IMPORTANT:** Multisite Ultimate: WooCommerce Integration requires WooCommerce to be activated at least on your main site._

First, please go to the add-ons page. You can find it by going to **Multisite Ultimate > Settings**. You should see the **Add-ons** table. Click on **Check our Add-ons**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-WtOkJNuCsj.png)

After clicking on **Check our Add-ons** , you will be redirected to the add-ons page. Here you can find all Multisite Ultimate add-ons. Click on the **Multisite Ultimate: WooCommerce Integration** add-on.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-BmLWqj4yjt.png)

A window will pop up with the add-on details. Just click on **Install Now**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-fGaxHyPtsv.png)

After the installation is done, you will be redirected to the plugins page. Here, just click on **Network Activate** and the WooCommerce add-on will be activated on your network.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-TM2lYtgyM7.png)

After activating it, if you still don't have the WooCommerce plugin installed and activated on your website, you will receive a reminder.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-VwIGoJhzqc.png)

To read more about the WooCommerce Integration add-on, [click here](https://help.wpultimo.com/article/430-setting-up-the-woocommerce-integration).

### How to install the GoCardless add-on:

The steps to install the _GoCardless_ add-on are pretty much the same as the _WooCommerce_ add-on. Please go to the add-ons page and select the **Multisite Ultimate: GoCardless Gateway** add-on.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-BmLWqj4yjt.png)

The add-on window will pop up. Click on **Install Now**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-YIpPgP4VVo.png)

After the installation is done, you will be redirected to the plugins page. Here, just click on **Network Activate** and the _GoCardless_ add-on will be activated on your network.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FGurJpzaF0.png)

To learn how to get started with the _GoCardless_ gateway, [read this article](https://help.wpultimo.com/article/341-getting-started-with-the-gocardless-payment-gateway).

### How to install the Payfast add-on:

Go to the add-ons page and select the **Multisite Ultimate: Payfast Gateway** add-on.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-BmLWqj4yjt.png)

The add-on window will pop up. Click on **Install Now.**

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-1jpCakOHNy.png)

After the installation is done, you will be redirected to the plugins page. Here, just click on **Network Activate** and the _Payfast_ add-on will be activated on your network.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-49OQHBwPxk.png)
