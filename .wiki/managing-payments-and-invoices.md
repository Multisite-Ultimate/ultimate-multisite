# Managing Payments and Invoices

Manage payments in Multisite Ultimate is as easy as it gets. You can set the auto-renew, allow trials and configure invoices. In version 2.0 you can set different payment gateways to fulfill your customers needs. Currently, you can enable payments via Stripe, Stripe Checkout, PayPal or receive manually.

## General Payment Settings

To access your payment settings go to your Multisite Ultimate Settings page and navigate to the Payments tab.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FMFCBQ03jj.png)

There you will find the general options to your payments management.  
The options affect how prices are displayed on the frontend, the backend and in reports.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-0qiSSeZUrw.png)  
**Force Auto-Renew:** Toggle this option to create new memberships with auto-renew activated (if the gateway supports it) or deactivated. When deactivated an auto-renew option will be shown during checkout.

**Allow Trials without Payment Method:** Enable this option to only ask for a payment method when the trial period is over.

**Send Invoice on Payment Confirmation:** Enabling this option will attach a PDF invoice (marked paid) with the payment confirmation email.

**Invoice Numbering Scheme:** Decide what kind of numbering scheme should be used on the invoices. You can choose between Payment Code or Sequential Number. If you choose the last option, define the next invoice number and the invoice number prefix.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-iT6fhrbw0Y.png)

## Enabling Payment Gateways

You can activate up to four methods of payment on our payment settings page: Stripe, Stripe Checkout, PayPal and Manual.

**Stripe:** Toggle this option to activate Stripe payment. Fill the blanks with the Stripe Publishable and Secret Key and save. This method will show a space to insert the credit card number during the checkout.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-ILRw0MlWJw.png)

**Stripe Checkout:** Toggle this option to activate Stripe Checkout payment. Fill the blanks with the Stripe Publishable and Secret Key and save. This method will redirect the customer to a Stripe Checkout page during the checkout.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Qjt6371Y6i.png)

**PayPal:** Toggle this option to activate PayPal as a payment method. Fill the blanks with PayPal credentials that you can get in your PayPal account dashboard. This method will redirect the customer to a PayPal payment page during the checkout.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-LUHL1QLZOD.png)

_**Note:** you can activate Sandbox mode on all these gateways to test if the payment method is working_

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-DRymmzOvY3.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-E2xQUGWrTT.png)

**Manual:** Toggle this option to enable manual payments from your customers. You should write the payment instructions on the box. The message will be displayed to the customer on the “Thank you” page, after the checkout.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-cJiNLmbwJC.png)

### Confirming manual payments

To confirm a manual payment, go to the Payments menu on the left bar. There you can see all the payments on your network and their details, including their status. A manual payment will always have a Pending status until you manually change it.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-A36lX76aJs.png)

Enter the payment page by clicking the reference code. On this page you have all the details of the pending payment, such as reference ID, products, timestamps and more.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-BG4unpR9B1.png)

On the right column, you can alter the status of the payment. Changing it to Completed and toggling the Activate Membership option will enable your customer’s site and their membership will be active.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Fo979Vpbco.png)

You can also generate the payment's invoice by clicking on the button on the top of the page.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-5Z77YxPR48.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-TRfMFpzH2z.png)

### 

### Customizing Invoices

Multisite Ultimate allows you to customize the invoices you send to your customers.

To do it, navigate to Payments page. On the right column, on the Invoices box, click to Go to Customizer.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-5MqAivRwR8.png)

On the right side, you can alter general configurations (text on the paid tag, font-family, and content on footer), colors, and images, where you can alter the logo.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Zu0WkcoWF2.png)You can see the changes immediately on the template preview and save them clicking to Save Invoice Template, on the right column.
