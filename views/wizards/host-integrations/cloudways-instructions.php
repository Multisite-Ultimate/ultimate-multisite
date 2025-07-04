<?php
/**
 * Cloudways instructions view.
 *
 * @since 2.0.0
 */
?>
<article id="fullArticle">
	<h1 id="step-1-getting-a-cloudways-api-key" class="intercom-align-left" data-post-processed="true">Step 1: Getting a Cloudways API Key</h1>
	<p class="intercom-align-left">Follow the steps 1 and 2 of the official Cloudways tutorial to obtain an API key for your account (<a href="http://support.cloudways.com/how-to-use-the-cloudways-api/" rel="nofollow noopener noreferrer" target="_blank">Read the tutorial</a>). <b>Copy the API Key as we will need it in the following steps.</b></p>
	<h1 id="step-2-get-the-server-id" class="intercom-align-left" data-post-processed="true">Step 2: Get the Server ID</h1>
	<p class="intercom-align-left">The next piece of information we will need is the <b>server ID</b> of the server hosting your WordPress install on Cloudways. To discover the server ID, visit the <b>Server Management </b>screen of the server. The server ID will be present on the URL of that page after the “/server/” portion of it.</p>
	<div class="intercom-container intercom-align-left">
		<a href="https://s3.amazonaws.com/helpscout.net/docs/assets/6017c85715d41b7c717cdcf9/images/602125ca1f25b9041bebc76b/602125c8fb34b55df443e494-CSCslOPtZ-Capto_Capture-2018-04-20_14-38-46_.png" rel="nofollow noopener noreferrer" target="_blank"><img class="wu-w-full" src="//d33v4339jhl8k0.cloudfront.net/docs/assets/6017c85715d41b7c717cdcf9/images/602125ca1f25b9041bebc76b/602125c8fb34b55df443e494-CSCslOPtZ-Capto_Capture-2018-04-20_14-38-46_.png"></a>
	</div>
	<p class="intercom-align-center"><i>The URL takes the form of </i><i>https://platform.cloudways.com/server/</i><i></i><b><i>SERVER_ID_HERE</i></b><i>/access_detail</i></p>
	<h1 id="step-3-get-the-app-id" class="intercom-align-left" data-post-processed="true">Step 3: Get the App ID</h1>
	<p class="intercom-align-left">We’ll need to do a similar thing to obtain the App ID for your WordPress installation. Go to Application Management screen of your WordPress app and the App ID will be present on the URL, after the “/apps/” portion of it.</p>
	<div class="intercom-container intercom-align-left">
		<a href="https://s3.amazonaws.com/helpscout.net/docs/assets/6017c85715d41b7c717cdcf9/images/602125cb6867724dfc6f0a37/602125c8fb34b55df443e494-5FWGW2RK2-Capto_Capture-2018-04-20_14-56-24_.png" rel="nofollow noopener noreferrer" target="_blank"><img class="wu-w-full" src="//d33v4339jhl8k0.cloudfront.net/docs/assets/6017c85715d41b7c717cdcf9/images/602125cb6867724dfc6f0a37/602125c8fb34b55df443e494-5FWGW2RK2-Capto_Capture-2018-04-20_14-56-24_.png"></a>
	</div>
	<p class="intercom-align-center"><i>The same thing happens here: the URL takes the form of </i><i>https://platform.cloudways.com/apps/</i><i></i><b><i>YOUR_APP_ID_HERE</i></b><i>/access_detail</i></p>

	<h1 id="since-161--additional-step--extra-domains" class="intercom-align-left" data-post-processed="true">Additional Step – Extra Domains</h1>
	<p class="intercom-align-left">The Cloudways API is a bit strange in that it doesn’t offer a way to add or remove just one domain, only a way to update the whole domain list. That means that Multisite Ultimate <b>will replace all domains</b> you might have there with the list of mapped domains of the network every time a new domain is added.</p>
	<p class="intercom-align-left">If there are domains you want to keep on the list, use the <b>WU_CLOUDWAYS_EXTRA_DOMAINS</b> as demonstrated below, with a comma-separated list of the domains you wanna keep (this is useful if you need a wildcard setting, for example, that needs to be on that list at all times).</p>
	<pre class="wu-overflow-auto wu-p-4 wu-m-0 wu-mt-2 wu-rounded wu-content-center wu-bg-gray-800 wu-text-white wu-font-mono wu-border wu-border-solid wu-border-gray-300 wu-max-h-screen wu-overflow-y-auto">define('WU_CLOUDWAYS_EXTRA_DOMAINS', '*.yourdomain.com,extradomain1.com,extradomain2.com');</pre>
	<p class="intercom-align-left">Here’s how it should look on your wp-config.php (fake values used below):</p>
	<div class="intercom-container intercom-align-left">
		<a href="https://s3.amazonaws.com/helpscout.net/docs/assets/6017c85715d41b7c717cdcf9/images/602125ccfb34b55df443e495/602125c8fb34b55df443e494-meGSy675Z-Screen-Shot-2018-04-23-at-21.34.56.png" rel="nofollow noopener noreferrer" target="_blank"><img class="wu-w-full" src="//d33v4339jhl8k0.cloudfront.net/docs/assets/6017c85715d41b7c717cdcf9/images/602125ccfb34b55df443e495/602125c8fb34b55df443e494-meGSy675Z-Screen-Shot-2018-04-23-at-21.34.56.png"></a>
	</div>
	<p class="intercom-align-center"><i>You’re all set!</i></p>
	<p class="intercom-align-left">Now, every time a new domain is mapped in the network (via the Aliases tab by the network admin or via the custom domain meta-box on the user’s Account page) will be added to the Cloudways platform automatically.</p>
	<p class="intercom-align-left">The same is true for domain removals. Every time a domain is deleted from the network, that change will be communicated to your Cloudways account instantly!</p>
</article>
