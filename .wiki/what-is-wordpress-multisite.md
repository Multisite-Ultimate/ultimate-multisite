# What is WordPress Multisite?

Within its core, WordPress provides a feature called ‘Multisite’ which traces its origins back to 2010 at the launch of WordPress 3.0. Since then it has received a number of revisions aimed at introducing new features and tightening security.

In essence, a WordPress multisite can be thought of as this: A University maintains a single installation of WordPress but each faculty maintains their own WordPress site.

## 

## What Exactly is WordPress Multisite?

Multisite is a feature of WordPress that allows multiple sites to share a single WordPress installation. When multisite is activated, the original WordPress site is converted to support what is usually referred to as a **network of sites**.

This network shares the file system (meaning **plugins and themes are also shared**), the database, the WordPress core files, the wp-config.php, etc.

This means that WordPress, theme, and plugin updates need to be performed only once for all of your network sites as they share the same files on the filesystem.

This fact is one of the main advantages of multisite, as it allows you to grow the number of sites you manage while keeping the number of tasks you need to perform to maintain your customers’ sites the same.

## 

## Subdomain or Subdirectory?

There are two modes of running WordPress multisite – and you need to choose one when converting your regular WordPress installation into a multisite installation:

**Subdomain:** ex.: [site.domain.com](http://site.domain.com)

…or

**Subdirectory:** ex.: [yourdomain.com/site](http://yourdomain.com/site)

Each mode has advantages and disadvantages that you need to take into consideration when making this decision.

One thing is important to note, though: once you make your decision, changing your network from subdirectory to subdomain or vice-versa is really hard – especially if you already have a handful of sites created.

Before making that decision, here are a couple of points to keep in mind:

**Subdirectory Mode** is the easiest mode in terms of setup and maintenance. This happens because all the sites are just paths attached to the main domain (e.g. [yourdomain.com/subsite](http://yourdomain.com/subsite)). As a result, you only need **one SSL certificate** for the main domain and that will cover the entire network.

At the same time, due to its URL structure, Google and most other search engines will consider all subsites on your subdirectory-based network as one giant site. As a result, content added to subsites by your end-customers might affect the SEO performance of your landing site, for example. The level of impact is debatable and there is an argument to be made that having such an arrangement can even be beneficial for SEO performance.

**Subdomain Mode** is a bit more complex to set up, but its URL structure (e.g. [subsite.yournetwork.com](http://subsite.yournetwork.com)) is generally perceived to look “more professional”.

One of the main challenges in setting up subdomain mode is SSL coverage (HTTPS) for the entire network. It comes down to the fact that browsers consider subdomains to be isolated entities. As a result, you’ll need a different SSL certificate for each subdomain on your network, or a special kind of certificate called a **Wildcard SSL certificate**. In recent years, hosting providers and panels are upping up their game in terms of SSL provisioning and some offer wildcard certificates at the click of a button, closing the gap between the two modes in terms of complexity in setting it up.

In contrast to subdirectory mode, subsites on a subdomain-based network are considered by search engines as separate websites, which means that content present on one subsite does not interfere with the SEO performance of other subsites at all.

## The Super Admin

Single-site WordPress installations allow you to add an unlimited number of users and give those users different user roles with different permissions.

In WordPress Multisite, a new type of user gets unlocked: **the super admin** – and a new admin panel gets unlocked: **the network admin panel**.

As the name implies, the super admin has superpowers over the network, being capable of managing all of its subsites, plugins, themes, everything!

Once you convert your single-site WordPress installation into multisite, the original admin of the single site will be automatically upgraded to super admin.

Plugins and themes can only be installed or uninstalled from the network admin panel by super admins. Subsite admins can then choose to activate or deactivate those plugins or themes unless the super admin network activates a plugin, which forces it to be active for all subsites all the time.

_Note: as you can see, inviting someone to your network and granting them super admin status gives this user total control over your network. As an example, other super admins can even remove your super admin status, effectively locking you out of your own network admin panel. In order to allow Multisite Ultimate customers to have granular control over what additional super admins can do, we have an add-on called Support Agents. This add-on allows you to create yet another kind of user – an agent – with only the permissions they might need to perform their tasks on the network._

## What is shared among subsites and what is not

As we mentioned before, one of the key advantages of WordPress multisite is that all the subsites are sharing the same configurations, core files, themes, plugins, WordPress core files, etc.

There are, however, elements that are nicely scoped on a per-subsite basis.

\- For example, each subsite gets its own uploads folder. As a result, uploads made by users of one particular subsite cannot be accessed on another subsite.

\- Each subsite has its own dedicated admin panel and can activate or deactivate plugins or themes unless they were network active by a super admin.

\- Most database tables are created for each subsite, meaning that posts, comments, pages, settings, and more are scoped for each subsite.

## User management on WordPress Multisite

One delicate subject on WordPress multisite is user management. The WordPress user table is one of the few that is shared among all subsites.

This arrangement can generate some issues depending on what you’re planning to build with your network. The example below helps to illustrate the most pressing one.

Imagine the following scenario:

You create a WordPress multisite network and start to offer subsites for a monthly fee to people that want to have an e-commerce store.

You get your first paying customer – John. You create a site for John on your network, install all the necessary plugins, then create a user for John so he can manage his store.

Then along comes a second customer – Alice. You do the same thing for her and she now has a store on your network as well.

John and Alice are both your customers, but they don’t know each other. More importantly, if one of them visits the store website of the other, there’s no way to know that this store is being hosted on the same network of sites.

One day, John needs to buy a new pair of shoes and he finds the perfect ones in Alice’s store. When he tries to finish up the purchase, he gets an “email already in use” error message, which is bizarre as John is 100% sure this is the first time he has ever visited Alice’s website.

What happened here is that John’s user is shared across the entire network so when he tries to create an account to checkout on Alice’s site, WordPress will detect that a user with the same email address already exists and throw an error.

_Note: We realize how bad that can be depending on your use-case, so Multisite Ultimate has an option that bypasses the regular checks for an existing user, allowing multiple accounts to be created using the same email address. Each account is bound to a subsite, so the risk of collision is kept to a minimal. In the example above, John would not get an error message and would be able to buy those shoes without an issue. This option is called Enable Multiple Accounts, and can be activate on Multisite Ultimate → Settings → Login & Registration._

Even though the user table is shared, users can be added to and removed from subsites by the subsite admins or the super admin, and they can even have different user roles on different subsites.

## Performance considerations

WordPress multisite is really powerful when it comes to the number of sites it can support. This can be tested by the fact that [WordPress.com](https://WordPress.com), Edublogs, and Campuspress are all multisite-based services and each host thousands of sites.

While in theory there is no maximum number of sites you can host on a single WordPress multisite installation, in practice the number of sites you can satisfactorily run can vary widely depending on a number of different factors: how dynamic the sites are, which plugins are available to subsites, etc.

As a rule of thumb, the simpler your network is, the better. Favoring sites where the content is not really dynamic (which makes them great candidates for aggressive caching strategies) and keeping the plugin stack as light as possible (the lower the number of active plugins the better) can drastically increase the number of subsites you can host.

The best part is that since it’s all WordPress here, the same tools you already know and love for performance improvements will also work for a multisite network.

The main bottleneck for multisite is the database but if everything else is set up correctly, it can take a couple of thousand sites before you need to worry about it. Even then, there are solutions that can be progressively added at that point (like database sharding solutions, for example).
