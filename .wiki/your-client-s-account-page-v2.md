# Your Client's Account Page (v2)

_**IMPORTANT NOTE: This article refers to Multisite Ultimate version 2.x.**_

When customers subscribe to a plan on your network, they get access to a website and its dashboard with important information regarding their payments, memberships, domains, plan limitations, etc...

In this tutorial, we will guide you through the customer's account page and you will see what your customers can see and do inside it.

## The Account Page

The account page is accessible by clicking on **Account** inside your customer's dashboard.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-LrGNKCDc8R.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FTDgqcwaO0.png)

After a customer click on it, they will se an overview of their membership, billing address, invoices, domains, site limitations, and will also be able to change the [**Site Template** (if it's allowed in your network)](https://help.wpultimo.com/article/369-site-templates).

They can also change the membership to another plan, or purchase another package or service that you offer. Let's take a look at each section separately.

### Your Membership Overview:

The first block right below your customers' website name shows an overview of they current plan and services/packages that were purchased with it. The block also shows the membership number, the initial amount paid for it, how much the plan and any service/package costs and how many times they were billed for this membership. They can also see if the membership is **Active** , **Expired** or **Canceled**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-XNTPsXaqzp.png)

Right below this block, your customers can see the **About This Site** and the **Site Limits** blocks. These blocks show them all the limitations that comes to their plan: disk space, posts, pages, visits, etc... These limits can be configured on each plan page on **Multisite Ultimate > Products**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-6kVmMDoiCU.png)

On the right side of **Your Membership** , customers can click on **Change**. This will show them all available plans and packages/services. If they choose another plan, the limitations for the plan will take place instead of the current limitations of the membership - doesn't matter if they are downgrading or upgrading it.

Now, if your customers choose to purchase packages or services for this current membership - like more disk space or visits - the current membership will not be changed but only the new packages will be added to it.

Note that coupon codes cannot be added on this membership change page. If the customer used a coupon code on the first membership purchase, the code will also apply to this new membership.

### Updating the Billing Address:

On the account page, your customers can also update their billing address. They just need to click on **Update** next to _Billing Address_.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-GLQ21kppU2.png)

A new window will appear to your customer. All he need to do is to fill in the new address and click on _Save Changes_.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Z2nY05cYvD.png)

### Changing the Site Template:

To allow your customers to change their site templates, you need to go to **Multisite Ultimate > Settings > Sites** and toggle on the option **Allow Template Switching**.

Also, on **Multisite Ultimate > Products**, select your plans and go to the **Site Templates** tab. Make sure the option **Allow Site Templates** is toggled on and on **Site Template Selection Mode** , the option **Choose Available Site Templates** is selected.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-rjixD7iShD.png)

You will be able to see all the available site templates on your website. Choose which ones you want to make available and which ones you want to not be available to your customers subscribed under this plan. Note that this options also affects the checkout form, so any template that is chosen as **Not Available** will not appear on the registration page for this plan.

Now your customers can click on **Change Site Template** inside their account page.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-5pdvAuh6h3.png)  
A list of all available Site Templates for this plan will appear to your customer.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-bQSA663xNo.png)

After selecting the one they want to change to, they will be asked to confirm the change.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-6azvPydgAU.png)

After toggling on the confirmation and clicking to **Process Switch** , the new site template will be used on your customer's website.

### Adding Custom Domains:

Your customers will also have the option to add a custom domain for this plan on their account page. To allow your customers to use custom domains, go to **Multisite Ultimate > Settings >** [Domain Mapping](https://help.wpultimo.com/article/365-domain-mapping-101).

Toggle on the option **Enable Domain Mapping**. This will allow your customers to use custom domains on a network level.

Don't forget to also check if the domain mapping is enabled on a product basis - because you can limit a product to not allow your customers to use custom domains.

Go to **Multisite Ultimate > Products**. Select the plan of your choice and go to the **Custom Domains** tab. Toggle on the option **Allow Custom Domains**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-J7mrCTfet6.png)

This will allow all customers subscribed to this specific plan to use custom domains. Now, on the Account page, your customers can add a custom domain by clicking on **Add Domain**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-0mbLgC86SG.png)

The first window that opens will show your customers a message instructing them on how to update their DNS records in order to make this custom domain work on your network.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-emhM26Abhn.png)

This message can be edited (by you) on **Multisite Ultimate > Settings > Domain Mapping > Add New Domain Instructions**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-6ZjI8Fk6Gw.png)

After clicking **Next Step** , your customers can add their custom domain name and choose if this custom domain will be the primary one. Note that your customers can use more than one custom domain for their websites, so they can choose which one will be the primary one.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-pSzZowjc2a.png)

After clicking on **Add Domain** , the domain will be added to your customer's account. All they need to do now is changing the DNS records of this custom domain on their domain registrar.

### Changing Password:

Inside the account dashboard, your customers can also change their password by clicking on **Change Password**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-sihWaIk4B8.png)

This will show a new window where your customers will need to fill their current password and then fill the new password they want to use.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-5zCgtcHfgB.png)

### Danger Zone:

We also have two options that are shown on the **Danger Zone** part: **Delete Site** and **Delete Account**. They are both on the Danger Zone part because these two actions are irreversible. If your customers delete their website or their account, they cannot recover them back.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FMNX249rFi.png)

If your customers click on any of these two options, they will be shown a window where they will need to toggle on the option to remove the website or account and they will be warned that this action cannot be undone.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-MRI6taFdJK.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-itpv48GPgY.png)

If they delete their website, their account and membership will still be untouched. They will just lose all the content on their website. If they delete their account, all websites, memberships and information regarding this account will be lost.
