# The Registration Flow (v2)

_**IMPORTANT NOTE: This article refers to Multisite Ultimate version 2.x.**_

Users can register in different ways to your network. They can use your registration form or a shareable link to a pre-selected plan. Here we will show you how your customers can register on your network using the available paths and what happens after they register on your network.

## Using the Registration Form:

This is the standard registration process. You create a registration page with a [checkout form](https://help.wpultimo.com/article/406-customizing-your-registration-form) and this will be where your customers will go to register on your network and subscribe to a plan. You can have multiple registration pages, each one with a different registration form if you want.

The default page for registration is [_**yourdomain.com/register**_](http://yourdomain.com/register), but you can change this at any time on **Multisite Ultimate > Settings > Login & Registration > Default Registration Page**.

After a user get to your registration page (usually clicking on a **Sign in** or **Buy now** button), they will se your registration form there.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-hDcy7S8pBK.png)

All they have to do is filling all the mandatory fields - email, username, password, etc... - and pay for the plan or confirm their email address if they are registering for a free plan or a paid plan with trial period without payment information.

On the "Thank you" page, they will see a message telling them if they need to confirm their email address or if their website is already activated and they can start using it.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FnXxCLt0YW.png)

If an email address confirmation is required, they will need to go to their email inbox and click on the verification link. Their website will not be activated if their email address doesn't get verified.

If they have registered on a paid plan or the email verification is not mandatory on your network, they will have their website activated right after the checkout and will be shown a link to sign in to their dashboard.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-DJwvUqQnQU.png)

## Using a Shareable Link:

The process of registering using a shareable link is basically the same as the registration form, the only difference is that using a shareable link, your customers can have a product or website template pre-selected on the checkout form (refer to the section Pre-selecting products and templates via URL parameters) or maybe a coupon code added (refer to the section Using URL Parameters).

The registration process will be the same: they will need to fill their name, username, email address, website name and title, etc... but the plan or site template will be already pre-selected for them.

### Registering Using Manual Payments:

If you do not want to use PayPal, Stripe or any other payment gateway offered by Multisite Ultimate or its add-on integrations, you can use manual payments for your customers. This way, you can generate an invoice for them to pay on your preferred payment processor after they register on your network.

The registration process will be exactly the same as above, but on the registration page your customers will see a message stating that they will receive an email with further instructions to complete the payment.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-iSli6MoFGw.png)

And after the registration is completed, they will see the payment instructions that you set (and also receive it on their email).

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-hX0GPWYfEA.png)

The payment instructions can be changed on **Multisite Ultimate > Settings > Payments** after toggling on the **Manual** payment option:

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-RpPGDd0BZo.png)

After your customers complete the manual payment and send you the confirmation, you need to **manually confirm the payment** to activate the customer membership and website.

To do this, go to **Multisite Ultimate > Payments** and find the customer payment. It should still show a **Pending** status.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-2rW0tFemEP.png)

Click on the payment number and you will be able to change its status to **Completed**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-KiRQKw3wGg.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-NPFNchZrUa.png)

After changing its status to **Completed** , you should see an **Activate membership** message. Toggle this option **on** to activate the membership and website associated with this customer. Then, click to **Save Payment**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-e3R91KmnUV.png)

Your customer should now be able to access the dashboard and all features that they subscribed to.
