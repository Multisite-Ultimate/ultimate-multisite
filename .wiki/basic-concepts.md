# Basic Concepts

For a new WordPress Multisite user and someone who just started using Multisite Ultimate, there can be a lot of new words and phrases to take on board at first. Learning them is an important task because you’ll need to understand the platform and how it works as a whole.

In this article, we’ll attempt to define and explain some of the key concepts in WordPress. Some of these are more relevant for users, others for developers, and some for both.

## WordPress Multisite

WordPress [Multisite](https://help.wpultimo.com/article/265-how-do-i-install-wordpress-multisite) is a type of WordPress installation that allows you to create and manage a network of multiple websites from a single WordPress dashboard. You can manage everything including the number of sites, features, themes, and user roles. It is possible to manage hundreds and thousands of sites.

## Network

In terms of WordPress, a multisite network is where a number of **subsites** can be managed from a single dashboard. Although creating a multisite network differs between hosting providers, the end result is usually a few additional directives in the **wp-config.php** file to let WordPress know that it is operating in this specific mode.

There are a number of distinct differences between a multisite network and a stand-alone WordPress installation which we shall briefly discuss.

## Database

A database is a structured, organized set of data. In computing terminology, a database refers to software used to store and organize data. Think of it as a file cabinet where you store data in different sections called tables.

WordPress Multisite uses one database and each subsite gets its own tables with the blog id in the prefix, so once you install a network installation your database and create a subsite, you should have these tables:

_wp_1_options_ \- options table for first subsite

_wp_2_options_ \- options table for second subsite

## Hosting provider

A hosting provider is a company that enables businesses and individuals to make their websites available through the World Wide Web. The services that web hosting providers offer will vary but usually include website design, storage space on a host, and connectivity to the Internet.

## Domain

A domain name is an address people use to visit your site. It tells the web browser where to look for your site. Just like a street address, a domain is how people visit your website online. And, like having a sign in front of your store. If you wish to visit our website, you will have to type our web address on your browser's address which is [_www.wpultimo.com_](http://www.wpultimo.com) _,_ where [**wpultimo.com**](http://wpultimo.com) is the domain name.

## Subdomain

A subdomain is a type of website hierarchy under the main domain, but instead of using folders to organize content on a website, it kind of gets a website of its own. It is presented as [**https://site1.domain.com/**](https://site1.domain.com/) where _site1_ is the subdomain name and [_domain.com_](http://domain.com) is the main domain.

## Subdirectory

A subdirectory is a type of website hierarchy under a root domain that uses folders to organize content on a website. A subdirectory is the same as a subfolder and the names can be used interchangeably. It is presented as [**https://domain.com/site1**](https://domain.com/site1) where _site1_ is the subdirectory name and [_domain.com_](http://domain.com) is the main domain.

## Subsite

Subsite is a child site you create on a Multisite network. It can either be a **subdomain** or **subdirectory** depending on how your WordPress Multisite installation is configured.

## Super Admin

A WordPress Super Admin is a user role with full capabilities to manage all subsites on a Multisite network. For Multisite users, it is the **highest level of access** that you can provide to your WordPress installation.

## Plugin

In general, a plugin is a set of code that adds extra functionality to your WordPress site. This could be as simple as changing the login logo or as complex as adding e-commerce functionality. _Woocommerce and Contact Form_ are examples of a plugin.

On a WordPress Multisite, plugins can only be installed from the network admin dashboard by Super Admin. Subsite Admins can only activate and deactivate plugins within their subsite.

## Themes

A WordPress theme is a group of files (_graphics, style sheets, and code_) that dictates the overall appearance of the site. It provides all of the front-end stylings such as font styling, page layout, colors, etc.

Same as plugins, themes in WordPress Multisite can only be installed by Super Admin and can be activated on the subsite level by subsite admins.

## Site Template

[Site Template](https://help.wpultimo.com/article/369-getting-started-with-site-templates-v2) is a boilerplate site that can be used as a base when creating new sites in your network.

This means you can create a base site, activate different plugins, set an active theme, and customize it in any way you like. Then, when your customer creates a new account, instead of getting a default WordPress site with no meaningful content inside it, they will get a copy of your base site with all the customizations and contents already in place.

## Domain Mapping

[Domain mapping](https://help.wpultimo.com/article/365-domain-mapping-101) with WordPress is a way to redirect users to the correct host, through a website’s address. In a WordPress Multisite, subsites are created using either a subdirectory or subdomain. What domain mapping does is it allows subsite users to use a top-level domain like [**joesbikeshop.com**](http://joesbikeshop.com) to make their site address looks more professional.

## SSL

SSL stands for **Secure Sockets Layer**. It is a digital certificate that authenticates a web site's identity and enables an encrypted connection. Nowadays it is used as the standard technology for keeping an internet connection secure and safeguarding any sensitive data that is being sent between two systems, preventing criminals from reading and modifying any information transferred, including potential personal details. Modern browsers require SSL which makes it essential when creating and running a website.

## Media

Media are images, audio, video, and other files that make a website.

Network sites share a single database in a WordPress Multisite, they maintain separate paths on the filesystem for media files.

The standard WordPress location (wp-content/uploads) remains; however, its path is altered to reflect the network site’s unique ID. Consequently media files for a network site appear as wp-contents/uploads/site/[id].

## Permalinks

Permalinks are the permanent URLs of your individual blog post or page within your site. Permalinks are also referred to as **pretty links**. By default, WordPress URLs use the query string format which looks something like this:

[**http://www.example.com/registration**](http://www.example.com/registration)

## Multisite Ultimate

Multisite Ultimate is a WordPress plugin, made for WordPress Multisite installs, that transforms your WordPress install into a premium network of sites – like [WordPress.com](https://WordPress.com) – allowing clients to create sites via monthly, quarterly, or yearly fees (you can also create Free plans).

## Checkout Form

Checkout Form is a single or multi-step order form that involves the creation of subsite, membership, and user accounts through Multisite Ultimate registration. It consists of different fields and payment forms that a user must submit during the sign-up process.

## Webhook

A webhook (also called a web callback or HTTP push API) is a way for an app to provide other applications with real-time information. A webhook delivers data to other applications as it happens, meaning you get data immediately.

[Multisite Ultimate webhooks](https://help.wpultimo.com/article/337-integrating-wp-ultimo-with-zapier-using-webhooks) open infinite possibilities, allowing network admins to do all sorts of crazy-but-useful integrations, especially if used in conjunction with services like _Zapier and IFTTT_.

## Events

An Event is an action that occurs as a result of the user or another source action, such as a mouse click. Multisite Ultimate keeps a record of all the events and logs that are happening within your entire network. It tracks different activities happening in your multisite, like plan changes.
