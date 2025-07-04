# Multisite Ultimate 101

Multisite Ultimate is a WordPress Multisite plugin that enables you to offer a WaaS or Websites as a Service to customers. Before we dive in and learn how Multisite Ultimate can help your business and customers there is some foundational knowledge that we need to acquire.

## The WordPress Multisite

Most of us are familiar with the stock-standard WordPress installation. You either create it via the control panel of your hosting provider or, for the brave, set up a new web server and database, download the core files and begin the installation process.

This works for millions of WordPress sites all over the world but from the perspective of an agency or hosting provider let’s discuss volumes for a minute.

Whilst it is a synch to create one WordPress site or even a hundred via an automated control panel problems soon start to show themselves when it falls to the management of these sites. Left unmanaged you’re a prime target for malware. To manage means an exercise of effort and resources and although there are external tools and plugins available to help streamline the management and administration of WordPress sites the fact that customers maintain administrative access means that these efforts could easily be defeated.

Within its core, WordPress provides a feature simply titled ‘Multisite’ which traces its origins back to 2010 at the launch of WordPress 3.0. Since then it has received a number of revisions aimed at introducing new features and tightening security.

In essence, a WordPress multisite can be thought of as this: A University maintains a single installation of WordPress but each faculty maintains their own WordPress site.

To break down this statement let’s take a look at some of the basic terminology present not only in Multisite Ultimate’s documentation but also across the WordPress community.

### The Network

In terms of WordPress, a multisite network is where a number of subsites can be managed from a single dashboard. Although creating a multisite network differs between hosting providers, the end result is usually a few additional directives in the wp-config.php file to let WordPress know that it is operating in this specific mode.

There are a number of distinct differences between a multisite network and a stand-alone WordPress installation which we shall briefly discuss.

#### Subdomain vs. Subdirectory

One of the most immediate decisions you will need to make is whether the multisite installation will operate with _subdirectories_ or _subdomains_. Multisite Ultimate works equally well with both choices but there are some architectural differences between the two configurations.

In _subdirectory_ configuration, network sites inherit a path based upon the main domain name. For example a network site labelled ‘site1’ will have it’s full URL as <http://domain.com/site1>. In _subdomain_ configuration, the network site will have its own _subdomain_ derived from the main domain name. Thus a site labelled ‘site1’ will have its full URL as <http://site1.domain.com/>.

Whilst both options are perfectly valid choices, the use of _subdomains_ does offer a number of advantages but also requires more thought and planning in its architecture.

In terms of DNS the use of _subdirectories_ presents a relatively simple challenge. As network sites are simply children of the parent path, only a single domain name entry needs to exist for the main domain name. For _subdomains_ the challenge is a little more complex requiring either a separate CNAME entry for each network site or a wildcard (*) entry in the DNS records.

A further area of consideration is that of SSL and the issuance and use of SSL certificates. In _subdirectory_ configuration a single domain certificate can be used as the network sites are simply paths of the main domain name. Thus a certificate for domain.com will adequately provide SSL for <https://domain.com/site1>, <https://domain.com/site2> and so on.

In _subdomain_ configuration the use of a wildcard SSL certificate is one of the most common options. This type of SSL certificate provides encryption for a domain and its _subdomains_. Therefore a wildcard SSL certificate will provide encryption for <https://site1.domain.com>, <https://site2.domain.com> and <https://domain.com> itself.

Although other options exist, these are often limited in scope and application and require additional configuration and consideration with regards to suitability.

#### Plugins and Themes

What WordPress giveth it taketh away as well, at least from the perspective of the customer. In a stand-alone WordPress installation if the site administrator installs a bad plugin or fails to keep their installation up to date the only victim and casualty of this act is themselves. However, a site administrator installing a bad plugin on a multisite installation creates a victim of every site installed in the network.

For this reason when configured as a multisite WordPress removes the capability from site administrators to install plugins and themes and instead moves this capability to a newly created network administrator or ‘super admin’ role. This privileged role can then decide whether to allow administrators of network sites to see or access the plugins menu in their dashboard and, if so, whether such permissions extend to _activating_ or _deactivating_ plugins.

To this extent the network administrator is responsible for installing plugins and themes into the network and delegates permissions to make use of these plugins and themes to network sites. Site administrators cannot install plugins and themes or access plugins and themes not assigned to their site.

#### Users and Administrators

In a WordPress Multisite, all network sites share the same database and therefore share the same users, roles and capabilities. The most apt way to think of it is that all users are members of the network and not a particular site.

Given this understanding it may be undesirable to allow users to be created and for this reason WordPress Multisite removes this capability from the site administrators and moves this capability to that of the network administrator. In turn the network administrator can delegate the necessary privileges to a site administrator to allow them to create user accounts for their own site.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-zg50L2qdJEphzPcgwlo_pqNiYDknE6wiJo4zQUlhQwBCtDeAef2_aTzBBMx76YvTweROsbUH4uvosFRitBj8kgatWzCi_C822oJPWr5bKpwLuoBvTIZ5M9O1nFxOepiav1FkRHNv)

Reiterating the statement above, although the user accounts appear to be related to the site they are in fact allocated to the network and therefore must be unique across the network. There may be instances where usernames are unavailable to be registered due to this reason.

Although not a foreign concept in enterprise systems, this single source of user registration and authentication is often a difficult concept to understand for people familiar with stand-alone WordPress installations where user administration is somewhat easier.

#### Media

Where network sites share a single database in a WordPress Multisite, they maintain separate paths on the filesystem for media files.

The standard WordPress location (wp-content/uploads) remains; however, its path is altered to reflect the network site’s unique ID. Consequently media files for a network site appear as wp-contents/uploads/site/[id].

#### Permalinks

We mentioned before that there are distinctive advantages of _subdomain_ over _subdirectory_ configuration and here it is: paths.

In a _subdirectory_ configuration, the main site (the first site created when the network is established) and network subsites must share the same path leading from the domain name. This has the potential for a great number of conflicts.

For posts, a mandatory /blog/ path is added to the main site to prevent clashes with network sites. This means that pretty permalinks such as ‘Post name’ will be presented as domain.name/blog/post-name/

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-QUlDfXAzHqImjngoE7UsJHa6DOl6XbQWV17LYthxpxDBy-k453GE5TlJVBA6-sOootX3Fsi34sHv5nSgi1kZmlUD1iMneztCc_HQvZmXpbZEdX2a1il8GJQqxUT8aVPgW5ikR5uG)

In a _subdomain_ configuration this action is not necessary because each network site benefits from complete domain separation and thus need not rely on a single path. They instead maintain their own distinct paths based on their _subdomain_.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-2wgqTosYo3GAa3jwaBlPADbFiMbZ-kKUTuQtk0gv5pvkj81UfxnxSyA8R-jD1EZmRMBLjJFoWZJOirTSe8K9OQKeoSYeDj602XBHRrZeRFABE1sw-JERzJzEzMd7FmvrM9G1L9MP)

#### Static Pages

In _subdirectory_ configuration the potential for naming conflicts extends to static pages as the main site and network sites share the same path.

To prevent this, WordPress provides a means to blacklist certain site names so that they do not conflict with the names of the first site. Typically the network administrator would enter the root paths of the main site’s pages.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-sqwI-k_-3krK0_ortenskDcdGrKpgOD6itvwDc2KEuGk1gZO-rqo_OF9yTqclSmQdrOBwsGPiiOc1oF6c0GMyNELJ-7gbyQNE81juSM3IvgTdWqhZ_UEVt06xJRu8Z8oyAKfLLz-)

In _subdomain_ configuration the possibility of naming conflicts are mitigated by the _subdomain_ as it is unique to the network site and not related in any way to the main site.

### Registration

Within the network settings of WordPress Multisite several new user registration options are available, allowing new and existing users to create sites.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-nbH9Ka0YhI7nvo3nnKvOr_FoI_FpdAy5hz-f199CW-PV1D-tNKGawhIK_YwlUvM19TjLnhVb6Ro6J0ZpI6s2TRUaHgyGPc4qQI06eQ2O2jeMb_SaktkKwPUw3BSyaNegZYSjXMVX)

As opposed to stand-alone WordPress installations, network sites do not maintain the familiar options to allow user registrations or assign those registrations to roles.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-7I21vdReE43e0Utj_KWdnuWA08ZXz7PX33rjSEjwf6T8NDiCBYbeP9GH0J36ekFNkmsXXLBYAWjQJo4vo_kWqL6hXJuFzd9RyA52zy13lT-mMsaK-JdVivUkd5SJF3UF13r2hj28)

When user accounts are created those accounts are generated at the network level. Thus instead of belonging to any one particular site they instead belong to the network. This has some distinctive advantages and disadvantages.

For example, assume your WordPress Multisite was in the business of news and information. You would establish the multisite and then create network sites for finance, technology, entertainment and other areas of interest whilst maintaining overall control of plugins and themes. Each network site would in turn have a far greater level of control over the look and feel and user experience of their network site than would custom post types or regular post categories.

To this extent when a user logs in they log in to the network and ultimately are logged in to each network site as well to provide a seamless experience. If your new site was subscription based this would be the ideal solution and outcome.

If, however, the intended nature and purpose of the multisite was to offer disparate network sites who have no relationship to each other it is almost always the case that external or additional plugins be required to manipulate the user roles.

### Domain and SSL

Let’s talk about a WordPress Multisite installation that almost escapes our attention - Wordpress.com. This is by far the most extensive example of a Wordpress multisite and demonstrates its extensive abilities to be customized and moulded to fulfil a purpose.

These days on the modern internet the use of SSL is almost mandatory and network administrators of WordPress multisites are soon presented with these challenges.

In _subdomain_ configuration sites are created based on the root domain name. Thus a site labelled ‘site1’ would be created as ‘site1.domain.com’. Making use of a wildcard SSL certificate, a network administrator can successfully address this challenge and provide SSL encryption abilities for the network.

WordPress Multisite contains a domain mapping function that allows for network sites to be associated with custom domain names or domain names different from the network’s root domain.

For network administrators this presents an additional layer of complexity both in domain name configuration as well as the issuance and maintenance of SSL certificates.

To this extent whilst WordPress Multisite provides a means to allow [www.anotherdomain.com](http://www.anotherdomain.com) to be mapped to ‘site1’ the network administrator is left with the challenge of externally managing the DNS entries and the implementation of SSL certificates.

## Multisite Ultimate

With the differences between a stand-alone WordPress installation and a Multisite installation understood, let's take a look at how Multisite Ultimate is the ultimate arsenal for providing Websites as a Service.

### Introduction

Multisite Ultimate is your Swiss Army knife when it comes to creating a Website as a Service (WaaS). Think of Wix.com, Squarespace, WordPress.com and then think of owning your own service.

Under the hood Multisite Ultimate makes use of WordPress Multisite but it does so in a way that not only solves the myriad of challenges network administrators face with multisite installations but enhances the capabilities allowing for a wide variety of use cases to be supported.

In the following sections we will take a look at some common use cases and considerations required to support those cases.

### Use Cases

#### Case 1: An Agency

Typically the core skills of an agency lie in the design of websites with aspects such as their hosting or marketing being listed as additional services.

For agencies Multisite Ultimate presents an incredible value proposition in its abilities to host and manage multiple websites on a single platform. Even more so for agencies who standardize their designs on particular themes such as GeneratePress, Astra, OceanWP or others can leverage Multisite Ultimate’s abilities to automatically activate these themes for each new site.

Similarly with the abundance of deals for agency pricing to common and popular plugins, the use of Multisite Ultimate allows agencies to leverage existing investments by providing a common platform from which plugins can be installed, maintained and made use of.

Most likely the use of a configuration would be desired and fortunately Multisite Ultimate makes it incredibly easy to facilitate domain mapping and SSL certificates with its integrations for a number of popular hosting providers as well as services such as Cloudflare and cPanel.

Thus by leveraging one of these providers or by placing Multisite Ultimate behind Cloudflare aspects such as the management of domains and SSL certificates become somewhat trivial.

Agencies who prefer to keep a tight control over the creation of sites will appreciate the ease at which they can create sites and associate sites with customers and plans through Multisite Ultimate’s streamlined interface.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-4cYCjjZwK0DZhrlY2NLHTL4waL99PANVZmSJ4AN5MXLTxb1pVF0aAtC4dWJP2hW1pD_v6hL8X7G80LRk-NuazRQDkIPCuhuRJgIMjA4DxuQzVjWEz1Ag2RKnkqwkvmSfcgy2PLrS)

Tight control over plugins and themes are maintained on a per-product basis through Multisite Ultimate’s intuitive interfaces allowing plugins and themes to be made available or hidden as well as their activation state when instantiated for a new site.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-VVpPcr8bvKd2qf9zPB_1SBiVpEYdtmskG_iO0tNCJOm2RXbU6kSC1czyLV1CaU5Mw2fWd-k2r1bnQV_yA4zOL6qnYKLWohnI-EDYhXcpxD_4n-rabGlxjQO8iyjtOgXhuDL5r2y7)

Themes provide similar functionality, allowing for particular themes to be activated or hidden on site creation.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-3CEl6U5CPdKatlXAcG5B9jJux_iWOYpUTI4crCgW15EFkhh4pODF7hYlMyzc3na8QAefadz1kcnY_T7Yn6ZyxbBMJdfRfa16IFZma_-u7NHTtMbLZwJ9f7JbqG1QTh0y1l9WWj5z)

Agencies will find peace of mind with Multisite Ultimate allowing them to do what they do best - design exceptional web sites.

#### Case 2: Niche Provider

There is an old saying which says, “do one thing and do it well”. For many specialists this means creating a product or service around a single core idea.

Perhaps you are an avid golfer promoting websites to clubs or you might be an avid esports gamer providing websites to clans. An individual promoting a booking service to restaurants perhaps?

For many reasons you would want to provide services based on a common framework and platform. It could be that you have designed or invested in bespoke plugins to provide the required functionality or it may be the case that industry best practices require some form of standardized approach to design.

One of Multisite Ultimate’s innovative features is the use of template sites. A template site is one where the theme has been installed and activated, necessary plugins installed and activated and sample posts or pages created. When a customer creates a new site based upon the template, the contents and settings of the template are copied to the newly created site.

For a provider of niche sites and services this provides an unparalleled advantage in the ability to instantly create a site ready to go with custom plugins and design. The customer need only provide the most minimal input to complete the service.

Depending on the requirements both _subdirectory_ or _subdomain_ configurations may suit, in which case the architecture choices would be between a simple SSL certificate for _subdirectories_ or a wildcard SSL certificate for _subdomains_.

#### Case 3: WordPress Web Hosting

There are a myriad of ways to host WordPress sites but rarely is it as simple as providing web space to a customer with a pre-installed version of WordPress. This is because a number of decisions and considerations need to come together to provide a meaningful service.

Multisite Ultimate excels in this area by providing a comprehensive turnkey solution for the hosting of WordPress sites. Included in the solution are the core mechanisms to provide subscription services, payment collection, checkout forms, discount vouchers and customer communications.

Much of the integral work required to correctly install, configure and maintain a WordPress Multisite is facilitated by Multisite Ultimate to the extent that network administrators need only consider aspects as it relates to their service or niche such as product tiers, pricing and service offers.

For developers wishing to integrate with Multisite Ultimate, the solution also offers a comprehensive RESTful API and Webhooks for event notification.

Without reliance on a myriad of external plugins and licenses, Multisite Ultimate provides a feature rich and comparable solution to that of Wix, Squarespace, WordPress.com and others.

### Architecture Considerations

Whilst not a comprehensive guide, the following items should serve as guidance to the correct selection of technologies to support a Multisite Ultimate installation.

#### Shared vs. Dedicated Hosting

Unfortunately not all hosting providers are equal and some practice extreme server densities. Low-cost providers typically generate revenue by maximizing server density. As such your Multisite Ultimate installation may only be one of several hundred sites on the same server.

Without appropriate safeguards in place from the provider, sites on a shared server experience the ‘noisy neighbour’ problem. That is, a site on the same server consuming that many resources that other sites have to compete for the remaining resources. Often this presents itself as sites that are slow or fail to respond in a timely manner.

As a provider of web hosting yourself the flow on effects will mean that your customers experience poor speeds, low page rank and high bounce rates often resulting in customer churn as they seek services elsewhere.

In short, cheap does not mean good.

Multisite Ultimate is known to work with a number of good hosting providers and integrates well with their environment to provide functions such as domain mapping and automatic SSL. These providers value performance and provide a higher grade service than shared hosting.

For a list of compatible providers and complete set-up instructions for each please check the documentation of Compatible Providers.

#### Performance Considerations

Multisite Ultimate is not a slow application, rather, it is remarkably fast. It does, however, perform only as good as the underlying application and infrastructure and can leverage only that which it has access to.

Consider this: You’re the network administrator of a Multisite Ultimate installation with 100 sites. Some of those sites are doing well and attract a number of website visitors each day.

This scenario would be different on a smaller scale of say one to five sites but before long problems of scale would be evident.

Left unattended, the single Multisite Ultimate site would be responsible for fulfilling the requests of all visitors to the sites. These requests could be for dynamic PHP pages or static assets such as stylesheets, javascript or media files. Whether one or a hundred sites, these tasks become repetitive, monotonous and wasteful. It is unnecessary to use CPU power and memory to process a PHP file when the output is the same static information for every request.

Similarly one request for a PHP or HTML page in turn generates multiple succeeding requests for scripts, stylesheets and image files. Those requests are targeted directly to your Multisite Ultimate server.

One could easily solve this problem by upgrading the server but it does not fix a secondary problem - geographic latencies. Only multiple servers in multiple locations could properly address this problem.

For this reason most network administrators make use of front-end caching solutions and content distribution networks (CDN) to fulfill the requests for static pages. Fulfilling these requests and serving assets before the request reaches the server saves processing resources, eliminates delays, avoids unnecessary upgrades and maximizes technology investments.

Multisite Ultimate includes a sophisticated Cloudflare add-on enabling network administrators to place their installations behind Cloudflare and make use of not only its caching capabilities but DNS hosting, SSL certificates and security mechanisms as well.

#### Backups

One could ask 50 people for advice on backups and receive 50 different opinions on backup strategies. The answer is, it depends.

What is not disputed is that backups are required and that it is almost inconceivable that these are not managed by the provider, specifically one that offers a managed service. Consequently customers will look to the network administrator to provide and manage this service. Who the network administrator looks to is an entirely different problem.

For the purposes of this section let us agree that a backup is a point-in-time copy of the system state at the time the backup was initiated. Simply put, whatever the state of the system is at the time of the backup that state is captured and locked away in the backup.

With this understanding the answer as to how to achieve the backups and what is best for your environment will largely depend on your requirements and the hosting provider’s ability to satisfy those requirements. However, in the order of most opinionated to least opinionated, the below options should provide some guidance.

#### Snapshots

Snapshots are the silver bullets to backups because they are easy, uncomplicated (until you want to restore) and ‘just work’. It does require some help from your provider though and mostly applies only if you have a VPS (Virtual Private Server) or similar. Several providers listed in our ‘Compatible Providers’ documentation offer backups requiring no further intervention or consideration by the network administrator.

Where traditional backups target files and databases, a snapshot targets the entire disk. This means not only is the site’s data captured in the snapshot but the operating system and configuration as well. For many this is a distinct advantage as a new system can be spawned nearly instantly from a snapshot and be brought into operation to replace an ailing instance. Similarly, the recovery process to retrieve files only requires attaching the snapshot image as a disk to an existing instance so that the files can be accessed and copied.

Snapshots may attract an additional cost with the hosting provider but it is an insurance policy against accidents.

#### External Scripts

There appears to be no shortage of external scripts and solutions to backup WordPress and MySQL resources and these would work well for Multisite Ultimate as it is a WordPress plugin making use of the WordPress filesystem and database. Thus a solution that backs up WordPress sites would adequately cover Multisite Ultimate’s needs.

We cannot recommend any one script over another but our general advice is to run several backup and restore tests to ensure that the results are desired and to ‘be sure to be sure’ by continuously evaluating the script and its functionality specifically where some form of differential backup strategy is applied.

It should be noted these scripts, whilst running, will increase system load which should be taken into account.

#### Plugins

There is almost no problem in WordPress that cannot be solved with a plugin and if managing external scripts is not your cup of java then perhaps a plugin is the next best option.

Whilst plugins vary in options and features they mostly perform the same function and that is to make a copy of the WordPress files and database contents. Thereafter functionalities differ as some plugins can ship the backups to external services such as Google Drive or Dropbox or to some sort of compatible object storage service such as S3, Wasabi or others. The more comprehensive plugins provide differential backups or some sort of strategy to backup only data that has been changed to save external storage costs.

In selecting your plugin, do take care to verify that it is multisite aware. Due to its nature of operation whilst the backup is running you can expect temporary load on the server until the process has been completed.

#### Domain and SSL

Much has been discussed already regarding domain names in multisite _subdomain_ mode. An almost universal solution for network administrators is to make use of wildcard DNS entries.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-GwkLVUQ9Wb.png)

This type of DNS entry will successfully resolve _subdomains_ such as ‘site1.domain.com’ and ‘site2.domain.com’ to an IP address of 1.2.3.4 thus supporting Multisite Ultimate and to a larger extent WordPress Multisite using _subdomain_ mode.

This may work perfectly well for HTTP because the target host is read from the HTTP headers but rarely is the web so simple these days where secure HTTPS transactions are almost mandatory.

Fortunately there are easy options for SSL certificates. In _subdirectory_ mode a regular domain certificate can be used. These are readily and freely available from hosting providers who might use the free LetsEncrypt service or another source. Otherwise these are commercially available from authorities if you are able to generate the certificate signing request.

For _subdomain_ mode the use of a wildcard SSL certificate will pair perfectly with a wildcard domain and allow the certificate to be authoritative for the root domain and all _subdomains_ without extraneous configuration.

However, it should be noted that wildcard SSL certificates may not work with services such as Cloudflare unless you are on an enterprise plan or set the entry to DNS only in which case all caching and optimization is bypassed.

Out-of-the-box Multisite Ultimate provides a solution to this problem demonstrating our extensive experience with the needs of WordPress multisites. Activating this simple add-on will have Multisite Ultimate make use of your Cloudflare credentials to automatically add DNS entries for network sites in Cloudflare and set their mode to ‘proxied’. In this manner each network subsite, when created, will have the full protection and benefits of Cloudflare including SSL.

Depending on the nature and purpose of your Multisite Ultimate installation there may be a need for customers to use their own domains. In this case the network administrator is charged with solving two problems. One, the hosting of the domain name and two, SSL certificates for the domain.

For many, the use of Cloudflare is an easy option. The customer need only place their domain on Cloudflare, point a CNAME to the root domain of Multisite Ultimate and map their domain in Multisite Ultimate to begin taking advantage of their custom domain name.

Outside of this, alternative solutions need to be sought which is why Multisite Ultimate recommends a list of Compatible Providers. This is because the process of setting up DNS and SSL can be a non-trivial process. However, with Multisite Ultimate’s integration with these providers the complexity is much removed and the procedure is automated.

#### Plugins

It is highly likely that you would need additional plugins to provide functionality to your customers or network sites. Do all plugins work with WordPress Multisite and Multisite Ultimate? Well, it depends.

Whilst most plugins are installable in a WordPress Multisite their activation and licensing varies from author to author.

The challenge lies in how licensing is applied with some plugins requiring licensing on a per-domain basis. This would mean that for some plugins the network administrator needs to manually activate the license for each plugin on each new site.

Therefore it might be best to check with the plugin author as to how their plugin would work with a WordPress Multisite and any special requirements or procedures required to license it.
