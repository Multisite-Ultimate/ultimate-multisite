# How to configure domain mapping (v1)

_**IMPORTANT NOTE: This article refers to Multisite Ultimate version 1.x. If you're using v2,**_ [_**see this article**_](1677127282-domain-mapping.html) _**.**_

A powerful feature of a premium network is the ability to offer your clients a chance to attach a top-level domain to their sites. After all, [_**joesbikeshop.com**_](http://joesbikeshop.com) sounds much more professional than [ _**joesbikeshop.yournetwork.com**_](http://joesbikeshop.yournetwork.com) _,_ right? That’s why Multisite Ultimate offers this feature baked-in, and you don't need to rely on other third-party plugins.

## **What’s domain mapping?**

As the name suggests, domain mapping is the ability offered by Multisite Ultimate to take in a request for a custom domain and map that request to the corresponding site in the network with that particular domain attached.

## **How to setup domain mapping on your Multisite Ultimate Network**

Domain mapping requires some setting up on your part to work. Thankfully, Multisite Ultimate automates your hard work so you can easily meet the requirements.

During Multisite Ultimate installation, on the _Settings_ part, you can check the _Enable Domain Mapping_ option.

![](https://support.delta.nextpress.co/rails/active_storage/blobs/redirect/eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaHBBZzBmIiwiZXhwIjpudWxsLCJwdXIiOiJibG9iX2lkIn19--d6cf9dec743cbebabf65e138bcbe50568e714107/DM%201.png)

Alternatively, you can also enable this option on **Multisite Ultimate > Settings > Domain Mapping and SSL**.

![](https://support.delta.nextpress.co/rails/active_storage/blobs/redirect/eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaHBBZzRmIiwiZXhwIjpudWxsLCJwdXIiOiJibG9iX2lkIn19--0183f6725100a93b99d98dd7727334f530897747/Captura%20de%20Pantalla%202023-10-09%20a%20la\(s\)%2016.51.56.png)

_**NOTE:** To have the custom domain mapping working correctly, it is important to make sure you have the `sunrise.php` file from your `wp-ultimo` directory copied to your `wp-content` directory, and to have added `define('SUNRISE', true);` to your `wp-config.php` file, as shown in the Multisite Ultimate Wizard Setup._

To make custom domains available to plans, check the option inside the specific plan editing page, on the _**Plan Settings**_ tab.

![](https://support.delta.nextpress.co/rails/active_storage/blobs/redirect/eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaHBBaElmIiwiZXhwIjpudWxsLCJwdXIiOiJibG9iX2lkIn19--bf608dbb5ef04318d6d5206b7e501dd3fb7ea13e/DM%208.png)

## **Making sure the domain DNS settings are properly configured**

For a mapping to work, you need to make sure the domain you are planning to map is pointing to your Network’s IP address. Note that you need the Network IP address - the IP address of the domain where Multisite Ultimate is installed - not the IP address of the custom domain you want to map. To search for the IP address of a specific domain, we suggest going to [Site24x7](https://www.site24x7.com/find-ip-address-of-web-site.html), for example.

To correctly map the domain, you need to add an **A RECORD** on your **DNS** configuration pointing to that **IP address**. DNS management varies greatly between different domain registrars, but there are plenty of tutorials online covering that if you search for “  _Creating A Record on XXXX_ ” where XXXX is your domain registrar (ex.: "  _Creating A Record on_  _GoDaddy_ ”).

If you find yourself having trouble getting this to work, **contact your domain registrar support** and they will be able to help you with this part.

If you plan to allow your clients to map their own domains, they will have to do the work on this part themselves. Point them towards their registrar support system if they find themselves unable to create the A Record.

## **Mapping a Custom Domain Name**

### **As a Super Admin**

When you are logged in as super admin on your network, you can add and manage custom domain names by going to your _**Sites**_ menu and clicking to access the site's option.

![](https://support.delta.nextpress.co/rails/active_storage/blobs/redirect/eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaHBBaE1mIiwiZXhwIjpudWxsLCJwdXIiOiJibG9iX2lkIn19--79f47ec095ea927dd5f080c0b807a064c2b65318/DM%205.png)

Go to the Aliases tab and click on _**Add New**._

![](https://support.delta.nextpress.co/rails/active_storage/blobs/redirect/eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaHBBaFFmIiwiZXhwIjpudWxsLCJwdXIiOiJibG9iX2lkIn19--f1137725ea0620492fc2698aa9312b9e1e577a3b/DM%206.png)

Add the domain name, mark it as active, and click on _**Add Alias**_.

![](https://support.delta.nextpress.co/rails/active_storage/blobs/redirect/eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaHBBaFlmIiwiZXhwIjpudWxsLCJwdXIiOiJibG9iX2lkIn19--12147d399f8df74f22c66a8b27b4318df0a52221/DM%207.png)

### **On Your Customer's Account Page**

On the right column of your customer's account page, there's a module where you can set the custom domain.

Just add the domain and click on _**Set Custom Domain**._

![](https://support.delta.nextpress.co/rails/active_storage/blobs/redirect/eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaHBBaGNmIiwiZXhwIjpudWxsLCJwdXIiOiJibG9iX2lkIn19--f46e16d3777edb402e1e1c759b956cf2742a231e/DM%209.png)
