# Setting Up The Stripe Gateway (v2)

_**IMPORTANT NOTE: This article refers to Multisite Ultimate version 2.x.**_

You can activate up to four methods of payment on our payment settings page: Stripe, Stripe Checkout, PayPal and Manual. In this article, we will see how to integrate with **Stripe**.

## Enabling Stripe

To enable Stripe as an available payment gateway on your network, go to **Multisite Ultimate > Settings > Payments** and tick the toggle next to **Stripe** or **Stripe Checkout** on the Active Payment Gateways section.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-UUtLaJgx7R.png)

### Stripe vs Stripe Checkout:

**Stripe:** This method will show a space to insert the credit card number during the checkout.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-k73ZUl1hTW.png)

**Stripe Checkout:** This method will redirect the customer to a Stripe Checkout page during the checkout.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-PCZ16DhYrj.png)

Getting your Stripe API keys

Once Stripe is enabled as a payment gateway, you will need to populate the fields for **Stripe Publishable Key** and **Stripe Secret Key** . You can get this by logging in to your Stripe account.

_**Note:** you can activate **Sandbox mode** to test if the payment method is working._

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-dhnvBN03ii.png)

On your Stripe dashboard, click **Developers** on the top-right corner, and then **API Keys** in the left menu.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-HVqsu1SXuE.png)

You can either use **Test Data** (to test if the integration is working on your production site) or not. To change this, twitch the **Viewing test data** toggle.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-kdVC3W8Bsr.png)

Copy the value from the **Publishable key** and **Secret key** , from the **Token** column and paste it on Multisite Ultimate Stripe Gateway fields. Then click to **Save Changes**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-JyAifSGNOn.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-4rFGxkXr1K.png)

## Setting up Stripe Webhook

Stripe sends webhook events that notify Multisite Ultimate any time an event happens on **your stripe account**.

Click **Developers** and then choose the **Webhooks** item in the left menu. Then on the right hand side click **Add endpoint** *.*

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-LmYsdylNdd.png)

You will need an **Endpoint URL** *.* Multisite Ultimate automatically generates the endpoint URL which you can find right below the **Webhook Listener URL** field in **Multisite Ultimate Stripe Gateway** section_._

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-sZrCX9OZaw.png)

**Copy** the endpoint URL and **paste** it on Stripe **Endpoint URL** field.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-tMlomo8gx1.png)

Next is to select an **Event** *.* Under this option, you just simply need to check the **Select all events** box and click to **Add events**. After that click **Add Endpoint** to save the changes.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Hv8KzaGMrq.png)

That’s it, your Stripe payment integration is complete!
