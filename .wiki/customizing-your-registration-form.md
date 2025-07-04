# Customizing your Registration Form

To make your network look unique from all the other SaaS built on WordPress platform, Multisite Ultimate allows you to customize your registration and login pages with our **Checkout Forms** feature.

Although they are an easy and flexible way to experiment with different approaches when trying to convert new customers, they are mostly use to create personalized registration forms. This article aims to show you how you can do it.

## Login and registration pages:

Upon Multisite Ultimate installation, it automatically creates custom login and registration pages on your main site. You can change these default pages any time by going under your **Multisite Ultimate > Settings > Login & Registration** page.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-K3a5Ol4prD.png)

Let's take a look at each one of the options you can customize on the **Login & Registration** page:

  * **Enable registration:** This option will enable or disable the registration on your network. If it is toggled off, your customers will not be able to register and subscribe to your products.

  * **Enable email verification:** If this option is toggled on, customers that subscribe for a free plan or a paid plan with a trial period will receive a verification email and will need to click on the verification link for their websites to be created.

  * **Default registration page:** This is the default page for registration. This page needs to be published on your website and have a registration form (also know as checkout form) - where your clients will subscribe to your products. You can create as many registration pages and checkout forms as you want, just remember to put the checkout form shortcode on the registration page, else it will not appear.

  * **Use custom login page:** This option allows you to use a customized login page, other than the default wp-login.php page. If this option is toggled on, you can select which page will be used for login on the **Default login page** option (right below).

  * **Obfuscate the original login url (wp-login.php)** : If you want to hide the original login URL, you can toggle this option on. This is useful to prevent brute-force attacks. If this option is enabled, Multisite Ultimate will display a 404 error when a user tries to access the original wp-login.php link

  * **Force synchronous site publication:** After a customer subscribe to a product on a network, the new pending site needs to be converted into a real network site. The publishing process happens via Job Queue, asynchronously. Enable this option to force the publication to happen in the same request as the signup.

Now, lets see other options that are still relevant to the login and registration process. They are right below **Other options** on the same Login & registration page:

  * **Default role:** This is the role that your customers will have on their website after the signup process.

  * **Add users to the main site as well:** Enabling this option will also add the user to the main site of your network after the signup process. If you enable this option, an option to set the **default role** of these users on your website will also appear right below.

  * **Enable multiple accounts:** Allow users to have accounts in different sites of your network with the same email address. If this option is off, your customers will not be able to create an account on other websites running on your network with the same email address.

And that's all the options related to login and registration that you can customize! Don’t forget to save your settings after you finish editing them.

## Using multiple registration forms:

Multisite Ultimate 2.0 offers a checkout form editor that allows you to create as many forms as you want, with different fields, products on offer, etc.

Both the login and registration pages are embedded with shortcodes: **[wu_login_form]** on the login page and**[wu_checkout]** for the registration page. You can further customize the registration page by building or creating checkout forms.

To access this feature, go to the **Checkout Forms** menu, on the left side-bar.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-vvxvSRGAfu.png)

On this page, you can see all the checkout forms you have.

If you want to create a new one, just click on **Add Checkout Form** on the top of the page.

You can select one of these three options as your starting point: single step, multi-step or blank. Then, click to **Go to the Editor**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-OVx7AlxoX5.png)

Alternatively, you can edit or duplicate the forms you already have by clicking on the options below its name. There, you will also find the options to copy the form’s shortcode or to delete the form.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-LdsdAu67WF.png)

If you select single step or multi-step, the checkout form will already be pre-populated with the basic steps for it to work. Then, if you want, you can add extra steps to it.

### Editing a Checkout Form:

As we mentioned before, you can create checkout forms for different purposes. In this example we will work on a registration form.

After navigating to the checkout form editor, give your form a name (that will be used for internal reference only) and a slug (used to create shortcakes, for example).

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-RvbtUn4r3w.png)

Forms are made of steps and fields. You can add a new step by clicking on **Add New Checkout Step**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-9Wgpw8DTj9.png)

On the first tab of the modal window, fill the content of your form’s step. Give it an ID, a name and a description. These items are mostly used internally.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-lr6RKlgll1.png)

Next, set the visibility of the step. You can choose between **Always show** , **Only show for logged in users** or **Only show for guests**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-0Q6LxP9E90.png)

Finally, configure the step style. These are optional fields.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-WmBuHXYH7N.png)

Now, it’s time to add fields to our first step. Just click to **Add New Field** and select the type of section you want.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-vM8n8QuTzk.png)![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-HkNUp6cWRG.png)

Each field has different parameters to be filled. For this first entrance, we will select the **Username** field.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-PFaP6RufZU.png)![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-U6Mm10qIeF.png)![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Lr1mEi1DlH.png)

You can add as many steps and fields as you need. To display your products for your customers to pick one, use the Pricing Table field. If you want to let your clients choose a template, add the Template Selection field. And so on.

_**Note:** If you create a product after creating your checkout form, you will need to add the product in the Pricing table section. If you don't add it, the product will not appear to your customers on the registration page._

_**Note 2:** username, email, password, site title, site URL, order summary, payment, and submit button are mandatory fields to create a checkout form._

While you are working on your checkout form, you can always use the Preview button to see how your clients will see the form. You can also alternate between view as an existing user or a visitor.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-3zPxZNqzkG.png)![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-YXZp7n5Nuw.png)

Finally, on **Advanced Options** you can configure the message for the **Thank You** page, add snippets to track conversions, add custom CSS to your checkout form or restrict it to certain countries.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-TVQ9EUbGJ6.png)

You can also manually enable or disable your checkout form by toggling this option on the right column, or delete permanently the form.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-N5wdel1IIp.png)

Don’t forget to save your checkout form!

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-rJPV89yQZt.png)

To get your form’s shortcode click to **Generate Shortcode** and copy the result shown on the modal window.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-RTJclNTnJZ.png)

_**Note:** You will need to add this shortcode to your registration page in order to have this checkout form added to it._

## Pre-selecting products and templates via URL parameters:

If you want to create customized pricing tables for your products and pre-select on the checkout form the product or template your customer chooses from your pricing table or templates page, you can use URL parameters for this.

### **For plans:**

Go to **Multisite Ultimate > Products > Select a plan**. You should see the **Click to copy Shareable Link** button at the the top of the page. This is the link you can use to pre-select this specific plan on your checkout form.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-bUyOlBEfNj.png)

Note that this shareable link is only valid for **Plans**. You cannot use shareable links for packages or services.

### For templates:

If you want to pre-select site templates on your checkout form, you can use the parameter: **?template_id=X** on your registration page URL. The "X" needs to be replaced by the **site template ID number**. To get this number, go to **Multisite Ultimate > Sites**.

Click on **Manage** right below the site template you want to use. You will see the SITE ID number. Just use this number for this specific site template to be pre selected on your checkout form. In our case here, the URL parameter would be **?template_id=2**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-JEgVAVNYMu.png)

Lets say our network website is [**www.mynetwork.com**](http://www.mynetwork.com) and our registration page with our checkout form is located on the **/register** page. The whole URL with this site template pre-selected will look like [**www.mynetwork.com/register/?template**](http://www.mynetwork.com/register/?template)**_id=2**.

And if you want, you can pre-select both products and templates to your checkout form. All you need to do is to copy the shareable link of the plan and paste the template parameter at the end. It will look like [**www.mynetwork.com/register/premium-plan/?template**](http://www.mynetwork.com/register/premium-plan/?template)**_id=2**.
