# Site Templates (v2)

_**NOTE: This article refers to Multisite Ultimate version 2.x. If you are using version 1.x,**_ [see this article](https://help.wpultimo.com/article/404-site-templates) _**.**_

Our goal when creating a premium network with Multisite Ultimate is to automate as many processes as possible while giving our clients flexibility and different options to choose from when creating their websites. One easy way to achieve this balance is to make use of the Multisite Ultimate Site Templates feature.

## What is a Site Template?

As the name suggests, a Site Template is a boilerplate site that can be used as a base when creating new sites in your network.

This means you can create a base site, activate different plugins, set an active theme, and customize it in any way you like. Then, when your customer creates a new account, instead of getting a default WordPress site with no meaningful content inside it, they will get a copy of your base site with all the customizations and contents already in place.

That sounds awesome, but how do I create a new site template? It is as simple as it can possibly get.

## Creating and Editing a new Site Template

Site Templates are just normal sites on your network. To create a new template you can simply go to **Network Admin > Multisite Ultimate > Sites > Add Site.**

**![add site template](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-D1F8SOUTYU.png)**

This will open a modal window where it will ask for the **Site title, Site Domain/path,** and **Site type**. Under the **Site Type** drop-down field make sure that you select **Site Template** *.*

_![Add site template modal](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-gGWUnGCS36.png)_

At the bottom of the form, you will notice a **Copy Site** toggle switch. This will allow you to create a new site template based on an existing site template as your starting point to help you save time instead of creating a site template from scratch.

![Add site template modal 2](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-DVSq5dgWfa.png)

### Customizing the contents of a Site Template

To customize your site template, simply navigate to its dashboard panel and make the changes you need. You can create new posts, pages, activate plugins and change the active theme. You can even go to the Customizer and change all sorts of customization options.

All of that data will be copied over when a customer creates a new site based on that Site Template.

### Advanced Options

If you know your way around some custom coding, you can make use of our Search and Replace API to automatically replace information on the new site after its creation. This is useful for things like replacing company names on an About page, replacing the contact email on the Contact page, etc.

### Using Site Templates

Ok, so you created a bunch of different Site Templates with different designs, themes and settings. How do you make them useful on your network now?

Basically, there are two approaches you can use now (not simultaneously):

  * Attaching one Site Template to each of your Plans

**OR**

  * Allowing your clients to choose the site templates themselves during sign-up.

#### Mode 1: Assign Site Template

In this mode, your clients won’t be able to choose a template when they create an account, but rather you will define which template should be used on each of your Plans.

To do that, you’ll need to go to **Multisite Ultimate > Products > Edit**.

![Assign site template](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-t7UjzQEG5g.png)

This will bring you to the **Edit Produc** t page. Under the **Product Options** section, find the **Site template** tab and select the **Assign Site** **Template** option from the drop-down field. This will bring up the list of site templates available and it will allow you to select only one site template dedicated to the product.

![Assign site template](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-HTcetltjLR.png)

#### Mode 2: Choose Available Site Template

In this mode, you’ll give your clients a choice during the sign-up process. They will be able to select from different site templates you define under the product settings. You have an option to limit the site template they can choose from under the selected product. This will allow you to have different sets of site templates under each product which is ideal to highlight different functions and features for a higher-priced product.

On the **Edit Product** page. Under the **Product Options** section, find the **Site template** tab and select the **Choose Available Site Template** option from the drop-down field. This will bring up the list of site templates available and it will allow you to select the site template you wish to be available. You can do this by choosing its Behavior: **Available** if you want the site template to be included on the list. _**Not Available**_ if you want the site template not to show as an option. And **Pre-selected** if you wish one of the site templates listed to be the default selected.

![Assign site template](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-dY8gSreOfG.png)

### Default Mode: Site template selection on the Checkout form

If you wish all your site templates to be available during the registration, or maybe do not prefer doing extra work of assigning or specifying site templates under each product you create. Then you can simply set the site template selection under your **Checkout Form**. To do this, you just need to go to **Multisite Ultimate > Checkout Forms**. Then click **Edit** under the form you wish to configure.

![Assign site template](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FaXifuzMu3.png)

This will bring up the **Edit Checkout Form** page. Find the **Template Selection** field and click **Edit** under it.

![Assign site template](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-ZDAp4vdnve.png)

A modal window will appear. Under the **Template Sites** field you can select and list down all the site templates you wish to be available during registration. The site templates you specify from here will be available regardless of whatever product the user selected.

![Assign site template](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-pCSfzJkf4d.png)

### Site Template Options

There are other site templates functions available that you can turn on or off under Multisite Ultimate settings.

![Site template option](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-wrwT3rqFEf.png)

#### Allow Template Switching

Enabling this option will allow your clients to switch the template they choose during the sign-up process after the account and site is created. This is useful from a client’s point of view since it allows them to re-select a template if they later find out their original choice was not the best one for their particular needs.

#### Allow Users to use their Site as templates

Since subsite users spent time building and designing their own site, they may want to clone and use it as one of the site templates available upon creating another subsite on your network. This option will allow them to achieve that.

#### Copy Media on Template Duplication

Checking this option will copy the media uploaded on the template site to the newly created site. This can be overridden on each of the plans.

#### **Prevent Search Engines from indexing Site Templates**

Site templates as discussed in this article are boilerplate but still part of your network which means that it is still available for search engines to find. This option will allow you to hide the site templates so that search engines can index them.

## Pre-populating Site Templates with auto search-and-replace

One of the most powerful features of Multisite Ultimate is the ability to add arbitrary text, color, and select fields onto the registration form. Once we have that data captured, we can use it to pre-populate the content in certain parts of the site template selected. Then, when the new site is being published, Multisite Ultimate will replace the placeholders with the actual information entered during registration.

For example, if you wish to get your end-user's company name during registration and automatically put the company name on the home page. On your template site home page you need to add the placeholders, like in the image below (placeholders should be added surrounded by double curly braces - {{placeholder_name}}).

![homepage placeholder](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-yEOlZVv2Gr.png)

Then, you can simply add a matching registration field on your checkout form to capture that data:

![checkout form](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-9adTd73gNT.png)

Your customer will then be able to fill that field during the registration.

![registration field](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-jHQ9ZOGWlh.png)

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-preview)

Multisite Ultimate will then replace the placeholders with the data provided by the customer automatically.

![replace placeholder](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-chL5wf7wmY.png)

### Solving the "template full of placeholders" problem

All of that is great, but we do run into an ugly problem: now our site templates - that can be visited by our customers - are full of ugly placeholders that don't tell much.

To solve that, we offer the option of setting fake values for the placeholders, and we use those values to search and replace their contents on the template sites while your customers are visiting.

You can have access to the template placeholders editor by heading to **Multisite Ultimate > Settings > Sites**, and then, on the sidebar, clicking the **Edit Placeholders** link.

![placeholder settings](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-ztZnAexNEZ.png)

That will take you to the placeholders' content editor, where you can add placeholders and their respective content.

![template placeholders](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-95QJa1MRGz.png)
