# Creating your First Subscription Product (v2)

_**IMPORTANT NOTE: This article is only for Multisite Ultimate version 2.x users. If you are using version 1.x,**_ [_**see this article**_](https://help.wpultimo.com/article/268-getting-started-with-new-plans).

To start running your network and begin selling your services to potential users, you need to have different subscription options. How do you create these products? What are the types of products you can offer? In this article, we will cover everything you need to know about products.

## Product Type

With Multisite Ultimate you can offer two categories of products to your clients: **plans** and **add-ons** **(Order Bump)**. Add-ons can be divided into two types:**packages** and **services**. We will see their differences and particularities next.

  * **Plans** : the fundamental product of Multisite Ultimate. Your client can only have a membership if it’s attached to a plan. A plan provides your clients with one or more sites (it depends on the configurations of your plan) with the limitations you set on your product editing page.

  * **Packages** : add-ons that impact directly on Multisite Ultimate plans’ functionalities. They alter limitations or add new resources, plugins or themes to the original plan your client bought. For example, a basic plan might allow 1,000 visits per month and you can make available a package that extends this number to 10,000.

  * **Services:** add-ons that do not alter Multisite Ultimate’s functionalities. They are tasks that you will realize for your client in addition to the plan they bought. For example, your customer might buy a plan that allows for a single site and also pays for an extra service that will make this site design.

## Managing Products

For many the **Products** tab in Multisite Ultimate **(Multisite Ultimate > Products)** can be equated with plans in a traditional hosting environment.

Within Multisite Ultimate the Products tab defines the construct and limitations applicable to a specific product or service. Such constructs extend to product or service description, price, taxes, and permissions.

This section will guide your understanding of this essential cornerstone of Multisite Ultimate.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-1YccQg0IBG.png)

## Adding Products

Whether a plan, package, or service the entry point to defining a new item is via **Multisite Ultimate > Products > Add Product**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-C0AmJMeqen.png)

The interface contains two predominant sections. On the left are several tabs which assist with the definition of the product and on the right are a few sections to define the base price of the product, its active state, and product image.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-WRwYrlk0BS.png)

### Description

The base product information can be defined by supplying a product name and description. These identifiers are displayed wherever the product information is required such as plan and pricing selection, invoices, upgrades, and the like.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-U9YjF0dghJ.png)

### Pricing Type

On the right side of the interface, the base pricing can be defined.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-oHwhlrBFC9.png)

Multisite Ultimate supports three different pricing types. The **paid** option prompts the network administrator for information regarding the product’s price and billing frequency.

### Pricing

The price component defines the base product price and billing interval.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-T03WJISsoh.png)

thus an example price of $29.99 with a setting of 1 month will bill $29.99 each month. Similarly, a price of $89.97 with a setting of 3 months will bill that amount each quarter.

### Billing Cycles

The billing cycles section specifies the frequency of the aforementioned billing interval and is generally understood in the light of contracts or fixed terms.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-hVVAoxC4Jt.png)

For example, a product price of $29.99 with an interval of 1 month and 12 billing cycles would bill $29.99 per month for the product over the succeeding 12 months. In other words, such a setting would establish a fixed-price term of $29.99 per month for 12 months and then cease billing.

### Trial Period

Enabling the offer trial toggle allows the network administrator to define a trial period for the product.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-MzxhWHqJLC.png)

During the trial period, customers are free to use the product and will not be billed until the trial period has been exhausted.

### Setup Fee

You can also apply a setup fee to you plan.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-8ZA2YvhWyK.png)

This means that your client will pay an extra amount on the first charge (in addition to the price plan) that corresponds to the fee you defined in this section.

### Active

The active toggle effectively defines whether the product is available to customers for new sign-ups.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-74ET2mPskK.png)

If there are existing customers on this plan setting the toggle to its disabled state effectively grandfathers the plan removing it from future sign-ups. **Existing customers on the plan will continue to be billed** until they are transitioned to a new plan or removed from the plan.

### Product Image

The **Upload Image** button allows the network administrator to make use of the media library to select or upload a product image.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-9tHhGvokLA.png)

### Delete

The **Delete Product** button deletes the product from the system. It appears once the product is published.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-rrmQohvw3S.png)

Unlike other deletions, the product is not placed in any trash state. Thus once deleted the action is irreversible.

### Product Options

Once the base-level product information is defined, the product options aid the network administrator to further define the specific attributes of the product.

#### General

The **General** tab defines the general attributes of the product not applicable to any of the other product-specific tabs.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-z32g4XQeyT.png)

The self-explanatory **product slug** defines the slug with which the product is identified in URLs and other areas of Multisite Ultimate.

Multisite Ultimate supports several product types namely Plan, Package, and Service. The **Product Options** tabs are dynamically adjusted depending on the product type specified.

The **Customer Role** specifies the role that the customer is assigned when the site is created. Typically for most network administrators, this will be the Multisite Ultimate default or Administrator. The Multisite Ultimate default role can be set in **Multisite Ultimate > Settings > Login & Registration**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Ap5I4lXWwB.png)

#### Up & Downgrades

This tab specifies the upgrade and downgrade paths available to a customer within their specific tier.

To understand this concept consider an example where a niche Multisite Ultimate installation provides learning management solutions to its customers. To achieve this three plans (Basic, Plus, and Premium) are defined and specific plugins are activated for each plan (see later in this section for instructions on how to activate plugins).

If the Multisite Ultimate installation also services business websites or eCommerce websites those plans may require different plugins to be installed and activated.

To this extent, it would be undesirable and problematic to allow eLearning customers to transition to eCommerce plans as these plans, pricing, and limitations may not be an appropriate fit.

Thus to restrict the path of the customer and to prevent incidents the network administrator can define a plan group and within that group specify the plans the customer can transition to.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-JnrZ4fWFEC.png)

To define a plan group specify the compatible plans within the **plan group** list. The **product order** determines how the plans are ordered and displayed from the lowest to the highest.

Multisite Ultimate also includes an **order bump** feature where appropriate add-on products and services can be added to plans. These are offered to the customer as additional items which can be added to plans on checkout or during an upgrade.

#### Price Variations

Price variations allow the network administrator to specify alternate pricing tiers depending on duration. This setting makes it possible for 3 months, 6 months, or annual pricing tiers to be established or any other duration and frequency as determined by the use case.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-d75YUO3opm.png)

To establish price variations, set the **enable price variations** toggle to active and click the **Add new Price Variation** button.

To enter a variation, set the duration, period, and price of the variation. Additional variations can be entered by clicking the button again.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-x73uxkMU8o.png)

#### Taxes

The **Taxes** tab aligns with the tax settings specified in **Multisite Ultimate > Settings > Taxes** and more specifically the tax rates defined. To enable taxes and define applicable tax rates please see the documentation at **Multisite Ultimate: Settings**

**![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-xYLtpFySzL.png)**

In a previous example, we defined a local tax rate of 7.25% applicable to customers in California (United States of America).

Once the tax rate is defined in **Multisite Ultimate > Settings > Manage Tax Rates** it is selectable at the product level.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Sh1BVGVj6i.png)

To indicate that a product is a taxable item, set the **Is Taxable** toggle to active and select the applicable tax rate from the Tax Category dropdown.

#### Site Templates

In essence, site templates are complete WordPress websites that are cloned to a customer’s site at the start of their subscription.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-llMSMXCDt4.png)

The network administrator creates and configures the template site as a regular WordPress site with activated and configured themes, plugins, and content. The template site is cloned verbatim to the customer.

This tab allows the network administrator to specify the behavior of site templates upon a new subscription. To make use of site templates, set the **allow site templates** toggle to its active state.

The **site template selection mode** defines the behavior of site templates during the subscription process.

The **D** **efault** setting follows the steps in the checkout form. If the network administrator has defined a template selection step in the checkout process and the step has been defined with templates this setting will honor the directives established in the checkout step.

Specifying **A** **ssign Site Template** forces the selection of the specified template. Consequently, any template selection steps in the checkout process are removed.

Lastly, **C** **hoose Available Site Templates** overrides the templates specified in the checkout step with the templates selected in this setting. A pre-selected template can also be defined to aid the customer in selection.

Ultimately if the network administrator desires template selection to occur in the checkout steps the setting of ‘ _default_ ‘ will suffice. Alternatively to remove and lock template selection and delegate the selection to the plan settings the ‘ _assign new template_ ’ or ‘ _choose available site templates_ ’ options may be desirable.

#### Sites

The **Sites** tab is part of Multisite Ultimate’s limitations functionality.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-A8fzNucGyC.png)

This setting specifies the maximum number of sites a customer can create under their membership.

To enable the limitation, set the **limit sites** toggle to its active state and specify the maximum number of sites in the **site allowance** field.

#### Visits

The **Visits** tab is a further part of Multisite Ultimate’s limitations system. This setting allows for the accounting and subsequent throttling of unique visitors to a customer’s site.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-e7f5otg89m.png)

From a marketing perspective network, administrators can make use of this setting as a means to encourage customers to upgrade their plan once limits are reached. This setting can also assist the network administrator to curb and prevent excessive traffic to sites to preserve system resources.

To make use of this feature, set the **limit unique visits** toggle to its active state and specify the maximum number of unique visitors in the **unique visits quota** field.

Once this limit is reached Multisite Ultimate will cease to serve the customer’s site instead of displaying a message to indicate that limits have been exceeded.

#### Users

Multisite Ultimate’s ‘Users’ limitations allow the network administrator to impose limits on the number of users that can be created and assigned to roles.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-maGYqW7WlP.png)

To enable the limitations feature, set the **limit user** toggle to its active state by sliding it to the right.

Next for each role to be limited, set the toggle next to it to an active state and define the maximum upper limit in the appropriate field.

#### Post Types

The **Post Types** tab allows the network administrator to impose granular limits on the extensive array of post types within WordPress.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-ZELQvvqyvn.png)

Due to the construction of WordPress, posts and post types are a significant component of its core functionality, and thus Multisite Ultimate’s limitations system is designed to assist the network administrator in establishing and maintaining limits.

To enable this limits subsystem, set the **limit post types** toggle to its active state by sliding it to the right.

Next, for each post type to be limited, toggle it on by sliding it to the right and specifying the maximum upper limit in the appropriate field.

#### Disk Space

The **Disk Space** tab allows network administrators to restrict the space consumed by customers.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-EqlaLO0ebw.png)

Typically in a WordPress multisite the core files are shared amongst all sites and individual directories created for media files and uploads to which these settings and limitations apply.

To enable the disk usage limitation, set the **limit disk size per site** toggle to its active state by sliding it to the right.

Next, specify the maximum upper limit in megabytes in the **disk space allowanc** e field.

#### Custom Domain

By toggling this option you can allow custom domains on this plan specifically.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-KEMuUG76Fg.png)

#### Themes

The **Themes** tab within the product options allows the network administrator to make themes available to customers for selection and to optionally force the state of the theme.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-rkyVSGnDqo.png)

_**Note: For themes to be made available to customers they must be network enabled by the network administrator.**_

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-HAQShYB7Y1.png)

The **visibility** option defines whether or not this theme is visible to the customer when viewing their **Appearance > Themes** tab within their site. Setting this option to **Hidden** removes the theme from view and thus restricts the ability to select and activate it.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-ivsGIABl55.png)![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-jPTC92eZwc.png)

The **behavior** selection allows the network administrator to define the state of the theme upon the creation of the customer site.

In the **A** **vailable** state the theme is made available to the customer for self-activation. Conversely, the **Not Available** state removes from the customer the ability to activate the theme. Lastly, the **Force Activate** option forces the selection and activation of the theme thus setting it as default upon site creation.

#### Plugins

Similar to the Themes tab, Multisite Ultimate allows the network administrator to define the visibility of plugins to customers as well as their state upon the creation of a new site.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Ipzv31FIb6.png)

The **visibility** drop-down allows for the plugin to either be visible or hidden from the customer when viewed on their site through the Plugins menu option.

The network administrator can further manipulate the behavior of the plugins by making use of the options in the behavior drop-down.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-jPTC92eZwc.png)

The **Default** selection honors the plugin state defined in the site template selected by the customer. Thus plugins that are activated within the template will remain activated when the template is cloned to the customer’s site.

The **Force Activate** places the plugin in an active state upon site creation and conversely the **Force Inactivate** deactivates the plugin upon site creation. In both of these circumstances, the plugin’s state can manually be altered by the customer through their WordPress Plugins menu.

The **Force Activate & Lock** setting operates similarly but prevents the plugin state from being altered by the customer. Thus a setting of Force Activate and Lock will force the plugin into its active state and prevent the customer from deactivating it. Similarly, the **Force Inactivate & Lock** setting will force the plugin to its inactive state and prevent the user from activating the plugin.

The network administrator may wish to consider the Force Activate & Lock and Force Inactivate & Lock settings in conjunction with site templates as plugins and plugin states within the templates may be impacted by these settings if selected.

#### Reset Limitations

The **Reset Limitations** tab resets all custom limits defined on the product. To reset limitations click on the **reset limitations** button.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-UXNQerLby7.png)

To confirm the action slide the **confirm reset** toggle to its active state on the right and click the **reset limitations** button.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-xKySIUIvrI.png)

#### Legacy Options

The **Legacy Options** tab observes certain options and behaviors defined in Multisite Ultimate 1.x.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Fn3BjwTH4r.png)

These options are offered for compatibility and ease of transition and will be deprecated in future releases.

## Edit, Duplicate, or Delete Product

Existing products can be edited, duplicated or deleted by navigating to **Multisite Ultimate > Products** and hovering over the existing product name.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-GcHtJl6WmV.png)

## 

### 
